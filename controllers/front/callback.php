<?php

use Apirone\SDK\Invoice;
use Apirone\SDK\Service\Utils;

/**
 * Package: Prestashop Apirone Payment gateway
 *
 * Another header line 1
 * Another header line 2
 *
 */

class ApironeCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $this->checkCallbackData();

        Invoice::callbackHandler($this->handlerWrapper($invoice));
        exit;
    }

    protected function callbackProcess(Invoice $invoice)
    {
        if (!in_array($invoice->status, ['paid', 'overpaid', 'completed'])) {
            return;
        }

        $cart = new Cart($invoice->order);

        // Create order
        if (!$cart->orderExists() && in_array($invoice->status, ['paid', 'overpaid'])) {
            $order_status = (int) Configuration::get('APIRONE_OS_ACCEPTED');
            try {
                $this->module->validateOrder(
                    (int) $cart->id,
                    (int) $order_status,
                    $cart->getOrderTotal(),
                    $this->module->displayName,
                    null,
                    [],
                    (int) $cart->id_currency,
                    false,
                    $cart->secure_key
                );
            }
            catch (Exception $e) {
                $this->module->log('error', 'Order could not be validated', [$e]);
                $this->errors[] = $this->module->l('There has been an error processing your order.');
                $this->redirectWithNotifications($this->context->link->getPageLink('order', true, null, ['step' => '3', ]));
            }

            // TODO: Store last order status into invoce
            return;
        }

        // Update
        if ($cart->orderExists() && $invoice->status == 'completed') {
            $order = Order::getByCartId($cart->id);

            $current_status = (int) $order->getCurrentState();
            $new_status = (int)  Configuration::get('APIRONE_OS_CONFIRMED');

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
            }
        }
    }

    protected function handlerWrapper() {
        $handler = function(Invoice $invoice) {
            $this->callbackProcess($invoice);
        };
        return $handler;
    }

    private function checkCallbackData()
    {
        $secret = Tools::getValue('id');
        $data = file_get_contents('php://input');
        $data = ($data) ? json_decode(Utils::sanitize($data)) : new \stdClass;
        
        $invoice = property_exists($data, 'invoice') ? $data->invoice : null; 
        if ($invoice) {
            $invoice = Invoice::getInvoice($invoice);
            $cart = new Cart($invoice->order);

            if ($cart->id && $secret == md5($cart->id . $cart->secure_key)) {
                unset($cart);
                return;
            }
        }

        $message = sprintf($this->l('Secret %s not valid for invoice %s'), $secret, $invoice ? $invoice : 'is null');
        $this->module->log('info', $message);
        Utils::send_json($message, 400);
        exit;
    }
}
