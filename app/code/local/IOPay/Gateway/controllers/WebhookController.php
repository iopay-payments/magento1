<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_WebhookController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action to receive webhook from IoPay API
     * Controller /iopay/webhook/
     * @throws Mage_Core_Exception
     */
    public function indexAction() {
        $data = json_decode(file_get_contents("php://input"), true);
        $json = json_encode($data);

        $this->helper()->log("--- IoPay Webhook ---");
        $this->helper()->log($data);

        if ($data['status'] && $data['reference_id']) {
            $orderIncrementId   = $data['reference_id'];
            $status             = $data['status'];
            $order              = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);

            if ($order->getId()) {
                $payment = $order->getPayment();
                $payment->setCcStatus($status);
                $payment->save();

                /* Insert comment on order comments */
                $order->addStatusHistoryComment("IoPay Return: {$json}")->save();

                $token = Mage::helper('iopay_gateway/authentication')->getToken();

                //Check on IoPay API status transaction
                $this->helper()->log(" ----- Consulting Payment by Webhook ------");
                $headers = array(
                    "Authorization: Bearer {$token}",
                    "cache-control: no-cache",
                    "content-type: application/json",
                );

                $this->helper()->log($headers);

                $id  = $data['id'];
                $api = Mage::helper('iopay_gateway/api');
                $api->setHeader($headers);
                $api->setUri("/v1/transaction/get/{$id}");
                $api->connect(false);

                $response = $api->getResponse();

                $this->helper()->log($response);

                if (isset($response['success']['id'])) {
                    $status = $response['success']['status'];
                    $this->helper()->log("Transation {$id} - status {$status}");
                    /* Check webhook status return */
                    switch ($status) {
                        case 'approved':
                        case 'succeeded':
                            if ($this->helper()->getIopayautoInvoice()) {
                                $this->helper()->generateInvoice($order);
                            }
                            break;
                        case 'canceled':
                        case 'failed':
                        case 'charged_back':
                            if ($order->canCancel()) {
                                $order->cancel()->save();
                            }
                            break;
                        default:
                            break;
                    }
                }


            } else {
                Mage::throwException("Order {$orderIncrementId} not found.");
            }
        } else {
            Mage::throwException('Empty data.');
        }
    }

    //reconsulta pelo id

    /**
     * Helper
     * @return Mage_Core_Helper_Abstract|null
     */
    protected function helper() {
        return Mage::helper('iopay_gateway');
    }
}