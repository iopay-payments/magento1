<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Model_Pix extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Environment variables
     * @var string
     */
    protected $_code = 'iopay_gateway_pix';
    protected $_isGateway                   = true;
    protected $_canUseForMultishipping      = false;
    protected $_isInitializeNeeded          = true;
    protected $_canUseInternal              = true;
    protected $_formBlockType = 'iopay_gateway/form_pix';
    protected $_infoBlockType = 'iopay_gateway/info_pix';
    protected $_canOrder  = true;

    /**
     * Check id method is available
     * @param null $quote
     * @return bool
     */
    public function isAvailable($quote = null)
    {
        if (Mage::getStoreConfigFlag("payment/{$this->_code}/active") == false) {
            return false;
        }

        return true;
    }

    /**
     * @param mixed $data
     * @return $this|Mage_Payment_Model_Info
     * @throws Mage_Core_Exception
     */
    public function assignData($data)
    {
        $this->log(" ----- assignData IOPay Pix ------");
        $this->log($data);

        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $additional = array();

        if (Mage::helper('iopay_gateway')->getTaxfield() == 'iopay' && $data->getIopayPixCpf()) {
            $additional['iopay_cpf'] = $data->getIopayPixCpf();
        }

        if ($additional) {
            $info->setAdditionalInformation($additional);
        }
        return $this;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return $this|Mage_Payment_Model_Abstract
     */
    public function initialize($paymentAction, $stateObject)
    {
        $this->_placeOrder();
        return $this;
    }

    /**
     * @return $this|Mage_Payment_Model_Abstract
     */
    public function validate()
    {
        parent::validate();
        $info = $this->getInfoInstance();
        return $this;
    }

    /**
     * @return $this|false
     * @throws Mage_Core_Exception
     */
    public function _placeOrder() {
        try {
            $this->log(" ----- Place Order IOPay Pix ------");

            $payment  = $this->getInfoInstance();
            $order    = $payment->getOrder();

            $customerIopayId = $this->helperComprador()->getIopayCustomerId($order);
            if (!$customerIopayId) {
                Mage::throwException('IoPay: Erro ao criar ou recuperar o id do comprador no pix.');
            }

            $pixArray = array(
                "amount"        => round($order->getGrandTotal(), 2) * 100,
                "currency"      => "BRL",
                "description"   => Mage::helper('iopay_gateway')->__("Pedido # %s na loja %s", $order->getIncrementId(), Mage::app()->getStore()->getName()),
                "statement_descriptor"   => Mage::app()->getStore()->getName(),
                "io_seller_id" => $this->helper()->getIopaySellerId(),
                "payment_type" => "pix",
                "reference_id" => $order->getIncrementId()
            );

            $this->log(" ----- Calling IOPay API ------");
            $this->log($pixArray);

            $token = Mage::helper('iopay_gateway/authentication')->getToken();
            if (!$token) {
                Mage::throwException('Pix: Erro ao recuperar o access token');
                return false;
            }

            $headers = array(
                "Authorization: Bearer {$token}",
                "cache-control: no-cache",
                "content-type: application/json",
            );

            $api = Mage::helper('iopay_gateway/api');
            $api->setHeader($headers);
            $api->setUri("/v1/transaction/new/{$customerIopayId}");
            $api->setData($pixArray);
            $api->connect();

            $response = $api->getResponse();

            if (isset($response['success']['id'])) {
                $payment_type    = $response['success']['payment_type'];
                $payment_method  = $response['success']['payment_method'];
                $id              = $payment_method['id'];
                $pix_link        = $payment_method['pix_link'];
                $qrcode          = $payment_method['qr_code']['emv'];
                $qrcode_url      = $response['success']['pix_qrcode_url'];
                $expiration_date = $payment_method['expiration_date'];

                try {
                    $payment
                        ->setIopayCustomer($customerIopayId)
                        ->setIopayPixLink($pix_link)
                        ->setIopayPixQrcodeUrl($qrcode_url)
                        ->setIopayPixQrcode($qrcode)
                        ->setIopayPaymentId($id)
                        ->setIopayPaymentType($payment_type)
                        ->setIopayExpirationDate($expiration_date)
                        ->save();
                } catch (Exception $e) {
                    Mage::throwException($e->getMessage());
                    $this->log('IOPay Pix Error Payment Save: ' . $e->getMessage());
                }
            } else {
                if (isset($response['error'])) {
                    $err = null;
                    foreach ($response['error'] as $group => $errors) {
                        foreach ($errors as $k => $v) {
                            $err .= "- Error [{$group}]: ".$v;
                        }
                    }
                    Mage::throwException($err);
                } else {
                    $err = json_encode($response);
                    Mage::throwException("IoPay Pix - Erro desconhecido: {$err}");
                }
            }
        }  catch (Mage_Core_Exception $e) {
            Mage::throwException($e->getMessage());
            $this->log('IOPay pix error: '.$e->getMessage());
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
            $this->log('IOPay pix error: ' . $e->getMessage());
        }

        return $this;
    }

    /**
     * Helper comprador
     * @return Mage_Core_Helper_Abstract|null
     */
    protected function helperComprador() {
        return Mage::helper('iopay_gateway/comprador');
    }

    /**
     * Helper
     * @return Mage_Core_Helper_Abstract|null
     */
    protected function helper() {
        return Mage::helper('iopay_gateway');
    }

    /**
     * Write log
     * @param $message
     */
    protected function log($message) {
        Mage::helper('iopay_gateway')->log($message);
    }
}