<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Magento_Product_Rule_Chooser_Category
    extends Mage_Adminhtml_Block_Catalog_Category_Checkboxes_Tree
{
    //########################################

    public function getLoadTreeUrl($expanded=null)
    {
        $params = array(
            '_current' => true,
            'id' => null,
            'store' => $this->getRequest()->getParam('store', 0)
        );

        if (($expanded === null && Mage::getSingleton('admin/session')->getIsTreeWasExpanded())
            || $expanded == true) {
            $params['expand_all'] = true;
        }

        return $this->getUrl('*/*/categoriesJson', $params);
    }

    //########################################
}
