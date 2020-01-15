<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Amazon_Order|Ess_M2ePro_Model_Ebay_Order|Ess_M2ePro_Model_Walmart_Order getChildObject()
 */
class Ess_M2ePro_Model_Order extends Ess_M2ePro_Model_Component_Parent_Abstract
{
    const ADDITIONAL_DATA_KEY_IN_ORDER = 'm2epro_order';

    const MAGENTO_ORDER_CREATION_FAILED_NO  = 0;
    const MAGENTO_ORDER_CREATION_FAILED_YES = 1;

    protected $_account = null;

    protected $_marketplace = null;

    protected $_magentoOrder = null;

    protected $_shippingAddress = null;

    /** @var Ess_M2ePro_Model_Resource_Order_Item_Collection */
    protected $_itemsCollection = null;

    protected $_proxy = null;

    /** @var Ess_M2ePro_Model_Order_Reserve */
    protected $_reserve = null;

    /** @var Ess_M2ePro_Model_Order_Log */
    protected $_logModel = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Order');
    }

    //########################################

    public function deleteInstance()
    {
        if ($this->isLocked()) {
            return false;
        }

        foreach ($this->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */
            $item->deleteInstance();
        }

        $this->deleteChildInstance();

        Mage::getResourceModel('M2ePro/Order_Change_Collection')
            ->addFieldToFilter('order_id', $this->getId())
            ->walk('deleteInstance');

         Mage::getResourceModel('M2ePro/Order_Note_Collection')
             ->addFieldToFilter('order_id', $this->getId())
             ->walk('deleteInstance');

        $this->_account         = null;
        $this->_magentoOrder    = null;
        $this->_itemsCollection = null;
        $this->_proxy           = null;

        $this->delete();

        return true;
    }

    //########################################

    public function getAccountId()
    {
        return $this->getData('account_id');
    }

    public function getMarketplaceId()
    {
        return $this->getData('marketplace_id');
    }

    public function getMagentoOrderId()
    {
        return $this->getData('magento_order_id');
    }

    public function isMagentoOrderCreationFailed()
    {
        return (bool)(int)$this->getData('magento_order_creation_failure');
    }

    public function getMagentoOrderCreationFailsCount()
    {
        return (int)$this->getData('magento_order_creation_fails_count');
    }

    public function getMagentoOrderCreationLatestAttemptDate()
    {
        return $this->getData('magento_order_creation_latest_attempt_date');
    }

    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * @return int
     */
    public function getReservationState()
    {
        return (int)$this->getData('reservation_state');
    }

    public function getAdditionalData()
    {
        return $this->getSettings('additional_data');
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Account $account
     * @return $this
     */
    public function setAccount(Ess_M2ePro_Model_Account $account)
    {
        $this->_account = $account;
        return $this;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Account
     */
    public function getAccount()
    {
        if ($this->_account === null) {
            $this->_account = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(), 'Account', $this->getAccountId()
            );
        }

        return $this->_account;
    }

    //########################################

    /**
     * @param Ess_M2ePro_Model_Marketplace $marketplace
     * @return $this
     */
    public function setMarketplace(Ess_M2ePro_Model_Marketplace $marketplace)
    {
        $this->_marketplace = $marketplace;
        return $this;
    }

    /**
     * @throws LogicException
     * @return Ess_M2ePro_Model_Marketplace
     */
    public function getMarketplace()
    {
        if ($this->_marketplace === null) {
            $this->_marketplace = Mage::helper('M2ePro/Component')->getCachedComponentObject(
                $this->getComponentMode(), 'Marketplace', $this->getMarketplaceId()
            );
        }

        return $this->_marketplace;
    }

    //########################################

    /**
     * @return Mage_Core_Model_Store
     */
    public function getStore()
    {
        return Mage::app()->getStore($this->getStoreId());
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Reserve
     */
    public function getReserve()
    {
        if ($this->_reserve === null) {
            $this->_reserve = Mage::getModel('M2ePro/Order_Reserve', $this);
        }

        return $this->_reserve;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Log
     */
    public function getLog()
    {
        if (!$this->_logModel) {
            $this->_logModel = Mage::getModel('M2ePro/Order_Log');
            $this->_logModel->setComponentMode($this->getComponentMode());
        }

        return $this->_logModel;
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Resource_Order_Item_Collection
     */
    public function getItemsCollection()
    {
        if ($this->_itemsCollection === null) {
            $this->_itemsCollection = Mage::helper('M2ePro/Component')
                                          ->getComponentCollection($this->getComponentMode(), 'Order_Item')
                                          ->addFieldToFilter('order_id', $this->getId());

            foreach ($this->_itemsCollection as $item) {
                /** @var $item Ess_M2ePro_Model_Order_Item */
                $item->setOrder($this);
            }
        }

        return $this->_itemsCollection;
    }

    // ---------------------------------------

    /**
     * Check whether the order has only single item ordered
     *
     * @return bool
     */
    public function isSingle()
    {
        return $this->getItemsCollection()->getSize() == 1;
    }

    /**
     * Check whether the order has multiple items ordered
     *
     * @return bool
     */
    public function isCombined()
    {
        return $this->getItemsCollection()->getSize() > 1;
    }

    // ---------------------------------------

    /**
     * Get instances of the channel items (Ebay_Item, Amazon_Item etc)
     *
     * @return array
     */
    public function getChannelItems()
    {
        $channelItems = array();

        foreach ($this->getItemsCollection()->getItems() as $item) {
            $channelItem = $item->getChildObject()->getChannelItem();

            if ($channelItem === null) {
                continue;
            }

            $channelItems[] = $channelItem;
        }

        return $channelItems;
    }

    // ---------------------------------------

    /**
     * Check whether the order has items, listed by M2E Pro (also true for mapped 3rd party listings)
     *
     * @return bool
     */
    public function hasListingItems()
    {
        $channelItems = $this->getChannelItems();

        return !empty($channelItems);
    }

    /**
     * Check whether the order has items, listed by 3rd party software
     *
     * @return bool
     */
    public function hasOtherListingItems()
    {
        $channelItems = $this->getChannelItems();

        return count($channelItems) != $this->getItemsCollection()->getSize();
    }

    //########################################

    public function addLog($description, $type, array $params = array(), array $links = array())
    {
        /** @var $log Ess_M2ePro_Model_Order_Log */
        $log = $this->getLog();

        if (!empty($params)) {
            $description = Mage::helper('M2ePro/Module_Log')->encodeDescription($description, $params, $links);
        }

        $log->addMessage($this->getId(), $description, $type);
    }

    public function addSuccessLog($description, array $params = array(), array $links = array())
    {
        $this->addLog($description, Ess_M2ePro_Model_Log_Abstract::TYPE_SUCCESS, $params, $links);
    }

    public function addNoticeLog($description, array $params = array(), array $links = array())
    {
        $this->addLog($description, Ess_M2ePro_Model_Log_Abstract::TYPE_NOTICE, $params, $links);
    }

    public function addWarningLog($description, array $params = array(), array $links = array())
    {
        $this->addLog($description, Ess_M2ePro_Model_Log_Abstract::TYPE_WARNING, $params, $links);
    }

    public function addErrorLog($description, array $params = array(), array $links = array())
    {
        $this->addLog($description, Ess_M2ePro_Model_Log_Abstract::TYPE_ERROR, $params, $links);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        if ($this->_shippingAddress === null) {
            $this->_shippingAddress = $this->getChildObject()->getShippingAddress();
        }

        return $this->_shippingAddress;
    }

    //########################################

    public function setMagentoOrder($order)
    {
        $this->_magentoOrder = $order;
        return $this;
    }

    /**
     * @return null|Mage_Sales_Model_Order
     */
    public function getMagentoOrder()
    {
        if ($this->getMagentoOrderId() === null) {
            return null;
        }

        if ($this->_magentoOrder === null) {
            $this->_magentoOrder = Mage::getModel('sales/order')->load($this->getMagentoOrderId());
        }

        return $this->_magentoOrder->getId() !== null ? $this->_magentoOrder : null;
    }

    //########################################

    public function addCreatedMagentoShipment(Mage_Sales_Model_Order_Shipment $magentoShipment)
    {
        $additionalData = $this->getAdditionalData();
        $additionalData['created_shipments_ids'][] = $magentoShipment->getId();
        $this->setSettings('additional_data', $additionalData)->save();

        return $this;
    }

    public function isMagentoShipmentCreatedByOrder(Mage_Sales_Model_Order_Shipment $magentoShipment)
    {
        $additionalData = $this->getAdditionalData();
        if (empty($additionalData['created_shipments_ids']) || !is_array($additionalData['created_shipments_ids'])) {
            return false;
        }

        return in_array($magentoShipment->getId(), $additionalData['created_shipments_ids']);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Order_Proxy
     */
    public function getProxy()
    {
        if ($this->_proxy === null) {
            $this->_proxy = $this->getChildObject()->getProxy();
        }

        return $this->_proxy;
    }

    //########################################

    /**
     * Find the store, where order should be placed
     * @throws Ess_M2ePro_Model_Exception
     */
    public function associateWithStore()
    {
        $storeId = $this->getStoreId() ? $this->getStoreId() : $this->getChildObject()->getAssociatedStoreId();
        $store = Mage::getModel('core/store')->load($storeId);

        if ($store->getId() === null) {
            throw new Ess_M2ePro_Model_Exception('Store does not exist.');
        }

        if ($this->getStoreId() != $store->getId()) {
            $this->setData('store_id', $store->getId())->save();
        }

        if (!Mage::getStoreConfig('payment/m2epropayment/active', $store)) {
            throw new Ess_M2ePro_Model_Exception(
                'Payment method "M2E Pro Payment" is disabled in Magento Configuration.'
            );
        }

        if (!Mage::getStoreConfig('carriers/m2eproshipping/active', $store)) {
            throw new Ess_M2ePro_Model_Exception(
                'Shipping method "M2E Pro Shipping" is disabled in Magento Configuration.'
            );
        }
    }

    //########################################

    /**
     * Associate each order item with product in magento
     * @throws Exception|null
     */
    public function associateItemsWithProducts()
    {
        foreach ($this->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */
            $item->associateWithProduct();
        }
    }

    //########################################

    public function isReservable()
    {
        if ($this->getMagentoOrderId() !== null) {
            return false;
        }

        if ($this->getReserve()->isPlaced()) {
            return false;
        }

        if (!$this->getChildObject()->isReservable()) {
            return false;
        }

        foreach ($this->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */

            if (!$item->isReservable()) {
                return false;
            }
        }

        return true;
    }

    //########################################

    public function canCreateMagentoOrder()
    {
        if ($this->getMagentoOrderId() !== null) {
            return false;
        }

        if (!$this->getChildObject()->canCreateMagentoOrder()) {
            return false;
        }

        foreach ($this->getItemsCollection()->getItems() as $item) {
            /** @var $item Ess_M2ePro_Model_Order_Item */

            if (!$item->canCreateMagentoOrder()) {
                return false;
            }
        }

        return true;
    }

    //########################################

    protected function beforeCreateMagentoOrder($canCreateExistOrder)
    {
        if ($this->getMagentoOrderId() !== null && !$canCreateExistOrder) {
            throw new Ess_M2ePro_Model_Exception('Magento Order is already created.');
        }

        if (method_exists($this->getChildObject(), 'beforeCreateMagentoOrder')) {
            $this->getChildObject()->beforeCreateMagentoOrder();
        }

        $reserve = $this->getReserve();

        if ($reserve->isPlaced()) {
            $reserve->setFlag('order_reservation', true);
            $reserve->release();
        }
    }

    public function createMagentoOrder($canCreateExistOrder = false)
    {
        try {
            // Check if we are wrapped by an another MySql transaction
            // ---------------------------------------
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            if ($transactionLevel = $connection->getTransactionLevel()) {
                Mage::helper('M2ePro/Module_Logger')->process(
                    array(
                        'transaction_level' => $transactionLevel
                    ),
                    'MySql Transaction Level Problem'
                );

                while ($connection->getTransactionLevel()) {
                    $connection->rollBack();
                }
            }

            // ---------------------------------------

            // Store must be initialized before products
            // ---------------------------------------
            $this->associateWithStore();
            $this->associateItemsWithProducts();
            // ---------------------------------------

            $this->beforeCreateMagentoOrder($canCreateExistOrder);

            // Create magento order
            // ---------------------------------------
            $proxy = $this->getProxy()->setStore($this->getStore());

            /** @var $magentoQuoteBuilder Ess_M2ePro_Model_Magento_Quote */
            $magentoQuoteBuilder = Mage::getModel('M2ePro/Magento_Quote', $proxy);
            $magentoQuoteBuilder->buildQuote();

            /** @var $magentoOrderBuilder Ess_M2ePro_Model_Magento_Order */
            $magentoOrderBuilder = Mage::getModel('M2ePro/Magento_Order', $magentoQuoteBuilder->getQuote());
            $magentoOrderBuilder->setAdditionalData(
                array(
                self::ADDITIONAL_DATA_KEY_IN_ORDER => $this
                )
            );
            $magentoOrderBuilder->buildOrder();

            $this->_magentoOrder = $magentoOrderBuilder->getOrder();

            $magentoOrderId = $this->getMagentoOrderId();
            if (empty($magentoOrderId)) {
                $this->addData(
                    array(
                        'magento_order_id'                           => $this->_magentoOrder->getId(),
                        'magento_order_creation_failure'             => self::MAGENTO_ORDER_CREATION_FAILED_NO,
                        'magento_order_creation_latest_attempt_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                    )
                );

                $this->setMagentoOrder($this->_magentoOrder);
                $this->save();
            }

            $this->afterCreateMagentoOrder();

            unset($magentoQuoteBuilder);
            unset($magentoOrderBuilder);
            // ---------------------------------------

            /** @var Mage_Sales_Model_Order $magentoOrder */
            $magentoOrder = Mage::getModel('sales/order')->load($magentoOrderId);
            $magentoOrder->setCustomerGroupId($this->getProxy()->getCustomer()->getGroupId());
            $magentoOrder->save();
        } catch (Exception $e) {
            unset($magentoQuoteBuilder);
            unset($magentoOrderBuilder);

            /**
             * Mage_CatalogInventory_Model_Stock::registerProductsSale() could open an transaction and may does not
             * close it in case of Exception. So all the next changes may be lost.
             */
            $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
            if ($transactionLevel = $connection->getTransactionLevel()) {
                Mage::helper('M2ePro/Module_Logger')->process(
                    array(
                        'transaction_level' => $transactionLevel,
                        'error'             => $e->getMessage(),
                        'trace'             => $e->getTraceAsString()
                    ),
                    'MySql Transaction Level Problem'
                );

                while ($connection->getTransactionLevel()) {
                    $connection->rollBack();
                }
            }

            Mage::dispatchEvent('m2epro_order_place_failure', array('order' => $this));

            $this->addData(
                array(
                'magento_order_creation_failure'             => self::MAGENTO_ORDER_CREATION_FAILED_YES,
                'magento_order_creation_fails_count'         => $this->getMagentoOrderCreationFailsCount() + 1,
                'magento_order_creation_latest_attempt_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
                )
            );
            $this->save();

            $this->addErrorLog('Magento Order was not created. Reason: %msg%', array('msg' => $e->getMessage()));
            Mage::helper('M2ePro/Module_Exception')->process($e, false);

            // ---------------------------------------
            if ($this->isReservable()) {
                $this->getReserve()->place();
            }

            // ---------------------------------------

            throw $e;
        }
    }

    public function afterCreateMagentoOrder()
    {
        // add history comments
        // ---------------------------------------
        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
        $magentoOrderUpdater->updateComments($this->getProxy()->getComments());
        $magentoOrderUpdater->finishUpdate();
        // ---------------------------------------

        Mage::dispatchEvent('m2epro_order_place_success', array('order' => $this));

        $this->addSuccessLog(
            'Magento Order #%order_id% was created.', array(
            '!order_id' => $this->getMagentoOrder()->getRealOrderId()
            )
        );

        if (method_exists($this->getChildObject(), 'afterCreateMagentoOrder')) {
            $this->getChildObject()->afterCreateMagentoOrder();
        }
    }

    public function updateMagentoOrderStatus()
    {
        if ($this->getMagentoOrder() === null) {
            return;
        }

        /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
        $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
        $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
        $magentoOrderUpdater->updateStatus($this->getChildObject()->getStatusForMagentoOrder());
        $magentoOrderUpdater->finishUpdate();
    }

    //########################################

    /**
     * @return bool
     */
    public function canCancelMagentoOrder()
    {
        $magentoOrder = $this->getMagentoOrder();

        if ($magentoOrder === null || $magentoOrder->isCanceled()) {
            return false;
        }

        return true;
    }

    public function cancelMagentoOrder()
    {
        if (!$this->canCancelMagentoOrder()) {
            return;
        }

        try {
            /** @var $magentoOrderUpdater Ess_M2ePro_Model_Magento_Order_Updater */
            $magentoOrderUpdater = Mage::getModel('M2ePro/Magento_Order_Updater');
            $magentoOrderUpdater->setMagentoOrder($this->getMagentoOrder());
            $magentoOrderUpdater->cancel();

            $this->addSuccessLog(
                'Magento Order #%order_id% was canceled.', array(
                '!order_id' => $this->getMagentoOrder()->getRealOrderId()
                )
            );
        } catch (Exception $e) {
            $this->addErrorLog(
                'Magento Order #%order_id% was not canceled. Reason: %msg%', array(
                '!order_id' => $this->getMagentoOrder()->getRealOrderId(),
                'msg' => $e->getMessage()
                )
            );
            throw $e;
        }
    }

    //########################################

    public function createInvoice()
    {
        $invoice = null;

        try {
            $invoice = $this->getChildObject()->createInvoice();
        } catch (Exception $e) {
            $this->addErrorLog('Invoice was not created. Reason: %msg%', array('msg' => $e->getMessage()));
        }

        if ($invoice !== null) {
            $this->addSuccessLog(
                'Invoice #%invoice_id% was created.', array(
                '!invoice_id' => $invoice->getIncrementId()
                )
            );
        }

        return $invoice;
    }

    //########################################

    public function createShipment()
    {
        $shipment = null;

        try {
            $shipment = $this->getChildObject()->createShipment();
        } catch (Exception $e) {
            $this->addErrorLog('Shipment was not created. Reason: %msg%', array('msg' => $e->getMessage()));
        }

        if ($shipment !== null) {
            $this->addSuccessLog(
                'Shipment #%shipment_id% was created.', array(
                '!shipment_id' => $shipment->getIncrementId()
                )
            );

            $this->addCreatedMagentoShipment($shipment);
        }

        return $shipment;
    }

    //########################################
}
