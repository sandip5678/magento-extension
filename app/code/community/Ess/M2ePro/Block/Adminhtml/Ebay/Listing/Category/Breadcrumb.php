<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Breadcrumb extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('ebayListingBreadcrumb');
        // ---------------------------------------

        $this->setTemplate('M2ePro/ebay/listing/category/breadcrumb.phtml');
    }

    //########################################
}
