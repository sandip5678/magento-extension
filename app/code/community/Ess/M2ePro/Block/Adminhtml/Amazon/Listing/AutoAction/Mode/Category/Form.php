<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Listing_AutoAction_Mode_Category_Form
    extends Ess_M2ePro_Block_Adminhtml_Listing_AutoAction_Mode_Category_Form
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setTemplate('M2ePro/amazon/listing/auto_action/mode/category/form.phtml');
    }

    //########################################

    public function getDefault()
    {
        return array(
            'id' => null,
            'title' => null,
            'category_id' => null,
            'adding_mode' => Ess_M2ePro_Model_Listing::ADDING_MODE_NONE,
            'deleting_mode' => Ess_M2ePro_Model_Listing::DELETING_MODE_NONE,
            'adding_add_not_visible' => Ess_M2ePro_Model_Listing::AUTO_ADDING_ADD_NOT_VISIBLE_YES,
            'adding_description_template_id' => null
        );
    }

    //########################################
}
