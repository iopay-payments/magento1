<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Model_Source_Taxfield
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array(
            'iopay' => array(
                'value' => 'iopay',
                'label' => 'IOPay Taxvat'
            ),
            'address' => array(
                'value' => 'address',
                'label' => 'Billing Address Taxvat'
            ),
            'customer' => array(
                'value' => 'customer',
                'label' => 'Customer Taxvat'
            ),
        );

        return $options;
    }

    /**
     * Get options in "key-value" format
     * @return array
     */
    public function toArray()
    {
        $arr  = array(array('' => '-'));
        $optionArray = $this->toOptionArray();
        foreach($optionArray as $option){
            $arr[$option['value']] = $option['label'];
        }
    }
}