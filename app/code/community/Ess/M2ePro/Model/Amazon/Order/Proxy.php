<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    /** @var Ess_M2ePro_Model_Amazon_Order */
    protected $_order = null;

    /** @var Ess_M2ePro_Model_Amazon_Order_Item_Proxy[] */
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
        if ($this->_order->getAmazonAccount()->isMagentoOrdersCustomerPredefined() ||
            $this->_order->getAmazonAccount()->isMagentoOrdersCustomerNew()) {
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
        return $this->_order->getAmazonAccount()->isMagentoOrdersNumberSourceChannel();
    }

    /**
     * @return bool
     */
    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->_order->getAmazonAccount()->isMagentoOrdersNumberSourceMagento();
    }

    /**
     * @return mixed
     */
    public function getChannelOrderNumber()
    {
        return $this->_order->getAmazonOrderId();
    }

    /**
     * @return null|string
     */
    public function getOrderNumberPrefix()
    {
        $amazonAccount = $this->_order->getAmazonAccount();
        if (!$amazonAccount->isMagentoOrdersNumberPrefixEnable()) {
            return '';
        }

        $prefix = $amazonAccount->getMagentoOrdersNumberRegularPrefix();

        if ($amazonAccount->getMagentoOrdersNumberAfnPrefix() && $this->_order->isFulfilledByAmazon()) {
            $prefix .= $amazonAccount->getMagentoOrdersNumberAfnPrefix();
        }

        if ($amazonAccount->getMagentoOrdersNumberPrimePrefix() && $this->_order->isPrime()) {
            $prefix .= $amazonAccount->getMagentoOrdersNumberPrimePrefix();
        }

        if ($amazonAccount->getMagentoOrdersNumberB2bPrefix() && $this->_order->isBusiness()) {
            $prefix .= $amazonAccount->getMagentoOrdersNumberB2bPrefix();
        }

        return $prefix;
    }

    //########################################

    /**
     * @return false|Mage_Core_Model_Abstract|Mage_Customer_Model_Customer
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getCustomer()
    {
        $customer = Mage::getModel('customer/customer');

        if ($this->_order->getAmazonAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->_order->getAmazonAccount()->getMagentoOrdersCustomerId());

            if ($customer->getId() === null) {
                throw new Ess_M2ePro_Model_Exception(
                    'Customer with ID specified in Amazon Account
                    Settings does not exist.'
                );
            }
        }

        if ($this->_order->getAmazonAccount()->isMagentoOrdersCustomerNew()) {
            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($this->_order->getAmazonAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if ($customer->getId() !== null) {
                return $customer;
            }

            $customerInfo['website_id'] = $this->_order->getAmazonAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->_order->getAmazonAccount()->getMagentoOrdersCustomerNewGroupId();

            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer')->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();
        }

        return $customer;
    }

    //########################################

    /**
     * @return array
     */
    public function getBillingAddressData()
    {
        if ($this->_order->getAmazonAccount()->isMagentoOrdersBillingAddressSameAsShipping()) {
            return parent::getBillingAddressData();
        }

        if ($this->_order->getShippingAddress()->hasSameBuyerAndRecipient()) {
            return parent::getBillingAddressData();
        }

        $customerNameParts = $this->getNameParts($this->_order->getBuyerName());

        return array(
            'firstname'  => $customerNameParts['firstname'],
            'middlename' => $customerNameParts['middlename'],
            'lastname'   => $customerNameParts['lastname'],
            'country_id' => '',
            'region'     => '',
            'region_id'  => '',
            'city'       => 'Amazon does not supply the complete billing Buyer information.',
            'postcode'   => '',
            'street'     => array(),
            'company'    => ''
        );
    }

    /**
     * @return bool
     */
    public function shouldIgnoreBillingAddressValidation()
    {
        if ($this->_order->getAmazonAccount()->isMagentoOrdersBillingAddressSameAsShipping()) {
            return false;
        }

        if ($this->_order->getShippingAddress()->hasSameBuyerAndRecipient()) {
            return false;
        }

        return true;
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
            'component_mode'        => Ess_M2ePro_Helper_Component_Amazon::NICK,
            'payment_method'        => '',
            'channel_order_id'      => $this->_order->getAmazonOrderId(),
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
            'carrier_title'   => Mage::helper('M2ePro')->__('Amazon Shipping')
        );

        if ($this->_order->isPrime()) {
            $shippingData['shipping_method'] .= ' | Is Prime';
        }

        if ($this->_order->isBusiness()) {
            $shippingData['shipping_method'] .= ' | Is Business';
        }

        if ($this->_order->isMerchantFulfillmentApplied()) {
            $merchantFulfillmentInfo = $this->_order->getMerchantFulfillmentData();

            $shippingData['shipping_method'] .= ' | Amazon\'s Shipping Services';

            if (!empty($merchantFulfillmentInfo['shipping_service']['carrier_name'])) {
                $carrier = $merchantFulfillmentInfo['shipping_service']['carrier_name'];
                $shippingData['shipping_method'] .= ' | Carrier: '.$carrier;
            }

            if (!empty($merchantFulfillmentInfo['shipping_service']['name'])) {
                $service = $merchantFulfillmentInfo['shipping_service']['name'];
                $shippingData['shipping_method'] .= ' | Service: '.$service;
            }

            if (!empty($merchantFulfillmentInfo['shipping_service']['date']['estimated_delivery']['latest'])) {
                $deliveryDate = $merchantFulfillmentInfo['shipping_service']['date']['estimated_delivery']['latest'];
                $shippingData['shipping_method'] .= ' | Delivery Date: '.$deliveryDate;
            }
        }

        return $shippingData;
    }

    /**
     * @return float
     */
    protected function getShippingPrice()
    {
        $price = $this->_order->getShippingPrice() - $this->_order->getShippingDiscountAmount();

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

        if ($this->_order->getPromotionDiscountAmount() > 0) {
            $discount = Mage::getSingleton('M2ePro/Currency')
                ->formatPrice($this->getCurrency(), $this->_order->getPromotionDiscountAmount());

            $comment = Mage::helper('M2ePro')->__(
                '%value% promotion discount amount was subtracted from the total amount.',
                $discount
            );

            $comments[] = $comment;
        }

        if ($this->_order->getShippingDiscountAmount() > 0) {
            $discount = Mage::getSingleton('M2ePro/Currency')
                ->formatPrice($this->getCurrency(), $this->_order->getShippingDiscountAmount());

            $comment = Mage::helper('M2ePro')->__(
                '%value% discount amount was subtracted from the shipping Price.',
                $discount
            );

            $comments[] = $comment;
        }

        // Gift Wrapped Items
        // ---------------------------------------
        $itemsGiftPrices = array();

        /** @var Ess_M2ePro_Model_Order_Item[] $items */
        $items = $this->_order->getParentObject()->getItemsCollection();
        foreach ($items as $item) {
            $giftPrice = $item->getChildObject()->getGiftPrice();
            if (empty($giftPrice)) {
                continue;
            }

            $itemsGiftPrices[] = array(
                'name'  => $item->getMagentoProduct()->getName(),
                'type'  => $item->getChildObject()->getGiftType(),
                'price' => $giftPrice,
            );
        }

        if (!empty($itemsGiftPrices)) {
            $comment = '<u>'.
                Mage::helper('M2ePro')->__('The following Items are purchased with gift wraps') .
                ':</u><br/>';

            foreach ($itemsGiftPrices as $productInfo) {
                $formattedCurrency = Mage::getSingleton('M2ePro/Currency')->formatPrice(
                    $this->getCurrency(), $productInfo['price']
                );

                $comment .= '<b>'.$productInfo['name'].'</b> > '
                    .$productInfo['type'].' ('.$formattedCurrency.')';
            }

            $comments[] = $comment;
        }

        // ---------------------------------------

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
            ->getGroupValue('/amazon/order/tax/product_price/', 'is_include_tax');

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
            ->getGroupValue('/amazon/order/tax/shipping_price/', 'is_include_tax');

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
        return $this->_order->getAmazonAccount()->isMagentoOrdersTaxModeNone();
    }

    /**
     * @return bool
     */
    public function isTaxModeMagento()
    {
        return $this->_order->getAmazonAccount()->isMagentoOrdersTaxModeMagento();
    }

    /**
     * @return bool
     */
    public function isTaxModeChannel()
    {
        return $this->_order->getAmazonAccount()->isMagentoOrdersTaxModeChannel();
    }

    //########################################
}
