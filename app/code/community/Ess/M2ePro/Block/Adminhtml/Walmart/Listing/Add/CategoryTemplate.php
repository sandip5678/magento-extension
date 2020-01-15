<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Add_CategoryTemplate
    extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    const MODE_SAME     = 'same';
    const MODE_MANUALLY = 'manually';
    const MODE_CATEGORY = 'category';

    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingAddCategoryTemplate');
        // ---------------------------------------

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $this->_headerText = Mage::helper('M2ePro')->__(
                'Set %component_name% Category Policy',
                Mage::helper('M2ePro/Component_Walmart')->getTitle()
            );
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Set Category Policy');
        }

        $listingId = $this->getRequest()->getParam('id');
        $url = $this->getUrl(
            '*/adminhtml_walmart_listing_productAdd/removeAddedProducts', array(
            'id' => $listingId,
            '_current' => true
            )
        );
        $this->_addButton(
            'back', array(
            'label'     => Mage::helper('M2ePro')->__('Back'),
            'class'     => 'back',
            'onclick'   => 'setLocation(\''.$url.'\');'
            )
        );

        $this->_addButton(
            'next', array(
            'label'     => Mage::helper('M2ePro')->__('Continue'),
            'class'     => 'scalable next',
            'onclick'   => "categoryTemplateModeFormSubmit()"
            )
        );

        $this->setTemplate('M2ePro/walmart/listing/add/category_template.phtml');
    }

    //########################################

    public function getHeaderWidth()
    {
        return 'width:50%;';
    }

    //########################################

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();

        $listing = Mage::helper('M2ePro/Component_Walmart')->getCachedObject(
            'Listing', $this->getRequest()->getParam('id')
        );

        $viewHeaderBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_listing_view_header', '',
            array('listing' => $listing)
        );

        $this->setChild('view_header', $viewHeaderBlock);

        // ---------------------------------------
        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Continue'),
                'onclick' => '',
                )
            );
        $this->setChild('mode_same_remember_pop_up_confirm_button', $buttonBlock);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Listing
     * @throws Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->listing === null) {
            $this->listing = Mage::helper('M2ePro/Component_Walmart')
                ->getObject('Listing', $listingId);
        }

        return $this->listing;
    }

    //########################################

    public function getProductsIds()
    {
        return $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');
    }

    //########################################

    public function getCategoryTemplateMode()
    {
        $mode = self::MODE_SAME;

        $listingAdditionalData = $this->getListing()->getSettings('additional_data');

        if (isset($listingAdditionalData['source']) && $listingAdditionalData['source'] === self::MODE_CATEGORY) {
                $mode = self::MODE_CATEGORY;
        }

        if (!empty($listingAdditionalData['category_template_mode'])) {
            $mode = $listingAdditionalData['category_template_mode'];
        }

        return $mode;
    }

    //########################################
}
