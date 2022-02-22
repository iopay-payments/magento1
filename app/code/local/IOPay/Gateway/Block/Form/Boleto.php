<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Block_Form_Boleto extends Mage_Payment_Block_Form
{
    /**
     * Constructor method
     */
    protected function _construct()
    {
        $title      = $this->getMethodTitle();
        $label = '<svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg"  xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                     viewBox="0 0 16 16" style="enable-background:new 0 0 16 16; width: 20px; margin-bottom: -4px" xml:space="preserve" >
                                        <style type="text/css">
                                            .st2{fill:#BC5797;}
                                        </style>
                                    <path class="st2" d="M1.5,1C1.2,1,1,1.2,1,1.5v3C1,4.8,0.8,5,0.5,5S0,4.8,0,4.5v-3C0,0.7,0.7,0,1.5,0h3C4.8,0,5,0.2,5,0.5
                                        S4.8,1,4.5,1H1.5z M11,0.5C11,0.2,11.2,0,11.5,0h3C15.3,0,16,0.7,16,1.5v3C16,4.8,15.8,5,15.5,5S15,4.8,15,4.5v-3
                                        C15,1.2,14.8,1,14.5,1h-3C11.2,1,11,0.8,11,0.5z M0.5,11C0.8,11,1,11.2,1,11.5v3C1,14.8,1.2,15,1.5,15h3C4.8,15,5,15.2,5,15.5
                                        S4.8,16,4.5,16h-3C0.7,16,0,15.3,0,14.5l0,0v-3C0,11.2,0.2,11,0.5,11z M15.5,11c0.3,0,0.5,0.2,0.5,0.5v3c0,0.8-0.7,1.5-1.5,1.5l0,0
                                        h-3c-0.3,0-0.5-0.2-0.5-0.5s0.2-0.5,0.5-0.5h3c0.3,0,0.5-0.2,0.5-0.5v-3C15,11.2,15.2,11,15.5,11z M3,4.5C3,4.2,3.2,4,3.5,4
                                        S4,4.2,4,4.5v7C4,11.8,3.8,12,3.5,12S3,11.8,3,11.5V4.5z M5,4.5C5,4.2,5.2,4,5.5,4S6,4.2,6,4.5v7C6,11.8,5.8,12,5.5,12
                                        S5,11.8,5,11.5V4.5z M7,4.5C7,4.2,7.2,4,7.5,4S8,4.2,8,4.5v7C8,11.8,7.8,12,7.5,12S7,11.8,7,11.5V4.5z M9,4.5C9,4.2,9.2,4,9.5,4h1
                                        C10.8,4,11,4.2,11,4.5v7c0,0.3-0.2,0.5-0.5,0.5h-1C9.2,12,9,11.8,9,11.5V4.5z M12,4.5C12,4.2,12.2,4,12.5,4S13,4.2,13,4.5v7
                                        c0,0.3-0.2,0.5-0.5,0.5S12,11.8,12,11.5V4.5z"/>
                                    </svg>';

        parent::_construct();
        $this->setTemplate('iopay/gateway/form/boleto.phtml')
            ->setMethodTitle($title)
            ->setMethodLabelAfterHtml($label);
    }
}