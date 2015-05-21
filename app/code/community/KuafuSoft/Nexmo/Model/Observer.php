<?php
class KuafuSoft_Nexmo_Model_Observer
{
    public function validateAdminAccessCode(Varien_Event_Observer $observer)
    {
        $user = $observer->geUser();
        if($user->getPhone()) {
            if(!$user->getNexmoId()) {
                Mage::throwException(Mage::helper('ks_nexmo')->__('Please request access code first'));
                return $this;
            }
            $status = $this->_getApi()->check($user->getNexmoId(), Mage::app()->getRequest()->getParam('nexmo'));
            if(true === $status) {
                $user->setNexmoId('')->save();
            }
            else {
                Mage::throwException($status);
            }
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
