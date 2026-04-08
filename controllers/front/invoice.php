<?php
class ApironeInvoiceModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {
        $this->context->smarty->assign([
            'service_url' => substr($this->context->link->getModuleLink('apirone', '/', [], true), 0, -1),
            'invoice_app_config' => sprintf('logo: %s,', $this->module->settings->logo ? 'true' : 'false'),
        ]);
        return $this->setTemplate('module:apirone/views/templates/front/invoice.tpl');
    }
}
