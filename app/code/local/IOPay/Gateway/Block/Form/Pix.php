<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Block_Form_Pix extends Mage_Payment_Block_Form
{
    /**
     * Constructor method
     */
    protected function _construct()
    {
        $useLogo    = Mage::helper('iopay_gateway')->getLogoCheckout();
        $label      = "";
        $title      = $this->getMethodTitle();

        if ($useLogo) {
            $title = " - Pix";
            $label = '<img src="https://iopay.com.br/assets/img/logo/iopay-dark.png" width="50px" height="15px"  alt="" class="v-middle" />';
        }
        parent::_construct();
        $this->setTemplate('iopay/gateway/form/pix.phtml')
            ->setMethodTitle($title)
            ->setMethodLabelAfterHtml($label);
    }
}