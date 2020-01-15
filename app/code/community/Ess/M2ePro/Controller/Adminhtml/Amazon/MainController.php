<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    //########################################

    protected function getCustomViewNick()
    {
        return Ess_M2ePro_Helper_View_Amazon::NICK;
    }

    //########################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK);
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $this->setComponentPageHelpLink();

        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu(Ess_M2ePro_Helper_View_Amazon::MENU_ROOT_NODE_NICK);
        $tempResult->_title(Mage::helper('M2ePro/View_Amazon')->getMenuRootNodeLabel());
        return $tempResult;
    }

    //########################################

    protected function setComponentPageHelpLink($view = null)
    {
        $this->setPageHelpLink(Ess_M2ePro_Helper_Component_Amazon::NICK, $view);
    }

    //########################################
}
