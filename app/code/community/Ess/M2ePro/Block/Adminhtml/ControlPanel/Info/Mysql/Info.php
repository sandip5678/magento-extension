<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Info_Mysql_Info extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelAboutMysqlInfo');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/info/mysql/info.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->mySqlDatabaseName = Mage::helper('M2ePro/Magento')->getDatabaseName();
        $this->mySqlVersion = Mage::helper('M2ePro/Client')->getMysqlVersion();
        $this->mySqlApi = Mage::helper('M2ePro/Client')->getMysqlApiName();
        $this->mySqlPrefix = Mage::helper('M2ePro/Magento')->getDatabaseTablesPrefix();
        $this->mySqlSettings = Mage::helper('M2ePro/Client')->getMysqlSettings();

        return parent::_beforeToHtml();
    }

    //########################################
}
