<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use Apirone\SDK\Invoice;

class ApironeLinkbackModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $cart_id = Tools::getValue('id');
        $invoices = Invoice::getByOrder($cart_id);
        if (!count($invoices)) {
            $this->module->log('info', 'Cart is not found', ['cart' => $cart_id]);
            Tools::redirect(__PS_BASE_URI__);
        }
        $invoice = $invoices[0];
        $cart = new Cart($invoice->order);

        $key = Tools::getValue('key');
        $amount = $invoice->details->amount;
        if (!$this->module->hashValid($cart, $key, $amount)) {
            $this->module->log('info', 'Invalid key', [
                'key' => $key,
                'amount' => $amount,
                'invoice' => $invoice->invoice,
            ]);
            Tools::redirect(__PS_BASE_URI__);
        }

        if ($cart->orderExists()) {
            $order = Order::getByCartId($cart->id);
            $request = [
                'id_cart' => $order->id_cart,
                'id_module' => $this->module->id,
                'id_order' => $order->id,
                'key' => $order->secure_key,
            ];
            $redirect = $this->context->link->getPageLink('order-confirmation', true, null, $request);
        }
        else {
            $redirect = $this->context->link->getPageLink('order', true, null, ['step' => '3', ]);
        }
        Tools::redirect($redirect);
    }
}