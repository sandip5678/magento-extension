<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_MainChecks
    extends Ess_M2ePro_Block_Adminhtml_ControlPanel_Inspection_Abstract
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('controlPanelInspectionMainChecks');
        // ---------------------------------------

        $this->setTemplate('M2ePro/controlPanel/inspection/mainChecks.phtml');
    }

    //########################################

    protected function _toHtml()
    {
        // ---------------------------------------
        $this->filesValidityData = array(
            'status' => $this->getFilesValidity(),
            'url' => $this->getUrl('*/adminhtml_controlPanel_tools_m2epro_install/checkFilesValidity')
        );
        $this->unwritableDirData = array(
            'status' => $this->getUnWritableDirectories(),
            'url' => $this->getUrl('*/adminhtml_controlPanel_tools_m2epro_install/showUnWritableDirectories')
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->tablesStructureValidityData = array(
            'status' => $this->getTablesStructureValidity(),
            'url' => $this->getUrl('*/adminhtml_controlPanel_tools_m2epro_install/checkTablesStructureValidity')
        );
        $this->configsValidityData = array(
            'status' => $this->getConfigsValidity(),
            'url' => $this->getUrl('*/adminhtml_controlPanel_tools_m2epro_install/checkConfigsValidity')
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->localPoolOverwrites = array(
            'status' => $this->getLocalPoolOverwrites(),
            'url' => $this->getUrl('*/adminhtml_controlPanel_tools_magento/showLocalPoolOverwrites')
        );
        // ---------------------------------------

        return parent::_toHtml();
    }

    //########################################

    protected function getFilesValidity()
    {
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('files', 'get', 'info');
        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (empty($responseData)) {
            return false;
        }

        $problems = false;
        $baseDir = Mage::getBaseDir() . '/';
        foreach ($responseData['files_info'] as $info) {
            if (!is_file($baseDir . $info['path'])) {
                $problems = true;
                break;
            }

            $fileContent = trim(file_get_contents($baseDir . $info['path']));
            $fileContent = str_replace(array("\r\n","\n\r",PHP_EOL), chr(10), $fileContent);

            if (Zend_Crypt::hash('md5', $fileContent) !== $info['hash']) {
                $problems = true;
                break;
            }
        }

        return $problems;
    }

    protected function getUnWritableDirectories()
    {
        $unWritableDirectories = Mage::helper('M2ePro/Module')->getUnWritableDirectories();

        if (empty($unWritableDirectories)) {
            return false;
        }

        return count($unWritableDirectories);
    }

    protected function getTablesStructureValidity()
    {
        $tablesInfo = Mage::helper('M2ePro/Module_Database_Structure')->getModuleTablesInfo();

        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'tables', 'get', 'diff',
            array('tables_info' => Mage::helper('M2ePro')->jsonEncode($tablesInfo))
        );

        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (!isset($responseData['diff'])) {
            return false;
        }

        return count($responseData['diff']);
    }

    protected function getConfigsValidity()
    {
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector('configs', 'get', 'info');
        $dispatcherObject->process($connectorObj);
        $responseData = $connectorObj->getResponseData();

        if (!isset($responseData['configs_info'])) {
            return false;
        }

        $originalData = $responseData['configs_info'];
        $currentData = array();

        foreach ($originalData as $tableName => $configInfo) {
            $currentData[$tableName] = Mage::helper('M2ePro/Module_Database_Structure')
                ->getConfigSnapshot($tableName);
        }

        $differences = array();

        foreach ($originalData as $tableName => $configInfo) {
            foreach ($configInfo as $codeHash => $item) {
                if (array_key_exists($codeHash, $currentData[$tableName])) {
                    continue;
                }

                $differences[] = array('table'    => $tableName,
                    'item'     => $item,
                    'solution' => 'insert');
            }
        }

        foreach ($currentData as $tableName => $configInfo) {
            foreach ($configInfo as $codeHash => $item) {
                if (array_key_exists($codeHash, $originalData[$tableName])) {
                    continue;
                }

                $differences[] = array('table'    => $tableName,
                    'item'     => $item,
                    'solution' => 'drop');
            }
        }

        return count($differences);
    }

    protected function getLocalPoolOverwrites()
    {
        $localPoolOverwrites = Mage::helper('M2ePro/Magento')->getLocalPoolOverwrites();
        return count($localPoolOverwrites);
    }

    //########################################
}
