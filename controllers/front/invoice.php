<?php
use Apirone\SDK\Invoice;
use Apirone\SDK\Service\Render;
use Apirone\SDK\Service\Utils;

class ApironeInvoiceModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $invoice = $this->setRenderParams();

        if (Render::isAjaxRequest()) {
            return Invoice::renderAjax();
        }

        $this->context->smarty->assign(['invoice' => Invoice::renderLoader($invoice)]);

        return $this->setTemplate('module:apirone/views/templates/front/invoice.tpl');
    }

    protected function setRenderParams()
    {
        Invoice::dataUrl($this->context->link->getModuleLink('apirone', 'invoice', []));

        $id = Tools::getValue('id', null);
        if (Render::isAjaxRequest()) {
            $data = Tools::file_get_contents('php://input');
            $params = ($data) ? json_decode(Utils::sanitize($data)) : null;

            if ($params) {
                $id = property_exists($params, 'invoice') ? (string) $params->invoice : null;
            }
        }
        $invoice = Invoice::getInvoice($id);

        // Create backlink
        $backlink = '';
        if (property_exists($invoice, 'order') && $invoice->order !== 0) {
            $cart = new Cart($invoice->order);
            $backlink = $this->context->link->getModuleLink('apirone', 'linkback', ['id' => md5($cart->id . $cart->secure_key)], true);
        }

        // Set render params
        Render::$idParam = 'id';
        Render::$backlink = $backlink;

        return $invoice;
    }
}
