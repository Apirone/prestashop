<?php
use Apirone\SDK\Invoice;
use Apirone\SDK\Model\UserData;

class ApironePaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            $this->backToCart();
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            $this->backToCart();
        }

        $currency_crypto = Tools::getValue('coin');

        $crypto = $this->module->getCrypto($currency_crypto);
        if (!$crypto) {
            $this->module->log('error', 'Can\'t get crypto details', [$currency_crypto]);
            $this->errors[] = $this->module->l('There has been an error processing your order.');
            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => '3']));
        }

        // Show an apirone invoice
        $cart_id = $cart->id;
        $amount_fiat = $cart->getOrderTotal();
        $currency_fiat = $this->context->currency->iso_code;
        $price_fiat = $amount_fiat.' '.strtoupper($currency_fiat);

        // Check if invoice exist, not expired & has same crypto & has same fiat price (amount and currency)
        $cart_invoices = Invoice::getByOrder($cart_id);
        if (count($cart_invoices)) {
            $invoice = $cart_invoices[0];
            // Update existing invoice status
            $invoice->update(30);
            if ($invoice->status !== 'expired'
                && $invoice->details->currency == $currency_crypto
                && $invoice->details->userData->price == $price_fiat
            ) {
                // Show existing invoice when page is loaded or reloaded & status != expired & the same crypto currency & the same fiat price
                $this->showInvoice($invoice->invoice);
            }
        }

        // Create new invoice
        $estimation = $this->module->getEstimation($amount_fiat, $currency_fiat, $currency_crypto);
        if (!$estimation) {
            $this->backToCart();
        }
        $amount_crypto = $estimation->min;

        $settings = $this->module->settings;

        $userData = UserData::init()
            ->merchant($settings->merchant ?: Configuration::get('PS_SHOP_NAME'))
            ->url($this->context->shop->getBaseURL())
            ->price($price_fiat);

        try {
            $invoice = Invoice::init($settings->account, $currency_crypto)
                ->amount($amount_crypto)
                ->order($cart_id)
                ->estimation($estimation)
                ->userData($userData)
                ->lifetime($settings->timeout)
                ->callbackUrl($this->context->link->getModuleLink('apirone', 'callback', ['key' => $this->module->getHash($cart)], true))
                ->linkback($this->context->link->getModuleLink('apirone', 'linkback', ['id' => $cart_id, 'key' => $this->module->getHash($cart, $amount_crypto)], true))
                ->create();

            $this->showInvoice($invoice->invoice);
        }
        catch (\Exception $e) {
            $this->module->log('warning', $e->getMessage());
            $this->errors[] = $this->module->l('There has been an error processing your order.');
            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => '3']));
        }
    }

    protected function backToCart(): void
    {
        Tools::redirect('index.php?controller=order&step=1');
    }

    protected function showInvoice($invoice)
    {
        Tools::redirect($this->context->link->getModuleLink($this->module->name, 'invoice', ['id' => $invoice], true));
    }
}
