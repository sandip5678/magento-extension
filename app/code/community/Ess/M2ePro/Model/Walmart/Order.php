<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

/**
 * @method Ess_M2ePro_Model_Order getParentObject()
 * @method Ess_M2ePro_Model_Resource_Walmart_Order getResource()
 */
class Ess_M2ePro_Model_Walmart_Order extends Ess_M2ePro_Model_Component_Child_Walmart_Abstract
{
    const STATUS_CREATED             = 0;
    const STATUS_UNSHIPPED           = 1;
    const STATUS_SHIPPED_PARTIALLY   = 2;
    const STATUS_SHIPPED             = 3;
    const STATUS_CANCELED            = 5;

    protected $_subTotalPrice = null;

    protected $_grandTotalPrice = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/Walmart_Order');
    }

    //########################################

    public function getProxy()
    {
        return Mage::getModel('M2ePro/Walmart_Order_Proxy', $this);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Walmart_Account
     */
    public function getWalmartAccount()
    {
        return $this->getParentObject()->getAccount()->getChildObject();
    }

    //########################################

    public function getWalmartOrderId()
    {
        return $this->getData('walmart_order_id');
    }

    public function getBuyerName()
    {
        return $this->getData('buyer_name');
    }

    public function getBuyerEmail()
    {
        return $this->getData('buyer_email');
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return (int)$this->getData('status');
    }

    public function getCurrency()
    {
        return $this->getData('currency');
    }

    public function getShippingService()
    {
        return $this->getData('shipping_service');
    }

    /**
     * @return float
     */
    public function getShippingPrice()
    {
        return (float)$this->getData('shipping_price');
    }

    /**
     * @return Ess_M2ePro_Model_Walmart_Order_ShippingAddress
     */
    public function getShippingAddress()
    {
        $address = Mage::helper('M2ePro')->jsonDecode($this->getData('shipping_address'));

        return Mage::getModel('M2ePro/Walmart_Order_ShippingAddress', $this->getParentObject())
            ->setData($address);
    }

    //########################################

    /**
     * @return float
     */
    public function getPaidAmount()
    {
        return (float)$this->getData('paid_amount');
    }

    //########################################

    /**
     * @return array
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function getTaxDetails()
    {
        return $this->getSettings('tax_details');
    }

    /**
     * @return float
     */
    public function getProductPriceTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        return !empty($taxDetails['product']) ? (float)$taxDetails['product'] : 0.0;
    }

    /**
     * @return float
     */
    public function getShippingPriceTaxAmount()
    {
        $taxDetails = $this->getTaxDetails();
        return !empty($taxDetails['shipping']) ? (float)$taxDetails['shipping'] : 0.0;
    }

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        $taxAmount = $this->getProductPriceTaxAmount();
        if ($taxAmount <= 0) {
            return 0;
        }

        if ($this->getSubtotalPrice() <= 0) {
            return 0;
        }

        $taxRate = ($taxAmount / $this->getSubtotalPrice()) * 100;

        return round($taxRate, 4);
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        $taxAmount = $this->getShippingPriceTaxAmount();
        if ($taxAmount <= 0) {
            return 0;
        }

        if ($this->getShippingPrice() <= 0) {
            return 0;
        }

        $taxRate = ($taxAmount / $this->getShippingPrice()) * 100;

        return round($taxRate, 4);
    }

    //########################################

    /**
     * @return bool
     */
    public function isCreated()
    {
        return $this->getStatus() == self::STATUS_CREATED;
    }

    /**
     * @return bool
     */
    public function isUnshipped()
    {
        return $this->getStatus() == self::STATUS_UNSHIPPED;
    }

    /**
     * @return bool
     */
    public function isPartiallyShipped()
    {
        return $this->getStatus() == self::STATUS_SHIPPED_PARTIALLY;
    }

    /**
     * @return bool
     */
    public function isShipped()
    {
        return $this->getStatus() == self::STATUS_SHIPPED;
    }

    /**
     * @return bool
     */
    public function isCanceled()
    {
        return $this->getStatus() == self::STATUS_CANCELED;
    }

    //########################################

    /**
     * @return float|null
     */
    public function getSubtotalPrice()
    {
        if ($this->_subTotalPrice === null) {
            $this->_subTotalPrice = $this->getResource()->getItemsTotal($this->getId());
        }

        return $this->_subTotalPrice;
    }

    /**
     * @return float
     */
    public function getGrandTotalPrice()
    {
        if ($this->_grandTotalPrice === null) {
            $this->_grandTotalPrice = $this->getSubtotalPrice();
            $this->_grandTotalPrice += $this->getProductPriceTaxAmount();
            $this->_grandTotalPrice += $this->getShippingPrice();
            $this->_grandTotalPrice += $this->getShippingPriceTaxAmount();
        }

        return round($this->_grandTotalPrice, 2);
    }

    //########################################

    public function getStatusForMagentoOrder()
    {
        $status = '';
        $this->isUnshipped()        && $status = $this->getWalmartAccount()->getMagentoOrdersStatusProcessing();
        $this->isPartiallyShipped() && $status = $this->getWalmartAccount()->getMagentoOrdersStatusProcessing();
        $this->isShipped()          && $status = $this->getWalmartAccount()->getMagentoOrdersStatusShipped();

        return $status;
    }

    //########################################

    /**
     * @return int|null
     */
    public function getAssociatedStoreId()
    {
        $storeId = null;

        $channelItems = $this->getParentObject()->getChannelItems();

        if (empty($channelItems)) {
            // 3rd party order
            // ---------------------------------------
            $storeId = $this->getWalmartAccount()->getMagentoOrdersListingsOtherStoreId();
            // ---------------------------------------
        } else {
            // M2E Pro order
            // ---------------------------------------
            if ($this->getWalmartAccount()->isMagentoOrdersListingsStoreCustom()) {
                $storeId = $this->getWalmartAccount()->getMagentoOrdersListingsStoreId();
            } else {
                $firstChannelItem = reset($channelItems);
                $storeId = $firstChannelItem->getStoreId();
            }

            // ---------------------------------------
        }

        if ($storeId == 0) {
            $storeId = Mage::helper('M2ePro/Magento_Store')->getDefaultStoreId();
        }

        return $storeId;
    }

    //########################################

    /**
     * @return bool
     */
    public function isReservable()
    {
        return true;
    }

    /**
     * Check possibility for magento order creation
     *
     * @return bool
     */
    public function canCreateMagentoOrder()
    {
        if ($this->isCanceled()) {
            return false;
        }

        return true;
    }

    public function canAcknowledgeOrder()
    {
        foreach ($this->getParentObject()->getItemsCollection()->getItems() as $item) {
            if (!$item->canCreateMagentoOrder()) {
                return false;
            }
        }

        return true;
    }

    //########################################

    public function beforeCreateMagentoOrder()
    {
        if ($this->isCanceled()) {
            throw new Ess_M2ePro_Model_Exception(
                'Magento Order Creation is not allowed for canceled Walmart Orders.'
            );
        }
    }

    public function afterCreateMagentoOrder()
    {
        if ($this->getWalmartAccount()->isMagentoOrdersCustomerNewNotifyWhenOrderCreated()) {
            if (method_exists($this->getParentObject()->getMagentoOrder(), 'queueNewOrderEmail')) {
                $this->getParentObject()->getMagentoOrder()->queueNewOrderEmail(false);
            } else {
                $this->getParentObject()->getMagentoOrder()->sendNewOrderEmail();
            }
        }
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateInvoice()
    {
        if ($this->getWalmartAccount()->isMagentoInvoiceCreationDisabled()) {
            return false;
        }

        if (!$this->getWalmartAccount()->isMagentoOrdersInvoiceEnabled()) {
            return false;
        }

        if ($this->isCanceled()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasInvoices() || !$magentoOrder->canInvoice()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    /**
     * @return Mage_Sales_Model_Order_Invoice|null
     * @throws Exception
     */
    public function createInvoice()
    {
        if (!$this->canCreateInvoice()) {
            return null;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        // Create invoice
        // ---------------------------------------
        /** @var $invoiceBuilder Ess_M2ePro_Model_Magento_Order_Invoice */
        $invoiceBuilder = Mage::getModel('M2ePro/Magento_Order_Invoice');
        $invoiceBuilder->setMagentoOrder($magentoOrder);
        $invoiceBuilder->buildInvoice();
        // ---------------------------------------

        $invoice = $invoiceBuilder->getInvoice();

        if ($this->getWalmartAccount()->isMagentoOrdersCustomerNewNotifyWhenInvoiceCreated()) {
            $invoice->sendEmail();
        }

        return $invoice;
    }

    //########################################

    /**
     * @return bool
     */
    public function canCreateShipment()
    {
        if (!$this->getWalmartAccount()->isMagentoOrdersShipmentEnabled()) {
            return false;
        }

        if (!$this->isShipped()) {
            return false;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();
        if ($magentoOrder === null) {
            return false;
        }

        if ($magentoOrder->hasShipments() || !$magentoOrder->canShip()) {
            return false;
        }

        return true;
    }

    // ---------------------------------------

    /**
     * @return Mage_Sales_Model_Order_Shipment|null
     */
    public function createShipment()
    {
        if (!$this->canCreateShipment()) {
            return null;
        }

        $magentoOrder = $this->getParentObject()->getMagentoOrder();

        // Create shipment
        // ---------------------------------------
        /** @var $shipmentBuilder Ess_M2ePro_Model_Magento_Order_Shipment */
        $shipmentBuilder = Mage::getModel('M2ePro/Magento_Order_Shipment');
        $shipmentBuilder->setMagentoOrder($magentoOrder);
        $shipmentBuilder->buildShipment();
        // ---------------------------------------

        return $shipmentBuilder->getShipment();
    }

    //########################################

    /**
     * @param array $trackingDetails
     * @return bool
     */
    public function canUpdateShippingStatus(array $trackingDetails = array())
    {
        if ($this->isCanceled()) {
            return false;
        }

        return true;
    }

    /**
     * @param array $trackingDetails
     * @param array $items
     * @return bool
     */
    public function updateShippingStatus(array $trackingDetails = array(), array $items = array())
    {
        if (!$this->canUpdateShippingStatus($trackingDetails)) {
            return false;
        }

        if (empty($trackingDetails['tracking_number'])) {
            $this->getParentObject()->addErrorLog(
                'Walmart Order was not shipped. Reason: %msg%',
                array('msg' => 'Order status was not updated to Shipped on Walmart because a tracking number
                                is missing. Please insert the valid tracking number into the Order shipment.')
            );
            return false;
        }

        if (!isset($trackingDetails['fulfillment_date'])) {
            $trackingDetails['fulfillment_date'] = Mage::helper('M2ePro')->getCurrentGmtDate();
        }

        if (!empty($trackingDetails['carrier_code'])) {
            $trackingDetails['carrier_title'] = Mage::helper('M2ePro/Component_Walmart')->getCarrierTitle(
                $trackingDetails['carrier_code'],
                isset($trackingDetails['carrier_title']) ? $trackingDetails['carrier_title'] : ''
            );
        }

        if (!empty($trackingDetails['carrier_title'])) {
            if ($trackingDetails['carrier_title'] == Ess_M2ePro_Model_Order_Shipment_Handler::CUSTOM_CARRIER_CODE &&
                !empty($trackingDetails['shipping_method']))
            {
                $trackingDetails['carrier_title'] = $trackingDetails['shipping_method'];
            }
        }

        $params = array(
            'walmart_order_id'  => $this->getWalmartOrderId(),
            'fulfillment_date' => $trackingDetails['fulfillment_date'],
            'items'            => array()
        );

        foreach ($items as $item) {
            if (!isset($item['walmart_order_item_id']) || !isset($item['qty'])) {
                continue;
            }

            if ((int)$item['qty'] <= 0) {
                continue;
            }

            $params['items'][] = array(
                'walmart_order_item_id' => $item['walmart_order_item_id'],
                'qty' => (int)$item['qty'],
                'tracking_details' => array(
                    'ship_date' => $trackingDetails['fulfillment_date'],
                    'method'    => $this->getShippingService(),
                    'carrier'   => $trackingDetails['carrier_title'],
                    'number'    => $trackingDetails['tracking_number'],
                ),
            );
        }

        $orderId     = $this->getParentObject()->getId();
        $action      = Ess_M2ePro_Model_Order_Change::ACTION_UPDATE_SHIPPING;
        $creatorType = $this->getParentObject()->getLog()->getInitiator();
        $component   = Ess_M2ePro_Helper_Component_Walmart::NICK;

        /** @var Ess_M2ePro_Model_Order_Change $change */
        $change = Mage::getModel('M2ePro/Order_Change')->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->addFieldToFilter('action', $action)
            ->addFieldToFilter('processing_attempt_count', 0)
            ->getFirstItem();

        if (!$change->getId()) {
            $change::create($orderId, $action, $creatorType, $component, $params);
            return true;
        }

        $existingParams = $change->getParams();
        foreach ($params['items'] as $newItem) {
            foreach ($existingParams['items'] as &$existingItem) {
                if ($newItem['walmart_order_item_id'] === $existingItem['walmart_order_item_id']) {
                    $newQtyTotal = $newItem['qty'] + $existingItem['qty'];

                    $maxQtyTotal  = Mage::getModel('M2ePro/Walmart_Order_Item')->getCollection()
                        ->addFieldToFilter(
                            'walmart_order_item_id',
                            $existingItem['walmart_order_item_id']
                        )
                        ->getFirstItem()
                        ->getQty();
                    $newQtyTotal >= $maxQtyTotal && $newQtyTotal = $maxQtyTotal;
                    $existingItem['qty'] = $newQtyTotal;
                    continue 2;
                }
            }

            unset($existingItem);
            $existingParams['items'][] = $newItem;
        }

        $change->setData('params', Mage::helper('M2ePro')->jsonEncode($existingParams));
        $change->save();

        return true;
    }

    //########################################

    /**
     * @return bool
     */
    public function canRefund()
    {
        if ($this->getStatus() == self::STATUS_CANCELED) {
            return false;
        }

        return true;
    }

    /**
     * @param array $items
     * @return bool
     * @throws Ess_M2ePro_Model_Exception_Logic
     */
    public function refund(array $items = array())
    {
        if (!$this->canRefund()) {
            return false;
        }

        $params = array(
            'order_id' => $this->getWalmartOrderId(),
            'currency' => $this->getCurrency(),
            'items'    => $items,
        );

        $orderId     = $this->getParentObject()->getId();
        $creatorType = $this->getParentObject()->getLog()->getInitiator();
        $component   = Ess_M2ePro_Helper_Component_Walmart::NICK;

        $action = Ess_M2ePro_Model_Order_Change::ACTION_CANCEL;
        if ($this->isShipped() || $this->isPartiallyShipped() || $this->isSetProcessingLock('update_shipping_status')) {
            if (empty($items)) {
                $this->getParentObject()->addErrorLog(
                    'Walmart Order was not refunded. Reason: %msg%',
                    array('msg' => 'Refund request was not submitted.
                                    To be processed through Walmart API, the refund must be applied to certain products
                                    in an order. Please indicate the number of each line item, that need to be refunded,
                                    in Credit Memo form.')
                );
                return false;
            }

            $action = Ess_M2ePro_Model_Order_Change::ACTION_REFUND;
        }

        Mage::getModel('M2ePro/Order_Change')->create($orderId, $action, $creatorType, $component, $params);

        return true;
    }

    //########################################

    public function deleteInstance()
    {
        return $this->delete();
    }

    //########################################
}
