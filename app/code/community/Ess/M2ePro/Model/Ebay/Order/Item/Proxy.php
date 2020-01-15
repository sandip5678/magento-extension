<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Order_Item_Proxy extends Ess_M2ePro_Model_Order_Item_Proxy
{
    //########################################

    /**
     * @return float
     */
    public function getOriginalPrice()
    {
        $price = $this->_item->getPrice();

        if (($this->getProxyOrder()->isTaxModeNone() && $this->hasTax()) || $this->isVatTax()) {
            $price += $this->_item->getTaxAmount();
        }

        return $price;
    }

    /**
     * @return int
     */
    public function getOriginalQty()
    {
        return $this->_item->getQtyPurchased();
    }

    //########################################

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->_item->getTaxRate();
    }

    //########################################

    public function getWasteRecyclingFee()
    {
        return $this->_item->getWasteRecyclingFee();
    }

    //########################################

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        if (empty($this->_additionalData)) {
            $this->_additionalData[Ess_M2ePro_Helper_Data::CUSTOM_IDENTIFIER]['items'][] = array(
                'item_id' => $this->_item->getItemId(),
                'transaction_id' => $this->_item->getTransactionId()
            );
        }

        return $this->_additionalData;
    }

    //########################################
}
