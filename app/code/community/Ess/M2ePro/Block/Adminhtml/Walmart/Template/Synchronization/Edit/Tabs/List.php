<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Template_Synchronization_Edit_Tabs_List
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartTemplateSynchronizationEditTabsList');
        $this->setTemplate('M2ePro/walmart/template/synchronization/list.phtml');
    }

    //########################################

    public function getAdvancedRulesBlock()
    {
        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array(
                'prefix' => Ess_M2ePro_Model_Walmart_Template_Synchronization::LIST_ADVANCED_RULES_PREFIX,
                'use_custom_options' => true
            )
        );

        $formData = $this->getData('form_data');
        if (!empty($formData['list_advanced_rules_filters'])) {
            $ruleModel->loadFromSerialized($formData['list_advanced_rules_filters']);
        }

        $ruleBlock = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_magento_product_rule')
            ->setData(array('rule_model' => $ruleModel));

        return $ruleBlock;
    }

    //########################################
}
