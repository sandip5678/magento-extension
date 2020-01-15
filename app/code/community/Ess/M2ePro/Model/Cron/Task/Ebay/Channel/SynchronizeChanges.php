<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Ebay_Channel_SynchronizeChanges extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'ebay/channel/synchronize_changes';

    /**
     * @var int (in seconds)
     */
    protected $_interval = 300;

    //########################################

    /**
     * @return Ess_M2ePro_Model_Synchronization_Log
     */
    protected function getSynchronizationLog()
    {
        $synchronizationLog = parent::getSynchronizationLog();
        $synchronizationLog->setComponentMode(Ess_M2ePro_Helper_Component_Ebay::NICK);

        return $synchronizationLog;
    }

    //########################################

    protected function performActions()
    {
        $this->processItemsChanges();
        $this->processOrdersChanges();
    }

    //########################################

    protected function processItemsChanges()
    {
        $itemsProcessor = Mage::getModel('M2ePro/Cron_Task_Ebay_Channel_SynchronizeChanges_ItemsProcessor');

        $synchronizationLog = $this->getSynchronizationLog();
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_OTHER);

        $itemsProcessor->setSynchronizationLog($synchronizationLog);

        $operationHistory = $this->getOperationHistory()->getParentObject();
        if ($operationHistory !== null) {
            $itemsProcessor->setReceiveChangesToDate($operationHistory->getData('start_date'));
        }

        $itemsProcessor->process();
    }

    protected function processOrdersChanges()
    {
        $ordersProcessor = Mage::getModel('M2ePro/Cron_Task_Ebay_Channel_SynchronizeChanges_OrdersProcessor');

        $synchronizationLog = $this->getSynchronizationLog();
        $synchronizationLog->setSynchronizationTask(Ess_M2ePro_Model_Synchronization_Log::TASK_ORDERS);

        $ordersProcessor->setSynchronizationLog($synchronizationLog);

        $operationHistory = $this->getOperationHistory()->getParentObject();
        if ($operationHistory !== null) {
            $ordersProcessor->setReceiveOrdersToDate($operationHistory->getData('start_date'));
        }

        $ordersProcessor->process();
    }

    //########################################
}
