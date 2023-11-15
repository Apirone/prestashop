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