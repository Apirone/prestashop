<?php
use Apirone\SDK\Service\Api;
use Apirone\SDK\Service\Utils;

class ApironeInvoicesModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $invoice_id = Tools::getValue('id');
        if (!$invoice_id) {
            $message = 'Invoice id not specified';
            $this->module->log('error', $message);
            Utils::sendJson($message, 400);
            exit;
        }
        Api::checkInterval(30);
        Api::invoices($invoice_id, $this->module->getPaymentProcessor());
        exit;
    }
}
