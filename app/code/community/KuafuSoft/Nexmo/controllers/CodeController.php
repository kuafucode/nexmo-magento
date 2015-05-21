<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright  Copyright (c) 2006-2015 X.commerce, Inc. (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Index admin controller
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class KuafuSoft_Nexmo_NexmoController extends Mage_Core_Controller_Varien_Action
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
                    $this->_getApi()->sendAdminLoginCode($user);
                }
                else {
                    $result['success'] = true;
                    $result['message'] = Mage::helper('ks_nexmo')->__('No phone number bind to this account, you can login without access code');
                }
            }
        }

        $this->loadLayout();
        $this->renderLayout();
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
