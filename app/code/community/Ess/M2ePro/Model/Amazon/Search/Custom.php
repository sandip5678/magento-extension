<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Search_Custom
{
    /** @var Ess_M2ePro_Model_Listing_Product $_listingProduct */
    protected $_listingProduct = null;

    protected $_query = null;

    //########################################

    /**
     * @param Ess_M2ePro_Model_Listing_Product $listingProduct
     * @return $this
     */
    public function setListingProduct(Ess_M2ePro_Model_Listing_Product $listingProduct)
    {
        $this->_listingProduct = $listingProduct;
        return $this;
    }

    /**
     * @param $query
     * @return $this
     */
    public function setQuery($query)
    {
        $this->_query = (string)$query;
        return $this;
    }

    //########################################

    public function process()
    {
        $dispatcherObject = Mage::getModel('M2ePro/Amazon_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getCustomConnector(
            'Amazon_Search_Custom_'.ucfirst($this->getSearchMethod()).'_Requester',
            $this->getConnectorParams(), $this->_listingProduct->getAccount()
        );

        $dispatcherObject->process($connectorObj);
        return $this->prepareResult($connectorObj->getPreparedResponseData());
    }

    //########################################

    protected function getConnectorParams()
    {
        $searchMethod = $this->getSearchMethod();

        /** @var Ess_M2ePro_Model_Amazon_Listing_Product $amazonListingProduct */
        $amazonListingProduct = $this->_listingProduct->getChildObject();
        $isModifyChildToSimple = !$amazonListingProduct->getVariationManager()->isRelationParentType();

        $params = array(
            'variation_bad_parent_modify_child_to_simple' => $isModifyChildToSimple,
        );

        if ($searchMethod == 'byQuery') {
            $params['query'] = $this->_query;
        } else {
            $params['query'] = $this->getStrippedQuery();
        }

        if ($searchMethod == 'byIdentifier') {
            $params['query_type'] = $this->getIdentifierType();
        }

        return $params;
    }

    protected function getSearchMethod()
    {
        $validationHelper = Mage::helper('M2ePro');
        $amazonHelper     = Mage::helper('M2ePro/Component_Amazon');
        $strippedQuery    = $this->getStrippedQuery();

        if ($amazonHelper->isASIN($strippedQuery)) {
            return 'byAsin';
        }

        if ($validationHelper->isEAN($strippedQuery) ||
            $validationHelper->isUPC($strippedQuery) ||
            $validationHelper->isISBN($strippedQuery)
        ) {
            return 'byIdentifier';
        }

        return 'byQuery';
    }

    protected function getIdentifierType()
    {
        $query = $this->getStrippedQuery();

        $validationHelper = Mage::helper('M2ePro');

        return (Mage::helper('M2ePro/Component_Amazon')->isASIN($query) ? 'ASIN' :
               ($validationHelper->isISBN($query)                       ? 'ISBN' :
               ($validationHelper->isUPC($query)                        ? 'UPC'  :
               ($validationHelper->isEAN($query)                        ? 'EAN'  : false))));
    }

    protected function prepareResult($searchData)
    {
        $connectorParams = $this->getConnectorParams();

        if ($this->getSearchMethod() == 'byQuery') {
            $type = 'string';
        } else {
            $type = $this->getIdentifierType();
        }

        if ($searchData !== false && $this->getSearchMethod() == 'byAsin') {
            if (is_array($searchData) && !empty($searchData)) {
                $searchData = array($searchData);
            } else if ($searchData === null) {
                $searchData = array();
            }
        }

        return array(
            'type'  => $type,
            'value' => $connectorParams['query'],
            'data'  => $searchData,
        );
    }

    protected function getStrippedQuery()
    {
        return str_replace('-', '', $this->_query);
    }

    //########################################
}