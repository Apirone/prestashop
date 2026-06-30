<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use Apirone\SDK\Service\Api;

class ApironeWalletsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        Api::wallets();
        exit;
    }
}
