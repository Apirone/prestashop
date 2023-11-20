<?php
use Apirone\SDK\Invoice;

class ApironeLinkbackModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $redirect = __PS_BASE_URI__;
        $invoice = Invoice::getInvoice(Tools::getValue('invoice', null));

        if (property_exists($invoice, 'order') && $invoice->order !== 0) {
            $cart = new Cart($invoice->order);

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
        }

        Tools::redirect($redirect);
    }
}