<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_Qty
    extends Ess_M2ePro_Model_Walmart_Listing_Product_Action_DataBuilder_Abstract
{
    //########################################

    /**
     * @return array
     */
    public function getData()
    {
        if (!isset($this->_cachedData['qty'])) {
            $this->_cachedData['qty'] = $this->getWalmartListingProduct()->getQty();
        }

        $data = array(
            'qty' => $this->_cachedData['qty'],
        );

        $this->checkQtyWarnings();

        return $data;
    }

    //########################################

    public function checkQtyWarnings()
    {
        $qtyMode = $this->getWalmartListing()->getWalmartSellingFormatTemplate()->getQtyMode();
        if ($qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT_FIXED ||
            $qtyMode == Ess_M2ePro_Model_Template_SellingFormat::QTY_MODE_PRODUCT) {
            $listingProductId = $this->getListingProduct()->getId();
            $productId = $this->getWalmartListingProduct()->getActualMagentoProduct()->getProductId();
            $storeId = $this->getListing()->getStoreId();

            if (!empty(Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'])) {
                $qtys = Ess_M2ePro_Model_Magento_Product::$statistics[$listingProductId][$productId][$storeId]['qty'];
                foreach ($qtys as $type => $override) {
                    $this->addQtyWarnings($type);
                }
            }
        }
    }

    public function addQtyWarnings($type)
    {
        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_MANAGE_STOCK_NO) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Manage Stock No" '.
                'field were taken into consideration.'
            );
        }

        if ($type === Ess_M2ePro_Model_Magento_Product::FORCING_QTY_TYPE_BACKORDERS) {
            $this->addWarningMessage(
                'During the Quantity Calculation the Settings in the "Backorders" '.
                'field were taken into consideration.'
            );
        }
    }

    //########################################
}
