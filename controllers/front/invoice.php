<?php

use Apirone\SDK\Invoice;
use Apirone\SDK\Service\Render;
use Apirone\SDK\Service\Utils;

/**
 * Package: Prestashop Apirone Payment gateway
 *
 * Another header line 1
 * Another header line 2
 *
 */

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
        $backlink = $this->context->link->getPageLink('order', true, null, ['step' => '3', ]);

        $id = array_key_exists('id', $_GET) ? Utils::sanitize($_GET['id']) : null;
        if (Render::isAjaxRequest()) {
            $data = file_get_contents('php://input');
            $params = ($data) ? json_decode(Utils::sanitize($data)) : null;

            if ($params) {
                $id = property_exists($params, 'invoice') ? (string) $params->invoice : null;
            }
        }
        $invoice = Invoice::getInvoice($id);

        Render::$idParam = 'id';
        Render::$backlink = $backlink;

        return $invoice;
    }
}
