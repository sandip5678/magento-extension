<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Amazon_Template_Synchronization_Edit_Tabs_Revise
    extends Mage_Adminhtml_Block_Widget
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('amazonTemplateSynchronizationEditTabsRevise');
        // ---------------------------------------

        $this->setTemplate('M2ePro/amazon/template/synchronization/revise.phtml');
    }

    //########################################
}
