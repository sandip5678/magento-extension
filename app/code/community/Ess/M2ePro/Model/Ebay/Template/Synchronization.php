<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Template_Synchronization getParentObject()
 * @method Ess_M2ePro_Model_Resource_Ebay_Template_Synchronization getResource()
 */
class Ess_M2ePro_Model_Ebay_Template_Synchronization extends Ess_M2ePro_Model_Component_Child_Ebay_Abstract
{
    const LIST_ADVANCED_RULES_PREFIX   = 'ebay_template_synchronization_list_advanced_rules';
    const RELIST_ADVANCED_RULES_PREFIX = 'ebay_template_synchronization_relist_advanced_rules';
    const STOP_ADVANCED_RULES_PREFIX   = 'ebay_template_synchronization_stop_advanced_rules';

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Ebay_Template_Synchronization');
    }

    /**
     * @return string
     */
    public function getNick()
    {
        return Ess_M2ePro_Model_Ebay_Template_Manager::TEMPLATE_SYNCHRONIZATION;
    }

    //########################################

    /**
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function isLocked()
    {
        if (parent::isLocked()) {
            return true;
        }

        return (bool)Mage::getModel('M2ePro/Ebay_Listing')
                            ->getCollection()
                            ->addFieldToFilter(
                                'template_synchronization_mode',
                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                            )
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize() ||
               (bool)Mage::getModel('M2ePro/Ebay_Listing_Product')
                            ->getCollection()
                            ->addFieldToFilter(
                                'template_synchronization_mode',
                                Ess_M2ePro_Model_Ebay_Template_Manager::MODE_TEMPLATE
                            )
                            ->addFieldToFilter('template_synchronization_id', $this->getId())
                            ->getSize();
    }

    //########################################

    /**
     * @return bool
     */
    public function isListMode()
    {
        return $this->getData('list_mode') != 0;
    }

    /**
     * @return bool
     */
    public function isListStatusEnabled()
    {
        return $this->getData('list_status_enabled') != 0;
    }

    /**
     * @return bool
     */
    public function isListIsInStock()
    {
        return $this->getData('list_is_in_stock') != 0;
    }

    /**
     * @return bool
     */
    public function isListWhenQtyMagentoHasValue()
    {
        return $this->getData('list_qty_magento') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isListWhenQtyCalculatedHasValue()
    {
        return $this->getData('list_qty_calculated') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isListAdvancedRulesEnabled()
    {
        $rules = $this->getListAdvancedRulesFilters();
        return $this->getData('list_advanced_rules_mode') != 0 && !empty($rules);
    }

    public function getListAdvancedRulesFilters()
    {
        return $this->getData('list_advanced_rules_filters');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdateQtyMaxAppliedValueMode()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value_mode');
    }

    /**
     * @return bool
     */
    public function isReviseUpdateQtyMaxAppliedValueModeOn()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == 1;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateQtyMaxAppliedValueModeOff()
    {
        return $this->getReviseUpdateQtyMaxAppliedValueMode() == 0;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdateQtyMaxAppliedValue()
    {
        return (int)$this->getData('revise_update_qty_max_applied_value');
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdatePriceMaxAllowedDeviationMode()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation_mode');
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePriceMaxAllowedDeviationModeOn()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode() == 1;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePriceMaxAllowedDeviationModeOff()
    {
        return $this->getReviseUpdatePriceMaxAllowedDeviationMode() == 0;
    }

    // ---------------------------------------

    /**
     * @return int
     */
    public function getReviseUpdatePriceMaxAllowedDeviation()
    {
        return (int)$this->getData('revise_update_price_max_allowed_deviation');
    }

    // ---------------------------------------

    public function isPriceChangedOverAllowedDeviation($onlinePrice, $currentPrice)
    {
        if ((float)$onlinePrice == (float)$currentPrice) {
            return false;
        }

        if ((float)$onlinePrice <= 0) {
            return true;
        }

        if ($this->isReviseUpdatePriceMaxAllowedDeviationModeOff()) {
            return true;
        }

        $deviation = round(abs($onlinePrice - $currentPrice) / $onlinePrice * 100, 2);

        return $deviation > $this->getReviseUpdatePriceMaxAllowedDeviation();
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isReviseUpdateQty()
    {
        return $this->getData('revise_update_qty') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePrice()
    {
        return $this->getData('revise_update_price') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateTitle()
    {
        return $this->getData('revise_update_title') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateSubtitle()
    {
        return $this->getData('revise_update_sub_title') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateDescription()
    {
        return $this->getData('revise_update_description') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateImages()
    {
        return $this->getData('revise_update_images') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateCategories()
    {
        return $this->getData('revise_update_categories') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateShipping()
    {
        return $this->getData('revise_update_shipping') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdatePayment()
    {
        return $this->getData('revise_update_payment') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateReturn()
    {
        return $this->getData('revise_update_return') != 0;
    }

    /**
     * @return bool
     */
    public function isReviseUpdateOther()
    {
        return $this->getData('revise_update_other') != 0;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isRelistMode()
    {
        return $this->getData('relist_mode') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistFilterUserLock()
    {
        return $this->getData('relist_filter_user_lock') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistStatusEnabled()
    {
        return $this->getData('relist_status_enabled') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistIsInStock()
    {
        return $this->getData('relist_is_in_stock') != 0;
    }

    /**
     * @return bool
     */
    public function isRelistWhenQtyMagentoHasValue()
    {
        return $this->getData('relist_qty_magento') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistWhenQtyCalculatedHasValue()
    {
        return $this->getData('relist_qty_calculated') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isRelistAdvancedRulesEnabled()
    {
        $rules = $this->getRelistAdvancedRulesFilters();
        return $this->getData('relist_advanced_rules_mode') != 0 && !empty($rules);
    }

    public function getRelistAdvancedRulesFilters()
    {
        return $this->getData('relist_advanced_rules_filters');
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isStopMode()
    {
        return $this->getData('stop_mode') != 0;
    }

    /**
     * @return bool
     */
    public function isStopStatusDisabled()
    {
        return $this->getData('stop_status_disabled') != 0;
    }

    /**
     * @return bool
     */
    public function isStopOutOfStock()
    {
        return $this->getData('stop_out_off_stock') != 0;
    }

    /**
     * @return bool
     */
    public function isStopWhenQtyMagentoHasValue()
    {
        return $this->getData('stop_qty_magento') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isStopWhenQtyCalculatedHasValue()
    {
        return $this->getData('stop_qty_calculated') != Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE;
    }

    /**
     * @return bool
     */
    public function isStopAdvancedRulesEnabled()
    {
        $rules = $this->getStopAdvancedRulesFilters();
        return $this->getData('stop_advanced_rules_mode') != 0 && !empty($rules);
    }

    public function getStopAdvancedRulesFilters()
    {
        return $this->getData('stop_advanced_rules_filters');
    }

    //########################################

    public function getListWhenQtyMagentoHasValueType()
    {
        return $this->getData('list_qty_magento');
    }

    public function getListWhenQtyMagentoHasValueMin()
    {
        return $this->getData('list_qty_magento_value');
    }

    public function getListWhenQtyMagentoHasValueMax()
    {
        return $this->getData('list_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getListWhenQtyCalculatedHasValueType()
    {
        return $this->getData('list_qty_calculated');
    }

    public function getListWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('list_qty_calculated_value');
    }

    public function getListWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('list_qty_calculated_value_max');
    }

    // ---------------------------------------

    public function getRelistWhenQtyMagentoHasValueType()
    {
        return $this->getData('relist_qty_magento');
    }

    public function getRelistWhenQtyMagentoHasValueMin()
    {
        return $this->getData('relist_qty_magento_value');
    }

    public function getRelistWhenQtyMagentoHasValueMax()
    {
        return $this->getData('relist_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getRelistWhenQtyCalculatedHasValueType()
    {
        return $this->getData('relist_qty_calculated');
    }

    public function getRelistWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('relist_qty_calculated_value');
    }

    public function getRelistWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('relist_qty_calculated_value_max');
    }

    // ---------------------------------------

    public function getStopWhenQtyMagentoHasValueType()
    {
        return $this->getData('stop_qty_magento');
    }

    public function getStopWhenQtyMagentoHasValueMin()
    {
        return $this->getData('stop_qty_magento_value');
    }

    public function getStopWhenQtyMagentoHasValueMax()
    {
        return $this->getData('stop_qty_magento_value_max');
    }

    // ---------------------------------------

    public function getStopWhenQtyCalculatedHasValueType()
    {
        return $this->getData('stop_qty_calculated');
    }

    public function getStopWhenQtyCalculatedHasValueMin()
    {
        return $this->getData('stop_qty_calculated_value');
    }

    public function getStopWhenQtyCalculatedHasValueMax()
    {
        return $this->getData('stop_qty_calculated_value_max');
    }

    //########################################

    /**
     * @return array
     */
    public function getDefaultSettings()
    {
        return array_merge(
            $this->getListDefaultSettings(),
            $this->getReviseDefaultSettings(),
            $this->getRelistDefaultSettings(),
            $this->getStopDefaultSettings()
        );
    }

    /**
     * @return array
     */
    public function getListDefaultSettings()
    {
        return array(
            'list_mode'           => 1,
            'list_status_enabled' => 1,
            'list_is_in_stock'    => 1,

            'list_qty_magento'           => Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE,
            'list_qty_magento_value'     => '1',
            'list_qty_magento_value_max' => '10',

            'list_qty_calculated'           => Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE,
            'list_qty_calculated_value'     => '1',
            'list_qty_calculated_value_max' => '10',

            'list_advanced_rules_mode'    => 0,
            'list_advanced_rules_filters' => null
        );
    }

    /**
     * @return array
     */
    public function getReviseDefaultSettings()
    {
        return array(
            'revise_update_qty'                              => 1,
            'revise_update_qty_max_applied_value_mode'       => 1,
            'revise_update_qty_max_applied_value'            => 5,
            'revise_update_price'                            => 1,
            'revise_update_price_max_allowed_deviation_mode' => 1,
            'revise_update_price_max_allowed_deviation'      => 0,
            'revise_update_title'                            => 0,
            'revise_update_sub_title'                        => 0,
            'revise_update_description'                      => 0,
            'revise_update_images'                           => 0,
            'revise_update_categories'                       => 0,
            'revise_update_shipping'                         => 0,
            'revise_update_payment'                          => 0,
            'revise_update_return'                           => 0,
            'revise_update_other'                            => 0,
        );
    }

    /**
     * @return array
     */
    public function getRelistDefaultSettings()
    {
        return array(
            'relist_mode'             => 1,
            'relist_filter_user_lock' => 1,
            'relist_status_enabled'   => 1,
            'relist_is_in_stock'      => 1,

            'relist_qty_magento'           => Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE,
            'relist_qty_magento_value'     => '1',
            'relist_qty_magento_value_max' => '10',

            'relist_qty_calculated'           => Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE,
            'relist_qty_calculated_value'     => '1',
            'relist_qty_calculated_value_max' => '10',

            'relist_advanced_rules_mode'    => 0,
            'relist_advanced_rules_filters' => null
        );
    }

    /**
     * @return array
     */
    public function getStopDefaultSettings()
    {
        return array(
            'stop_mode' => 1,

            'stop_status_disabled' => 1,
            'stop_out_off_stock'   => 1,

            'stop_qty_magento'           => Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE,
            'stop_qty_magento_value'     => '0',
            'stop_qty_magento_value_max' => '10',

            'stop_qty_calculated'           => Ess_M2ePro_Model_Template_Synchronization::QTY_MODE_NONE,
            'stop_qty_calculated_value'     => '0',
            'stop_qty_calculated_value_max' => '10',

            'stop_advanced_rules_mode'    => 0,
            'stop_advanced_rules_filters' => null
        );
    }

    //########################################

    public function save()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::save();
    }

    public function delete()
    {
        Mage::helper('M2ePro/Data_Cache_Permanent')->removeTagValues('template_synchronization');
        return parent::delete();
    }

    //########################################
}
