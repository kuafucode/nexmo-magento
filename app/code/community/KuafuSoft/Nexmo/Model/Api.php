<?php
class KuafuSoft_Nexmo_Model_Api
{
    const URL_REQUEST = 'https://api.nexmo.com/verify/json';
    const URL_CHECK = 'https://api.nexmo.com/verify/check/json';

    protected $_debug = false;

    /**
     * HTTP client
     *
     * @var Varien_Http_Client
     */
    protected $_httpClient;

    public function __construct()
    {
        $this->_httpClient = new Varien_Http_Client();
        $this->_httpClient
            ->setParameterGet('api_key', $this->_getConfig('key'))
            ->setParameterGet('api_secret', $this->_getConfig('secret'))
            ->setConfig(array('timeout' => $this->_getConfig('timeout')));
    }

    protected function _getConfig($key)
    {
        return Mage::getStoreConfig('ks_nexmo/settings/' . $key);
    }

    protected function _sendCode($brand, $number, $model)
    {
        if($this->_debug) {
            return true;
        }
        $response = $this->_httpClient
            ->setParameterGet('number', $number)
            ->setParameterGet('brand', $brand)
            ->setUri(self::URL_REQUEST)
            ->request('GET')
            ->getBody();

        Mage::log($response, null, 'nexmo.log');

        $response = Zend_Json::decode($response);
        if( isset($response['status']) ) {
            switch($response['status']) {
                case 0:
                case 10;
                    try {
                        $model->setNexmoId($response['request_id'])->save();
                    }
                    catch(Exception $e) {
                        Mage::logException($e);
                        return $e->getMessage();
                    }
                    return true;
                default:
                    if($response['error_text']) {
                        return $response['error_text'];
                    }
            }
        }
        else {
            return $this->_helper()->__('Wrong nexmo request response');
        }
    }

    public function sendAdminLoginCode($user)
    {
        $brand = $this->_helper()->__('admin code');
        //prevent wired password update
        $user->unsPassword();
        return $this->_sendCode($brand, $user->getPhone(), $user);
    }

    public function sendCartCode(Mage_Sales_Model_Quote $quote)
    {
        $brand = $this->_helper()->__('checkout code');
        return $this->_sendCode($brand, $quote->getBillingAddress()->getTelephone(), $quote);
    }

    public function check($requestId, $code)
    {
        if($this->_debug) {
            return true;
        }
        $response = $this->_httpClient
            ->setParameterGet('request_id', $requestId)
            ->setParameterGet('code', $code)
            ->setUri(self::URL_CHECK)
            ->request('GET')
            ->getBody();

        Mage::log($response, null, 'nexmo.log');

        $response = Zend_Json::decode($response);
        if( isset($response['status']) ) {
            switch($response['status']) {
                case 0:
                    return true;
                case 16:
                case 3:
                    return $this->_helper()->__('Wrong access code');
                case 101:
                    return $this->_helper()->__('Please request access code first');
                default:
                    return $this->_helper()->__('Invalid nexmo status');
            }
        }
        else {
            return $this->_helper()->__('Wrong nexmo check response');
        }
    }

    /**
     * @return KuafuSoft_Nexmo_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('ks_nexmo');
    }
}
