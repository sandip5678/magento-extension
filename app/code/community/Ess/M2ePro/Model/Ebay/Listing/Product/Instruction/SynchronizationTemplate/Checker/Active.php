<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract as ChangeProcessorAbstract;

class Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Active
    extends Ess_M2ePro_Model_Ebay_Listing_Product_Instruction_SynchronizationTemplate_Checker_Abstract
{
    //########################################

    protected function getStopInstructionTypes()
    {
        return array(
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_ENABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_MODE_DISABLED,
            Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor::INSTRUCTION_TYPE_STOP_SETTINGS_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_QTY_DATA_POTENTIALLY_CHANGED,
            ChangeProcessorAbstract::INSTRUCTION_TYPE_PRODUCT_STATUS_DATA_POTENTIALLY_CHANGED,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_OTHER,
            Ess_M2ePro_Model_Listing::INSTRUCTION_TYPE_PRODUCT_MOVED_FROM_LISTING,
            Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_QTY_CHANGED,
            Ess_M2ePro_Model_Ebay_Listing_Product::INSTRUCTION_TYPE_CHANNEL_STATUS_CHANGED,
            Ess_M2ePro_Model_Ebay_Template_ChangeProcessor_Abstract::INSTRUCTION_TYPE_QTY_DATA_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_PRODUCT_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_STATUS_CHANGED,
            Ess_M2ePro_PublicServices_Product_SqlChange::INSTRUCTION_TYPE_QTY_CHANGED,
            Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract::INSTRUCTION_TYPE_MAGMI_PLUGIN_PRODUCT_CHANGED,
            Ess_M2ePro_Model_Cron_Task_Listing_Product_InspectDirectChanges::INSTRUCTION_TYPE,
        );
    }

    //########################################

    public function isAllowed()
    {
        if (!$this->_input->hasInstructionWithTypes($this->getStopInstructionTypes()) &&
            !$this->_input->hasInstructionWithTypes($this->getReviseInstructionTypes())
        ) {
            return false;
        }

        $listingProduct = $this->_input->getListingProduct();

        if ($listingProduct->isHidden()) {
            return false;
        }

        if (!$listingProduct->isRevisable() && !$listingProduct->isStoppable()) {
            return false;
        }

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }

        return true;
    }

    //########################################

    public function process(array $params = array())
    {
        $scheduledAction = $this->_input->getScheduledAction();
        if ($scheduledAction === null) {
            $scheduledAction = Mage::getModel('M2ePro/Listing_Product_ScheduledAction');
        }

        if ($this->_input->hasInstructionWithTypes($this->getStopInstructionTypes())) {
            if (!$this->isMeetStopRequirements()) {
                if ($scheduledAction->isActionTypeStop() && !$scheduledAction->isForce()) {
                    $this->getScheduledActionManager()->deleteAction($scheduledAction);
                    $scheduledAction->unsetData();
                }
            } else {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
                $ebayListingProduct = $this->_input->getListingProduct()->getChildObject();

                $actionType = Ess_M2ePro_Model_Listing_Product::ACTION_STOP;

                $additionalData = array(
                    'params' => $params,
                );

                $tags = array();

                if ($ebayListingProduct->isOutOfStockControlEnabled()) {
                    $actionType = Ess_M2ePro_Model_Listing_Product::ACTION_REVISE;
                    $additionalData['params']['replaced_action'] = Ess_M2ePro_Model_Listing_Product::ACTION_STOP;

                    $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
                    $configurator->disableAll()->allowQty()->allowVariations();

                    $tags[] = 'qty';

                    $additionalData['configurator'] = $configurator->getData();
                } else {
                    if ($scheduledAction->isActionTypeRevise()) {
                        $this->setPropertiesForRecheck($this->getPropertiesDataFromInputScheduledAction());
                    }
                }

                $scheduledAction->addData(
                    array(
                        'listing_product_id' => $this->_input->getListingProduct()->getId(),
                        'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
                        'action_type'        => $actionType,
                        'tag'                => '/'.implode('/', $tags).'/',
                        'additional_data'    => Mage::helper('M2ePro')->jsonEncode($additionalData),
                    )
                );

                if ($scheduledAction->getId()) {
                    $this->getScheduledActionManager()->updateAction($scheduledAction);
                } else {
                    $this->getScheduledActionManager()->addAction($scheduledAction);
                }
            }
        }

        $additionalData = $scheduledAction->getAdditionalData();

        if ($scheduledAction->isActionTypeStop() ||
            ($scheduledAction->isActionTypeRevise() &&
             isset($additionalData['params']['replaced_action']) &&
             $additionalData['params']['replaced_action'] == Ess_M2ePro_Model_Listing_Product::ACTION_STOP)
        ) {
            if ($this->_input->hasInstructionWithTypes($this->getReviseInstructionTypes())) {
                $this->setPropertiesForRecheck($this->getPropertiesDataFromInputInstructions());
            }

            return;
        }

        $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
        $configurator->disableAll();

        $tags = array();

        if ($scheduledAction->isActionTypeRevise()) {
            if ($scheduledAction->isForce()) {
                return;
            }

            $additionalData = $scheduledAction->getAdditionalData();

            if (isset($additionalData['configurator'])) {
                $configurator->setData($additionalData['configurator']);
            } else {
                $configurator->enableAll();
            }

            $tags = explode('/', $scheduledAction->getTag());
        }

        $tags = array_flip($tags);

        if ($this->_input->hasInstructionWithTypes($this->getReviseQtyInstructionTypes())) {
            if ($this->isMeetReviseQtyRequirements()) {
                $configurator->allowQty()->allowVariations();
                $tags['qty'] = true;
            } else {
                $configurator->disallowQty();
                unset($tags['qty']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePriceInstructionTypes())) {
            if ($this->isMeetRevisePriceRequirements()) {
                $configurator->allowPrice()->allowVariations();
                $tags['price'] = true;
            } else {
                $configurator->disallowPrice();
                unset($tags['price']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseTitleInstructionTypes())) {
            if ($this->isMeetReviseTitleRequirements()) {
                $configurator->allowTitle();
                $tags['title'] = true;
            } else {
                $configurator->disallowTitle();
                unset($tags['title']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseSubtitleInstructionTypes())) {
            if ($this->isMeetReviseSubtitleRequirements()) {
                $configurator->allowSubtitle();
                $tags['subtitle'] = true;
            } else {
                $configurator->disallowSubtitle();
                unset($tags['subtitle']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseDescriptionInstructionTypes())) {
            if ($this->isMeetReviseDescriptionRequirements()) {
                $configurator->allowDescription();
                $tags['description'] = true;
            } else {
                $configurator->disallowDescription();
                unset($tags['description']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseImagesInstructionTypes())) {
            if ($this->isMeetReviseImagesRequirements()) {
                $configurator->allowImages();

                if ($this->_input->hasInstructionWithTypes($this->getReviseVariationImagesInstructionTypes())) {
                    $configurator->allowVariations();
                }

                $tags['images'] = true;
            } else {
                $configurator->disallowImages();
                unset($tags['images']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseCategoriesInstructionTypes())) {
            if ($this->isMeetReviseCategoriesRequirements()) {
                $configurator->allowCategories();
                $tags['categories'] = true;
            } else {
                $configurator->disallowCategories();
                unset($tags['categories']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseShippingInstructionTypes())) {
            if ($this->isMeetReviseShippingRequirements()) {
                $configurator->allowShipping();
                $tags['shipping'] = true;
            } else {
                $configurator->disallowShipping();
                unset($tags['shipping']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getRevisePaymentInstructionTypes())) {
            if ($this->isMeetRevisePaymentRequirements()) {
                $configurator->allowPayment();
                $tags['payment'] = true;
            } else {
                $configurator->disallowPayment();
                unset($tags['payment']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseReturnInstructionTypes())) {
            if ($this->isMeetReviseReturnRequirements()) {
                $configurator->allowReturn();
                $tags['return'] = true;
            } else {
                $configurator->disallowReturn();
                unset($tags['return']);
            }
        }

        if ($this->_input->hasInstructionWithTypes($this->getReviseOtherInstructionTypes())) {
            if ($this->isMeetReviseOtherRequirements()) {
                $configurator->allowOther();
                $tags['other'] = true;
            } else {
                $configurator->disallowOther();
                unset($tags['other']);
            }
        }

        $types = $configurator->getAllowedDataTypes();
        if (empty($types) || (count($types) == 1 && $configurator->isVariationsAllowed())) {
            if ($scheduledAction->getId()) {
                $this->getScheduledActionManager()->deleteAction($scheduledAction);
            }

            return;
        }

        $tags = array_keys($tags);

        $scheduledAction->addData(
            array(
                'listing_product_id' => $this->_input->getListingProduct()->getId(),
                'component'          => Ess_M2ePro_Helper_Component_Ebay::NICK,
                'action_type'        => Ess_M2ePro_Model_Listing_Product::ACTION_REVISE,
                'tag'                => '/'.implode('/', $tags).'/',
                'additional_data' => Mage::helper('M2ePro')->jsonEncode(
                    array(
                        'params'       => $params,
                        'configurator' => $configurator->getData()
                    )
                ),
            )
        );

        if ($scheduledAction->getId()) {
            $this->getScheduledActionManager()->updateAction($scheduledAction);
        } else {
            $this->getScheduledActionManager()->addAction($scheduledAction);
        }
    }

    //########################################

    public function isMeetStopRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isStopMode()) {
            return false;
        }

        if (!$ebayListingProduct->isSetCategoryTemplate()) {
            return false;
        }

        $variationResource = Mage::getResourceModel('M2ePro/Listing_Product_Variation');

        if ($ebaySynchronizationTemplate->isStopStatusDisabled()) {
            if (!$listingProduct->getMagentoProduct()->isStatusEnabled()) {
                return true;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllStatusesDisabled(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return true;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isStopOutOfStock()) {
            if (!$listingProduct->getMagentoProduct()->isStockAvailability()) {
                return true;
            } else if ($ebayListingProduct->isVariationsReady()) {
                $temp = $variationResource->isAllDoNotHaveStockAvailabilities(
                    $listingProduct->getId(),
                    $listingProduct->getListing()->getStoreId()
                );

                if ($temp !== null && $temp) {
                    return true;
                }
            }
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyMagentoHasValue()) {
            $productQty = (int)$listingProduct->getMagentoProduct()->getQty(true);

            $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyMagentoHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($ebaySynchronizationTemplate->isStopWhenQtyCalculatedHasValue()) {
            $productQty = (int)$ebayListingProduct->getQty();

            $typeQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueType();
            $minQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMin();
            $maxQty = (int)$ebaySynchronizationTemplate->getStopWhenQtyCalculatedHasValueMax();

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_LESS &&
                $productQty <= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_MORE &&
                $productQty >= $minQty) {
                return true;
            }

            if ($typeQty == Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_BETWEEN &&
                $productQty >= $minQty && $productQty <= $maxQty) {
                return true;
            }
        }

        if ($ebaySynchronizationTemplate->isStopAdvancedRulesEnabled()) {
            $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
                array(
                    'store_id' => $listingProduct->getListing()->getStoreId(),
                    'prefix'   => Ess_M2ePro_Model_Ebay_Template_Synchronization::STOP_ADVANCED_RULES_PREFIX
                )
            );
            $ruleModel->loadFromSerialized($ebaySynchronizationTemplate->getStopAdvancedRulesFilters());

            if ($ruleModel->validate($listingProduct->getMagentoProduct()->getProduct())) {
                return true;
            }
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetReviseQtyRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
        $configurator->disableAll()->allowQty()->allowVariations();

        if (!$ebaySynchronizationTemplate->isReviseUpdateQty()) {
            return false;
        }

        $ebaySellingFormatTemplate = $ebayListingProduct->getEbaySellingFormatTemplate();

        if (!$ebayListingProduct->getOutOfStockControl() && $ebaySellingFormatTemplate->getOutOfStockControl()) {
            return true;
        }

        $isMaxAppliedValueModeOn = $ebaySynchronizationTemplate->isReviseUpdateQtyMaxAppliedValueModeOn();
        $maxAppliedValue = $ebaySynchronizationTemplate->getReviseUpdateQtyMaxAppliedValue();

        if (!$ebayListingProduct->isVariationsReady()) {
            $productQty = $ebayListingProduct->getQty();
            $channelQty = $ebayListingProduct->getOnlineQty() - $ebayListingProduct->getOnlineQtySold();

            // Check ReviseUpdateQtyMaxAppliedValue
            if ($isMaxAppliedValueModeOn && $productQty > $maxAppliedValue && $channelQty > $maxAppliedValue) {
                return false;
            }

            if ($productQty != $channelQty) {
                return true;
            }
        } else {
            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $ebayVariation = $variation->getChildObject();

                $productQty = $ebayVariation->getQty();
                $channelQty = $ebayVariation->getOnlineQty() - $ebayVariation->getOnlineQtySold();

                if ($productQty != $channelQty &&
                    (!$isMaxAppliedValueModeOn || $productQty <= $maxAppliedValue || $channelQty <= $maxAppliedValue)) {
                    return true;
                }
            }
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetRevisePriceRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        $configurator = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_Configurator');
        $configurator->disableAll()->allowPrice()->allowVariations();

        if (!$ebaySynchronizationTemplate->isReviseUpdatePrice()) {
            return false;
        }

        if (!$ebayListingProduct->isVariationsReady()) {
            if ($ebayListingProduct->isListingTypeFixed()) {
                $needRevise = $ebaySynchronizationTemplate->isPriceChangedOverAllowedDeviation(
                    $ebayListingProduct->getOnlineCurrentPrice(),
                    $ebayListingProduct->getFixedPrice()
                );

                if ($needRevise) {
                    return true;
                }
            }

            if ($ebayListingProduct->isListingTypeAuction()) {
                $needRevise = $ebaySynchronizationTemplate->isPriceChangedOverAllowedDeviation(
                    $ebayListingProduct->getOnlineStartPrice(),
                    $ebayListingProduct->getStartPrice()
                );

                if ($needRevise) {
                    return true;
                }

                $needRevise = $ebaySynchronizationTemplate->isPriceChangedOverAllowedDeviation(
                    $ebayListingProduct->getOnlineReservePrice(),
                    $ebayListingProduct->getReservePrice()
                );

                if ($needRevise) {
                    return true;
                }

                $needRevise = $ebaySynchronizationTemplate->isPriceChangedOverAllowedDeviation(
                    $ebayListingProduct->getOnlineBuyItNowPrice(),
                    $ebayListingProduct->getBuyItNowPrice()
                );

                if ($needRevise) {
                    return true;
                }
            }
        } else {
            $variations = $listingProduct->getVariations(true);

            foreach ($variations as $variation) {

                /** @var Ess_M2ePro_Model_Ebay_Listing_Product_Variation $ebayVariation */
                $ebayVariation = $variation->getChildObject();

                $needRevise = $ebaySynchronizationTemplate->isPriceChangedOverAllowedDeviation(
                    $ebayVariation->getOnlinePrice(),
                    $ebayVariation->getPrice()
                );

                if ($needRevise) {
                    return true;
                }
            }
        }

        return false;
    }

    // ---------------------------------------

    public function isMeetReviseTitleRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateTitle()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Title');
        $actionDataBuilder->setListingProduct($listingProduct);

        $actionData = $actionDataBuilder->getData();

        if ($actionData['title'] == $ebayListingProduct->getOnlineTitle()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetReviseSubtitleRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateSubtitle()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Subtitle');
        $actionDataBuilder->setListingProduct($listingProduct);

        $actionData = $actionDataBuilder->getData();

        if ($actionData['subtitle'] == $ebayListingProduct->getOnlineSubTitle()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetReviseDescriptionRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateDescription()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Description');
        $actionDataBuilder->setListingProduct($listingProduct);

        $actionData = $actionDataBuilder->getData();

        if ($actionData['description'] == $ebayListingProduct->getOnlineDescription()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetReviseImagesRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateImages()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Images');
        $actionDataBuilder->setListingProduct($listingProduct);
        $actionDataBuilder->setIsVariationItem($ebayListingProduct->isVariationsReady());

        if ($actionDataBuilder->getData() == $ebayListingProduct->getOnlineImages()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetReviseCategoriesRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateCategories()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Categories');
        $actionDataBuilder->setListingProduct($listingProduct);

        if ($actionDataBuilder->getData() == $ebayListingProduct->getOnlineCategoriesData()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetRevisePaymentRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdatePayment()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Payment');
        $actionDataBuilder->setListingProduct($listingProduct);

        if ($actionDataBuilder->getData() == $ebayListingProduct->getOnlinePaymentData()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetReviseShippingRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateShipping()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Shipping');
        $actionDataBuilder->setListingProduct($listingProduct);

        if ($actionDataBuilder->getData() == $ebayListingProduct->getOnlineShippingData()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetReviseReturnRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateReturn()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Return');
        $actionDataBuilder->setListingProduct($listingProduct);

        if ($actionDataBuilder->getData() == $ebayListingProduct->getOnlineReturnData()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    public function isMeetReviseOtherRequirements()
    {
        $listingProduct = $this->_input->getListingProduct();

        /** @var Ess_M2ePro_Model_Ebay_Listing_Product $ebayListingProduct */
        $ebayListingProduct = $listingProduct->getChildObject();

        $ebaySynchronizationTemplate = $ebayListingProduct->getEbaySynchronizationTemplate();

        if (!$ebaySynchronizationTemplate->isReviseUpdateOther()) {
            return false;
        }

        $actionDataBuilder = Mage::getModel('M2ePro/Ebay_Listing_Product_Action_DataBuilder_Other');
        $actionDataBuilder->setListingProduct($listingProduct);

        if ($actionDataBuilder->getData() == $ebayListingProduct->getOnlineOtherData()) {
            return false;
        }

        return true;
    }

    //########################################
}
