<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Model_Creditcard extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Environment variables
     * @var string
     */
    protected $_code = 'iopay_gateway_creditcard';
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canRefund = true;
    protected $_isInitializeNeeded = true;
    protected $_formBlockType = 'iopay_gateway/form_creditcard';
    protected $_infoBlockType = 'iopay_gateway/info_creditcard';

    /**
     * Check if method is available
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

        $this->log(" ----- assignData IOPay CreditCard ------");
        $this->log($data);

        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }

        $info = $this->getInfoInstance();
        $additional = array();

        if ($data->getMethod()) {
            $additional['method'] = $data->getMethod();
        }

        if ($data->getCcOwner()) {
            $additional['cc_owner'] = $data->getCcOwner();
        }

        if ($data->getCcNumber()) {
            $additional['cc_number'] = $data->getCcNumber();
        }

        if ($data->getCcExpMonth()) {
            $additional['cc_exp_month'] = $data->getCcExpMonth();
        }

        if ($data->getCcExpYear()) {
            $additional['cc_exp_year'] = $data->getCcExpYear();
        }

        if ($data->getCcCid()) {
            $additional['cc_cid'] = $data->getCcCid();
        }

        if ($data->getCcInstallment()) {
            $additional['cc_installment'] = $data->getCcInstallment();
        }

        if (Mage::helper('iopay_gateway')->getTaxfield() == 'iopay' && $data->getCcCpf()) {
            $additional['cc_cpf'] = $data->getCcCpf();
        }

        if ($data->getToken()) {
            $additional['token'] = $data->getToken();
            $info->setCcNumberEnc($data->getToken());
        }

        if ($additional) {
            $info->setAdditionalInformation($additional);
        }
        return $this;
    }

    /**
     * @param string $paymentAction
     * @param object $stateObject
     * @return bool
     */
    public function initialize($paymentAction, $stateObject)
    {
        $response = NULL;

        switch ($paymentAction) {
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE:
            case Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE:
                $response = $this->_placeOrder();
                break;
        }

        if ($response) {
            $stateObject->setState(Mage_Sales_Model_Order::STATE_NEW);
            $stateObject->setStatus('pending');
            $stateObject->setIsNotified(false);
            return true;
        }

        return false;
    }

    /**
     * @param Varien_Object $payment
     * @param float $amount
     * @return Mage_Payment_Model_Abstract|void
     */
    public function capture(Varien_Object $payment, $amount)
    {
        $this->log(" ----- Capture IOPay CreditCard ------");
    }

    /**
     * @return $this|false
     * @throws Mage_Core_Exception
     */
    public function _placeOrder()
    {
        try {
            $this->log(" ----- Place Order IOPay CreditCard ------");

            $payment            = $this->getInfoInstance();
            $order              = $payment->getOrder();
            $capture            = false;

            if ($this->getConfigPaymentAction() == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE) {
                $capture = true;
            }

            $customerIopayId = $this->helperComprador()->getIopayCustomerId($order);
            if (!$customerIopayId) {
                Mage::throwException('IoPay: Erro ao criar ou recuperar o id do comprador no cartão.');
            }

            $token = Mage::helper('iopay_gateway/authentication')->getToken();
            if (!$token) {
                Mage::throwException('IOPay: Erro ao recuperar o access token');
                return false;
            }

            $cardToken = $payment->getCcNumberEnc();
            if (!$cardToken || $cardToken == null) {
                Mage::throwException('Cartão não criptografado, verifique os dados informados e tente novamente...');
            }

            $installments               = (int) $payment->getAdditionalInformation('cc_installment');
            $orderTotalWithInstallments = $this->helper()->getOrderTotalWithInstallments($installments);

            $ccArray = array(
                "amount"        => round($orderTotalWithInstallments, 2) * 100,
                "currency"      => "BRL",
                "description"   => Mage::helper('iopay_gateway')->__("Pedido # %s na loja %s", $order->getIncrementId(), Mage::app()->getStore()->getName()),
                "token"         => $cardToken,
                "capture"       => 1,
                "statement_descriptor"   => Mage::app()->getStore()->getName(),
                "installment_plan" => array(
                    "number_installments" => $installments
                ),
                "io_seller_id"  => $this->helper()->getIopaySellerId(),
                "payment_type"  => "credit",
                "reference_id"  => (string)$order->getIncrementId(),
                "products"      => $this->helper()->getShoppingCart($order)
            );

            $this->log($ccArray);

            /* Check antifraud options */
            $antifraud_plan = Mage::helper('iopay_gateway')->getAntifraudPlan();
            $sessionId      = Mage::helper('iopay_gateway')->getSessionId();

            if ($antifraud_plan == 'with_anti_fraud' || $antifraud_plan == 'with_anti_fraud_insurance') {
                $ccArray['antifraud_sessid'] = $sessionId;

                $cpf = $payment->getAdditionalInformation('cc_cpf');
                if (!$cpf) {
                    $cpf = $this->helperComprador()->getCpf($order);
                }

                $ccArray['shipping'] = array(
                    "taxpayer_id"       => preg_replace("/[^0-9]/", "", $cpf),
                    "firstname"          => $order->getCustomerFirstname(),
                    "lastname"          => $order->getCustomerLastname(),
                    "address_1"         => $this->helperComprador()->getAddressData($order)['line1'],
                    "address_2"         => $this->helperComprador()->getAddressData($order)['line2'],
                    "address_3"         => $this->helperComprador()->getAddressData($order)['line3'],
                    "postal_code"       => $this->helperComprador()->getAddressData($order)['postal_code'],
                    "city"              => $this->helperComprador()->getAddressData($order)['city'],
                    "state"             => $this->helperComprador()->getAddressData($order)['state'],
                    "client_type"       => "pf",
                    "phone_number"      => $this->helperComprador()->getTelephone($order)
                );
            }

            $this->log(" ----- Calling IOPay API ------");
            $this->log($ccArray);

            $headers = array(
                "Authorization: Bearer {$token}",
                "cache-control: no-cache",
                "content-type: application/json",
            );

            $this->log($headers);

            $api = Mage::helper('iopay_gateway/api');
            $api->setHeader($headers);
            $api->setUri("/v1/transaction/new/{$customerIopayId}");
            $api->setData($ccArray);
            $api->connect();

            $response = $api->getResponse();

            $this->log($response);

            if (isset($response['success']['id'])) {
                $payment_type       = $response['success']['payment_type'];
                $payment_method     = $response['success']['payment_method'];
                $id                 = $payment_method['id'];

                try {
                    //succeeded,
                    //failed,
                    //canceled,
                    //pre_authorized,
                    //reversed,
                    //refunded,
                    //pending,
                    //new,
                    //partial_refunded,
                    //dispute,
                    //charged_back
                    switch ($response['success']['status']) {
                        case 'approved':
                        case 'succeeded':
                            break;
                        case 'canceled':
                        case 'failed':
                        case 'charged_back':
                            Mage::throwException("A transação falhou com o seguinte status: {$response['success']['status']}");
                            break;
                        default:
                            break;
                    }

                    $payment
                        ->setIopayCustomer($customerIopayId)
                        ->setIopayPaymentId($id)
                        ->setIopayPaymentType($payment_type)
                        ->save();

                    $payment->setAdditionalInformation('iopay_response',json_encode($response));
                    $payment->setCcStatusDescription("IoPay ID: {$response['success']['id']}");
                    $payment->setCcStatus($response['success']['status']);
                    $payment->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, json_encode($response['success']));
                    $payment->save();

                    /* Auto Invoice Order */
                    if ($this->helper()->getIopayautoInvoice()) {
                        $this->helper()->generateInvoice($order);
                    }

                } catch (Exception $e) {
                    Mage::throwException($e->getMessage());
                    $this->log('IOPay Credit Card Error Payment Save: ' . $e->getMessage());
                }
            } else {
                if (isset($response['error'])) {
                    $this->log($response);
                    $err = null;
                    foreach ($response['error'] as $group => $errors) {
                        foreach ($errors as $k => $v) {
                            $err .= "- Error [{$group}]: ".$v;
                        }
                    }

                    if (isset($response['error']['message_display'])) {
                        $err = $response['error']['message_display'];
                    }

                    if (isset($response['error']['message'])) {
                        $err = $response['error']['message'];
                    }

                    Mage::throwException($err);
                } else {
                    $err = json_encode($response);
                    Mage::throwException("IoPay Cartão - Erro desconhecido: {$err}");
                }
            }

            return $this;

        }  catch (Mage_Core_Exception $e) {
            Mage::throwException($e->getMessage());
            $this->log('IOPay card error: '.$e->getMessage());
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
            $this->log('IOPay card error: ' . $e->getMessage());
        }
    }

    /**
     * Helper
     * @return Mage_Core_Helper_Abstract|null
     */
    protected function helper() {
        return Mage::helper('iopay_gateway');
    }

    /**
     * Helper comprador
     * @return Mage_Core_Helper_Abstract|null
     */
    protected function helperComprador() {
        return Mage::helper('iopay_gateway/comprador');
    }

    /**
     * Write log
     * @param $message
     */
    protected function log($message) {
        Mage::helper('iopay_gateway')->log($message);
    }
}