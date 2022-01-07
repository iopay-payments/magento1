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
}