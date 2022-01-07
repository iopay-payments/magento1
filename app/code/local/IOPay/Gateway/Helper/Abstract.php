<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Helper_Abstract extends Mage_Core_Helper_Abstract
{
    /**
     * Helper
     * @return Mage_Core_Helper_Abstract|null
     */
    protected function helper() {
        return Mage::helper('iopay_gateway');
    }

    /**
     * Method to write log for software debug
     * File /var/log/iopay.log
     * @param $message
     */
    public function log($message) {
        if (Mage::getStoreConfig('payment/iopay_gateway/log')) {
            Mage::log($message, null, 'iopay.log', true);
        }
    }
}