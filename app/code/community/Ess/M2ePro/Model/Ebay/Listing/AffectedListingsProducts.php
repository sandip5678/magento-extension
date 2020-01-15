<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Listing_AffectedListingsProducts
    extends Ess_M2ePro_Model_Template_AffectedListingsProducts_Abstract
{
    //########################################

    public function getObjects(array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('listing_id', $this->_model->getId());

        if (isset($filters['template'])) {
            $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
            $templateManager->setTemplate($filters['template']);

            $collection->addFieldToFilter(
                $templateManager->getModeColumnName(), Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT
            );
        }

        return $collection->getItems();
    }

    public function getData($columns = '*', array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('listing_id', $this->_model->getId());

        if (isset($filters['template'])) {
            $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
            $templateManager->setTemplate($filters['template']);

            $collection->addFieldToFilter(
                $templateManager->getModeColumnName(), Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT
            );
        }

        if (is_array($columns) && !empty($columns)) {
            $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
            $collection->getSelect()->columns($columns);
        }

        return $collection->getItems();
    }

    public function getIds(array $filters = array())
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('listing_id', $this->_model->getId());

        if (isset($filters['template'])) {
            $templateManager = Mage::getModel('M2ePro/Ebay_Template_Manager');
            $templateManager->setTemplate($filters['template']);

            $collection->addFieldToFilter(
                $templateManager->getModeColumnName(), Ess_M2ePro_Model_Ebay_Template_Manager::MODE_PARENT
            );
        }

        return $collection->getAllIds();
    }

    //########################################
}
