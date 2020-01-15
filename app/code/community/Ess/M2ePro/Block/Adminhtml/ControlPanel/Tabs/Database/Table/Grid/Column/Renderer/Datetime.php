<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_ControlPanel_Tabs_Database_Table_Grid_Column_Renderer_Datetime extends
    Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Datetime
{
    //########################################

    public function render(Varien_Object $row)
    {
        if ($data = $this->_getValue($row)) {
            return $data;
        }

        return $this->getColumn()->getDefault();
    }

    //########################################

    public function escapeHtml($data, $allowedTags = null)
    {
        return $data;
    }

    //########################################
}
