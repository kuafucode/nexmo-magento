<?php
class KuafuSoft_Nexmo_CodeController extends Mage_Core_Controller_Front_Action
{
    /**
     * Administrator login action
     */
    public function adminloginAction()
    {
        $result = array();
        if (Mage::getSingleton('admin/session')->isLoggedIn()) {
            $result['redirect'] = Mage::getUrl('adminhtml/index/index');
        }
        else {
            $loginData = $this->getRequest()->getPost('login');
            $user = Mage::getModel('admin/user');
            if($user->authenticate($loginData['username'], $loginData['password'])){
                if($user->getPhone()) {
                    $status = $this->_getApi()->sendAdminLoginCode($user);
                    if($status === true) {
                        $result['success'] = true;
                        $result['message'] = Mage::helper('ks_nexmo')->__('An access code has been sent to your phone number, please enter the code.');
                    }
                    else {
                        $result['success'] = false;
                        $result['message'] = $status;
                    }
                }
                else {
                    $result['success'] = true;
                    $result['message'] = Mage::helper('ks_nexmo')->__('No phone number bind to this account, you can login without access code');
                }
            }
            else {
                $result['success'] = false;
                $result['message'] = Mage::helper('ks_nexmo')->__('Invalid username or password');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Send verify request for billing
     */
    public function ordercodeAction()
    {
        if ($this->_expireAjax()) {
            return null;
        }
        $result = array();

        /* @var $quote Mage_Sales_Model_Quote */
        $quote = Mage::getSingleton('checkout/session')->getQuote();

        if ($phone = $quote->getBillingAddress()->getTelephone()) {
            $status = $this->_getApi()->sendCartCode($quote);
            if($status === true) {
                $result['success'] = true;
                $result['message'] = Mage::helper('ks_nexmo')->__('An access code has been sent to the billing phone number, please enter the code.');
            }
            else {
                $result['success'] = false;
                $result['message'] = $status;
            }
        }
        else {
            $result['success'] = false;
            $result['message'] = Mage::helper('ks_nexmo')->__('Please provide the phone number in billing address');
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Validate ajax request and redirect on failure
     *
     * @return bool
     */
    protected function _expireAjax()
    {
        $onepage = Mage::getSingleton('checkout/type_onepage');
        /* @var $quote Mage_Sales_Model_Quote */
        //$quote = Mage::getSingleton('checkout/session')->getQuote();
        $quote = $onepage->getQuote();
        if (!$quote->hasItems()
            || $quote->getHasError()
            || $quote->getIsMultiShipping()
        ) {
            var_dump($quote->getId(), $quote->hasItems(), $quote->getHasError(), $quote->getIsMultiShipping());exit();
            $this->_ajaxRedirectResponse();
            return true;
        }
        if (Mage::getSingleton('checkout/session')->getCartWasUpdated(true)) {
            $this->_ajaxRedirectResponse();
            return true;
        }
        return false;
    }

    /**
     * Send Ajax redirect response
     *
     * @return KuafuSoft_Nexmo_NexmoController
     */
    protected function _ajaxRedirectResponse()
    {
        $this->getResponse()
            ->setHeader('HTTP/1.1', '403 Session Expired')
            ->setHeader('Login-Required', 'true')
            ->sendResponse();
        return $this;
    }

    /**
     * @return KuafuSoft_Nexmo_Model_Api
     */
    protected function _getApi()
    {
        // to avoid old settings from previous calls, use getModel instead of getSingleton
        return Mage::getModel('ks_nexmo/api');
    }
}
