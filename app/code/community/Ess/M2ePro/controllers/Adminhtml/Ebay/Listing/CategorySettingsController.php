<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Mode as CategoryTemplateBlock;
use Ess_M2ePro_Block_Adminhtml_Ebay_Listing_SourceMode as SourceModeBlock;

class Ess_M2ePro_Adminhtml_Ebay_Listing_CategorySettingsController
    extends Ess_M2ePro_Controller_Adminhtml_Ebay_MainController
{
    protected $_sessionKey = 'ebay_listing_category_settings';

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')
            ->addJs('M2ePro/Ebay/Listing/Category/ChooserHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/SpecificHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Chooser/BrowseHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/UgAJAQ");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Ebay::MENU_ROOT_NODE_NICK . '/listings'
        );
    }

    //########################################

    public function indexAction()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if (!$this->checkProductAddIds()) {
            return $this->_redirect(
                '*/adminhtml_ebay_listing_productAdd', array('listing_id' => $listingId,
                '_current' => true)
            );
        }

        Mage::helper('M2ePro/Data_Global')->setValue(
            'temp_data',
            Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId)
        );

        $step = (int)$this->getRequest()->getParam('step');

        if ($this->getSessionValue('mode') === null) {
            $step = 1;
        }

        switch ($step) {
            case 1:
                return $this->stepOne();
            case 2:
                $action = 'stepTwo';
                break;
            case 3:
                $action = 'stepThree';
                break;
            // ....
            default:
                return $this->_redirect('*/*/', array('_current' => true,'step' => 1));
        }

        $action .= 'Mode'. ucfirst($this->getSessionValue('mode'));

        return $this->$action();
    }

    //########################################

    protected function stepOne()
    {
        if ($builderData = $this->getListingFromRequest()->getSetting('additional_data', 'mode_same_category_data')) {
            $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);

            $this->saveModeSame($categoryTemplate, $otherCategoryTemplate, false);

            return $this->reviewAction();
        }

        $source = $this->getListingFromRequest()->getParentObject()->getSetting('additional_data', 'source');

        if ($this->getRequest()->isPost()) {
            $mode = $this->getRequest()->getParam('mode');

            $this->setSessionValue('mode', $mode);

            if ($mode == CategoryTemplateBlock::MODE_SAME) {
                $temp = $this->getSessionValue($this->getSessionDataKey());
                $temp['remember'] = (bool)$this->getRequest()->getParam('mode_same_remember_checkbox', false);
                $this->setSessionValue($this->getSessionDataKey(), $temp);
            }

            if ($source) {
                $this->getListingFromRequest()
                     ->getParentObject()
                    ->setSetting(
                        'additional_data',
                        array('ebay_category_settings_mode', $source),
                        $mode
                    )
                     ->save();
            }

            return $this->_redirect(
                '*/*/', array(
                'step' => 2,
                '_current' => true,
                'skip_get_suggested' => null
                )
            );
        }

        $this->setWizardStep('categoryStepOne');

        $defaultMode = CategoryTemplateBlock::MODE_SAME;
        if ($source == SourceModeBlock::SOURCE_CATEGORIES) {
            $defaultMode = CategoryTemplateBlock::MODE_CATEGORY;
        }

        $mode = null;

        $temp = $this->getListingFromRequest()
            ->getSetting('additional_data', array('ebay_category_settings_mode', $source));

        $temp && $mode = $temp;

        $temp = $this->getSessionValue('mode');
        $temp && $mode = $temp;

        if ($mode) {
           !in_array($mode, array('same','category','product','manually')) && $mode = $defaultMode;
        } else {
            $mode = $defaultMode;
        }

        $this->clearSession();

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_mode');
        $block->setData('mode', $mode);

        $this->_initAction()
             ->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
             ->_addContent($block)
             ->renderLayout();
    }

    //########################################

    protected function stepTwoModeSame()
    {
        if ($this->getRequest()->isPost()) {
            $categoryParam = $this->getRequest()->getParam('category_data');
            $categoryData = array();
            if (!empty($categoryParam)) {
                $categoryData = Mage::helper('M2ePro')->jsonDecode($categoryParam);
            }

            $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);

            $data = array();
            $keys = array(
                'category_main_mode',
                'category_main_id',
                'category_main_attribute',

                'category_secondary_mode',
                'category_secondary_id',
                'category_secondary_attribute',

                'store_category_main_mode',
                'store_category_main_id',
                'store_category_main_attribute',

                'store_category_secondary_mode',
                'store_category_secondary_id',
                'store_category_secondary_attribute',
            );
            foreach ($categoryData as $key => $value) {
                if (!in_array($key, $keys)) {
                    continue;
                }

                $data[$key] = $value;
            }

            $listingId = $this->getRequest()->getParam('listing_id');
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

            $this->addCategoriesPath($data, $listing);
            $data['marketplace_id'] = $listing->getMarketplaceId();

            $templates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()->getItemsByPrimaryCategories(
                array($data)
            );

            $templateExists = (bool)$templates;

            $specifics = array();
            /** @var $categoryTemplate Ess_M2ePro_Model_Ebay_Template_Category */
            if ($categoryTemplate = reset($templates)) {
                $specifics = $categoryTemplate->getSpecifics();
            }

            $useLastSpecifics = $this->useLastSpecifics();

            $sessionData['mode_same']['category'] = $data;
            $sessionData['mode_same']['specific'] = $specifics;

            Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, $sessionData);

            if (!$useLastSpecifics || !$templateExists) {
                return $this->_redirect(
                    '*/*', array('_current' => true, 'step' => 3)
                );
            }

            $builderData = $data;
            $builderData['account_id'] = $this->getListingFromRequest()->getParentObject()->getAccountId();
            $builderData['marketplace_id'] = $this->getListingFromRequest()->getParentObject()->getMarketplaceId();

            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);

            $this->saveModeSame(
                $categoryTemplate, $otherCategoryTemplate, !empty($sessionData['mode_same']['remember'])
            );

            return $this->reviewAction();
        }

        $this->setWizardStep('categoryStepTwo');

        $listing = $this->getListingFromRequest();
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);

        $internalData = array();

        $internalData = array_merge(
            $internalData, $listing->getLastPrimaryCategory(array('ebay_primary_category','mode_same'))
        );
        $internalData = array_merge(
            $internalData, $listing->getLastPrimaryCategory(array('ebay_store_primary_category','mode_same'))
        );

        !empty($sessionData['mode_same']['category']) && $internalData = $sessionData['mode_same']['category'];

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/UQAJAQ");

        $this->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_ebay_listing_category_same_chooser', '',
                    array(
                    'internal_data' => $internalData
                    )
                )
            )->renderLayout();
    }

    protected function stepTwoModeCategory()
    {
        $categoriesIds = $this->getCategoriesIdsByListingProductsIds(
            $this->getListingFromRequest()->getAddedListingProductsIds()
        );

        if (empty($categoriesIds) && !$this->getRequest()->isXmlHttpRequest()) {
            $this->_getSession()->addError(
                Mage::helper('M2ePro')->__(
                    'Magento Category is not provided for the products you are currently adding.
                Please go back and select a different option to assign Channel category to your products. '
                )
            );
        }

        $this->initSessionData($categoriesIds);

        $listing = $this->getListingFromRequest();

        $previousCategoriesData = array();

        $tempData = $listing->getLastPrimaryCategory(array('ebay_primary_category','mode_category'));
        foreach ($tempData as $categoryId => $data) {
            !isset($previousCategoriesData[$categoryId]) && $previousCategoriesData[$categoryId] = array();
            $previousCategoriesData[$categoryId] += $data;
        }

        $tempData = $listing->getLastPrimaryCategory(array('ebay_store_primary_category','mode_category'));
        foreach ($tempData as $categoryId => $data) {
            !isset($previousCategoriesData[$categoryId]) && $previousCategoriesData[$categoryId] = array();
            $previousCategoriesData[$categoryId] += $data;
        }

        $categoriesData = $this->getSessionValue($this->getSessionDataKey());

        foreach ($categoriesData as $magentoCategoryId => &$data) {
            if (!isset($previousCategoriesData[$magentoCategoryId])) {
                continue;
            }

            $listingProductsIds = $this->getSelectedListingProductsIdsByCategoriesIds(array($magentoCategoryId));
            $data['listing_products_ids'] = $listingProductsIds;

            if ($data['category_main_mode'] != Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE) {
                continue;
            }

            $this->addCategoriesPath($previousCategoriesData[$magentoCategoryId], $listing->getParentObject());

            $data = array_merge($data, $previousCategoriesData[$magentoCategoryId]);
        }

        $this->setSessionValue($this->getSessionDataKey(), $categoriesData);

        $this->setWizardStep('categoryStepTwo');

        $block = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_category_category'
        );
        $block->getChild('grid')->setStoreId($listing->getParentObject()->getStoreId());
        $block->getChild('grid')->setCategoriesData($categoriesData);

        if ($this->getRequest()->isXmlHttpRequest()) {
            return $this->loadLayout()->getResponse()->setBody($block->getChild('grid')->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/UAAJAQ");

        $this->_title(Mage::helper('M2ePro')->__('Select Products (eBay Categories)'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Ebay/Listing/Category/GridHandler.js')
             ->addJs('M2ePro/Ebay/Listing/Category/Category/GridHandler.js');

         $this->_addContent($block)
              ->renderLayout();
    }

    protected function stepTwoModeManually()
    {
        $this->stepTwoModeProduct(false);
    }

    protected function stepTwoModeProduct($getSuggested = true)
    {
        $this->setWizardStep('categoryStepTwo');

        $this->_initAction();

        // ---------------------------------------
        $listing = Mage::helper('M2ePro/Data_Global')->getValue('temp_data');
        $listingProductAddIds = (array)Mage::helper('M2ePro')->jsonDecode($listing->getData('product_add_ids'));
        // ---------------------------------------

        // ---------------------------------------
        if (!$this->getRequest()->getParam('skip_get_suggested')) {
            Mage::helper('M2ePro/Data_Global')->setValue('get_suggested', $getSuggested);
        }

        $this->initSessionData($listingProductAddIds);
        // ---------------------------------------

        $this->getLayout()->getBlock('head')
            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/GridHandler.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Product/SuggestedSearchHandler.js')
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addCss('M2ePro/css/Plugin/DropDown.css');

        if ($getSuggested) {
            $this->setPageHelpLink(null, null, "x/ZwAJAQ");
        } else {
            $this->setPageHelpLink(null, null, "x/JQAJAQ");
        }

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product'));
        $this->renderLayout();
    }

    // ---------------------------------------

    public function stepTwoModeProductGridAction()
    {
        // ---------------------------------------
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        // ---------------------------------------

        // ---------------------------------------
        Mage::helper('M2ePro/Data_Global')->setValue('temp_data', $listing);
        // ---------------------------------------

        $this->loadLayout();

        $body = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_product_grid')->toHtml();
        $this->getResponse()->setBody($body);
    }

    // ---------------------------------------

    public function stepTwoGetSuggestedCategoryAction()
    {
        $this->loadLayout();

        // ---------------------------------------
        $listingProductIds = $this->getRequestIds();
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);
        $marketplaceId = (int)$listing->getData('marketplace_id');
        // ---------------------------------------

        // ---------------------------------------
        /** @var Ess_M2ePro_Model_Resource_Listing_Collection $collection */
        $collection = Mage::getResourceModel('M2ePro/Ebay_Listing')->getProductCollection($listingId);
        $collection->addAttributeToSelect('name');
        $collection->getSelect()->where('lp.id IN (?)', $listingProductIds);
        // ---------------------------------------

        if ($collection->getSize() == 0) {
            $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array()));
            return;
        }

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);

        $result = array('failed' => 0, 'succeeded' => 0);

        // ---------------------------------------
        foreach ($collection->getItems() as $product) {
            if (($query = $product->getData('name')) == '') {
                $result['failed']++;
                continue;
            }

            $attributeSetId = $product->getData('attribute_set_id');
            if (!Mage::helper('M2ePro/Magento_AttributeSet')->isDefault($attributeSetId)) {
                $query .= ' ' . Mage::helper('M2ePro/Magento_AttributeSet')->getName($attributeSetId);
            }

            try {
                $dispatcherObject = Mage::getModel('M2ePro/Ebay_Connector_Dispatcher');
                $connectorObj = $dispatcherObject->getConnector(
                    'category', 'get', 'suggested',
                    array('query' => $query), $marketplaceId
                );

                $dispatcherObject->process($connectorObj);
                $suggestions = $connectorObj->getResponseData();
            } catch (Exception $e) {
                $result['failed']++;
                continue;
            }

            if (!empty($suggestions)) {
                foreach ($suggestions as $key => $suggestion) {
                    if (!is_numeric($key)) {
                        unset($suggestions[$key]);
                    }
                }
            }

            if (empty($suggestions)) {
                $result['failed']++;
                continue;
            }

            $suggestedCategory = reset($suggestions);

            $categoryExists = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')
                ->exists(
                    $suggestedCategory['category_id'],
                    $marketplaceId
                );

            if (!$categoryExists) {
                $result['failed']++;
                continue;
            }

            $listingProductId = $product->getData('listing_product_id');
            $listingProductData = $sessionData['mode_product'][$listingProductId];
            $listingProductData['category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY;
            $listingProductData['category_main_id'] = $suggestedCategory['category_id'];
            $listingProductData['category_main_path'] = implode(' > ', $suggestedCategory['category_path']);

            $sessionData['mode_product'][$listingProductId] = $listingProductData;

            $result['succeeded']++;
        }

        // ---------------------------------------

        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, $sessionData);

        $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($result));
    }

    // ---------------------------------------

    public function stepTwoSuggestedResetAction()
    {
        // ---------------------------------------
        $listingProductIds = $this->getRequestIds();
        // ---------------------------------------

        $this->initSessionData($listingProductIds, true);
    }

    // ---------------------------------------

    public function stepTwoSaveToSessionAction()
    {
        $ids = $this->getRequestIds();
        $templateData = $this->getRequest()->getParam('template_data');
        $templateData = (array)Mage::helper('M2ePro')->jsonDecode($templateData);

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        $this->addCategoriesPath($templateData, $listing);

        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        if ($this->getSessionValue('mode') == CategoryTemplateBlock::MODE_CATEGORY) {
            foreach ($ids as $categoryId) {
                $sessionData[$categoryId]['listing_products_ids'] = $this->getSelectedListingProductsIdsByCategoriesIds(
                    array($categoryId)
                );
            }
        }

        foreach ($ids as $id) {
            $sessionData[$id] = array_merge($sessionData[$id], $templateData);
        }

        $this->setSessionValue($key, $sessionData);
    }

    // ---------------------------------------

    public function stepTwoModeValidateAction()
    {
        $sessionData = $this->getSessionValue($this->getSessionDataKey());
        $sessionData = $this->convertCategoriesIdstoProductIds($sessionData);

        $this->clearSpecificsSession();

        $failedProductsIds   = array();
        $succeedProducersIds = array();
        foreach ($sessionData as $listingProductId => $categoryData) {
            if ($categoryData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $key = 'category_main_id';
            } else {
                $key = 'category_main_attribute';
            }

            if (!$categoryData[$key]) {
                $failedProductsIds[] = $listingProductId;
            } else {
                $succeedProducersIds[] = $listingProductId;
            }
        }

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'validation'      => empty($failedProductsIds),
                'total_count'     => count($failedProductsIds) + count($succeedProducersIds),
                'failed_count'    => count($failedProductsIds),
                'failed_products' => $failedProductsIds
                )
            )
        );
    }

    // ---------------------------------------

    public function stepTwoDeleteProductsModeProductAction()
    {
        $ids = $this->getRequestIds();
        $ids = array_map('intval', $ids);

        $sessionData = $this->getSessionValue('mode_product');
        foreach ($ids as $id) {
            unset($sessionData[$id]);
        }

        $this->setSessionValue('mode_product', $sessionData);

        $collection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $ids));

        foreach ($collection->getItems() as $listingProduct) {
            $listingProduct->deleteInstance();
        }

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        $listingProductAddIds = $this->getListingFromRequest()->getAddedListingProductsIds();
        if (empty($listingProductAddIds)) {
            return;
        }

        $listingProductAddIds = array_map('intval', $listingProductAddIds);
        $listingProductAddIds = array_diff($listingProductAddIds, $ids);

        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($listingProductAddIds))->save();
    }

    //########################################

    protected function stepThreeModeSame()
    {
        if ($this->getRequest()->isPost()) {
            $specifics = $this->getRequest()->getParam('specific_data');

            if ($specifics) {
                $specifics = Mage::helper('M2ePro')->jsonDecode($specifics);
                $specifics = $specifics['specifics'];
            } else {
                $specifics = array();
            }

            $sessionData = $this->getSessionValue($this->getSessionDataKey());

            // save category template & specifics
            // ---------------------------------------
            $builderData = $sessionData['category'];
            $builderData['specifics'] = $specifics;
            $builderData['account_id'] = $this->getListingFromRequest()->getParentObject()->getAccountId();
            $builderData['marketplace_id'] = $this->getListingFromRequest()->getParentObject()->getMarketplaceId();

            $categoryTemplate = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData);
            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);

            $this->saveModeSame($categoryTemplate, $otherCategoryTemplate, !empty($sessionData['remember']));

            return $this->reviewAction();
        }

        $this->setWizardStep('categoryStepThree');

        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);
        $selectedCategoryMode = $sessionData['mode_same']['category']['category_main_mode'];
        if ($selectedCategoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $selectedCategoryValue = $sessionData['mode_same']['category']['category_main_id'];
        } else {
            $selectedCategoryValue = $sessionData['mode_same']['category']['category_main_attribute'];
        }

        $specificBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_category_same_specific', '',
            array(
                'category_mode' => $selectedCategoryMode,
                'category_value' => $selectedCategoryValue,
                'specifics' => $sessionData['mode_same']['specific']
            )
        );

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/TQAJAQ");

        $this->_title(Mage::helper('M2ePro')->__('Set Your eBay Categories'))
            ->_addContent($specificBlock)
            ->renderLayout();
    }

    protected function stepThreeModeCategory()
    {
        $this->stepThree();
    }

    protected function stepThreeModeProduct()
    {
        $this->stepThree();
    }

    protected function stepThreeModeManually()
    {
        $this->stepThree();
    }

    protected function stepThree()
    {
        $this->setWizardStep('categoryStepThree');

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        $templatesData = $this->getTemplatesData();

        if (empty($templatesData)) {
            $this->save($this->getSessionValue($this->getSessionDataKey()));
            return $this->_redirect(
                '*/*/review', array(
                'listing_id' => $this->getRequest()->getParam('listing_id'),
                'disable_list' => true
                )
            );
        }

        $this->initSpecificsSessionData($templatesData);

        $useLastSpecifics = $this->useLastSpecifics();

        $templatesExistForAll = true;
        foreach ($this->getSessionValue('specifics') as $categoryId => $specificsData) {
            if ($specificsData['template_exists'] && $useLastSpecifics) {
                unset($templatesData[$categoryId]);
            } else {
                $templatesExistForAll = false;
            }
        }

        if ($templatesExistForAll && $useLastSpecifics) {
            $this->save($this->getSessionValue($this->getSessionDataKey()));
            return $this->reviewAction();
        }

        $currentPrimaryCategory = $this->getCurrentPrimaryCategory();

        $this->setSessionValue('current_primary_category', $currentPrimaryCategory);

        $wrapper = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific_wrapper');
        $wrapper->setData('store_id', $listing->getStoreId());
        $wrapper->setData('categories', $templatesData);
        $wrapper->setData('current_category', $currentPrimaryCategory);

        $wrapper->setChild('specific', $this->getSpecificBlock());

        $this->_initAction();

        $this->getLayout()->getBlock('head')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Ebay/Listing/Category/Specific/WrapperHandler.js');

        $this->setPageHelpLink(null, null, "x/TQAJAQ");

        $this->_title(Mage::helper('M2ePro')->__('Specifics'));

        $this->_addContent($wrapper)
              ->renderLayout();
    }

    // ---------------------------------------

    public function stepThreeGetCategorySpecificsAction()
    {
        $category = $this->getRequest()->getParam('category');
        $templateData = $this->getTemplatesData();
        $templateData = $templateData[$category];

        if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $listingId = $this->getRequest()->getParam('listing_id');
            $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

            $hasRequiredSpecifics = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->hasRequiredSpecifics(
                $templateData['category_main_id'],
                $listing->getMarketplaceId()
            );
        } else {
            $hasRequiredSpecifics = true;
        }

        $this->setSessionValue('current_primary_category', $category);

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'text' => $this->getSpecificBlock()->toHtml(),
                'hasRequiredSpecifics' => $hasRequiredSpecifics
                )
            )
        );
    }

    // ---------------------------------------

    public function stepThreeSaveCategorySpecificsToSessionAction()
    {
        $category = $this->getRequest()->getParam('category');
        $categorySpecificsData = Mage::helper('M2ePro')->jsonDecode($this->getRequest()->getParam('data'));

        $sessionSpecificsData = $this->getSessionValue('specifics');

        $sessionSpecificsData[$category] = array_merge(
            $sessionSpecificsData[$category],
            array('specifics' => $categorySpecificsData['specifics'])
        );

        $this->setSessionValue('specifics', $sessionSpecificsData);
    }

    //########################################

    protected function checkProductAddIds()
    {
        $ids = $this->getListingFromRequest()->getAddedListingProductsIds();
        return !empty($ids);
    }

    //########################################

    protected function initSessionData($ids, $override = false)
    {
        $key = $this->getSessionDataKey();

        $sessionData = $this->getSessionValue($key);
        !$sessionData && $sessionData = array();

        foreach ($ids as $id) {
            if (!empty($sessionData[$id]) && !$override) {
                continue;
            }

            $sessionData[$id] = array(
                'category_main_id' => null,
                'category_main_path' => null,
                'category_main_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'category_main_attribute' => null,

                'category_secondary_id' => null,
                'category_secondary_path' => null,
                'category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'category_secondary_attribute' => null,

                'store_category_main_id' => null,
                'store_category_main_path' => null,
                'store_category_main_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'store_category_main_attribute' => null,

                'store_category_secondary_id' => null,
                'store_category_secondary_path' => null,
                'store_category_secondary_mode' => Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE,
                'store_category_secondary_attribute' => null,
            );
        }

        if (!$override) {
            foreach (array_diff(array_keys($sessionData), $ids) as $id) {
                unset($sessionData[$id]);
            }
        }

        $this->setSessionValue($key, $sessionData);
    }

    //########################################

    protected function setSessionValue($key, $value)
    {
        $sessionData = $this->getSessionValue();
        $sessionData[$key] = $value;

        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, $sessionData);

        return $this;
    }

    protected function getSessionValue($key = null)
    {
        $sessionData = Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey);

        if ($sessionData === null) {
            $sessionData = array();
        }

        if ($key === null) {
            return $sessionData;
        }

        return isset($sessionData[$key]) ? $sessionData[$key] : null;
    }

    protected function getSessionDataKey()
    {
        $key = '';

        switch (strtolower($this->getSessionValue('mode'))) {
            case CategoryTemplateBlock::MODE_SAME:
                $key = 'mode_same';
                break;
            case CategoryTemplateBlock::MODE_CATEGORY:
                $key = 'mode_category';
                break;
            case CategoryTemplateBlock::MODE_PRODUCT:
            case CategoryTemplateBlock::MODE_MANUALLY:
                $key = 'mode_product';
                break;
        }

        return $key;
    }

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->getValue($this->_sessionKey, true);
    }

    //########################################

    public function reviewAction()
    {
        $ids = Mage::helper('M2ePro/Data_Session')->getValue('added_products_ids');

        if (empty($ids) || $this->getRequest()->getParam('disable_list')) {
            return $this->_redirect(
                '*/adminhtml_ebay_listing/view', array(
                'id' => $this->getRequest()->getParam('listing_id')
                )
            );
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/SAAJAQ");

        /** @var Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Product_Review $blockReview */
        $blockReview = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_ebay_listing_product_review', '', array(
            'products_count' => count($ids)
            )
        );

        $listing = $this->getListingFromRequest()->getParentObject();
        $additionalData = $listing->getSettings('additional_data');

        if (isset($additionalData['source']) && $source = $additionalData['source']) {
            $blockReview->setSource($source);
        }

        unset($additionalData['source']);
        $listing->setSettings('additional_data', $additionalData);
        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode(array()));
        $listing->save();

        $this->_title(Mage::helper('M2ePro')->__('Listing Review'))
            ->_addContent($blockReview)
            ->renderLayout();
    }

    //########################################

    protected function setWizardStep($step)
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStep(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK, $step);
    }

    protected function endWizard()
    {
        $wizardHelper = Mage::helper('M2ePro/Module_Wizard');

        if (!$wizardHelper->isActive(Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK)) {
            return;
        }

        $wizardHelper->setStatus(
            Ess_M2ePro_Helper_View_Ebay::WIZARD_INSTALLATION_NICK,
            Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED
        );

        Mage::helper('M2ePro/Magento')->clearMenuCache();
    }

    //########################################

    protected function endListingCreation()
    {
        $listing = $this->getListingFromRequest();

        Mage::helper('M2ePro/Data_Session')->setValue(
            'added_products_ids',
            $this->getListingFromRequest()->getAddedListingProductsIds()
        );

        $sessionData = $this->getSessionValue($this->getSessionDataKey());

        if ($this->getSessionValue('mode') == CategoryTemplateBlock::MODE_SAME) {
            $listing->updateLastPrimaryCategory(
                array('ebay_primary_category', 'mode_same'),
                array('category_main_id' => $sessionData['category']['category_main_id'],
                      'category_main_mode' => $sessionData['category']['category_main_mode'],
                      'category_main_attribute' => $sessionData['category']['category_main_attribute'])
            );

            $listing->updateLastPrimaryCategory(
                array('ebay_store_primary_category', 'mode_same'),
                array('store_category_main_id' => $sessionData['category']['store_category_main_id'],
                      'store_category_main_mode' => $sessionData['category']['store_category_main_mode'],
                      'store_category_main_attribute' => $sessionData['category']['store_category_main_attribute'])
            );
        } elseif ($this->getSessionValue('mode') == CategoryTemplateBlock::MODE_CATEGORY) {
            foreach ($sessionData as $magentoCategoryId => $data) {
                $listing->updateLastPrimaryCategory(
                    array('ebay_primary_category', 'mode_category', $magentoCategoryId),
                    array(
                        'category_main_id' => $data['category_main_id'],
                        'category_main_mode' => $data['category_main_mode'],
                        'category_main_attribute' => $data['category_main_attribute']
                    )
                );

                $listing->updateLastPrimaryCategory(
                    array('ebay_store_primary_category', 'mode_category', $magentoCategoryId),
                    array(
                        'store_category_main_id' => $data['store_category_main_id'],
                        'store_category_main_mode' => $data['store_category_main_mode'],
                        'store_category_main_attribute' => $data['store_category_main_attribute']
                    )
                );
            }
        }

        //-- Remove successfully moved 3rd party items
        $additionalData = $listing->getParentObject()->getSettings('additional_data');
        if (isset($additionalData['source']) && $additionalData['source'] == SourceModeBlock::SOURCE_OTHER) {
            $this->deleteListingOthers();
        }

        //--

        $this->clearSession();
    }

    //########################################

    protected function getSpecificBlock()
    {
        $templatesData = $this->getTemplatesData();
        $currentPrimaryCategory = $this->getCurrentPrimaryCategory();

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        /** @var $specific Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Specific */
        $specific = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_specific');
        $specific->setMarketplaceId($listing->getMarketplaceId());

        $currentTemplateData = $templatesData[$currentPrimaryCategory];

        $categoryMode = $currentTemplateData['category_main_mode'];
        $specific->setCategoryMode($categoryMode);

        if ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
            $specific->setCategoryValue($currentTemplateData['category_main_id']);
        } elseif ($categoryMode == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
            $specific->setCategoryValue($currentTemplateData['category_main_attribute']);
        }

        $specificsData = $this->getSessionValue('specifics');

        $specific->setInternalData($specificsData[$currentPrimaryCategory]);
        $specific->setSelectedSpecifics($specificsData[$currentPrimaryCategory]['specifics']);

        return $specific;
    }

    //########################################

    public function getChooserBlockHtmlAction()
    {
        $ids = $this->getRequestIds();

        $key = $this->getSessionDataKey();
        $sessionData = $this->getSessionValue($key);

        $neededData = array();

        foreach ($ids as $id) {
            $neededData[$id] = $sessionData[$id];
        }

        // ---------------------------------------

        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        $accountId = $listing->getAccountId();
        $marketplaceId = $listing->getMarketplaceId();
        $internalData  = $this->getInternalDataForChooserBlock($neededData);

        /** @var $chooserBlock Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Category_Chooser */
        $chooserBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser');
        $chooserBlock->setDivId('chooser_main_container');
        $chooserBlock->setAccountId($accountId);
        $chooserBlock->setMarketplaceId($marketplaceId);
        $chooserBlock->setInternalData($internalData);

        // ---------------------------------------
        $wrapper = $this->getLayout()->createBlock('M2ePro/adminhtml_ebay_listing_category_chooser_wrapper');
        $wrapper->setChild('chooser', $chooserBlock);
        // ---------------------------------------

        $this->getResponse()->setBody($wrapper->toHtml());
    }

    //########################################

    protected function getInternalDataForChooserBlock($data)
    {
        $resultData = array();

        $firstData = reset($data);

        $tempKeys = array('category_main_id',
                          'category_main_path',
                          'category_main_mode',
                          'category_main_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data, $tempKeys)) {
            $resultData['category_main_id'] = 0;
            $resultData['category_main_path'] = null;
            $resultData['category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['category_main_attribute'] = null;
            $resultData['category_main_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------

        $tempKeys = array('category_secondary_id',
                          'category_secondary_path',
                          'category_secondary_mode',
                          'category_secondary_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data, $tempKeys)) {
            $resultData['category_secondary_id'] = 0;
            $resultData['category_secondary_path'] = null;
            $resultData['category_secondary_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['category_secondary_attribute'] = null;
            $resultData['category_secondary_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------

        $tempKeys = array('store_category_main_id',
                          'store_category_main_path',
                          'store_category_main_mode',
                          'store_category_main_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data, $tempKeys)) {
            $resultData['store_category_main_id'] = 0;
            $resultData['store_category_main_path'] = null;
            $resultData['store_category_main_mode'] = Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['store_category_main_attribute'] = null;
            $resultData['store_category_main_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------

        $tempKeys = array('store_category_secondary_id',
                          'store_category_secondary_path',
                          'store_category_secondary_mode',
                          'store_category_secondary_attribute');

        foreach ($tempKeys as $key) {
            $resultData[$key] = $firstData[$key];
        }

        if (!Mage::helper('M2ePro')->theSameItemsInData($data, $tempKeys)) {
            $resultData['store_category_secondary_id'] = 0;
            $resultData['store_category_secondary_path'] = null;
            $resultData['store_category_secondary_mode'] =
                Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
            $resultData['store_category_secondary_attribute'] = null;
            $resultData['store_category_secondary_message'] = Mage::helper('M2ePro')->__(
                'Please, specify a value suitable for all chosen Products.'
            );
        }

        // ---------------------------------------

        return $resultData;
    }

    //########################################

    protected function clearSpecificsSession()
    {
        $this->setSessionValue('specifics', null);
        $this->setSessionValue('current_primary_category', null);
    }

    //########################################

    protected function getCurrentPrimaryCategory()
    {
        $currentPrimaryCategory = $this->getSessionValue('current_primary_category');

        if ($currentPrimaryCategory !== null) {
            return $currentPrimaryCategory;
        }

        $useLastSpecifics = $this->useLastSpecifics();

        $specifics = $this->getSessionValue('specifics');

        if (!$useLastSpecifics) {
            return key($specifics);
        }

        foreach ($specifics as $id => $specificsData) {
            if (!$specificsData['template_exists']) {
                $currentPrimaryCategory = $id;
                break;
            }
        }

        return $currentPrimaryCategory;
    }

    protected function getTemplatesData()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId);

        $templatesData = array();
        foreach ($this->getSessionValue($this->getSessionDataKey()) as $templateData) {
            if ($templateData['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $id = $templateData['category_main_id'];
            } else {
                $id = $templateData['category_main_attribute'];
            }

            if (empty($id)) {
                continue;
            }

            $templateData['marketplace_id'] = $listing->getMarketplaceId();
            $templatesData[$id] = $templateData;
        }

        ksort($templatesData);
        $templatesData = array_reverse($templatesData, true);

        return $templatesData;
    }

    //########################################

    protected function initSpecificsSessionData($templatesData)
    {
        $specificsData = $this->getSessionValue('specifics');
        $specificsData === null && $specificsData = array();

        $existingTemplates = Mage::getModel('M2ePro/Ebay_Template_Category')->getCollection()
            ->getItemsByPrimaryCategories($templatesData);

        foreach ($templatesData as $id => $templateData) {
            if (!empty($specificsData[$id])) {
                continue;
            }

            $specifics = array();
            $templateExists = false;

            if (isset($existingTemplates[$id])) {
                $specifics = $existingTemplates[$id]->getSpecifics();
                $templateExists = true;
            }

            $specificsData[$id] = array(
                'specifics' => $specifics,
                'template_exists' => $templateExists
            );
        }

        $this->setSessionValue('specifics', $specificsData);
    }

    //########################################

    protected function getSelectedListingProductsIdsByCategoriesIds($categoriesIds)
    {
        $productsIds = Mage::helper('M2ePro/Magento_Category')->getProductsFromCategories($categoriesIds);

        $listingProductIds = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
                ->addFieldToFilter('product_id', array('in' => $productsIds))->getAllIds();

        return array_values(
            array_intersect(
                $this->getListingFromRequest()->getAddedListingProductsIds(),
                $listingProductIds
            )
        );
    }

    //########################################

    public function saveAction()
    {
        $this->save($this->getSessionValue($this->getSessionDataKey()));
    }

    // ---------------------------------------

    protected function saveModeSame($categoryTemplate, $otherCategoryTemplate, $remember)
    {
        $this->assignTemplatesToProducts(
            $categoryTemplate->getId(),
            $otherCategoryTemplate->getId(),
            $this->getListingFromRequest()->getAddedListingProductsIds()
        );

        if ($remember) {
            $this->getListingFromRequest()->getParentObject()
                ->setSetting(
                    'additional_data', 'mode_same_category_data',
                    array_merge(
                        $categoryTemplate->getData(),
                        $otherCategoryTemplate->getData(),
                        array('specifics' => $categoryTemplate->getSpecifics())
                    )
                )
                ->save();
        }

        $this->endWizard();
        $this->endListingCreation();
    }

    protected function save($sessionData)
    {
        $specificsData = $this->getSessionValue('specifics');

        $sessionData = $this->convertCategoriesIdstoProductIds($sessionData);
        $sessionData = $this->getUniqueTemplatesData($sessionData);

        foreach ($sessionData as $templateData) {
            $listingProductsIds = $templateData['listing_products_ids'];
            $listingProductsIds = array_unique($listingProductsIds);

            if (empty($listingProductsIds)) {
                continue;
            }

            // category has not been selected
            if ($templateData['identifier'] === null) {
                $this->deleteListingProducts($listingProductsIds);
                continue;
            }

            // save category template & specifics
            // ---------------------------------------
            $builderData = $templateData;
            $builderData['account_id'] = $this->getListingFromRequest()->getParentObject()->getAccountId();
            $builderData['marketplace_id'] = $this->getListingFromRequest()->getParentObject()->getMarketplaceId();

            $builderData['specifics'] = $specificsData[$templateData['identifier']]['specifics'];
            $categoryTemplateId = Mage::getModel('M2ePro/Ebay_Template_Category_Builder')->build($builderData)
                                                                                         ->getId();

            $otherCategoryTemplate = Mage::getModel('M2ePro/Ebay_Template_OtherCategory_Builder')->build($builderData);
            // ---------------------------------------

            $this->assignTemplatesToProducts(
                $categoryTemplateId,
                $otherCategoryTemplate->getId(),
                $listingProductsIds
            );
        }

        $this->endWizard();
        $this->endListingCreation();
    }

    protected function getUniqueTemplatesData($templatesData)
    {
        $unique = array();

        foreach ($templatesData as $listingProductId => $data) {
            $hash = sha1(Mage::helper('M2ePro')->jsonEncode($data));

            $data['identifier'] = null;

            if ($data['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['identifier'] = $data['category_main_id'];
            }

            if ($data['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_ATTRIBUTE) {
                $data['identifier'] = $data['category_main_attribute'];
            }

            !isset($unique[$hash]) && $unique[$hash] = array();

            $unique[$hash] = array_merge($unique[$hash], $data);
            $unique[$hash]['listing_products_ids'][] = $listingProductId;
        }

        return array_values($unique);
    }

    //########################################

    protected function convertCategoriesIdstoProductIds($sessionData)
    {
        if ($this->getSessionValue('mode') !== CategoryTemplateBlock::MODE_CATEGORY) {
            return $sessionData;
        }

        foreach ($sessionData as $categoryId => $data) {
            $listingProductsIds = array();

            if (isset($data['listing_products_ids'])) {
                $listingProductsIds = $data['listing_products_ids'];
                unset($data['listing_products_ids']);
            }

            unset($sessionData[$categoryId]);

            foreach ($listingProductsIds as $listingProductId) {
                $sessionData[$listingProductId] = $data;
            }
        }

        foreach ($this->getListingFromRequest()->getAddedListingProductsIds() as $listingProductId) {
            if (!array_key_exists($listingProductId, $sessionData)) {
                $sessionData[$listingProductId]['category_main_mode'] =
                    Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_NONE;
                $sessionData[$listingProductId]['category_main_id'] = null;
                $sessionData[$listingProductId]['category_main_attribute'] = null;
            }
        }

        return $sessionData;
    }

    //########################################

    protected function getCategoriesIdsByListingProductsIds($listingProductsIds)
    {
        $listingProductCollection = Mage::helper('M2ePro/Component_Ebay')
            ->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $listingProductsIds));

        $productsIds = array();
        foreach ($listingProductCollection->getData() as $item) {
            $productsIds[] = $item['product_id'];
        }

        $productsIds = array_unique($productsIds);

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing',
            $this->getRequest()->getParam('listing_id')
        );

        return Mage::helper('M2ePro/Magento_Category')->getLimitedCategoriesByProducts(
            $productsIds,
            $listing->getStoreId()
        );
    }

    //########################################

    protected function addCategoriesPath(&$data,Ess_M2ePro_Model_Listing $listing)
    {
        $marketplaceId = $listing->getData('marketplace_id');
        $accountId = $listing->getAccountId();

        if (isset($data['category_main_mode'])) {
            if ($data['category_main_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $data['category_main_id'],
                    $marketplaceId
                );
            } else {
                $data['category_main_path'] = null;
            }
        }

        if (isset($data['category_secondary_mode'])) {
            if ($data['category_secondary_mode'] == Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Ebay')->getPath(
                    $data['category_secondary_id'],
                    $marketplaceId
                );
            } else {
                $data['category_secondary_path'] = null;
            }
        }

        if (isset($data['store_category_main_mode'])) {
            if ($data['store_category_main_mode'] ==
                    Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['store_category_main_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Store')
                    ->getPath(
                        $data['store_category_main_id'],
                        $accountId
                    );
            } else {
                $data['store_category_main_path'] = null;
            }
        }

        if (isset($data['store_category_secondary_mode'])) {
            if ($data['store_category_secondary_mode'] ==
                    Ess_M2ePro_Model_Ebay_Template_Category::CATEGORY_MODE_EBAY) {
                $data['store_category_secondary_path'] = Mage::helper('M2ePro/Component_Ebay_Category_Store')
                    ->getPath(
                        $data['store_category_secondary_id'],
                        $accountId
                    );
            } else {
                $data['store_category_secondary_path'] = null;
            }
        }
    }

    //########################################

    /** @return Ess_M2ePro_Model_Ebay_Listing
     * @throws Exception
     */
    protected function getListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('listing_id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component_Ebay')->getCachedObject('Listing', $listingId)->getChildObject();
    }

    //########################################

    protected function assignTemplatesToProducts($categoryTemplateId, $otherCategoryTemplateId, $productsIds)
    {
        if (empty($productsIds)) {
            return;
        }

        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');

        $connWrite->update(
            Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('M2ePro/Ebay_Listing_Product'),
            array(
                'template_category_id'       => $categoryTemplateId,
                'template_other_category_id' => $otherCategoryTemplateId
            ),
            'listing_product_id IN ('.implode(',', $productsIds).')'
        );
    }

    // ---------------------------------------

    protected function deleteListingProducts($ids)
    {
        $ids = array_map('intval', $ids);

        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product')
            ->addFieldToFilter('id', array('in' => $ids));

        foreach ($collection->getItems() as $listingProduct) {
            /**@var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $listingProduct->canBeForceDeleted(true);
            $listingProduct->deleteInstance();
        }

        $listing = Mage::helper('M2ePro/Component_Ebay')->getCachedObject(
            'Listing', (int)$this->getRequest()->getParam('listing_id')
        );

        $listingProductAddIds = $this->getListingFromRequest()->getAddedListingProductsIds();
        if (empty($listingProductAddIds)) {
            return;
        }

        $listingProductAddIds = array_map('intval', $listingProductAddIds);
        $listingProductAddIds = array_diff($listingProductAddIds, $ids);

        $listing->setData('product_add_ids', Mage::helper('M2ePro')->jsonEncode($listingProductAddIds));
        $listing->save();
    }

    protected function deleteListingOthers()
    {
        $listingProductsIds = $this->getListingFromRequest()->getAddedListingProductsIds();
        if (empty($listingProductsIds)) {
            return;
        }

        $otherProductsIds = array();

        /** @var Ess_M2ePro_Model_Resource_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Product');
        $collection->addFieldToFilter('id', array('in' => $listingProductsIds));
        foreach ($collection->getItems() as $listingProduct) {
            /** @var Ess_M2ePro_Model_Listing_Product $listingProduct */
            $otherProductsIds[] = (int)$listingProduct->getSetting(
                'additional_data', $listingProduct::MOVING_LISTING_OTHER_SOURCE_KEY
            );
        }

        if (empty($otherProductsIds)) {
            return;
        }

        /** @var Ess_M2ePro_Model_Resource_Listing_Other_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');
        $collection->addFieldToFilter('id', array('in' => $otherProductsIds));
        foreach ($collection->getItems() as $listingOther) {
            /** @var Ess_M2ePro_Model_Listing_Other $listingOther */
            $listingOther->moveToListingSucceed();
        }
    }

    //########################################

    protected function useLastSpecifics()
    {
        return (bool)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
            '/view/ebay/template/category/', 'use_last_specifics'
        );
    }

    //########################################
}