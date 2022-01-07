<?php

/**
 *
 * @category   IoPay
 * @package    IoPay_Gateway
 * @author     Proj3ct
 * @copyright 2022 Proj3ct
 */

class IOPay_Gateway_Helper_Comprador extends IOPay_Gateway_Helper_Abstract
{

    /**
     * Return CPF from order
     * @param $order
     * @return string|string[]|null
     */
    public function getCpf($order) {
        $payment            = $order->getPayment();
        $billing            = $order->getBillingAddress()->getData();

        /* Get admin configuration with cpf field */
        switch ($this->helper()->getTaxfield()) {
            case 'iopay':
                $cpf = $payment->getAdditionalInformation('iopay_cpf');
                break;
            case 'address':
                $cpf = $billing['vat_id'];
                break;
            case 'customer':
                $cpf = $order->getCustomerTaxvat();
                break;
            default:
                $cpf = $billing['vat_id'];
                break;
        }

        return preg_replace("/[^0-9]/", "", $cpf);
    }

    /**
     * Return telephone
     * @param $order
     * @return mixed
     */
    public function getTelephone($order) {
        $billing = $order->getBillingAddress()->getData();
        return $billing['telephone'];
    }

    /**
     * Send data to API to create customer on IoPay
     * @param $order
     * @return false|mixed
     */
    public function createComprador($order) {
        try {
            $this->log("--- IOPay Criando Comprador ---");

            $token = Mage::helper('iopay_gateway/authentication')->getToken();
            if (!$token) {
                Mage::throwException('Comprador: Erro ao recuperar o access token');
                return false;
            }

            $payment            = $order->getPayment();
            $billing            = $order->getBillingAddress()->getData();
            $customerFirstName  = $order->getCustomerFirstname();
            $customerLastName   = $order->getCustomerLastname();
            $customerEmail      = $order->getCustomerEmail();
            $customerPhone      = $billing['telephone'];
            $customerGender     = $order->getCustomerGender();
            $address            = $this->getAddressData($order);
            $cpf                = $this->getCpf($order);

            $comprador = array(
                'first_name'     => $customerFirstName,
                'last_name'     => $customerLastName,
                'email'         => $customerEmail,
                'taxpayer_id'   => $cpf,
                'phone_number'  => $customerPhone,
                'gender'        => ($customerGender == 1 ? 'male' : 'female'),
                'address'       => $address
            );

            $headers = array(
                "Authorization: Bearer {$token}",
                "cache-control: no-cache",
                "content-type: application/json",
            );

            $api = Mage::helper('iopay_gateway/api');
            $api->setHeader($headers);
            $api->setUri("/v1/customer/new");
            $api->setData($comprador);
            $api->connect();

            $response = $api->getResponse();

            if (isset($response['error'])) {
                Mage::throwException('Comprador: Erro ao criar comprador');
                return false;
            } else {
                if (isset($response['success']['id'])) {
                    return $response['success']['id'];
                }
            }

            return false;

        } catch (Exception $e) {
            $this->log("--- createComprador Error ---");
            $this->log($e->getMessage());
            return false;
        }
    }

    /**
     * Return customer ID from API
     * @param $id
     * @return false
     */
    public function getComprador($id) {
        try {
            $this->log("--- IOPay Recuperando Comprador ---");

            $token = Mage::helper('iopay_gateway/authentication')->getToken();
            if (!$token) {
                Mage::throwException('Comprador: Erro ao recuperar o access token');
                return false;
            }

            $headers = array(
                "Authorization: Bearer {$token}",
                "cache-control: no-cache",
                "content-type: application/json",
            );

            $api = Mage::helper('iopay_gateway/api');
            $api->setHeader($headers);
            $api->setUri("/v1/customer/get/{$id}");
            $api->connect();

            $response = $api->getResponse();

            $this->log($response);

        } catch (Exception $e) {
            $this->log("--- getComprador Error ---");
            $this->log($e->getMessage());
            return false;
        }
    }

