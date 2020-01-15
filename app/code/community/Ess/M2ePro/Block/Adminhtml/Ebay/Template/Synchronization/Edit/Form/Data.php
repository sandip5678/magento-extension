<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Template_Synchronization_Edit_Form_Data extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayTemplateSynchronizationEditFormData');
        $this->setTemplate('M2ePro/ebay/template/synchronization/form/data.phtml');
    }

    //########################################

    protected function _beforeToHtml()
    {
        $this->setChild(
            'ebay_template_synchronization_edit_form_tabs_list', $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_template_synchronization_edit_form_tabs_list',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );
        $this->setChild(
            'ebay_template_synchronization_edit_form_tabs_relist', $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_template_synchronization_edit_form_tabs_relist',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );
        $this->setChild(
            'ebay_template_synchronization_edit_form_tabs_revise', $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_template_synchronization_edit_form_tabs_revise',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );
        $this->setChild(
            'ebay_template_synchronization_edit_form_tabs_stop', $this->getLayout()->createBlock(
                'M2ePro/adminhtml_ebay_template_synchronization_edit_form_tabs_stop',
                '',
                array(
                    'form_data' => $this->getFormData()
                )
            )
        );

        return parent::_beforeToHtml();
    }

    //########################################

    public function isCustom()
    {
        if (isset($this->_data['is_custom'])) {
            return (bool)$this->_data['is_custom'];
        }

        return false;
    }

    public function getTitle()
    {
        if ($this->isCustom()) {
            return isset($this->_data['custom_title']) ? $this->_data['custom_title'] : '';
        }

        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_synchronization');

        if ($template === null) {
            return '';
        }

        return $template->getTitle();
    }

    //########################################

    public function getFormData()
    {
        $template = Mage::helper('M2ePro/Data_Global')->getValue('ebay_template_synchronization');

        if ($template === null || $template->getId() === null) {
            return array();
        }

        return $template->getData();
    }

    //########################################
}
