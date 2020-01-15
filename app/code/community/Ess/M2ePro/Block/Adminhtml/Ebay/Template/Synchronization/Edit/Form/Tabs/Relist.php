<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Tabs_Relist
    extends Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Data
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayTemplateSynchronizationEditFormTabsRelist');
        $this->setTemplate('M2ePro/ebay/template/synchronization/form/tabs/relist.phtml');
    }

    //########################################

    public function getDefault()
    {
        return Mage::getSingleton('M2ePro/Ebay_Template_Synchronization')->getRelistDefaultSettings();
    }

    public function getAdvancedRulesBlock()
    {
        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array(
                'prefix' => Ess_M2ePro_Model_Ebay_Template_Synchronization::RELIST_ADVANCED_RULES_PREFIX,
                'use_custom_options' => true
            )
        );

        $formData = $this->getData('form_data');
        if (!empty($formData['relist_advanced_rules_filters'])) {
            $ruleModel->loadFromSerialized($formData['relist_advanced_rules_filters']);
        }

        $ruleBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_magento_product_rule')
            ->setData(array('rule_model' => $ruleModel));

        return $ruleBlock;
    }

    //########################################
}
