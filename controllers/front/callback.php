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

    protected function handlerWrapper() {
        $handler = function(Invoice $invoice) {
            $this->module->apironePaymentProcess($invoice);
        };

        return $handler;
    }

    private function checkCallbackData()
    {
        $secret = Tools::getValue('id');
        $data = Tools::file_get_contents('php://input');
        $data = ($data) ? json_decode(Utils::sanitize($data)) : new \stdClass;

        $invoice = property_exists($data, 'invoice') ? $data->invoice : null;
        if ($invoice) {
            $invoice = Invoice::get($invoice);
            $cart = new Cart($invoice->order);

            if ($cart->id && $secret == md5($cart->id . $cart->secure_key)) {
                unset($cart);

                return;
            }
        }

        $message = sprintf($this->l('Secret %s not valid for invoice %s'), $secret, $invoice ? $invoice->invoice : 'is null');
        $this->module->log('info', $message);
        Utils::sendJson($message, 400);
        exit;
    }
}
