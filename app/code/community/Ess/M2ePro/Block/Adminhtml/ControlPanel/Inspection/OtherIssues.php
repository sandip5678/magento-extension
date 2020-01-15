<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_OtherIssues
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelInspectionOtherIssues');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/inspection/otherIssues.phtml');
    }

    //########################################

    protected function isShown()
    {
        return $this->isGdLibraryUnAvailable() ||
               $this->isZendOpcacheAvailable() ||
               $this->isSystemLogNotEmpty();
    }

    //########################################

    public function isGdLibraryUnAvailable()
    {
        return !extension_loaded('gd') || !function_exists('gd_info');
    }

    public function isZendOpcacheAvailable()
    {
        return Mage::helper('M2ePro/Client_Cache')->isZendOpcacheAvailable();
    }

    public function isSystemLogNotEmpty()
    {
        if (!Mage::helper('M2ePro/Module_Database_Structure')->isTableExists('m2epro_system_log')) {
            return false;
        }

        $resource = Mage::getSingleton('core/resource');

        $tableName = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_system_log');

        return (int)$resource->getConnection('core_read')
                             ->select()
                             ->from($tableName, array(new Zend_Db_Expr('COUNT(*)')))
                             ->query()
                             ->fetchColumn() > 0;
    }

    //########################################
}
