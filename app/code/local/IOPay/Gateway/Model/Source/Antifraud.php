<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Model_Source_Antifraud
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array(
            'with_anti_fraud' => array(
                'value' => 'with_anti_fraud',
                'label' => 'with_anti_fraud'
            ),
            'with_anti_fraud_insurance' => array(
                'value' => 'with_anti_fraud_insurance',
                'label' => 'with_anti_fraud_insurance'
            ),
            'without_antifraud' => array(
                'value' => 'without_antifraud',
                'label' => 'without_antifraud'
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