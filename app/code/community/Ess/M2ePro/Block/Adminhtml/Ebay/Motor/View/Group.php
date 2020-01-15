<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Motor_View_Group extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    protected $_listingProductId;

    protected $_motorsType;

    //########################################

    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('M2ePro/ebay/motor/view/group.phtml');
    }

    protected function _beforeToHtml()
    {
        //------------------------------
        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_motor_view_group_grid');
        $block->setListingProductId($this->getListingProductId());
        $block->setMotorsType($this->getMotorsType());
        $this->setChild('view_group_grid', $block);
        //------------------------------

        //------------------------------
        $data = array(
            'style' => 'float: right;',
            'label'   => Mage::helper('M2ePro')->__('Close'),
            'onclick' => 'Windows.getFocusedWindow().close();'
        );
        $closeBtn = $this->getLayout()->createBlock('adminhtml/widget_button')->setData($data);
        $this->setChild('motor_close_btn', $closeBtn);
        //------------------------------

        return parent::_beforeToHtml();
    }

    //########################################

    /**
     * @return null
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getListingProductId()
    {
        if ($this->_listingProductId === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Listing Product ID was not set.');
        }

        return $this->_listingProductId;
    }

    /**
     * @param null $listingProductId
     */
    public function setListingProductId($listingProductId)
    {
        $this->_listingProductId = $listingProductId;
    }

    //########################################

    public function setMotorsType($motorsType)
    {
        $this->_motorsType = $motorsType;
    }

    public function getMotorsType()
    {
        if ($this->_motorsType === null) {
            throw new Ess_M2ePro_Model_Exception_Logic('Motors type not set.');
        }

        return $this->_motorsType;
    }

    //########################################
}
