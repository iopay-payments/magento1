<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Helper_Authentication extends IOPay_Gateway_Helper_Abstract
{

    /**
     * Method to get token from API
     * @return access_token
     */
    public function getToken() {
        try {

            $email      = $this->helper()->getIopayEmail();
            $secret     = $this->helper()->getIopaySecret();
            $sellerId   = $this->helper()->getIopaySellerId();

            $auth = array(
                "email"         => $email,
                "secret"        => $secret,
                "io_seller_id"  => $sellerId
            );

            $headers = array(
                "cache-control: no-cache",
                "content-type: application/json",
            );

            $api = Mage::helper('iopay_gateway/api');
            $api->setHeader($headers);
            $api->setUri("/auth/login?email={$email}&secret={$secret}&io_seller_id={$sellerId}");
            $api->setData($auth);
            $api->connect();

            $response = $api->getResponse();

            if (isset($response['error'])) {
                Mage::throwException('IoPay: Erro ao recuperar access_token: '.json_encode($response['error']));
                return false;
            } else {
                if (isset($response['access_token'])) {
                    return $response['access_token'];
                }
            }
        } catch (Exception $e) {
            $this->log("--- getToken Error ---");
            $this->log($e->getMessage());
            return false;
        }
    }

    /**
     * Method to get token from card
     * @return access_token
     */
    public function getCardToken() {
        try {

            $email      = $this->helper()->getIopayEmail();
            $secret     = $this->helper()->getIopaySecret();
            $sellerId   = $this->helper()->getIopaySellerId();

            $auth = array(
                "email"         => $email,
                "secret"        => $secret,
                "io_seller_id"  => $sellerId
            );

            $headers = array(
                "cache-control: no-cache",
                "content-type: application/json",
            );

            $api = Mage::helper('iopay_gateway/api');
            $api->setHeader($headers);
            $api->setUri("/v1/card/authentication");
            $api->setData($auth);
            $api->connect();

            $response = $api->getResponse();

            if (isset($response['error'])) {
                Mage::throwException('IoPay: Erro ao recuperar card access_token: '.json_encode($response['error']));
                return false;
            } else {
                if (isset($response['access_token'])) {
                    return $response['access_token'];
                }
            }
        } catch (Exception $e) {
            $this->log("--- getToken Error ---");
            $this->log($e->getMessage());
            return false;
        }
    }
}