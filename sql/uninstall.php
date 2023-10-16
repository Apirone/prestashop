<?php

/**
 * Package: Prestashop Apirone Payment gateway
 *
 * Another header line 1
 * Another header line 2
 *
 */

$sql = [];

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
