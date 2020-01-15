<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Repricing
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Amazon_Listing_Product_Repricing', 'listing_product_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getSkus(Ess_M2ePro_Model_Account $account, $filterSkus = null, $repricingDisabled = null)
    {
        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $listingProductCollection */
        $listingProductCollection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $listingProductCollection->getSelect()->joinLeft(
            array('l' => Mage::getResourceModel('M2ePro/Listing')->getMainTable()),
            'l.id = main_table.listing_id'
        );
        $listingProductCollection->addFieldToFilter('is_variation_parent', 0);
        $listingProductCollection->addFieldToFilter('is_repricing', 1);
        $listingProductCollection->addFieldToFilter('l.account_id', $account->getId());
        $listingProductCollection->addFieldToFilter('second_table.sku', array('notnull' => true));
        if (!empty($filterSkus)) {
            $listingProductCollection->addFieldToFilter('second_table.sku', array('in' => $filterSkus));
        }

        $listingProductCollection->addFieldToFilter('second_table.online_regular_price', array('notnull' => true));

        if ($repricingDisabled !== null) {
            $listingProductCollection->getSelect()->joinLeft(
                array('alpr' => $this->getMainTable()),
                'alpr.listing_product_id = main_table.id'
            );

            $listingProductCollection->addFieldToFilter('alpr.is_online_disabled', (int)$repricingDisabled);
        }

        $listingProductCollection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $listingProductCollection->getSelect()->columns(
            array('sku'  => 'second_table.sku')
        );

        return $listingProductCollection->getColumnValues('sku');
    }

    //########################################

    public function markAsProcessRequired(array $listingsProductsIds)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'update_date'         => Mage::helper('M2ePro')->getCurrentGmtDate(),
                'is_process_required' => 1
            ),
            array(
                'listing_product_id IN (?)' => array_unique($listingsProductsIds),
                'is_process_required = ?'   => 0,
            )
        );
    }

    public function resetProcessRequired(array $listingsProductsIds)
    {
        $this->_getWriteAdapter()->update(
            $this->getMainTable(),
            array(
                'update_date'         => Mage::helper('M2ePro')->getCurrentGmtDate(),
                'is_process_required' => 0
            ),
            array(
                'listing_product_id IN (?)' => array_unique($listingsProductsIds),
                'is_process_required = ?'   => 1,
            )
        );
    }

    //########################################
}
