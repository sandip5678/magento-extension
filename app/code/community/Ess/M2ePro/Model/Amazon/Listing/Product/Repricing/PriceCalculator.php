<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Listing getComponentListing()
 * @method Ess_M2ePro_Model_Amazon_Template_SellingFormat getComponentSellingFormatTemplate()
 * @method Ess_M2ePro_Model_Amazon_Listing_Product getComponentProduct()
 */
class Ess_M2ePro_Model_Amazon_Listing_Product_Repricing_PriceCalculator
    extends Ess_M2ePro_Model_Listing_Product_PriceCalculator
{
    //########################################

    protected function isPriceVariationModeParent()
    {
        return $this->getPriceVariationMode() == Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_VARIATION_MODE_PARENT;
    }

    protected function isPriceVariationModeChildren()
    {
        return $this->getPriceVariationMode()
            == Ess_M2ePro_Model_Amazon_Account_Repricing::PRICE_VARIATION_MODE_CHILDREN;
    }

    //########################################

    public function getProductValue()
    {
        if ($this->isSourceModeNone()) {
            return null;
        }

        return parent::getProductValue();
    }

    public function getVariationValue(Ess_M2ePro_Model_Listing_Product_Variation $variation)
    {
        if ($this->isSourceModeNone()) {
            return null;
        }

        return parent::getVariationValue($variation);
    }

    //########################################

    protected function getCurrencyForPriceConvert()
    {
        return $this->getComponentListing()->getAmazonMarketplace()->getDefaultCurrency();
    }

    //########################################
}
