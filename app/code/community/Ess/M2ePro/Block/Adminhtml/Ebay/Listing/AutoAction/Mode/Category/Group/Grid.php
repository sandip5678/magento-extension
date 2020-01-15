<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_AutoAction_Mode_Category_Group_Grid
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Group_Grid
{
    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing_autoAction/getCategoryGroupGrid', array('_current' => true));
    }

    //########################################
}
