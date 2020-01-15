<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Proxy extends Ess_M2ePro_Model_Order_Proxy
{
    const USER_ID_ATTRIBUTE_CODE = 'ebay_user_id';

    /** @var $_order Ess_M2ePro_Model_Ebay_Order */
    protected $_order = null;

    //########################################

    /**
     * @return string
     */
    public function getCheckoutMethod()
    {
        if ($this->_order->getEbayAccount()->isMagentoOrdersCustomerNew() ||
            $this->_order->getEbayAccount()->isMagentoOrdersCustomerPredefined()) {
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
        return $this->_order->getEbayAccount()->isMagentoOrdersNumberSourceChannel();
    }

    /**
     * @return bool
     */
    public function isOrderNumberPrefixSourceMagento()
    {
        return $this->_order->getEbayAccount()->isMagentoOrdersNumberSourceMagento();
    }

    public function getChannelOrderNumber()
    {
        return $this->_order->getEbayOrderId();
    }

    public function getOrderNumberPrefix()
    {
        if (!$this->_order->getEbayAccount()->isMagentoOrdersNumberPrefixEnable()) {
            return '';
        }

        return $this->_order->getEbayAccount()->getMagentoOrdersNumberRegularPrefix();
    }

    //########################################

    /**
     * @return false|Mage_Customer_Model_Customer
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getCustomer()
    {
        $customer = Mage::getModel('customer/customer');

        if ($this->_order->getEbayAccount()->isMagentoOrdersCustomerPredefined()) {
            $customer->load($this->_order->getEbayAccount()->getMagentoOrdersCustomerId());

            if ($customer->getId() === null) {
                throw new Ess_M2ePro_Model_Exception(
                    'Customer with ID specified in eBay Account
                    Settings does not exist.'
                );
            }
        }

        if ($this->_order->getEbayAccount()->isMagentoOrdersCustomerNew()) {
            /** @var $customerBuilder Ess_M2ePro_Model_Magento_Customer */
            $customerBuilder = Mage::getModel('M2ePro/Magento_Customer');

            $userIdAttribute = Mage::getModel('eav/entity_attribute')->loadByCode(
                Mage::getModel('customer/customer')->getEntityTypeId(), self::USER_ID_ATTRIBUTE_CODE
            );

            if (!$userIdAttribute->getId()) {
                $customerBuilder->buildAttribute(self::USER_ID_ATTRIBUTE_CODE, 'eBay User ID');
            }

            $customerInfo = $this->getAddressData();

            $customer->setWebsiteId($this->_order->getEbayAccount()->getMagentoOrdersCustomerNewWebsiteId());
            $customer->loadByEmail($customerInfo['email']);

            if ($customer->getId() !== null) {
                $customer->setData(self::USER_ID_ATTRIBUTE_CODE, $this->_order->getBuyerUserId());
                $customer->save();

                return $customer;
            }

            $customerInfo['website_id'] = $this->_order->getEbayAccount()->getMagentoOrdersCustomerNewWebsiteId();
            $customerInfo['group_id'] = $this->_order->getEbayAccount()->getMagentoOrdersCustomerNewGroupId();

            $customerBuilder->setData($customerInfo);
            $customerBuilder->buildCustomer();

            $customer = $customerBuilder->getCustomer();

            $customer->setData(self::USER_ID_ATTRIBUTE_CODE, $this->_order->getBuyerUserId());
            $customer->save();
        }

        return $customer;
    }

    //########################################

    /**
     * @return array
     */
    public function getAddressData()
    {
        if (!$this->_order->isUseGlobalShippingProgram() &&
            !$this->_order->isUseClickAndCollect() &&
            !$this->_order->isUseInStorePickup()
        ) {
            return parent::getAddressData();
        }

        $addressModel = $this->_order->isUseGlobalShippingProgram() ? $this->_order->getGlobalShippingWarehouseAddress()
                                                                   : $this->_order->getShippingAddress();

        $rawAddressData = $addressModel->getRawData();

        $addressData = array();

        $recipientNameParts = $this->getNameParts($rawAddressData['recipient_name']);
        $addressData['firstname']   = $recipientNameParts['firstname'];
        $addressData['lastname']    = $recipientNameParts['lastname'];
        $addressData['middlename']  = $recipientNameParts['middlename'];

        $customerNameParts = $this->getNameParts($rawAddressData['buyer_name']);
        $addressData['customer_firstname']   = $customerNameParts['firstname'];
        $addressData['customer_lastname']    = $customerNameParts['lastname'];
        $addressData['customer_middlename']  = $customerNameParts['middlename'];

        $addressData['email']      = $rawAddressData['email'];
        $addressData['country_id'] = $rawAddressData['country_id'];
        $addressData['region']     = $rawAddressData['region'];
        $addressData['region_id']  = $addressModel->getRegionId();
        $addressData['city']       = $rawAddressData['city'];
        $addressData['postcode']   = $rawAddressData['postcode'];
        $addressData['telephone']  = $rawAddressData['telephone'];
        $addressData['company']    = !empty($rawAddressData['company']) ? $rawAddressData['company'] : '';

        // Adding reference id into street array
        // ---------------------------------------
        $referenceId = '';
        $addressData['street'] = !empty($rawAddressData['street']) ? $rawAddressData['street'] : array();

        if ($this->_order->isUseGlobalShippingProgram()) {
            $details = $this->_order->getGlobalShippingDetails();
            isset($details['warehouse_address']['reference_id']) &&
                  $referenceId = 'Ref #'.$details['warehouse_address']['reference_id'];
        }

        if ($this->_order->isUseClickAndCollect()) {
            $details = $this->_order->getClickAndCollectDetails();
            isset($details['reference_id']) && $referenceId = 'Ref #'.$details['reference_id'];
        }

        if ($this->_order->isUseInStorePickup()) {
            $details = $this->_order->getInStorePickupDetails();
            isset($details['reference_id']) && $referenceId = 'Ref #'.$details['reference_id'];
        }

        if (!empty($referenceId)) {
            if (count($addressData['street']) >= 2) {
                $addressData['street'] = array(
                    $referenceId,
                    implode(' ', $addressData['street']),
                );
            } else {
                array_unshift($addressData['street'], $referenceId);
            }
        }

        // ---------------------------------------

        $addressData['save_in_address_book'] = 0;

        return $addressData;
    }

    /**
     * @return array
     */
    public function getBillingAddressData()
    {
        if (!$this->_order->isUseGlobalShippingProgram()) {
            return parent::getBillingAddressData();
        }

        return parent::getAddressData();
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
        $paymentMethodTitle = $this->_order->getPaymentMethod();
        $paymentMethodTitle == 'None' && $paymentMethodTitle = Mage::helper('M2ePro')->__('Not Selected Yet');

        $paymentData = array(
            'method'                => Mage::getSingleton('M2ePro/Magento_Payment')->getCode(),
            'component_mode'        => Ess_M2ePro_Helper_Component_Ebay::NICK,
            'payment_method'        => $paymentMethodTitle,
            'channel_order_id'      => $this->_order->getEbayOrderId(),
            'channel_final_fee'     => $this->convertPrice($this->_order->getFinalFee()),
            'cash_on_delivery_cost' => $this->convertPrice($this->_order->getCashOnDeliveryCost()),
            'transactions'          => $this->getPaymentTransactions(),
            'tax_id'                => $this->_order->getBuyerTaxId(),
        );

        return $paymentData;
    }

    /**
     * @return array
     */
    public function getPaymentTransactions()
    {
        /** @var Ess_M2ePro_Model_Ebay_Order_ExternalTransaction[] $externalTransactions */
        $externalTransactions = $this->_order->getExternalTransactionsCollection()->getItems();

        $paymentTransactions = array();
        foreach ($externalTransactions as $externalTransaction) {
            $paymentTransactions[] = array(
                'transaction_id'   => $externalTransaction->getTransactionId(),
                'sum'              => $externalTransaction->getSum(),
                'fee'              => $externalTransaction->getFee(),
                'transaction_date' => $externalTransaction->getTransactionDate(),
            );
        }

        return $paymentTransactions;
    }

    //########################################

    /**
     * @return array
     */
    public function getShippingData()
    {
        $shippingData = array(
            'shipping_price'  => $this->getBaseShippingPrice(),
            'carrier_title'   => Mage::helper('M2ePro')->__('eBay Shipping'),
            'shipping_method' => $this->_order->getShippingService(),
        );

        if ($this->_order->isUseGlobalShippingProgram()) {
            $globalShippingDetails = $this->_order->getGlobalShippingDetails();
            $globalShippingDetails = $globalShippingDetails['service_details'];

            if (!empty($globalShippingDetails['service_details']['service'])) {
                $shippingData['shipping_method'] = $globalShippingDetails['service_details']['service'];
            }
        }

        if ($this->_order->isUseClickAndCollect() || $this->_order->isUseInStorePickup()) {
            if ($this->_order->isUseClickAndCollect()) {
                $shippingData['shipping_method'] = 'Click And Collect | '.$shippingData['shipping_method'];
                $details = $this->_order->getClickAndCollectDetails();
            } else {
                $shippingData['shipping_method'] = 'In Store Pickup | '.$shippingData['shipping_method'];
                $details = $this->_order->getInStorePickupDetails();
            }

            if (!empty($details['location_id'])) {
                $shippingData['shipping_method'] .= ' | Store ID: '.$details['location_id'];
            }

            if (!empty($details['reference_id'])) {
                $shippingData['shipping_method'] .= ' | Reference ID: '.$details['reference_id'];
            }

            if (!empty($details['delivery_date'])) {
                $shippingData['shipping_method'] .= ' | Delivery Date: '.$details['delivery_date'];
            }
        }

        return $shippingData;
    }

    /**
     * @return float
     */
    protected function getShippingPrice()
    {
        if ($this->_order->isUseGlobalShippingProgram()) {
            $globalShippingDetails = $this->_order->getGlobalShippingDetails();
            $price = $globalShippingDetails['service_details']['price'];
        } else {
            $price = $this->_order->getShippingPrice();
        }

        if ($this->isTaxModeNone() && !$this->isShippingPriceIncludeTax()) {
            $taxAmount = Mage::getSingleton('tax/calculation')
                ->calcTaxAmount($price, $this->getShippingPriceTaxRate(), false, false);

            $price += $taxAmount;
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

        if ($this->_order->isUseGlobalShippingProgram()) {
            $comments[] = '<b>'.
                          Mage::helper('M2ePro')->__('Global Shipping Program is used for this Order').
                          '</b><br/>';
        }

        $buyerMessage = $this->_order->getBuyerMessage();
        if (!empty($buyerMessage)) {
            $comment = '<b>' . Mage::helper('M2ePro')->__('Checkout Message From Buyer') . ': </b>';
            $comment .= $buyerMessage . '<br/>';

            $comments[] = $comment;
        }

        return $comments;
    }

    //########################################

    /**
     * @return bool
     */
    public function hasTax()
    {
        return $this->_order->hasTax();
    }

    /**
     * @return bool
     */
    public function isSalesTax()
    {
        return $this->_order->isSalesTax();
    }

    /**
     * @return bool
     */
    public function isVatTax()
    {
        return $this->_order->isVatTax();
    }

    // ---------------------------------------

    /**
     * @return float|int
     */
    public function getProductPriceTaxRate()
    {
        if (!$this->hasTax()) {
            return 0;
        }

        if ($this->isTaxModeNone() || $this->isTaxModeMagento()) {
            return 0;
        }

        return $this->_order->getTaxRate();
    }

    /**
     * @return float|int
     */
    public function getShippingPriceTaxRate()
    {
        if (!$this->hasTax()) {
            return 0;
        }

        if ($this->isTaxModeNone() || $this->isTaxModeMagento()) {
            return 0;
        }

        if (!$this->_order->isShippingPriceHasTax()) {
            return 0;
        }

        return $this->getProductPriceTaxRate();
    }

    // ---------------------------------------

    /**
     * @return bool|null
     */
    public function isProductPriceIncludeTax()
    {
        $configValue = Mage::helper('M2ePro/Module')
            ->getConfig()
            ->getGroupValue('/ebay/order/tax/product_price/', 'is_include_tax');

        if ($configValue !== null) {
            return (bool)$configValue;
        }

        if ($this->isTaxModeChannel() || ($this->isTaxModeMixed() && $this->hasTax())) {
            return $this->isVatTax();
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
            ->getGroupValue('/ebay/order/tax/shipping_price/', 'is_include_tax');

        if ($configValue !== null) {
            return (bool)$configValue;
        }

        if ($this->isTaxModeChannel() || ($this->isTaxModeMixed() && $this->hasTax())) {
            return $this->isVatTax();
        }

        return null;
    }

    // ---------------------------------------

    /**
     * @return bool
     */
    public function isTaxModeNone()
    {
        if ($this->_order->isUseGlobalShippingProgram()) {
            return true;
        }

        return $this->_order->getEbayAccount()->isMagentoOrdersTaxModeNone();
    }

    /**
     * @return bool
     */
    public function isTaxModeChannel()
    {
        return $this->_order->getEbayAccount()->isMagentoOrdersTaxModeChannel();
    }

    /**
     * @return bool
     */
    public function isTaxModeMagento()
    {
        return $this->_order->getEbayAccount()->isMagentoOrdersTaxModeMagento();
    }

    //########################################
}
