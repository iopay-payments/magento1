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
        $useLogo    = Mage::helper('iopay_gateway')->getLogoCheckout();
        $label      = "";
        $title      = $this->getMethodTitle();

        if ($useLogo) {
            $title = " - Cartão de Crédito";
            $label = '<img src="https://iopay.com.br/assets/img/logo/iopay-dark.png" width="50px" height="15px"  alt="" class="v-middle" />';
        }
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
        $amount         = Mage::getSingleton('checkout/type_onepage')->getQuote()->getGrandTotal();
        $installOpt     = Mage::helper('iopay_gateway')->getInstallments();
        $installments   = null;
        for ($i=1; $i <= $installOpt; $i++) {
            if ($i == 1) {
                $label = Mage::helper('iopay_gateway')->__('Pagamento à vista de %s', Mage::getModel('directory/currency')->format($amount, array('display'=>Zend_Currency::USE_SYMBOL), false));
            } else {
                $label = Mage::helper('iopay_gateway')->__('%sx de %s', $i, Mage::getModel('directory/currency')->format($amount/$i, array('display'=>Zend_Currency::USE_SYMBOL), false));
            }
            $installments[$i] = $label;
        }
        return $installments;
    }
}