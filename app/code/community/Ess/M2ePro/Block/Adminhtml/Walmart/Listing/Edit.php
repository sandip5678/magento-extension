<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Edit extends Mage_Adminhtml_Block_Widget_Form_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingEdit');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_walmart_listing';
        $this->_mode = 'edit';
        // ---------------------------------------

        // Set header text
        // ---------------------------------------
        $listingData = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $headerText = Mage::helper('M2ePro')->__(
                'Edit %component_name% Listing Settings "%listing_title%"',
                $componentName,
                $this->escapeHtml($listingData['title'])
            );
        } else {
            $headerText = Mage::helper('M2ePro')->__(
                'Edit Listing Settings "%listing_title%"',
                $this->escapeHtml($listingData['title'])
            );
        }

        $this->_headerText = $headerText;
        // ---------------------------------------

        // Set buttons actions
        // ---------------------------------------
        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');
        // ---------------------------------------

        if ($this->getRequest()->getParam('back') !== null) {
            // ---------------------------------------
            $url = Mage::helper('M2ePro')->getBackUrl(
                '*/adminhtml_walmart_listing/index'
            );
            $this->_addButton(
                'back', array(
                'label'     => Mage::helper('M2ePro')->__('Back'),
                'onclick'   => 'WalmartListingSettingsHandlerObj.back_click(\''.$url.'\')',
                'class'     => 'back'
                )
            );
            // ---------------------------------------
        }

        // ---------------------------------------
        $this->_addButton(
            'auto_action', array(
            'label'     => Mage::helper('M2ePro')->__('Auto Add/Remove Rules'),
            'onclick'   => 'ListingAutoActionHandlerObj.loadAutoActionHtml();'
            )
        );
        // ---------------------------------------

        $backUrl = Mage::helper('M2ePro')->getBackUrlParam('list');

        // ---------------------------------------
        $url = $this->getUrl(
            '*/adminhtml_walmart_listing/save',
            array(
                'id'    => $listingData['id'],
                'back'  => $backUrl
            )
        );
        $this->_addButton(
            'save', array(
            'label'     => Mage::helper('M2ePro')->__('Save'),
            'onclick'   => 'WalmartListingSettingsHandlerObj.save_click(\'' . $url . '\')',
            'class'     => 'save'
            )
        );
        // ---------------------------------------

        // ---------------------------------------
        $this->_addButton(
            'save_and_continue', array(
            'label'     => Mage::helper('M2ePro')->__('Save And Continue Edit'),
            'onclick'   => 'WalmartListingSettingsHandlerObj.save_and_edit_click(\''.$url.'\', 1)',
            'class'     => 'save'
            )
        );
        // ---------------------------------------
    }

    //########################################

    public function getFormHtml()
    {
        $listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $urls = Mage::helper('M2ePro')->getControllerActions(
            'adminhtml_walmart_listing_autoAction',
            array(
                'listing_id' => $this->getRequest()->getParam('id'),
                'component' => Ess_M2ePro_Helper_Component_Walmart::NICK
            )
        );
        $urls = Mage::helper('M2ePro')->jsonEncode($urls);

        /** @var $helper Ess_M2ePro_Helper_Data */
        $helper = Mage::helper('M2ePro');

        $translations = Mage::helper('M2ePro')->jsonEncode(
            array(
            'Auto Add/Remove Rules' => $helper->__('Auto Add/Remove Rules'),
            'Based on Magento Categories' => $helper->__('Based on Magento Categories'),
            'You must select at least 1 Category.' => $helper->__('You must select at least 1 Category.'),
            'Rule with the same Title already exists.' => $helper->__('Rule with the same Title already exists.')
            )
        );

        $js = <<<HTML
<script type="text/javascript">

    M2ePro.url.add({$urls});
    M2ePro.translator.add({$translations});

    ListingAutoActionHandlerObj = new WalmartListingAutoActionHandler();

</script>
HTML;

        return $viewHeaderBlock->toHtml() .  parent::getFormHtml() . $js;
    }

    //########################################
}
