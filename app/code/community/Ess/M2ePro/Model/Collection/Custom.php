<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Collection_Custom extends Varien_Data_Collection
{
    const CONDITION_LIKE  = 'like';
    const CONDITION_MATCH = 'match';

    //########################################

    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }

        parent::load($printQuery, $logQuery);
        $this->_setIsLoaded(true);

        $this->_renderFilters()
             ->_renderOrders()
             ->_renderLimit();

        return $this;
    }

    //########################################

    protected function _renderFilters()
    {
        foreach ($this->getItems() as $key => $item) {
            /**@var Varien_Object $item */

            foreach ($this->_filters as $filter) {
                /**@var Varien_Object $filter */

                switch ($filter->getData('type')) {
                    case self::CONDITION_LIKE:
                        $this->_applyLikeFilter($item, $key, $filter);
                        break;

                    case self::CONDITION_MATCH:
                        $this->_applyMatchFilter($item, $key, $filter);
                        break;
                }
            }
        }

        return $this;
    }

    protected function _applyLikeFilter(Varien_Object $item, $itemKey, Varien_Object $filter)
    {
        $conditions = !is_array($filter->getData('value')) ? array($filter->getData('value'))
                                                           : $filter->getData('value');

        if (empty($conditions)) {
            return;
        }

        $match = false;
        foreach ($conditions as $condition) {
            if (strpos($item->getData($filter->getData('field')), $condition) !== false) {
                $match = true;
            }
        }

        !$match && $this->removeItemByKey($itemKey);
    }

    protected function _applyMatchFilter(Varien_Object $item, $itemKey, Varien_Object $filter)
    {
        $conditions = !is_array($filter->getData('value')) ? array($filter->getData('value'))
                                                           : $filter->getData('value');

        if (empty($conditions)) {
            return;
        }

        $match = in_array($item->getData($filter->getData('field')), $conditions, true);
        !$match && $this->removeItemByKey($itemKey);
    }

    //########################################

    /**
     * Sorting by only one column is supported
     * @return $this
     */
    protected function _renderOrders()
    {
        if (empty($this->_orders)) {
            return $this;
        }

        $orderColumn    = key($this->_orders);
        $orderDirection = current($this->_orders);

        $sortByColumn = array();
        foreach ($this->getItems() as $item) {
            /**@var Varien_Object $item */
            $sortByColumn[] = $item->getData($orderColumn);
        }

        $orderDirection === self::SORT_ORDER_ASC  && array_multisort($sortByColumn, SORT_ASC, $this->_items);
        $orderDirection === self::SORT_ORDER_DESC && array_multisort($sortByColumn, SORT_DESC, $this->_items);

        return $this;
    }

    //########################################

    protected function _renderLimit()
    {
        if ($this->_pageSize) {
            $this->_totalRecords = count($this->_items);
            $this->_items = array_splice($this->_items, $this->_pageSize * ($this->getCurPage() - 1), $this->_pageSize);
        }

        return $this;
    }

    //########################################
}
