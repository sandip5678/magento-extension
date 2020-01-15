<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Listing_Product_Category_Summary_Help
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('walmartListingProductSourceCategoriesSummaryHelp');
        // ---------------------------------------

        $this->setTemplate('M2ePro/walmart/listing/product/summary/help.phtml');
    }

    //########################################
}
