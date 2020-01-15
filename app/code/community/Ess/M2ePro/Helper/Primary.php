<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Primary extends Mage_Core_Helper_Abstract
{
    //########################################

    /**
     * @return Ess_M2ePro_Model_Config_Primary
     */
    public function getConfig()
    {
        return Mage::getSingleton('M2ePro/Config_Primary');
    }

    //########################################
}
