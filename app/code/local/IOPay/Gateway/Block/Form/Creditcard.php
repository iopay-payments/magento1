<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Block_Form_Creditcard extends Mage_Payment_Block_Form
{
    /**
     * Construct method
     */
    protected function _construct()
    {
        $title = $this->getMethodTitle();
        $label = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     viewBox="0 0 16 16" style="enable-background:new 0 0 16 16; width: 20px; margin-bottom: -4px" xml:space="preserve">
                                        <style type="text/css">
                                            .st1{fill:#5B2886;}
                                        </style>
                                    <path class="st1" d="M11,5.5C11,5.2,11.2,5,11.5,5h2C13.8,5,14,5.2,14,5.5v1C14,6.8,13.8,7,13.5,7h-2C11.2,7,11,6.8,11,6.5V5.5z"/>
                                    <path class="st1" d="M2,2C0.9,2,0,2.9,0,4v8c0,1.1,0.9,2,2,2h12c1.1,0,2-0.9,2-2V4c0-1.1-0.9-2-2-2H2z M15,4v5H1V4c0-0.6,0.4-1,1-1
	                                    h12C14.6,3,15,3.4,15,4z M14,13H2c-0.6,0-1-0.4-1-1v-1h14v1C15,12.6,14.6,13,14,13z"/>
                                    </svg>';
        parent::_construct();
        $this->setTemplate('iopay/gateway/form/creditcard.phtml')
            ->setMethodTitle($title)
            ->setMethodLabelAfterHtml($label);
    }

    /**
     * Return months to populate dropdown in checkout field
     * @return array|mixed|null
     */
    public function getCcMonths()
    {
        $months = $this->getData('cc_months');
        if (is_null($months)) {
            $months[0] =  $this->__('Mês');
            for ($i=1; $i <= 12; $i++) {
                $months[$i] = str_pad($i, 2, '0', STR_PAD_LEFT);
            }
            $this->setData('cc_months', $months);
        }
        return $months;
    }

    /**
     * Return years to populate dropdown in checkout field
     * @return array|mixed|null
     */
    public function getCcYears()
    {
        $years = $this->getData('cc_years');
        if (is_null($years)) {
            $years = Mage::getSingleton('payment/config')->getYears();
            $years = array(0=>$this->__('Ano'))+$years;
            $this->setData('cc_years', $years);
        }
        return $years;
    }

    /**
     * Return installments options to populate dropdown in checkout field
     * @return array|mixed|null
     */
    public function getCcInstallments() {
        return Mage::helper('iopay_gateway')->getCcInstallments();
    }
}