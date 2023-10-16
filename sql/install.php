<?php

/**
 * Package: Prestashop Apirone Payment gateway
 *
 * Another header line 1
 * Another header line 2
 *
 */

use Apirone\API\Log\LoggerWrapper;
use Apirone\SDK\Service\InvoiceQuery;

$sql = [];

$sql[] = InvoiceQuery::createInvoicesTable(_DB_PREFIX_);

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
