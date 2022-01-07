<?php

$installer = $this;
$installer->startSetup();

try {
    $installer->run("
        ALTER TABLE `{$installer->getTable('sales/quote_payment')}` 
        ADD `iopay_id` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_customer` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_boleto_url` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_boleto_barcode` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_pix_qrcode_url` text DEFAULT NULL,
        ADD `iopay_pix_link` text DEFAULT NULL,    
        ADD `iopay_payment_id` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_payment_type` VARCHAR(255) DEFAULT NULL;
          
        ALTER TABLE `{$installer->getTable('sales/order_payment')}` 
        ADD `iopay_id` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_customer` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_boleto_url` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_boleto_barcode` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_pix_qrcode_url` text DEFAULT NULL,
        ADD `iopay_pix_link` text DEFAULT NULL,
        ADD `iopay_payment_id` VARCHAR(255) DEFAULT NULL,
        ADD `iopay_payment_type` VARCHAR(255) DEFAULT NULL;
    ");
} catch (Exception $e) {
    Mage::log($e->getMessage());
}

$installer->endSetup();