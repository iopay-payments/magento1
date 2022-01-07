<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `iopay_expiration_date` text DEFAULT NULL;
        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `iopay_payment_limit_date` text DEFAULT NULL;
        ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `iopay_expiration_date` text DEFAULT NULL;
        ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `iopay_payment_limit_date` text DEFAULT NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();