<?php
class KuafuSoft_Nexmo_Block_Checkout_Onepage_Code extends Mage_Core_Block_Template
{
    public function getCodeUrl()
    {
        return $this->getUrl('nexmo/code/ordercode');
    }

    protected function _toHtml()
    {
        if(!Mage::getStoreConfigFlag('ks_nexmo/settings/enable_billing_verify')) {
            return '';
        }
        return parent::_toHtml();
    }
}