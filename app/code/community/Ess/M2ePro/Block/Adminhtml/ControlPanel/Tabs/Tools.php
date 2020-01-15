<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Tools extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelTools');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/tabs/tools.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild(
            'controlPanel_tools_m2epro_general',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_controlPanel_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_ControlPanel_Command::CONTROLLER_TOOLS_M2EPRO_GENERAL)
            )
        );

        $this->setChild(
            'controlPanel_tools_m2epro_install',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_controlPanel_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_ControlPanel_Command::CONTROLLER_TOOLS_M2EPRO_INSTALL)
            )
        );

        $this->setChild(
            'controlPanel_tools_magento',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_controlPanel_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_ControlPanel_Command::CONTROLLER_TOOLS_MAGENTO)
            )
        );

        $this->setChild(
            'controlPanel_tools_additional',
            $this->getLayout()->createBlock(
                'M2ePro/adminhtml_controlPanel_tabs_command_group',
                '',
                array('controller_name' => Ess_M2ePro_Helper_View_ControlPanel_Command::CONTROLLER_TOOLS_ADDITIONAL)
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################
}
