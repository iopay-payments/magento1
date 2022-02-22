<?php

class IOPay_Gateway_Model_Source_Installmentsfee
{

    /**
     * Options getter
     * @return array
     */
    public function toOptionArray() {
        $options = array();

        $options[0] = array(
            'value' => 0,
            'label' => 'sem juros'
        );

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