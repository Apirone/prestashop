<?php

use Apirone\SDK\Invoice;
use Apirone\SDK\Service\Utils;

class ApironeCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $this->checkCallbackData();

        Invoice::callbackHandler($this->handlerWrapper());
        exit;
    }

    protected function callbackProcess(Invoice $invoice)
    {
        if (!in_array($invoice->status, ['paid', 'overpaid', 'completed'])) {
            return;
        }

        $cart = new Cart($invoice->order);
        $order_status = (int) Configuration::get('APIRONE_OC_PAYMENT_ACCEPTED');

        // Create order
        if (!$cart->orderExists() && in_array($invoice->status, ['paid', 'overpaid','expired'])) {
            $order_status = (int) Configuration::get('APIRONE_OC_PAYMENT_ACCEPTED');
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
                $this->module->log('error', 'Can\'t create an order.', [$e->getMessage()]);
                $message = 'Can\'t create an order.';
                Utils::send_json($message, 400);
            }

            $order = Order::getByCartId($cart->id);
            $invoice->setMeta('order_status', (int) $order->getCurrentState());
            return;
        }

        // Update
        if ($cart->orderExists() && $invoice->status == 'completed') {
            $order = Order::getByCartId($cart->id);

            $current_status = (int) $order->getCurrentState();
            if ($invoice->getMeta('order_status') !== $current_status) {
                $this->module->log('info', 'The invoice order status does not match the current order status. Order ref: ' . $order->reference);
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
        return;
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

        $message = sprintf($this->l('Secret %s not valid for invoice %s'), $secret, $invoice ? $invoice->invoice : 'is null');
        $this->module->log('info', $message);
        Utils::send_json($message, 400);
        exit;
    }
}
