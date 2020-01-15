<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Connector_OrderItem_Update_Status
    extends Ess_M2ePro_Model_Ebay_Connector_Command_RealTime
{
    /** @var $_orderItem Ess_M2ePro_Model_Order_Item */
    protected $_orderItem;

    //########################################

    public function setOrderItem(Ess_M2ePro_Model_Order_Item $orderItem)
    {
        $this->_orderItem = $orderItem;
        $this->_account   = $orderItem->getOrder()->getAccount();

        return $this;
    }

    //----------------------------------------

    /**
     * @return int
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getOrderChangeId()
    {
        if (isset($this->_params['change_id'])) {
            return (int)$this->_params['change_id'];
        }

        throw new Ess_M2ePro_Model_Exception_Logic('Order change id has not been set.');
    }

    //########################################

    protected function getCommand()
    {
        return array('orders', 'update', 'status');
    }

    protected function isNeedSendRequest()
    {
        return true;
    }

    public function getRequestData()
    {
        $action = Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP;
        if (!empty($this->_params['tracking_number']) && !empty($this->_params['carrier_code'])) {
            $action = Ess_M2ePro_Model_Ebay_Connector_Order_Dispatcher::ACTION_SHIP_TRACK;
        }

        $trackingNumber = !empty($this->_params['tracking_number']) ? $this->_params['tracking_number'] : null;
        $carrierCode = !empty($this->_params['carrier_code']) ? $this->_params['carrier_code'] : null;

        return array(
            'account'         => $this->_orderItem->getOrder()->getAccount()->getServerHash(),
            'action'          => $action,
            'item_id'         => $this->_orderItem->getChildObject()->getItemId(),
            'transaction_id'  => $this->_orderItem->getChildObject()->getTransactionId(),
            'tracking_number' => $trackingNumber,
            'carrier_code'    => $carrierCode
        );
    }

    //########################################

    public function process()
    {
        if (!$this->isNeedSendRequest()) {
            return false;
        }

        parent::process();

        foreach ($this->getResponse()->getMessages()->getEntities() as $message) {
            if (!$message->isError()) {
                continue;
            }

            $messageText = 'Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). Reason: %msg%';
            $this->_orderItem->getOrder()->addErrorLog(
                $messageText, array(
                    '!item_id' => $this->_orderItem->getChildObject()->getItemId(),
                    '!trn_id'  => $this->_orderItem->getChildObject()->getTransactionId(),
                    'msg'      => $message->getText(),
                )
            );
        }
    }

    //########################################

    protected function validateResponseData()
    {
        return true;
    }

    protected function prepareResponseData()
    {
        if ($this->getResponse()->isResultError()) {
            return;
        }

        $responseData = $this->getResponse()->getData();

        if (!isset($responseData['result']) || !$responseData['result']) {
            $message = 'Shipping status was not updated (Item: %item_id%, Transaction: %trn_id%). '.
                       'Reason: eBay Failure.';
            $this->_orderItem->getOrder()->addErrorLog(
                $message, array(
                    '!item_id' => $this->_orderItem->getChildObject()->getItemId(),
                    '!trn_id'  => $this->_orderItem->getChildObject()->getTransactionId(),
                )
            );

            return;
        }

        if (!empty($this->_params['tracking_number']) && !empty($this->_params['carrier_code'])) {
            $message = 'Tracking number "%num%" for "%code%" has been sent to eBay '.
                       '(Item: %item_id%, Transaction: %trn_id%).';
            $this->_orderItem->getOrder()->addSuccessLog(
                $message, array(
                    '!num' => $this->_params['tracking_number'],
                    'code' => $this->_params['carrier_code'],
                    '!item_id' => $this->_orderItem->getChildObject()->getItemId(),
                    '!trn_id'  => $this->_orderItem->getChildObject()->getTransactionId(),
                )
            );
        } else {
            $message = 'Order Item has been marked as Shipped (Item: %item_id%, Transaction: %trn_id%).';
            $this->_orderItem->getOrder()->addSuccessLog(
                $message, array(
                    '!item_id' => $this->_orderItem->getChildObject()->getItemId(),
                    '!trn_id'  => $this->_orderItem->getChildObject()->getTransactionId(),
                )
            );
        }

        Mage::getResourceModel('M2ePro/Order_Change')->deleteByIds(array($this->getOrderChangeId()));
    }

    //########################################
}