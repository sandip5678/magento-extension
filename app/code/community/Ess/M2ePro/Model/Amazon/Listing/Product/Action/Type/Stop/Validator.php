<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Stop_Validator
    extends Ess_M2ePro_Model_Amazon_Listing_Product_Action_Type_Validator
{
    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception
     */
    public function validate()
    {
        $params = $this->getParams();

        if (empty($params['remove']) && !$this->validateBlocked()) {
            return false;
        }

        if ($this->getVariationManager()->isRelationParentType() && !$this->validateParentListingProductFlags()) {
            return false;
        }

        if (!$this->validatePhysicalUnitAndSimple()) {
            return false;
        }

        if ($this->getAmazonListingProduct()->isAfnChannel()) {
            if (!empty($params['remove'])) {
                $this->addMessage('Stop Action for FBA Items is impossible as their Quantity is unknown.');
                $this->getListingProduct()->deleteInstance();
                $this->getListingProduct()->isDeleted(true);
            } else {
                $this->addMessage(
                    'Stop Action for FBA Items is impossible as their Quantity is unknown. You can run
                    Revise Action for such Items, but the Quantity value will be ignored.'
                );
            }

            return false;
        }

        if (!$this->getListingProduct()->isListed() || !$this->getListingProduct()->isStoppable()) {
            if (empty($params['remove'])) {
                $this->addMessage('Item is not active or not available');
            } else {
                $removeHandler = Mage::getModel(
                    'M2ePro/Amazon_Listing_Product_RemoveHandler',
                    array('listing_product' => $this->getListingProduct())
                );
                $removeHandler->process();
            }

            return false;
        }

        if (!$this->validateSku()) {
            return false;
        }

        return true;
    }

    //########################################
}