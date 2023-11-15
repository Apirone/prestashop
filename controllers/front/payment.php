<?php

/**
 * 2017-2023 apirone.com
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade AmazonPay to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author    Apirone OÜ <support@apirone.com>
 *  @copyright 2017-2023 Apirone OÜ
 *  @license   http://opensource.org/licenses/afl-3.0.php  The MIT License
 */

use Apirone\SDK\Invoice;
use Apirone\SDK\Model\UserData;

class ApironePaymentModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $cart = $this->context->cart;
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $crypto = $this->module->getCrypto();

        if ($crypto == false) {
            $this->module->log('error', 'Can\'t get crypto details', [Tools::getValue('coin')]);
            $this->errors[] = $this->module->l('There has been an error processing your order.');
            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => '3']));
        }

        $currency = $this->context->currency;

        // Check if invoice exist, not expired & has same crypto
        $cart_invoices = Invoice::getOrderInvoices($cart->id);
        if (!empty($cart_invoices)) {
            $invoice = $cart_invoices[0];
            $invoice->update();
            if ($invoice->status !== 'expired' && $invoice->details->currency == $crypto->abbr) {
                $this->invoice_redirect($invoice);
            }
        }

        // Create an apirone invoice
        $cart_total = $cart->getOrderTotal();

        $invoice = Invoice::fromFiatAmount($cart_total, $currency->iso_code, $crypto->abbr, $this->module->settings->getFactor());
        $invoice
            ->order($cart->id)
            ->lifetime($this->module->settings->getTimeout());
        
        // Set invoice secret & callback URL
        $invoice->callbackUrl($this->context->link->getModuleLink('apirone', 'callback', ['id' => md5($cart->id . $cart->secure_key)], true));
        $invoice->linkback($this->context->link->getModuleLink('apirone', 'linkback', ['id' => md5($cart->id . $cart->secure_key)], true));

        $userData = UserData::init();
        $merchant = $this->module->settings->getMerchant() ?? Configuration::get('PS_SHOP_NAME');

        $userData->setMerchant($merchant);
        $userData->setUrl(Context::getContext()->shop->getBaseURL(true));

        $userData->setPrice($cart_total . ' ' . strtoupper($currency->iso_code));

        $invoice->userData($userData);

        try {
            $invoice->create($this->module->settings->getAccount());
        }
        catch (Exception $e) {
            $this->module->log('warning', $e->getMessage());
            $this->errors[] = $this->module->l('There has been an error processing your order.');
            $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => '3']));
        }

        $this->invoice_redirect($invoice);
    }

    protected function invoice_redirect($invoice)
    {
        $params = ['id' => $invoice->invoice];
        Tools::redirect($this->context->link->getModuleLink($this->module->name, 'invoice', $params, true));
    }
}
