<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_List_Validator_Sku_General
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     */
    public function validate()
    {
        $sku = $this->getSku();

        if (empty($sku)) {
            $this->addMessage('SKU is not provided. Please, check Listing Settings.');
            return false;
        }

        if (strlen($sku) > Ess_M2ePro_Helper_Component_Amazon::SKU_MAX_LENGTH) {
            $this->addMessage('The length of SKU must be less than 40 characters.');
            return false;
        }

        $this->_data['sku'] = $sku;

        return true;
    }

    //########################################

    protected function getSku()
    {
        if (isset($this->_data['sku'])) {
            return $this->_data['sku'];
        }

        $sku = $this->getAmazonListingProduct()->getSku();
        if (!empty($sku)) {
            return $sku;
        }

        if ($this->getVariationManager()->isPhysicalUnit() &&
            $this->getVariationManager()->getTypeModel()->isVariationProductMatched()
        ) {
            $variations = $this->getListingProduct()->getVariations(true);
            if (empty($variations)) {
                throw new Ess_M2ePro_Model_Exception_Logic(
                    'There are no variations for a variation product.',
                    array(
                                                         'listing_product_id' => $this->getListingProduct()->getId()
                    )
                );
            }

            /** @var $variation Ess_M2ePro_Model_Listing_Product_Variation */
            $variation = reset($variations);
            $sku = $variation->getChildObject()->getSku();

            if (strlen($sku) >= Ess_M2ePro_Helper_Component_Amazon::SKU_MAX_LENGTH) {
                $sku = Mage::helper('M2ePro')->hashString($sku, 'md5', 'RANDOM_');
            }

            return $sku;
        }

        return $this->getAmazonListingProduct()->getListingSource()->getSku();
    }

    //########################################
}
