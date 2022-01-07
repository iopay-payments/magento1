<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` ADD `iopay_pix_qrcode` text DEFAULT NULL;
        ALTER TABLE `{$installer->getTable('sales/order_payment')}` ADD `iopay_pix_qrcode` text DEFAULT NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();