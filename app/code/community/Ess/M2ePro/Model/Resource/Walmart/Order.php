<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Resource_Walmart_Order
    extends Ess_M2ePro_Model_Resource_Component_Child_Abstract
{
    protected $_isPkAutoIncrement = false;

    //########################################

    public function _construct()
    {
        $this->_init('M2ePro/Walmart_Order', 'order_id');
        $this->_isPkAutoIncrement = false;
    }

    //########################################

    public function getItemsTotal($orderId)
    {
        /** @var $collection Ess_M2ePro_Model_Resource_Walmart_Order_Collection */
        $collection = Mage::helper('M2ePro/Component_Walmart')->getCollection('Order_Item');
        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->addFieldToFilter('order_id', (int)$orderId);
        $collection->getSelect()->columns(
            array(
            'items_total' => new Zend_Db_Expr('SUM((`price`)*`qty`)')
            )
        );
        $collection->getSelect()->group('order_id');

        return round($collection->getFirstItem()->getData('items_total'), 2);
    }

    //########################################
}
