<?php
class KuafuSoft_Nexmo_Block_Checkout_Onepage_Code extends Mage_Core_Block_Template
{
    public function getCodeUrl()
    {
        return $this->getUrl('nexmo/code/ordercode');
    }
}