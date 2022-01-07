<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Model_Source_Paymentaction
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray()
    {
        return array(
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE,
                'label' => Mage::helper('iopay_gateway')->__('Authorize Only')
            ),
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE,
                'label' => Mage::helper('iopay_gateway')->__('Authorize and Capture')
            ),
            array(
                'value' => Mage_Payment_Model_Method_Abstract::ACTION_ORDER,
                'label' => Mage::helper('iopay_gateway')->__('Order')
            ),
        );
    }
}