<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Model_Source_Environment
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array(
            'sandbox' => array(
                'value' => 'sandbox',
                'label' => 'Sandbox'
            ),
            'live' => array(
                'value' => 'live',
                'label' => 'Production'
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