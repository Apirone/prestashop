<?php
use Apirone\SDK\Service\Api;

class ApironeWalletsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        Api::wallets();
        exit;
    }
}
