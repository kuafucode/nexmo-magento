<?php
class KuafuSoft_Nexmo_Model_Observer
{
    public function validateAdminAccessCode(Varien_Event_Observer $observer)
    {
        if(!Mage::getStoreConfigFlag('ks_nexmo/settings/enable_admin_verify')) {
            return $this;
        }
        $user = $observer->getUser();
        if($user->getPhone()) {
            if(!$user->getNexmoId()) {
                Mage::throwException(Mage::helper('ks_nexmo')->__('Please request access code first'));
                return $this;
            }
            $login = Mage::app()->getRequest()->getParam('login');
            $status = $this->_getApi()->check($user->getNexmoId(), $login['nexmo']);
            if(true === $status) {
                //prevent wired password update
                $user->unsPassword();
                $user->setNexmoId('')->save();
            }
            else {
                Mage::throwException($status);
            }
        }
        return $this;
    }

    public function sendBillingCode(Varien_Event_Observer $observer)
    {
        /* @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getControllerAction();
        if(!Mage::getStoreConfigFlag('ks_nexmo/settings/enable_billing_verify')) {
            return $this;
        }
        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
        if($phone = $quote->getBillingAddress()->getTelephone()) {
            $status = $this->_getApi()->sendCartCode($quote);
            if(is_string($status)) {
                $result = array();
                $result['success']  = false;
                $result['error']    = $status;
                $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            }
        }
        return $this;
    }

    public function validateBillingNumber(Varien_Event_Observer $observer)
    {
        /* @var $action Mage_Core_Controller_Varien_Action */
        $action = $observer->getControllerAction();
        if(Mage::getStoreConfigFlag('ks_nexmo/settings/enable_billing_verify')) {
            /* @var $quote Mage_Sales_Model_Quote */
            $quote = Mage::getSingleton('checkout/type_onepage')->getQuote();
            if($quote->getNexmoId() && ($nexmo = $action->getRequest()->getPost('nexmo'))) {
                if(isset($nexmo['code'])) {
                    $status = $this->_getApi()->check($quote->getNexmoId(), $nexmo['code']);
                    if(true === $status) {
                        $quote->setNexmoId('')->save();
                        return $this;
                    }
                    else {
                        $result = array();
                        $result['success']  = false;
                        $result['error']    = true;
                        $result['error_messages'] = $status;
                        $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
                        $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
                        return $this;
                    }
                }
            }
            $result = array();
            $result['success']  = false;
            $result['error']    = true;
            $result['error_messages'] = Mage::helper('ks_nexmo')->__('Nexmo code not found.');
            $action->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
            $action->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
        }
        return $this;
    }

    /**
     * @return KuafuSoft_Nexmo_Model_Api
     */
    protected function _getApi()
    {
        return Mage::getModel('ks_nexmo/api');
    }
}
