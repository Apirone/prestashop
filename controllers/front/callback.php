<?php
use Apirone\SDK\Invoice;
use Apirone\SDK\Service\Utils;

class ApironeCallbackModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        Invoice::callbackHandler($this->module->getPaymentProcessor(), $this->getCallbackChecker());
    }

    private function getCallbackChecker()
    {
        return function(Invoice $invoice) 
        {
            $key = Tools::getValue('key');
            $cart = new Cart($invoice->order);

            if ($this->module->hashValid($cart, $key)) {
                unset($cart);
                return;
            }
            $this->module->log('info', 'Invalid key', [
                'key' => $key,
                'invoice' => $invoice->invoice,
            ]);
            Utils::sendJson('Invalid id', 403);
            exit;
        };
    }
}
