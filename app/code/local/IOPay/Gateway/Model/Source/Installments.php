<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Model_Source_Installments
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array();
        for($i=1;$i<=12;$i++) {
            $options[$i] = array(
                'value' => $i,
                'label' => $i.'x'
            );
        }

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