<?php

/**
 * Package: Prestashop Apirone Payment gateway
 *
 * Another header line 1
 * Another header line 2
 *
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once (_PS_MODULE_DIR_ . 'apirone/vendor/autoload.php');

use Apirone\API\Log\LoggerWrapper;
use Apirone\SDK\Model\Settings;
use Apirone\SDK\Invoice;
use Apirone\SDK\Service\InvoiceDb;
use Apirone\SDK\Service\InvoiceQuery;
use Apirone\SDK\Service\Utils;
class Apirone extends PaymentModule
{

    public ?Settings $settings = null;

    public ?Closure $logger = null;

    public function __construct()
    {
        $this->name = 'apirone';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';
        $this->author = 'apirone.com';
        $this->need_instance = 1;        
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Apirone Crypto Payments');
        $this->description = $this->l('Accept Crypto with Prestashop');
        $this->confirmUninstall = $this->l('Are you sure you want to remove the module?');
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        
        $this->settings = $this->getSettings();
        $this->logger = $this->logger_callback($this->settings->getDebug() ? FileLogger::DEBUG : FileLogger::INFO);
        
        Invoice::setLogger($this->logger);
        Invoice::db(static::db_callback(), _DB_PREFIX_);
        Invoice::settings($this->settings);
    }

    public function install()
    {
        $this->warning = null;

        // Check cURL extention
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

        // Add datatable
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
        $message = '';
        // Save settings if sent
        if (Tools::isSubmit('submitApironeSettings')) {
            $errors = [];
            $values = $this->getSettingsFormValues();

            $this->settings->setMerchant($values['merchant']);
            $this->settings->setExtra('testCustomer', $values['testCustomer']);
            $this->settings->setBacklink($values['backlink']);
            $this->settings->setLogo($values['logo']);
            $this->settings->setDebug($values['debug']);            

            // Validate timeout
            if ($values['timeout'] == 0) {
                $this->context->controller->errors[] = $this->l("'Payment timeout' must be positive integer");
            }
            else {
                $this->settings->setTimeout($values['timeout']);
            }

            // Validate factor
            if ($values['factor'] == 0) {
                $this->context->controller->errors[] = $this->l("'Price adjustment factor' must be positive float");
            }
            else {
                $this->settings->setFactor($values['factor']);
            }
            
            if (empty($this->context->controller->errors)) {
                Configuration::updateValue('APIRONE_SETTINGS', $this->settings->toJsonString());
                $message = $this->displayConfirmation($this->trans('Update successful', [], 'Admin.Notifications.Success'));
            }
        }
        // Save currencies if sent
        if (Tools::isSubmit('submitApironeCurrencies')) {
            $values = $this->getCurrenciesFormValues();

            foreach ($this->settings->getCurrencies() as $item) {
                $this->settings->getCurrency($item->abbr)->setAddress($values[$item->abbr])->setPolicy('percentage');
            }

            $this->settings->saveCurrencies();

            foreach ($this->settings->getCurrencies() as $item) {
                if ($item->hasError()) {
                    $this->context->controller->errors[] = $item->name . ' has error: ' . $item->getError();
                }
            }
            
            if (empty($this->context->controller->errors)) {
                Configuration::updateValue('APIRONE_SETTINGS', $this->settings->toJsonString());
                $message = $this->displayConfirmation($this->trans('Settings updated', [], 'Admin.Global'));
                $message = $this->displayConfirmation($this->trans('Update successful', [], 'Admin.Notifications.Success'));
            }
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        return $message . $this->renderSettingsForm() . $this->renderCurrenciesForm();
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
                        'label' => $this->l('Merchant'),
                        'hint' => $this->l('Shows Merchant name in the payment order. If this field is blank then "Invoice invoice_id" will be shown as title.'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'timeout',
                        'label' => $this->l('Payment timeout'),
                        'hint' => $this->l('The period during which a customer shall pay. Set value in seconds. Default value is 1800 (30 minutes)'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'testCustomer',
                        'label' => $this->l('Test currency customer'),
                        'hint' => $this->l('Enter an email of the registered customer to whom the test currencies will be shown.'),
                    ],
                    [
                        'type' => 'text',
                        'name' => 'factor',
                        'label' => $this->l('Price adjustment factor'),
                        'hint' => $this->l('If you want to add/substract percent to/from the payment amount, use the following  price adjustment factor multiplied by the amount. For example: 100% * 0.99 = 99% | 100% * 1.01 = 101%'),
                    ],
                    [
                        'type' => 'switch',
                        'name' => 'logo',
                        'label' => $this->l('Apirone Logo'),
                        'is_bool' => true,
                        'hint' => $this->l('Show Apirone logo on invoice page'),
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
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getSettingsFormValues(),
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$form_fields]);
    }

    /**
     * Generate Currencies form
     */
    protected function renderCurrenciesForm()
    {
        $currencies = array();

        foreach ($this->settings->getCurrencies() as $item) {
            $class = $item->isTestnet() ? 'icon-coin test-coin' : 'icon-coin';
            $hint = ($item->address) ? $this->l('Remove address to deactivete currency.') : $this->l('Enter valid address to activete currency.');
            $icon = '/modules/apirone/views/css/icons/crypto/' . $item->abbr . '.svg';
            $currency = array(
                'type' => 'text',
                'label' => $item->name,
                'name' => $item->abbr,
                'hint' => $hint,
                'values' => 'address ' . $item->abbr,
                'prefix' => '<i class="' . $class . '" style="background-image: url(' . $icon . ')"></i>',
            );
            if ($item->isTestnet()) {
                $currency['desc'] = $this->l('WARNING: Test currency. Use this currency for testing purposes only! It is displayed on the front end for `Test currency customer`!');
            }
            $currencies[] = $currency;
        }

        if (empty($currencies)) {
            $this->context->controller->errors[] = 'Can`t get currencies list from apirone gateway. Please, try later.';
        }

        $form_fields = [
            'form' => [
                'legend' => [
                    'title' => 'Currencies',
                    'icon' => 'icon-bitcoin',
                ],
                'class' => 'class',
                'input' => $currencies,
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
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
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
        $settings = $this->getSettings();
        $values = [];
        $values['merchant'] = pSQL(Tools::getValue('merchant', $settings->getMerchant()));
        $values['timeout'] = (int) pSQL(Tools::getValue('timeout', $settings->getTimeout()));
        $values['factor'] = (float) pSQL(Tools::getValue('factor', $settings->getFactor()));
        $values['testCustomer'] = pSQL(Tools::getValue('testCustomer', $settings->getExtra('testCustomer')));
        $values['backlink'] = pSQL(Tools::getValue('backlink', $settings->getBacklink()));
        $values['logo'] = pSQL(Tools::getValue('logo', $settings->getLogo()));
        $values['debug'] = pSQL(Tools::getValue('debug', $settings->getDebug()));

        return $values;
    }

    /**
     * Set values for the currencies inputs.
     */
    protected function getCurrenciesFormValues()
    {
        $currencies = $this->getSettings()->getCurrencies();
        $values = [];
        foreach ($currencies as $item) {
            $values[$item->abbr] = pSQL(Tools::getValue($item->abbr, $item->getAddress()));
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
        if (!$this->active) {
            return;
        }
        if (!$this->checkCurrency($params['cart'])) {
            return;
        }

        $coins = [];
        $cart = $params['cart'];
        $currency = $this->getCartCurrency($cart);

        foreach ($this->getAvailableCryptos() as $item) {
            try {
                $item->amount = utils::exp2dec(Utils::fiat2crypto($cart->getCartTotalPrice(), $currency['iso_code'], $item->abbr));
                $coins[] = $item;
            }
            catch(Exception $e) {
                LoggerWrapper::error($e->getMessage());
            }
        }

        if (empty($coins)) {
            $return;
        }

        $action = $this->context->link->getModuleLink($this->name, 'payment', array(), true);
        $this->context->smarty->assign(['action' => $action, 'coins' => $coins]);
        
        $option = new \PrestaShop\PrestaShop\Core\Payment\PaymentOption();
        $option->setCallToActionText($this->l('Pay with crypto'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', [], true))
            ->setForm($this->fetch('module:apirone/views/templates/hook/currencyselector.tpl'));

        return [$option];
    }

    public function hookDisplayAdminOrderTabLink($params)
    {
        // TODO: Move to template
        $return = '<li class="nav-item"><a href="#paypal" class="nav-link" data-toggle="tab" role="tab"><i class="material-icons">list</i>Order apirone invoices</a></li>';

        return $return;
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        $order = new Order($params['id_order']);
        $list = [];
        $invoices = Invoice::getOrderInvoices($params['id_order']);
        foreach ($invoices as $invoice) {
            $list[] = $invoice->details->invoice . ' - ' . $invoice->status;
        }

        return '<pre>' . print_r($order->getOrderPayments(), true) . '</pre>';
    }


    public function checkCurrency($cart)
    {
        $cart_currency = new Currency($cart->id_currency);
        $shop_currencies = $this->getCurrency($cart->id_currency);
        if (is_array($shop_currencies)) {
            foreach ($shop_currencies as $currency) {
                if ($cart_currency->id == $currency['id_currency']) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getCartCurrency($cart)
    {
        $cart_currency = new Currency($cart->id_currency);
        $available_currencies = $this->getCurrency($cart->id_currency);
        if (is_array($available_currencies)) {
            foreach ($available_currencies as $item) {
                if ($cart_currency->id == $item['id_currency']) {
                    return $item;
                }
            }
        }
        return false;
    }

    public function getAvailableCryptos(): array
    {
        $currencies = [];
        $testCustomer = $this->settings->getExtra('testCustomer');

        foreach ($this->settings->getCurrencies() as $item) {
            if ($item->getAddress() !== null && !$item->hasError()) {
                if ($item->isTestnet() && $testCustomer == $this->context->customer->email) {
                    $currencies[] = $item;
                }
                else {
                    $currencies[] = $item;
                }
            }
        }

        return $currencies;
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

    protected function getSettings(): Settings
    {
        $settings = Configuration::get('APIRONE_SETTINGS');
        
        if ($settings) {
            return Settings::fromJson($settings);
        }

        $settings = Settings::init()->createAccount();
        Configuration::updateValue('APIRONE_SETTINGS', $settings->toJsonString());

        return $settings;
    }

    private function createApironeTable()
    {
        if (!Db::getInstance()->execute(InvoiceQuery::createInvoicesTable(_DB_PREFIX_))) {
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
            'paymentOptions',
            'displayAdminOrderTabLink',
            'displayAdminOrderTabContent',
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
        $orderState = ($stateId) ? new OrderState((int)$stateId) : new OrderState();

        $orderState->name = [];
        $orderState->module_name = $this->name;
        $orderState->color = ($name == 'completed') ? '#5D8AB9' : '#AEC4DC';;
        $orderState->hidden = false;
        $orderState->delivery = false;
        $orderState->logable = true;
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
        if($this->logger !== null) {
            call_user_func_array($this->logger, array($level, $message, $context));
        }
    }

    public static function logFilename() {
        return '/var/logs/' . date('Ymd') . '_apirone.log';
    }

    // public static function logger_callback($log_level)
    public function logger_callback($log_level)
    {
        return function ($level, $message, $context = []) use ($log_level) {
            $logger = new FileLogger($log_level);
            $logger->setFilename(_PS_ROOT_DIR_ . static::logFilename());
            if (!empty($context)) {
                $message .= ' DETAILS: ' . json_encode($context, true);
            }
            $psr_logger = new PSRLoggerAdapter($logger);
            $psr_logger->log($level, $message);
        };
    }

    public static function db_callback() 
    {
        return static function($query) {
            $db = Db::getInstance();

            if (preg_match('/select/i', $query)) {
                $result = $db->executeS($query);
            }
            else {
                $result = $db->execute($query);
            }

            return $result;
        };
    }

    public function redirectWithNotice()
    {
        $notifications = json_encode(array(
            'error' => $this->context->controller->errors,
            'warning' => $this->context->controller->warning,
            'success' => $this->context->controller->success,
            'info' => $this->context->controller->info,
        ));

        if (session_status() == PHP_SESSION_ACTIVE) {
            $_SESSION['notifications'] = $notifications;
        } elseif (session_status() == PHP_SESSION_NONE) {
            session_start();
            $_SESSION['notifications'] = $notifications;
        } else {
            setcookie('notifications', $notifications);
        }

        return call_user_func_array(array('Tools', 'redirect'), func_get_args());
    }

}
function pa($mixed, $title = '') {

    echo '<pre>' . $title;
    print_r($mixed);
    echo '</pre>';
}