<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing extends Mage_Adminhtml_Block_Widget_Grid_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('ebayListing');
        $this->_blockGroup = 'M2ePro';
        $this->_controller = 'adminhtml_ebay_listing';

        $this->_headerText = '';

        $this->removeButton('back');
        $this->removeButton('reset');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $url = $this->getUrl('*/adminhtml_ebay_log/listing');
        $this->_addButton(
            'view_log', array(
            'label'     => Mage::helper('M2ePro')->__('Logs & Events'),
            'onclick'   => 'window.open(\''.$url.'\',\'_blank\');',
            'class'     => 'button_link'
            )
        );

        $url = $this->getUrl('*/adminhtml_ebay_listing_create/index', array('step' => 1, 'clear' => 1));
        $this->_addButton(
            'add', array(
            'label'     => Mage::helper('M2ePro')->__('Add Listing'),
            'onclick'   => 'setLocation(\'' .$url.'\')',
            'class'     => 'add'
            )
        );
    }

    //########################################

    protected function _toHtml()
    {
        $helpBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_help');

        return $helpBlock->toHtml() . parent::_toHtml();
    }

    //########################################
}
