<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once (_PS_MODULE_DIR_ . 'apirone/vendor/autoload.php');
require_once (_PS_MODULE_DIR_ . 'apirone/classes/FileLoggerWrapper.php');

use Apirone\API\Http\Request;
use Apirone\SDK\Model\Settings;
use Apirone\SDK\Invoice;
use Apirone\SDK\Service\Db as ApironeDb;
use Apirone\SDK\Service\Logger;
use Apirone\SDK\Service\Utils;

class Apirone extends PaymentModule
{
    public ?Closure $logger = null;
    public ?Settings $settings = null;

    public function __construct()
    {
        $this->name = 'apirone';
        $this->version = '2.0.0';
        $this->tab = 'payments_gateways';
        $this->author = 'apirone.com';
        $this->need_instance = 1;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Apirone Crypto Payments');
        $this->description = $this->l('Accept Crypto with PrestaShop');
        $this->confirmUninstall = $this->l('Are you sure you want to remove the module?');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];

        Request::userAgent('PrestaShop/' . _PS_VERSION_ . ' MCCP/' . $this->version);

        $this->settings = $this->getSettings();

        Logger::set(
            $this->logger = $this->logger_callback(
                $this->settings->debug ? FileLogger::INFO : FileLogger::ERROR));

        ApironeDb::adapter('mysql');
        ApironeDb::prefix(_DB_PREFIX_);
        ApironeDb::handler($this->db_callback());
    }

    public function install()
    {
        $this->warning = null;

        // Check cURL extension
        if (extension_loaded('curl') == false) {
            $this->warning = $this->l('You have to enable the cURL extension on your server to install this module.');
        }

        // Install module
        if (is_null($this->warning) && !parent::install()) {
            $this->warning = $this->l('An error occurred during the module installation process. The module is not installed.');
        }

        // Register hooks
        if (is_null($this->warning)) {
            $this->registerHooks();
        }

        // Add data table
        if (is_null($this->warning)) {
            $this->createApironeTable();
        }

        // Add orderStates
        if (is_null($this->warning) && !$this->addApironeOrderStates()) {
            $this->warning = $this->l('An error occurred while adding apirone order states.');
        }

        if ($this->warning !== null) {
            $this->_errors[] = $this->warning;
        }

        return is_null($this->warning);
    }

    public function uninstall()
    {
        Configuration::deleteByName('APIRONE_SETTINGS');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        $networks = $this->settings->networks;

        // Save settings if sent
        if (Tools::isSubmit('submitApironeSettings')) {
            $values = $this->getSettingsFormValues();

            $this->settings
                ->merchant(trim($values['merchant']))
                ->testCustomer(trim($values['testCustomer']))
                ->withFee(!!$values['withFee'])
                ->logo(!!$values['logo'])
                ->debug(!!$values['debug']);

            // Validate timeout
            $timeout = intval($values['timeout']);
            if ($timeout < 0) {
                $this->context->controller->errors[] = $this->l("'Payment timeout' must be a non-negative integer number");
            }
            else {
                $this->settings->timeout($timeout);
            }

            // Validate factor
            $factor = floatval($values['factor']);
            if ($factor <= 0) {
                $this->context->controller->errors[] = $this->l("'Payment adjustment factor' must be a positive floating point number");
            }
            else {
                $this->settings->factor($factor);
            }

            // Save processing fee plan
            $processingFee = $values['processingFee'];
            if ($this->settings->processingFee !== $processingFee) {
                if(!in_array($processingFee, ['percentage', 'fixed'])) {
                    $this->context->controller->errors[] = $this->l("'Processing fee plan' must be 'percentage' or 'fixed'");
                }
                else {
                    $this->settings->processingFee($processingFee);

                    foreach ($networks as $network) {
                        $network->policy($processingFee);
                    }
                    $errors = $this->settings->saveNetworks();

                    // Check for errors
                    foreach ($errors as $abbr => $error) {
                        $this->context->controller->errors[] = $networks[$abbr]->name . $this->l(' has error: ') . $error;
                    }
                }
            }

            // Show success message if no errors
            if (empty($this->context->controller->errors)) {
                Configuration::updateValue('APIRONE_SETTINGS', $this->settings->toJsonString());
                $message = $this->displayConfirmation($this->trans('Update successful', [], 'Admin.Notifications.Success'));
            }
        }
        // TODO: Save currencies if sent
        if (Tools::isSubmit('submitApironeCurrencies')) {
            $values = $this->getCurrenciesFormValues();
            $visible_coins = Tools::getValue('visible', []);
            if (count($values)) {
                $coins = [];
                foreach ($networks as $abbr => $network) {
                    $address = array_key_exists($abbr, $values)
                        ? trim($values[$abbr])
                        : null;
                    $network->address($address);
                    if (!$address) {
                        continue;
                    }
                    if (!count($tokens = $network->tokens)) {
                        $coins[] = $abbr;
                        continue;
                    }
                    foreach (array_merge([$abbr], array_keys($network->tokens)) as $abbr) {
                        if (!empty($visible_coins) && array_key_exists($abbr, $visible_coins) && $visible_coins[$abbr]) {
                            $coins[] = $abbr;
                        }
                    }
                }
                $this->settings->coins($coins);

                $errors = $this->settings->saveNetworks();

                // Check for errors
                foreach ($errors as $abbr => $error) {
                    $this->context->controller->errors[] = $networks[$abbr]->name . $this->l(' has error: ') . $error;
                }

                if (empty($this->context->controller->errors)) {
                    Configuration::updateValue('APIRONE_SETTINGS', $this->settings->toJsonString());
                    $message = $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
                    $message = $this->displayConfirmation($this->trans('Update successful', [], 'Admin.Notifications.Success'));
                }
            }
        }
        if (Tools::isSubmit('submitApironeCheckUpdate')) {
            $latest = $this->getLatestVersion();

            if (!$latest) {
                $message = $this->displayError($this->trans('Can\'t obtain latest version information. Please, try later.', [], 'apirone'));
            }
            elseif (version_compare($this->version, $latest, 'eq')) {
                $message = $this->displayConfirmation($this->trans('You are using latest plugin version.', [], 'apirone'));
            }
            elseif (version_compare($this->version, $latest, 'lt')) {
                $page = sprintf('<a href="https://github.com/apirone/prestashop/releases/latest" target="_blank">%s</a>', $this->trans('release page', [], 'apirone'));
                $message = $this->displayWarning(sprintf($this->trans('Latest plugin version %s is available. Go to %s.', [], 'apirone'), $latest , $page));
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);
        $this->context->smarty->assign('message', $message);
        $this->context->smarty->assign('settings', $this->renderSettingsForm());
        $this->context->smarty->assign('currencies', $this->renderCurrenciesForm());
        $this->context->smarty->assign('apirone_account', $this->settings->account);
        $this->context->smarty->assign('plugin_version', $this->version);
        $this->context->smarty->assign('ps_version', _PS_VERSION_);
        $this->context->smarty->assign('php_version', phpversion());
        $this->context->smarty->assign('releases_page', 'https://github.com/apirone/prestashop/releases');

        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/settings.tpl');
    }

    /**
     * Generate settings form
     */
    protected function renderSettingsForm()
    {
        $form_fields = [
            'form' => [
                'legend' => [
                    'title' => 'Settings',
                    'icon' => 'icon-cogs',
                ],
                'class' => 'class',
                'input' => [
                    [
                        'type' => 'text',
                        'name' => 'merchant',
                        'label' => $this->l('Merchant name'),
                        'hint' => $this->l('Show Merchant name on the plugin invoice page. If this field is empty, it will not be displayed for a customer.'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'testCustomer',
                        'label' => $this->l('Test currency customer'),
                        'hint' => $this->l('Enter an email of the customer to whom the test currencies will be shown.'),
                    ],
                    [
                        'type' => 'number',
                        'min' => '0',
                        'name' => 'timeout',
                        'label' => $this->l('Payment timeout'),
                        'hint' => $this->l('The period during which a customer shall pay. Set value in seconds. Default value is 1800 (30 minutes).'),
                        'required' => true,
                    ],
                    [
                        'type' => 'select',
                        'name' => 'processingFee',
                        'label' => $this->l('Processing fee plan'),
                        'options' => [
                            'query' => [
                                ['key' => 'percentage', 'name' => 'Percentage'],
                                ['key' => 'fixed', 'name' => 'Fixed'],
                            ],
                            'id' => 'key',
                            'name' => 'name'
                        ],
                    ],
                    [
                        'type' => 'number',
                        'min' => '0.01',
                        'step' => '0.01',
                        'name' => 'factor',
                        'label' => $this->l('Payment adjustment factor'),
                        'hint' => $this->l('If you want to add/subtract percent to/from the payment amount, use the following  price adjustment factor multiplied by the amount. For example: 100% * 0.99 = 99% | 100% * 1.01 = 101%'),
                        'required' => true,
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'withFee',
                        'label' => $this->l('Include fees'),
                        'is_bool' => true,
                        'hint' => $this->l('Adds service and network fees to total. Final amount per coin is shown in selector.'), // TODO
                        'values' => [
                            [
                                'id' => 'with_fee_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'with_fee_off',
                                'value' => false,
                                'label' => $this->l('No')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'logo',
                        'label' => $this->l('Apirone Logo'),
                        'is_bool' => true,
                        'hint' => $this->l('Show the Apirone logo on the invoice page.'),
                        'values' => [
                            [
                                'id' => 'logo_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'logo_off',
                                'value' => false,
                                'label' => $this->l('No')
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'debug',
                        'label' => $this->l('Debug mode'),
                        'is_bool' => true,
                        'hint' => $this->l('Write debug information into the log file.'),
                        'values' => [
                            [
                                'id' => 'debug_on',
                                'value' => true,
                                'label' => $this->l('Yes')
                            ],
                            [
                                'id' => 'debug_off',
                                'value' => false,
                                'label' => $this->l('No')
                            ],
                        ],
                    ],
                ],
                'submit' => [
                    'name' => 'submitApironeSettings',
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitApironeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getSettingsFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$form_fields]);
    }

    protected function renderCurrencyIcon($icon_name): string
    {
        // TODO: path 'views/img/currencies/' not works, PS inserts 'adm/' path segment, two variants available
        // $PATH = '/prestashop/modules/apirone/views/img/currencies/';
        $PATH = 'modules/apirone/views/img/currencies/';
        return '<img src="'.$PATH.$icon_name.'.svg" width="18" onerror="this.onerror=null;this.src=\''.$PATH.'placeholder.svg\'">';
    }

    /**
     * @return array Array of networks DTO with keys of networks abbreviations.
     * Each result array item is DTO with icon, name, tooltip, address and tokens array.
     * Each token array item is DTO with icon, visibility state and tooltip.
     */
    protected function getNetworksViewModel(): array
    {
        $coins = $this->settings->coins;

        $TESTNET_WARNING = $this->l(' WARNING: Test currency. Use this currency for testing purposes only! It is displayed on the front end for `Test currency customer`! ');

        foreach ($this->settings->networks as $network) {
            $network_abbr = $network->network;
            $name = $network->name;
            $address = $network->address;
            $testnet = $network->isTestnet();
            $tokens = $network->tokens;
            $has_tokens = count($tokens) > 0;

            $networks_dto[$network_abbr] = $network_dto = new \stdClass();

            $network_dto->icon = $this->renderCurrencyIcon($network_abbr);
            $network_dto->name = $name. ($has_tokens ? ' '.$this->l('Blockchain') : '');
            $network_dto->address = $address;
            $network_dto->tooltip = $this->l($address ? 'Remove address to deactivate currency.' : 'Enter valid address to activate currency.');
            $network_dto->testnet = $testnet;
            $network_dto->error = $network->error;

            if ($testnet) {
                $network_dto->test_tooltip = $TESTNET_WARNING;
            }
            if (!$has_tokens) {
                continue;
            }
            $tokens_dto = [];

            $tokens_dto[$network_abbr] = $token_dto = new \stdClass();

            $token_dto->checkbox_id = 'state_'.$network_abbr;
            $token_dto->icon = $this->renderCurrencyIcon($network_abbr);
            $token_dto->name = strtoupper($name);
            $token_dto->state = $address && is_array($coins) && in_array($network_abbr, $coins);
            $token_dto->tooltip = $this->l('Show/hide from currency selector');

            foreach ($tokens as $abbr => $token) {
                $tokens_dto[$abbr] = $token_dto = new \stdClass();

                $token_dto->checkbox_id = 'state_'.$network_abbr.'_'.$token->token;
                $token_dto->icon = $this->renderCurrencyIcon($token->token);
                $token_dto->name = strtoupper($token->alias);
                $token_dto->state = $address && is_array($coins) && in_array($abbr, $coins);
                $token_dto->tooltip = $this->l('Show/hide from currency selector');
            }
            $network_dto->tokens = $tokens_dto;
        }
        return $networks_dto;
    }

    protected function renderNetworkCoins ($coins)
    {
        $this->context->smarty->assign('coins', $coins);
        return $this->context->smarty->fetch($this->local_path.'views/templates/admin/token_checkboxes.tpl');
    }

    /**
     * Generate Currencies form
     */
    protected function renderCurrenciesForm()
    {
        $form_data = [];
        foreach ($this->getNetworksViewModel() as $abbr => $network_dto) {
            $item = [
                'type' => 'text',
                'label' => $network_dto->name,
                'name' => $abbr,
                'hint' => ($network_dto->test_tooltip ?? ''). $network_dto->tooltip,
                'values' => $abbr,
                'prefix' => $network_dto->icon,
            ];
            if ($coins = $network_dto->tokens) {
                $item['desc'] = $this->renderNetworkCoins($coins);
            }
            $form_data[] = $item;
        }

        if (empty($form_data)) {
            $this->context->controller->errors[] = 'Can`t get currencies list from apirone gateway. Please, try later.';
        }
        $form_fields = [
            'form' => [
                'legend' => [
                    'title' => 'Currencies',
                    'icon' => 'icon-bitcoin',
                ],
                'class' => 'class',
                'input' => $form_data,
                'submit' => [
                    'name' => 'submitApironeCurrencies',
                    'title' => $this->trans('Save', [], 'Admin.Actions'),
                ],
            ],
        ];

        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitApironeModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getCurrenciesFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$form_fields]);
    }

    /**
     * Set values for the settings inputs.
     */
    protected function getSettingsFormValues()
    {
        $values = [];

        $values['merchant'] = pSQL(Tools::getValue('merchant', $this->settings->merchant));
        $values['testCustomer'] = pSQL(Tools::getValue('testCustomer', $this->settings->testCustomer));
        $values['timeout'] = (int) pSQL(Tools::getValue('timeout', $this->settings->timeout));
        $values['processingFee'] = pSQL(Tools::getValue('processingFee', $this->settings->processingFee));
        $values['factor'] = (float) pSQL(Tools::getValue('factor', $this->settings->factor));
        $values['withFee'] = pSQL(Tools::getValue('withFee', $this->settings->withFee));
        $values['logo'] = pSQL(Tools::getValue('logo', $this->settings->logo));
        $values['debug'] = pSQL(Tools::getValue('debug', $this->settings->debug));

        return $values;
    }

    /**
     * Set values for the currencies inputs.
     */
    protected function getCurrenciesFormValues()
    {
        $values = [];
        foreach ($this->settings->networks as $abbr => $network) {
            $values[$abbr] = pSQL(Tools::getValue($abbr, $network->address));
        }
        return $values;
    }

    /**
     * Return payment options available for PS 1.7+
     *
     * @param array Hook parameters
     *
     * @return array|null
     */
    public function hookPaymentOptions($params)
    {
        // TODO:

        if (!$this->active) {
            return;
        }
        $coins = [];
        $cart = $params['cart'];
        $fiat = $this->getCartCurrency($cart);
        if (!$fiat) {
            return;
        }
        foreach ($this->getAvailableCryptos() as $currency) {
            try {
                // TODO: replace by Utils::estimate() or getAvailableCryptos() results
                // $amount = Utils::fiat2crypto($cart->getCartTotalPrice(), $fiat['iso_code'], $currency);
                // $currency->amount = Utils::humanizeAmount(Utils::cur2min($amount, $currency->unitsFactor), $currency);
                $coins[] = $currency;
            }
            catch(Exception $e) {
                Logger::error($e->getMessage());
            }
        }

        if (empty($coins)) {
            return;
        }

        $action = $this->context->link->getModuleLink($this->name, 'payment', [], true);
        $this->context->smarty->assign(['action' => $action, 'coins' => $coins]);

        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option
            ->setCallToActionText($this->l('Pay with crypto'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setForm($this->fetch('module:apirone/views/templates/hook/currencyselector.tpl'));

        return [$option];
    }

    public function hookDisplayAdminOrderMain($params)
    {
        // TODO: Invoice::getByOrder($order->id_cart) called in getOrderInvoicesByOrderId why duplicate here?

        if (empty($this->getOrderInvoicesByOrderId($params['id_order']))) {
            return;
        }

        $order = new Order($params['id_order']);
        $listItems = [];
        $invoices = Invoice::getByOrder($order->id_cart);
        foreach ($invoices as $invoice) {
            $details = $invoice->details;
            $currency = $this->settings->currency($details->currency);

            $itemInvoice = new stdClass();
            $itemInvoice->date = date($this->context->language->date_format_full, strtotime($details->created . 'Z'));
            $itemInvoice->invoice = $details->invoice;
            $itemInvoice->address = $details->address;
            $itemInvoice->addressUrl = Utils::getAddressLink($currency, $details->address);
            // $itemInvoice->amount = Utils::humanizeAmount($details->amount, $currency) . ' ' . strtoupper($details->currency);
            // TODO: check what in $details->amount
            $itemInvoice->amount = $details->amount . ' ' . strtoupper($details->currency);
            $itemInvoice->status = $details->status;
            $itemInvoice->history = [];

            foreach ($details->history as $item) {
                $itemHistory = new stdClass();
                $itemHistory->date = date($this->context->language->date_format_full, strtotime($item->date . 'Z'));
                $itemHistory->status = $item->status;
                if ($item->amount !== null) {
                    // TODO: check what in $details->amount
                    // $itemHistory->amount = Utils::humanizeAmount($item->amount, $currency);
                    $itemHistory->amount = $item->amount;
                    $itemHistory->txid = Utils::getTransactionLink($currency, $item->txid);
                }
                $itemInvoice->history[] = $itemHistory;
            }


            $listItems[] = $itemInvoice;
        }
        \Context::getContext()->smarty->assign('invoices', $listItems);

        return \Context::getContext()->smarty->fetch('module:apirone/views/templates/hook/orderInvoicesDetails.tpl');

    }

    public function hookActionAdminControllerSetMedia(array $params)
    {
        $action = Tools::getValue('action');

        if ($action === 'vieworder' || $action === 'addorder') {
            return;
        }

        $this->context->controller->addCSS(__PS_BASE_URI__ . '/modules/' . $this->name . '/views/css/back.css');
    }

    public function getCartCurrency($cart)
    {
        $cart_currency = new Currency($cart->id_currency);
        $shop_currencies = $this->getCurrency($cart->id_currency);
        if (is_array($shop_currencies)) {
            foreach ($shop_currencies as $currency) {
                if ($cart_currency->id == $currency['id_currency']) {
                    return $currency;
                }
            }
        }
        return false;
    }

    public function getAvailableCryptos(): array
    {
        // TODO

        // Do not show payment method for invalid account
        // TODO: replace with estimate from coins
        // try {
        //     Account::init($this->settings->account)->balance();
        // }
        // catch (Exception $e) {
        //     $this->log('error', $this->l('Can`t get available cryptos for currency selector: ') . $e->getMessage());
        //     return [];
        // }

        $coins = [];
        $networks = $this->settings->networks();
        $testCustomer = $this->settings->testCustomer;

        foreach ($networks as $network) {
            if ($network->getAddress() !== null && !$network->hasError()) {
                if ($network->isTestnet()) {
                    if ($testCustomer == $this->context->customer->email || $testCustomer == '*') {
                        $coins[] = $network;
                    }
                }
                else {
                    $tokens = $network->getTokens($this->settings->currencies);
                    if ($tokens) {
                        $tokens = array_merge([$network], $tokens);
                        foreach ($tokens as $token) {
                            if ($this->settings->getMeta($token->abbr) == 'on') {
                                $coins[] = $token;
                            }
                        }
                    }
                    else {
                        $coins[] = $network;
                    }
                }
            }
        }
        return $coins;
    }

    public function getCrypto()
    {
        $coin = Tools::getValue('coin');

        $cryptos = $this->getAvailableCryptos();
        foreach ($cryptos as $crypto ) {
            if($crypto->abbr == $coin) {
                return $crypto;
            }
        }

        return false;
    }

    protected function getOrderInvoicesByOrderId($id)
    {
        $invoices = [];
        $order = new Order($id);
        if (Validate::isLoadedObject($order)) {
            $invoices = Invoice::getByOrder($order->id_cart);
        }

        return $invoices;
    }

    protected function getSettings(): Settings
    {
        $json = Configuration::get('APIRONE_SETTINGS');
        if (!$json) {
            return $this->createSettings();
        }
        $settings = Settings::fromJson($json);

        if (!($settings->account && $settings->transferKey)) {
            return $this->createSettings();
        }
        if (empty((array)$settings->meta) || property_exists($settings, 'currencies')) {
            return $this->updateSettings($settings);
        }
        return $settings;
    }

    private function createSettings()
    {
        $settings = Settings::init()
            ->createAccount()
            ->processingFee('percentage')
            ->timeout(1800)
            ->factor(1)
            ->logo(true);
        Configuration::updateValue('APIRONE_SETTINGS', $settings->toJsonString());
        return $settings;
    }

    private function updateSettings($settings)
    {
        // TODO: check if props is got from JSON root
        $settings
            ->merchant($settings->merchant)
            ->timeout($settings->timeout)
            ->factor($settings->factor)
            ->logo($settings->logo)
            ->debug($settings->debug);

        // TODO: check if extra is got from JSON root
        if ($extra = $settings->extra) {
            foreach((array)$extra as $key => $val) {
                if ($val) {
                    $settings->meta[$key] = $val;
                }
            }
        }
        if (!$settings->processingFee) {
            $settings->processingFee('percentage');
        }
        if (property_exists($settings, 'currencies') && !$settings->coins) {
            $coins = [];
            foreach ($settings->networks as $network) {
                if (!$network->address) {
                    continue;
                }
                // address stored for currency
                $coins[] = $network->abbr;

                if (!count($tokens = $network->tokens)) {
                    // currency has no tokens
                    continue;
                }
                // currency has tokens, add all as visible by default
                foreach ($tokens as $token) {
                    $coins[] = $token->abbr;
                }
            }
            $settings->coins($coins);
        }
        Configuration::updateValue('APIRONE_SETTINGS', $settings->toJsonString());
        return $settings;
    }

    private function createApironeTable()
    {
        if (!ApironeDb::install()) {
            $this->warning = 'Can\'t create apirone table.';
            $this->log('error', $this->warning);
            return false;
        }
        return true;
    }

    private function registerHooks()
    {
        $errRedistredHooks = [];
        foreach ($this->getHooksList() as $hook) {

            if (!$this->registerHook($hook)) {
                $errRedistredHooks[] = $hook;
            }
        }
        if (!empty($errRedistredHooks)) {
            $this->warning = 'Failed to regisrer hooks: ' . implode(', ', $errRedistredHooks);
            $this->log('error', $this->warning);

            return false;
        }

        return true;
    }

    private function getHooksList()
    {
        return [
            'actionAdminControllerSetMedia',
            'displayAdminOrderMain',
            'paymentOptions',
        ];
    }

    public function addApironeOrderStates()
    {
        try {
            $this->createApironeOrderState('accepted');
            $this->createApironeOrderState('completed');
        }
        catch(Exception $e) {
            $this->log('error', $e->getMessage());
            $this->warning = $e->getMessage();

            return false;
        }

        return true;
    }

    private function createApironeOrderState($name)
    {
        $status = 'APIRONE_OC_PAYMENT_' . strtoupper($name);

        $stateId = Configuration::get($status);
        $orderState = ($stateId) ? new OrderState((int) $stateId) : new OrderState();

        $orderState->name = [];
        $orderState->module_name = $this->name;
        $orderState->color = ($name == 'completed') ? '#5D8AB9' : '#AEC4DC';
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = false;
        $orderState->invoice = $orderState->send_email = $orderState->paid = ($name == 'completed') ? true: false ;
        $orderState->template = ($orderState->send_email) ? 'payment' : '';

        foreach (Language::getLanguages() as $language) {
            $orderState->name[$language['id_lang']] = 'Payment ' . $name;
        }
        if ($orderState->save()) {
            $source = _PS_MODULE_DIR_ . 'apirone/logo.png';
            $destination = _PS_ROOT_DIR_ . '/img/os/' . (int) $orderState->id . '.gif';
            copy($source, $destination);
        }

        Configuration::updateValue($status, (int) $orderState->id);
    }

    public function log($level, $message, $context = [])
    {
        if($this->logger) {
            call_user_func_array($this->logger, [$level, $message, $context]);
        }
    }

    public static function logFilename() {
        return '/var/logs/' . date('Ymd') . '_apirone.log';
    }

    public function logger_callback($log_level)
    {
        return function ($level, $message, $context = []) use ($log_level) {
            $logger = new FileLoggerWrapper($log_level);
            $logger->log($level, $message, $context);
        };
    }

    public static function db_callback()
    {
        return static function($query) {
            $db = Db::getInstance();

            return preg_match('/select/i', $query)
                ? $db->executeS($query)
                : $db->execute($query);
        };
    }

    public function redirectWithNotice()
    {
        $notifications = json_encode([
            'error' => $this->context->controller->errors,
            'warning' => $this->context->controller->warning,
            'success' => $this->context->controller->success,
            'info' => $this->context->controller->info,
        ]);
        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION['notifications'] = $notifications;
        }
        else {
            setcookie('notifications', $notifications);
        }
        return call_user_func_array(['Tools', 'redirect'], func_get_args());
    }

    public function getLatestVersion() {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL => 'https://api.github.com/repos/apirone/prestashop/tags',
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_USERAGENT => 'apirone-prestashop-module',
        ));

        $response = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        switch ($http_status){
            case 200:
                $tags = json_decode($response);
                return $tags[0]->name;
            case 400:
            default:
                return false;
        }

    }

    public function apironePaymentProcess(Invoice $invoice)
    {
        if (!in_array($invoice->status, ['paid', 'overpaid', 'completed'], true)) {
            return;
        }

        $cart = new Cart($invoice->order);
        $order_status = (int) Configuration::get('APIRONE_OC_PAYMENT_ACCEPTED');

        // Create order
        if (!$cart->orderExists() && in_array($invoice->status, ['paid', 'overpaid','expired'], true)) {
            $order_status = (int) Configuration::get('APIRONE_OC_PAYMENT_ACCEPTED');
            try {
                $this->validateOrder(
                    (int) $cart->id,
                    (int) $order_status,
                    $cart->getOrderTotal(),
                    $this->displayName,
                    null,
                    [],
                    (int) $cart->id_currency,
                    false,
                    $cart->secure_key
                );
            }
            catch (Exception $e) {
                $this->log('error', 'Can\'t create an order.', [$e->getMessage()]);
                $message = 'Can\'t create an order.';
                Utils::sendJson($message, 400);
            }

            $order = Order::getByCartId($cart->id);
            $invoice->setMeta('order_status', (int) $order->getCurrentState());

            return;
        }

        // Update
        if ($cart->orderExists() && $invoice->status == 'completed') {
            $order = Order::getByCartId($cart->id);

            $current_status = (int) $order->getCurrentState();
            // TODO: check meta of invoice
            if ($invoice->order_status !== $current_status) {
                $this->log('info', 'The invoice order status does not match the current order status. Order ref: ' . $order->reference);

                return;
            }
            $new_status = (int) Configuration::get('APIRONE_OC_PAYMENT_COMPLETED');

            // Prevent duplicate state entry
            if ($current_status !== $new_status
                && false === (bool) $order->hasBeenShipped()
                && false === (bool) $order->hasBeenDelivered()
            ) {
                $orderHistory = new OrderHistory();
                $orderHistory->id_order = $order->id;
                $orderHistory->changeIdOrderState(
                    $new_status,
                    $order->id
                );
                $orderHistory->add();
                $invoice->setMeta('order_status', $new_status);
            }
        }
    }
}

function pa($mixed, $title = '')
{
    $title .= ($title) ? '<br/>' : '';
    echo '<pre>' . $title;
    print_r($mixed);
    echo '</pre>';
}
