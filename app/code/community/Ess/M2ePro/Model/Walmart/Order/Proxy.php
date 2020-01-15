<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var Ess_M2ePro_Model_Walmart_Order */
    protected $_order = null;

    /** @var Ess_M2ePro_Model_Walmart_Order_Item_Proxy[] */
    protected $_removedProxyItems = array();

    //########################################

    /**
     * @param Ess_M2ePro_Model_Order_Item_Proxy[] $items
     * @return Ess_M2ePro_Model_Order_Item_Proxy[]
     * @throws Exception
     */
    protected function mergeItems(array $items)
    {
        // Magento order can be created even it has zero price. Tested on Magento v. 1.7.0.2 and greater.
        // Doest not requires 'Zero Subtotal Checkout enabled'
        $minVersion = Mage::helper('M2ePro/Magento')->isCommunityEdition() ? '1.7.0.2' : '1.12';
        if (version_compare(Mage::helper('M2ePro/Magento')->getVersion(), $minVersion, '>=')) {
            return parent::mergeItems($items);
        }

        foreach ($items as $key => $item) {
            if ($item->getPrice() <= 0) {
                $this->_removedProxyItems[] = $item;
                unset($items[$key]);
            }
        }

        return parent::mergeItems($items);
    }

    //########################################

    /**
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->_order->getWalmartAccount()->isMagentoOrdersCustomerPredefined() ||
            $this->_order->getWalmartAccount()->isMagentoOrdersCustomerNew()) {
            return self::CHECKOUT_REGISTER;
        }

        return self::CHECKOUT_GUEST;
    }

    //########################################

    /**
     * @return bool
     */
    public function isOrderNumberPrefixSourceChannel()
    {
        return $this->_order->getWalmartAccount()->isMagentoOrdersNumberSourceChannel();
    }

    /**
     * @return bool
     */
    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->_order->getWalmartAccount()->isMagentoOrdersNumberSourceMagento();
    }

    /**
     * @return mixed
     */
    public function getChannelOrderNumber()
    {
        return $this->_order->getWalmartOrderId();
    }

    /**
     * @return null|string
     */
    public function getOrderNumberPrefix()
    {
        if (!$this->_order->getWalmartAccount()->isMagentoOrdersNumberPrefixEnable()) {
            return '';
        }

        return $this->_order->getWalmartAccount()->getMagentoOrdersNumberRegularPrefix();
    }

    //########################################

    /**
     * @return false|Mage_Core_Model_Abstract|Mage_Customer_Model_Customer
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getCustomer()
    {
        $customer = Mage::getModel('customer/customer');

        if ($this->_order->getWalmartAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->_order->getWalmartAccount()->getMagentoOrdersCustomerId());

            if ($customer->getId() === null) {
                throw new Ess_M2ePro_Model_Exception(
                    'Customer with ID specified in Walmart Account
                    Settings does not exist.'
                );
            }
        }

        if ($this->_order->getWalmartAccount()->isMagentoOrdersCustomerNew()) {
            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($this->_order->getWalmartAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if ($customer->getId() !== null) {
                return $customer;
            }

            $customerInfo['website_id'] = $this->_order->getWalmartAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->_order->getWalmartAccount()->getMagentoOrdersCustomerNewGroupId();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();
        }

        return $customer;
    }

    //########################################

    public function getCurrency()
    {
        return $this->_order->getCurrency();
    }

    //########################################

    /**
     * @return array
     */
    public function getPaymentData()
    {
        $paymentData = array(
            'method'                => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'        => Ess_M2ePro_Helper_Component_Walmart::NICK,
            'payment_method'        => '',
            'channel_order_id'      => $this->_order->getWalmartOrderId(),
            'channel_final_fee'     => 0,
            'cash_on_delivery_cost' => 0,
            'transactions'          => array()
        );

        return $paymentData;
    }

    //########################################

    /**
     * @return array
     */
    public function getShippingData()
    {
        $shippingData = array(
            'shipping_method' => $this->_order->getShippingService(),
            'shipping_price'  => $this->getBaseShippingPrice(),
            'carrier_title'   => Mage::helper('M2ePro')->__('Walmart Shipping')
        );

        return $shippingData;
    }

    /**
     * @return float
     */
    protected function getShippingPrice()
    {
        $price = $this->_order->getShippingPrice();

        if ($this->isTaxModeNone() && $this->getShippingPriceTaxRate() > 0) {
            $price += $this->_order->getShippingPriceTaxAmount();
        }

        return $price;
    }

    //########################################

    /**
     * @return array
     */
    public function getChannelComments()
    {
        $comments = array();

        // Removed Order Items
        // ---------------------------------------
        if (!empty($this->_removedProxyItems)) {
            $comment = '<u>'.
                Mage::helper('M2ePro')->__(
                    'The following SKUs have zero price and can not be included in Magento order line items'
                ).
                ':</u><br/>';

            $zeroItems = array();
            foreach ($this->_removedProxyItems as $item) {
                $productSku = $item->getMagentoProduct()->getSku();
                $qtyPurchased = $item->getQty();

                $zeroItems[] = "<b>{$productSku}</b>: {$qtyPurchased} QTY";
            }

            $comments[] = $comment . implode('<br/>,', $zeroItems);
        }

        // ---------------------------------------

        return $comments;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->_order->getProductPriceTaxRate() > 0;
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        return $this->hasTax();
    }

    /**
     * @return bool
     */
    public function isVatTax()
    {
        return false;
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        return $this->_order->getProductPriceTaxRate();
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        return $this->_order->getShippingPriceTaxRate();
    }

    // ---------------------------------------

    /**
     * @return bool|null
     */
    public function isProductPriceIncludeTax()
    {
        $configValue = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue('/walmart/order/tax/product_price/', 'is_include_tax');

        if ($configValue !== null) {
            return (bool)$configValue;
        }

        if ($this->isTaxModeChannel() || ($this->isTaxModeMixed() && $this->hasTax())) {
            return false;
        }

        return null;
    }

    /**
     * @return bool|null
     */
    public function isShippingPriceIncludeTax()
    {
        $configValue = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue('/walmart/order/tax/shipping_price/', 'is_include_tax');

        if ($configValue !== null) {
            return (bool)$configValue;
        }

        if ($this->isTaxModeChannel() || ($this->isTaxModeMixed() && $this->hasTax())) {
            return false;
        }

        return null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTaxModeNone()
    {
        return $this->_order->getWalmartAccount()->isMagentoOrdersTaxModeNone();
    }

    /**
     * @return bool
     */
    public function isTaxModeMagento()
    {
        return $this->_order->getWalmartAccount()->isMagentoOrdersTaxModeMagento();
    }

    /**
     * @return bool
     */
    public function isTaxModeChannel()
    {
        return $this->_order->getWalmartAccount()->isMagentoOrdersTaxModeChannel();
    }

    //########################################
}
