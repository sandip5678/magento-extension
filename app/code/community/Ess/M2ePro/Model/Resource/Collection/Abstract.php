<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Model_Resource_Collection_Abstract
    extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    //########################################

    protected function _toOptionArray($valueField = 'id', $labelField = 'title', $additional = array())
    {
        return parent::_toOptionArray($valueField, $labelField, $additional);
    }

    protected function _toOptionHash($valueField = 'id', $labelField = 'title')
    {
        return parent::_toOptionHash($valueField, $labelField);
    }

    //########################################
}