    /**
     * Get customer ID from magento
     * @param $order
     * @return array|false|mixed|null
     */
    public function getIopayCustomerId($order) {
        try {
            $this->log(" --- Buscando Comprador ---");
            if (Mage::getSingleton('customer/session')->isLoggedIn()) {
                $customerSession = Mage::getSingleton('customer/session')->getCustomer();
                $customer        = Mage::getModel('customer/customer')->load($customerSession->getId());
                $iopayId         = $customer->getData('iopayid');

                /* Check if not exists iopayid saved on customer */
                if (!$iopayId) {
                    $this->log("Comprador não encontrado no customer");
                    $iopayId    = $this->createComprador($order);
                    try {
                        $websiteId = Mage::app()->getWebsite()->getId();
                        $store = Mage::app()->getStore();
                        $customer->setWebsiteId($websiteId)->setStore($store)->setData('iopayid', $iopayId);
                        $customer->setIopayid($iopayId);
                        $customer->save();
                    } catch (Exception $e) {
                        $this->log("--- Save Customer ID Error ---");
                        $this->log($e->getMessage());
                    }
                }

                $this->log(" customer logged ");
                $this->log(" Comprador criado: {$iopayId} ");
            } else {
                $this->log(" customer not logged");
                $iopayId = $this->createComprador($order);
            }

            return $iopayId;

        } catch (Exception $e) {
            $this->log("--- getIopayCustomerId Error ---");
            $this->log($e->getMessage());
            return false;
        }
    }

    /**
     * Retrieves address data
     *
     * @param $address
     * @return string
     */
    public function getAddressData($order)
    {
        $address        = $order->getShippingAddress();
        $addressData    = array();

        if ($address) {
            $addressData = array(
                "line1"         => $this->_getAddressStreet($address),
                "line2"         => $this->_getAddressStreetNumber($address),
                "line3"         => ($this->_getAddressComplement($address) ? $this->_getAddressComplement($address) : "-"),
                "neighborhood"  => $this->_getAddressNeighborhood($address),
                "city"          => $address->getCity(),
                "state"         => (strlen($address->getRegion()) > 2 ? $this->getUf($address->getRegion()) : $address->getRegion()),
                "postal_code"   => $this->_getAddressPostalCode($address)
            );
        }

        return $addressData;
    }

    /**
     * Retrieves address street
     *
     * @param $address
     * @return string
     */
    protected function _getAddressStreet($address)
    {
        return $address->getStreet(1);
    }

    /**
     * Retrieves address street number
     *
     * @param $address
     * @return string
     */
    protected function _getAddressStreetNumber($address)
    {
        return ($address->getStreet(2)) ? $address->getStreet(2) : 'SN';
    }

    /**
     * Retrieves address complement
     *
     * @param $address
     * @return string
     */
    protected function _getAddressComplement($address)
    {
        return $address->getStreet(3);
    }

    /**
     * Retrieves address neighborhood
     *
     * @param $address
     * @return string
     */
    protected function _getAddressNeighborhood($address)
    {
        return $address->getStreet(4);
    }

    /**
     * Retrieves address postal code
     *
     * @param $address
     * @return string
     */
    protected function _getAddressPostalCode($address)
    {
        return preg_replace('/[^0-9]/', '', $address->getPostcode());
    }

    /**
     * Return Uf by Region
     * @param $estado
     * @return false|int|string
     */
    public function getUf($estado) {
        $estadosBrasileiros = array(
            'AC'=>'Acre',
            'AL'=>'Alagoas',
            'AP'=>'Amapá',
            'AM'=>'Amazonas',
            'BA'=>'Bahia',
            'CE'=>'Ceará',
            'DF'=>'Distrito Federal',
            'ES'=>'Espírito Santo',
            'GO'=>'Goiás',
            'MA'=>'Maranhão',
            'MT'=>'Mato Grosso',
            'MS'=>'Mato Grosso do Sul',
            'MG'=>'Minas Gerais',
            'PA'=>'Pará',
            'PB'=>'Paraíba',
            'PR'=>'Paraná',
            'PE'=>'Pernambuco',
            'PI'=>'Piauí',
            'RJ'=>'Rio de Janeiro',
            'RN'=>'Rio Grande do Norte',
            'RS'=>'Rio Grande do Sul',
            'RO'=>'Rondônia',
            'RR'=>'Roraima',
            'SC'=>'Santa Catarina',
            'SP'=>'São Paulo',
            'SE'=>'Sergipe',
            'TO'=>'Tocantins'
        );
        $uf = array_search($estado, $estadosBrasileiros);
        return $uf;
    }
}