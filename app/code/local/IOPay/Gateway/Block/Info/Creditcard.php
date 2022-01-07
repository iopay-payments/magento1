<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Block_Info_Creditcard extends Mage_Payment_Block_Info
{
    /**
     * Constructor method
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('iopay/gateway/info/creditcard.phtml');
    }
}