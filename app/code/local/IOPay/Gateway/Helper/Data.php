<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Helper_Data extends IOPay_Gateway_Helper_Abstract
{

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getTaxfield() {
        return Mage::getStoreConfig('payment/iopay_gateway/taxvat_field');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getIopayEnvironment() {
        return Mage::getStoreConfig('payment/iopay_gateway/environment');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getIopayEmail() {
        return Mage::getStoreConfig('payment/iopay_gateway/email');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getIopaySellerId() {
        return Mage::getStoreConfig('payment/iopay_gateway/io_seller_id');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getIopaySecret() {
        return Mage::getStoreConfig('payment/iopay_gateway/secret');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getIopayBoletoInstructions() {
        return Mage::getStoreConfig('payment/iopay_gateway_boleto/instructions');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getIopayPixInstructions() {
        return Mage::getStoreConfig('payment/iopay_gateway_pix/instructions');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getAntifraudPlan() {
        return Mage::getStoreConfig('payment/iopay_gateway_creditcard/antifraud_plan');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getAntifraudKey() {
        return Mage::getStoreConfig('payment/iopay_gateway_creditcard/antifraud_key');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getInstallments() {
        return Mage::getStoreConfig('payment/iopay_gateway_creditcard/installments');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getInstallmentsWithFee() {
        return Mage::getStoreConfig('payment/iopay_gateway_creditcard/installments_with_fee');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getInstallmentsFee() {
        return Mage::getStoreConfig('payment/iopay_gateway_creditcard/installments_fee');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getIopayautoInvoice() {
        return Mage::getStoreConfig('payment/iopay_gateway/invoice');
    }

    /**
     * Return configuration from admin settings on Payment Methods
     * @return mixed
     */
    public function getLogoCheckout() {
        return Mage::getStoreConfig('payment/iopay_gateway/logo_checkout');
    }

    /**
     * Return session id from user browser
     * @return mixed
     */
    public function getSessionId() {
        $session = Mage::getSingleton('core/session');
        return $session->getEncryptedSessionId();
    }

    /**
     * Retrieves order items
     *
     * @param Mage_Sales_Model_Order $order
     * @return array
     */
    public function getShoppingCart(Mage_Sales_Model_Order $order)
    {
        $result = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $result[] = array(
                "name"      => $item->getName(),
                "code"      => $item->getSku(),
                "quantity"  => intval($item->getQtyOrdered()),
                "amount"    => $this->_formatNumber($item->getBasePrice())
            );
        }

        return $result;
    }

    /**
     * Generate invoice from order
     * @param $order
     */
    public function generateInvoice($order)
    {
        try {
            if ($order->canInvoice()) {
                $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
                $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                $invoice->getOrder()->setCustomerNoteNotify(false);
                $invoice->getOrder()->setIsInProcess(true);

                $transactionSave = Mage::getModel('core/resource_transaction')
                    ->addObject($invoice)
                    ->addObject($invoice->getOrder());

                $transactionSave->save();
            }
        } catch (\Exception $e) {
            $this->log($e->getMessage());
        }
    }

    /**
     * Format Number
     *
     * @param $number
     * @return string
     */
    public function _formatNumber($number)
    {
        $number = Mage::getSingleton('core/locale')->getNumber($number);
        return (float) round(sprintf('%0.2f', $number), 2) * 100;
    }

    /**
     * Convert date to d/m/Y
     * @param $date
     * @return false|string
     */
    public function convertDate($date) {
        return date("d/m/Y", strtotime($date));
    }

    /**
     * Convert date to d/m/Y H:m:s
     * @param $date
     * @return false|string
     */
    public function convertDateHour($date) {
        return date("d/m/Y H:m:s", strtotime($date));
    }

    public function getCardBrand($number) {
        $brand = null;
        $brands = array(
            'visa'       => '/^4\d{12}(\d{3})?$/',
            'mastercard' => '/^(5[1-5]\d{4}|677189)\d{10}$/',
            'diners'     => '/^3(0[0-5]|[68]\d)\d{11}$/',
            'discover'   => '/^6(?:011|5[0-9]{2})[0-9]{12}$/',
            'elo'        => '/^((((636368)|(438935)|(504175)|(451416)|(636297))\d{0,10})|((5067)|(4576)|(4011))\d{0,12})$/',
            'amex'       => '/^3[47]\d{13}$/',
            'jcb'        => '/^(?:2131|1800|35\d{3})\d{11}$/',
            'aura'       => '/^(5078\d{2})(\d{2})(\d{11})$/',
            'hipercard'  => '/^(606282\d{10}(\d{3})?)|(3841\d{15})$/',
            'maestro'    => '/^(?:5[0678]\d\d|6304|6390|67\d\d)\d{8,15}$/',
        );

        foreach ($brands as $_brand => $regex) {
            if (preg_match($regex, $number) ) {
                $brand = $_brand;
                break;
            }
        }

        return $brand;
    }

    public function getBrandImage($brand) {
        switch ($brand) {
            case 'visa':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/visa.svg');
                break;
            case 'mastercard':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/master-card.svg');
                break;
            case 'diners':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/diners.svg');
                break;
            case 'discover':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/discover-network.svg');
                break;
            case 'elo':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/elo-card.svg');
                break;
            case 'amex':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/american-express.svg');
                break;
            case 'jcb':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/jcb.svg');
                break;
            case 'aura':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/aura.svg');
                break;
            case 'hipercard':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/hipercard.svg');
                break;
            case 'maestro':
                $brandImg = Mage::getDesign()->getSkinUrl('images/iopay/flags/maestro.svg');
                break;
            default:
                $brandImg = null;
                break;
        }
        return $brandImg;
    }

    /**
     * Return installments options to populate dropdown in checkout field
     * @return array|mixed|null
     */
    public function getCcInstallments() {
        $grandTotal         = Mage::getSingleton('checkout/type_onepage')->getQuote()->getGrandTotal();
        $installOpt         = Mage::helper('iopay_gateway')->getInstallments();
        $installWithFee     = Mage::helper('iopay_gateway')->getInstallmentsWithFee();
        $installFee         = Mage::helper('iopay_gateway')->getInstallmentsFee();
        $installments       = null;

        for ($i=1; $i <= $installOpt; $i++) {

            $installmentAmount  = $grandTotal/$i; //valor da parcela
            $labelFee           = "sem juros";
            $orderTotal         = $grandTotal;

            if ($installWithFee) {
                if ($i >= $installWithFee) { //juros nas parcelas acima de
                    $fee                = ($grandTotal / 100) * $installFee;
                    $installmentAmount  = $installmentAmount + $fee; //parcela + juro
                    $orderTotal         = $i * $installmentAmount;
                    $labelFee           = "com juros de {$installFee}%";
                }
            }

            $orderTotal         = Mage::getModel('directory/currency')->format($orderTotal, array('display'=>Zend_Currency::USE_SYMBOL));
            $installmentAmount  = Mage::getModel('directory/currency')->format($installmentAmount, array('display'=>Zend_Currency::USE_SYMBOL));
            $label              = "{$i}x de {$installmentAmount} {$labelFee} (Total {$orderTotal})";
            $installments[$i]   = $label;
        }

        return $installments;
    }

    /**
     * Return installments options to populate dropdown in checkout field
     * @return array|mixed|null
     */
    public function getOrderTotalWithInstallments($installment) {
        $grandTotal         = Mage::getSingleton('checkout/type_onepage')->getQuote()->getGrandTotal();
        $installOpt         = Mage::helper('iopay_gateway')->getInstallments();
        $installWithFee     = Mage::helper('iopay_gateway')->getInstallmentsWithFee();
        $installFee         = Mage::helper('iopay_gateway')->getInstallmentsFee();
        $installments       = null;

        for ($i=1; $i <= $installOpt; $i++) {

            $installmentAmount  = $grandTotal/$i; //valor da parcela
            $orderTotal         = $grandTotal;

            if ($installWithFee) {
                if ($i >= $installWithFee) { //juros nas parcelas acima de
                    $fee                = ($grandTotal / 100) * $installFee;
                    $installmentAmount  = $installmentAmount + $fee; //parcela + juro
                    $orderTotal         = $i * $installmentAmount;
                }
            }

            if ($i == $installment) {
                return $orderTotal;
            }
        }
    }
}