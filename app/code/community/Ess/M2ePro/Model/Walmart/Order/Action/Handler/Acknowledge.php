<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Model_Walmart_Order_Item as OrderItem;

class Ess_M2ePro_Model_Walmart_Order_Action_Handler_Acknowledge
    extends Ess_M2ePro_Model_Walmart_Order_Action_Handler_Abstract
{
    //########################################

    public function isNeedProcess()
    {
        if (!$this->getWalmartOrder()->isCreated()) {
            return false;
        }

        if (!$this->getWalmartOrder()->canAcknowledgeOrder()) {
            return false;
        }

        return true;
    }

    //########################################

    protected function getServerCommand()
    {
        return array('orders', 'acknowledge', 'entity');
    }

    protected function getRequestData()
    {
        return array(
            'channel_order_id' => $this->getWalmartOrder()->getWalmartOrderId(),
        );
    }

    protected function processResult(array $responseData)
    {
        if (!isset($responseData['result']) || !$responseData['result']) {
            $this->processError();
            return;
        }

        $itemsStatuses = array();

        foreach ($this->getOrder()->getItemsCollection() as $item) {
            $item->setData('status', OrderItem::STATUS_ACKNOWLEDGED)->save();
            $itemsStatuses[$item->getData('walmart_order_item_id')] = OrderItem::STATUS_ACKNOWLEDGED;
        }

        $orderStatus = Mage::getModel('M2ePro/Walmart_Order_Helper')->getOrderStatus($itemsStatuses);
        $this->getOrder()->setData('status', $orderStatus);
        $this->getOrder()->save();

        $this->getOrder()->getLog()->addMessage(
            $this->getOrder()->getId(),
            Mage::helper('M2ePro')->__('Order was successfully acknowledged.'),
            Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS
        );
    }

    /**
     * @param Ess_M2ePro_Model_Connector_Connection_Response_Message[] $messages
     */
    protected function processError(array $messages = array())
    {
        if (empty($messages)) {
            $message = Mage::getModel('M2ePro/Connector_Connection_Response_Message');
            $message->initFromPreparedData(
                Mage::helper('M2ePro')->__('Order was not acknowledged due to Walmart error.'),
                Ess_M2ePro_Model_Connector_Connection_Response_Message::TYPE_ERROR
            );

            $messages = array($message);
        }

        foreach ($messages as $message) {
            $this->getOrder()->getLog()->addMessage(
                $this->getOrder()->getId(),
                $message->getText(),
                Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR
            );
        }
    }

    //########################################
}
