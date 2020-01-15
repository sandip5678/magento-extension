<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

use Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Add_NewAsin as NewAsinTemplateBlock;

class Ess_M2ePro_Adminhtml_Amazon_Listing_ProductAddController
    extends Ess_M2ePro_Controller_Adminhtml_Amazon_MainController
{
    protected $_sessionKey = 'amazon_listing_product_add';

    protected $_listing;

    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
            ->_title(Mage::helper('M2ePro')->__('Manage Listings'))
            ->_title(Mage::helper('M2ePro')->__('Listings'));

        $this->getLayout()->getBlock('head')
            ->setCanLoadExtJs(true)
            ->addCss('M2ePro/css/Plugin/ProgressBar.css')
            ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
            ->addJs('mage/adminhtml/rules.js')
            ->addJs('M2ePro/ActionHandler.js')
            ->addJs('M2ePro/GridHandler.js')

            ->addJs('M2ePro/Plugin/ProgressBar.js')
            ->addJs('M2ePro/Plugin/AreaWrapper.js')
            ->addJs('M2ePro/Plugin/ActionColumn.js')

            ->addJs('M2ePro/Listing/ActionHandler.js')
            ->addJs('M2ePro/Listing/GridHandler.js')
            ->addJs('M2ePro/Listing/ProductGridHandler.js')
            ->addJs('M2ePro/GridHandler.js')
            ->addJs('M2ePro/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Listing/AutoActionHandler.js')

            ->addJs('M2ePro/Amazon/Listing/Category/Summary/GridHandler.js')
            ->addJs('M2ePro/Amazon/Listing/Category/TreeHandler.js')
            ->addJs('M2ePro/Amazon/Listing/AddListingHandler.js')

            ->addJs('M2ePro/Amazon/Listing/ActionHandler.js')
            ->addJs('M2ePro/Amazon/Listing/Template/DescriptionHandler.js')
            ->addJs('M2ePro/Amazon/Listing/ChannelSettingsHandler.js')
            ->addJs('M2ePro/Amazon/Listing/SearchAsinGridHandler.js')
            ->addJs('M2ePro/Amazon/Listing/ProductSearchHandler.js')
            ->addJs('M2ePro/Amazon/Listing/VariationProductManageHandler.js')
            ->addJs('M2ePro/Amazon/Listing/FulfillmentHandler.js')
            ->addJs('M2ePro/Amazon/Listing/RepricingHandler.js')
            ->addJs('M2ePro/Amazon/Listing/NewAsinTemplateDescriptionGridHandler.js')
            ->addJs('M2ePro/Amazon/Listing/AutoActionHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/MogVAQ");

        return $this;
    }

    //########################################

    public function indexAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = Mage::helper('M2ePro/Data_Session')->getValue('temp_products');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_amazon_listing/index');
            return;
        }

        if ($this->getRequest()->getParam('clear')) {
            $this->clear();
            $this->getRequest()->setParam('clear', null);
            $this->_redirect('*/*/*', array('_current' => true));
            return;
        }

        if ($source = $this->getRequest()->getParam('source')) {
            $this->getListing()->setSetting('additional_data', 'source', $source)->save();
        }

        if (!empty($listingProductsIds)) {
            $this->getListing()->setSetting('additional_data', 'adding_listing_products_ids', $listingProductsIds);
            $this->getListing()->save();

            Mage::helper('M2ePro/Data_Session')->setValue('temp_products', array());
        }

        $step = (int)$this->getRequest()->getParam('step');

        switch ($step) {
            case 1:
                $this->sourceMode();
                break;
            case 2:
                $source = $this->getListing()->getSetting('additional_data', 'source');
                if (!empty($source)) {
                    $action = 'addProductsFrom' . ucfirst($source);
                    $this->$action();
                    return;
                }

                $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
                break;
            case 3:
                $this->asinSearchView();
                break;
            case 4:
                $this->addNewAsinView();
                break;
            case 5:
                $this->review();
                break;
            // ....
            default:
                return $this->_redirect('*/*/index', array('_current' => true,'step' => 1));
        }
    }

    //########################################

    public function sourceMode()
    {
        if (!$this->getRequest()->getParam('new_listing')) {
            return $this->_redirect(
                '*/adminhtml_amazon_listing/view',
                array('id' => $this->getRequest()->getParam('id'))
            );
        }

        $this->_initAction()
            ->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_sourceMode'))
            ->renderLayout();
    }

    //########################################

    public function addProductsFromList()
    {
        if ($this->getRequest()->getParam('id') === null) {
            $this->_redirect('*/adminhtml_amazon_listing/index');
            return;
        }

        $this->setRuleData('amazon_rule_add_listing_product');

        $prefix = $this->getHideProductsInOtherListingsPrefix();

        if ($this->getRequest()->isPost()) {
            $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_product_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/N4gVAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_sourceProduct'))
            ->renderLayout();
    }

    public function addProductsFromCategories()
    {
        if ($this->getRequest()->getParam('id') === null) {
            $this->_redirect('*/adminhtml_amazon_listing/index');
            return;
        }

        $this->setRuleData('amazon_rule_add_listing_product');

        $prefix = $this->getHideProductsInOtherListingsPrefix();

        if ($this->getRequest()->isPost()) {
            $hideProductsOtherParam = $this->getRequest()->getPost('hide_products_others_listings', 1);
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, $hideProductsOtherParam);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('hide_products_others_listings_prefix', $prefix);

        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        if ($this->getRequest()->isXmlHttpRequest()) {
            if ($this->getRequest()->getParam('current_category_id')) {
                $this->setSessionValue('current_category_id', $this->getRequest()->getParam('current_category_id'));
            }

            $this->loadLayout();
            /** @var $grid Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Category_Grid */
            $grid = $this->getLayout()
                ->createBlock('M2ePro/adminhtml_amazon_listing_product_category_grid');

            $grid->setSelectedIds($selectedProductsIds);
            $grid->setCurrentCategoryId($this->getSessionValue('current_category_id'));

            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/P4gVAQ");

        $gridContainer = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_sourceCategory');
        $this->_addContent($gridContainer);

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_category_tree', '', array(
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => true
            )
            )
        );

        if ($this->getSessionValue('current_category_id') === null) {
            $currentNode = $treeBlock->getRoot()->getChildren()->getIterator()->current();
            if (!$currentNode) {
                throw new Ess_M2ePro_Model_Exception('No Categories found');
            }

            $this->setSessionValue('current_category_id', $currentNode->getId());
        }

        $treeBlock->setGridId($gridContainer->getChild('grid')->getId());
        $treeBlock->setSelectedIds($selectedProductsIds);
        $treeBlock->setCurrentNodeById($this->getSessionValue('current_category_id'));

        $gridContainer->getChild('grid')->setTreeBlock($treeBlock);
        $gridContainer->getChild('grid')->setSelectedIds($selectedProductsIds);
        $gridContainer->getChild('grid')->setCurrentCategoryId($this->getSessionValue('current_category_id'));

        $this->renderLayout();
    }

    //########################################

    protected function asinSearchView()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        $this->getListing()->setSetting(
            'additional_data',
            'adding_listing_products_ids',
            $this->filterProductsForSearch($listingProductsIds)
        );
        $this->getListing()->save();

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_searchAsin_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/J4kVAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_searchAsin'))
            ->renderLayout();
    }

    protected function addNewAsinView()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_newAsin');

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/zYgVAQ");

        $this->_addContent($block)->renderLayout();
    }

    protected function review()
    {
        $additionalData = $this->getListing()->getSettings('additional_data');

        Mage::helper('M2ePro/Data_Session')->setValue(
            'added_products_ids',
            $additionalData['adding_listing_products_ids']
        );

        $this->clear();

        $this->_initAction();

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_review'))
            ->renderLayout();
    }

    //########################################

    public function viewListingAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_amazon_listing/index');
            return;
        }

        return $this->_redirect(
            '*/adminhtml_amazon_listing/view', array(
            'id' => $listingId
            )
        );
    }

    public function viewListingAndListAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            $this->_redirect('*/adminhtml_amazon_listing/index');
            return;
        }

        return $this->_redirect(
            '*/adminhtml_amazon_listing/view', array(
            'id' => $listingId,
            'do_list' => true
            )
        );
    }

    //########################################

    public function addProductsAction()
    {
        $listingId = $this->getRequest()->getParam('listing_id');
        $listing = Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId);

        $productsIds = $this->getRequest()->getParam('products');
        $productsIds = explode(',', $productsIds);
        $productsIds = array_unique($productsIds);

        $listingProductIds = array();
        if (!empty($productsIds)) {
            foreach ($productsIds as $productId) {
                if ($productId == '' || $productsIds[0] == 'true') {
                    continue;
                }

                $tempResult = $listing->addProduct($productId, Ess_M2ePro_Helper_Data::INITIATOR_USER);
                if ($tempResult instanceof Ess_M2ePro_Model_Listing_Product) {
                    $listingProductIds[] = $tempResult->getId();
                }
            }
        }

        $tempProducts = Mage::helper('M2ePro/Data_Session')->getValue('temp_products');
        $tempProducts = array_merge((array)$tempProducts, $listingProductIds);
        Mage::helper('M2ePro/Data_Session')->setValue('temp_products', $tempProducts);

        $isLastPart = $this->getRequest()->getParam('is_last_part');
        if ($isLastPart == 'yes') {
            $backUrl = $this->getUrl(
                '*/*/index', array(
                'id' => $listingId,
                'skip_products_steps' => empty($tempProducts),
                'step' => 3
                )
            );

            $this->clearSession();

            return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('redirect' => $backUrl)));
        }

        $response = array('redirect' => '');
        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode($response));
    }

    public function getSessionProductsIdsAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'ids' => $selectedProductsIds
                )
            )
        );
    }

    //########################################

    public function removeAddedProductsAction()
    {
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        foreach ($listingProductsIds as $listingProductId) {
            try {
                $listingProduct = Mage::helper('M2ePro/Component_Amazon')
                    ->getObject('Listing_Product', $listingProductId);
                $listingProduct->deleteInstance();
            } catch (Exception $e) {
            }
        }

        $this->getListing()->setSetting('additional_data', 'adding_listing_products_ids', array());
        $this->getListing()->setSetting('additional_data', 'adding_new_asin_listing_products_ids', array());
        $this->getListing()->setSetting('additional_data', 'auto_search_was_performed', 0);
        $this->getListing()->save();

        $this->_redirect(
            '*/adminhtml_amazon_listing_productAdd/index', array(
            'step'   => 2,
            'id'     => $this->getRequest()->getParam('id')
            )
        );
        return;
    }

    //########################################

    public function getCategoriesJsonAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $selectedProductsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_category_tree', '', array(
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => false
            )
            )
        );
        $treeBlock->setSelectedIds($selectedProductsIds);

        $this->getResponse()->setBody(
            $treeBlock->getCategoryChildrenJson($this->getRequest()->getParam('category'))
        );
    }

    public function saveProductsToSessionAndGetInfoAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $all = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        $checked = $this->getRequest()->getParam('checked_ids');
        $initial = $this->getRequest()->getParam('initial_checked_ids');

        $checked = array_filter(explode(',', $checked));
        $initial = array_filter(explode(',', $initial));

        $initial = array_values(array_unique(array_merge($initial, $checked)));
        $all     = array_values(array_unique(array_merge($all, $initial)));

        $all = array_flip($all);

        foreach (array_diff($initial, $checked) as $id) {
            unset($all[$id]);
        }

        $tempSession['products_ids'] = array_values(array_filter(array_flip($all)));
        $this->setSessionValue('source_categories', $tempSession);

        // ---------------------------------------

        $this->_forward('getTreeInfo');
    }

    public function getTreeInfoAction()
    {
        $tempSession = $this->getSessionValue('source_categories');
        $tempSession['products_ids'] = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_category_tree', '', array(
            'tree_settings' => array(
                'show_products_amount' => true,
                'hide_products_this_listing' => false
            )
            )
        );
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $this->getResponse()->setBody($treeBlock->getInfoJson());
    }

    //########################################

    public function getCategoriesSummaryHtmlAction()
    {
        $this->loadLayout();

        $tempSession = $this->getSessionValue('source_categories');
        $productsIds = !isset($tempSession['products_ids']) ? array() : $tempSession['products_ids'];

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_category_tree');
        $treeBlock->setSelectedIds($productsIds);

        /** @var $block Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Product_Category_Summary_Grid */
        $block = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_product_category_summary_grid');
        $block->setStoreId($this->getListingFromRequest()->getStoreId());
        $block->setProductsIds($productsIds);
        $block->setProductsForEachCategory($treeBlock->getProductsCountForEachCategory());

        $this->getResponse()->setBody($block->toHtml());
    }

    public function removeSessionProductsByCategoryAction()
    {
        $categoriesIds = $this->getRequestIds();

        $tempSession = $this->getSessionValue('source_categories');
        if (!isset($tempSession['products_ids'])) {
            return;
        }

        /** @var $treeBlock Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Category_Tree */
        $treeBlock = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_category_tree');
        $treeBlock->setSelectedIds($tempSession['products_ids']);

        $productsForEachCategory = $treeBlock->getProductsForEachCategory();

        $products = array();
        foreach ($categoriesIds as $categoryId) {
            $products = array_merge($products, $productsForEachCategory[$categoryId]);
        }

        $tempSession['products_ids'] = array_diff($tempSession['products_ids'], $products);

        $this->setSessionValue('source_categories', $tempSession);
    }

    //########################################

    protected function setRuleData($prefix)
    {
        $listingData = $this->getListingFromRequest()->getData();

        $storeId = isset($listingData['store_id']) ? (int)$listingData['store_id'] : 0;
        $prefix .= isset($listingData['id']) ? '_'.$listingData['id'] : '';
        Mage::helper('M2ePro/Data_Global')->setValue('rule_prefix', $prefix);

        $ruleModel = Mage::getModel('M2ePro/Magento_Product_Rule')->setData(
            array(
                'prefix' => $prefix,
                'store_id' => $storeId,
            )
        );

        $ruleParam = $this->getRequest()->getPost('rule');
        if (!empty($ruleParam)) {
            Mage::helper('M2ePro/Data_Session')->setValue(
                $prefix, $ruleModel->getSerializedFromPost($this->getRequest()->getPost())
            );
        } elseif ($ruleParam !== null) {
            Mage::helper('M2ePro/Data_Session')->setValue($prefix, array());
        }

        $sessionRuleData = Mage::helper('M2ePro/Data_Session')->getValue($prefix);
        if (!empty($sessionRuleData)) {
            $ruleModel->loadFromSerialized($sessionRuleData);
        }

        Mage::helper('M2ePro/Data_Global')->setValue('rule_model', $ruleModel);
    }

    protected function getHideProductsInOtherListingsPrefix()
    {
        $id = $this->getRequest()->getParam('id');

        $prefix = 'amazon_hide_products_others_listings_';
        $prefix .= $id === null ? 'add' : $id;
        $prefix .= '_listing_product';

        return $prefix;
    }

    //########################################

    public function viewSearchSettingsAction()
    {
        $listingId = $this->getRequest()->getParam('id');

        if (empty($listingId)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $settings = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_add_searchAsin_searchSettings');

        return $this->getResponse()->setBody($settings->toHtml());
    }

    public function saveSearchSettingsAction()
    {
        $post = $this->getRequest()->getPost();

        if (empty($post['id'])) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $post['id']);

        $listingProduct->setData('general_id_mode', $post['general_id_mode'])->save();
        $listingProduct->setData('general_id_custom_attribute', $post['general_id_custom_attribute'])->save();
        $listingProduct->setData('worldwide_id_mode', $post['worldwide_id_mode'])->save();
        $listingProduct->setData('worldwide_id_custom_attribute', $post['worldwide_id_custom_attribute'])->save();
        $listingProduct->setData('search_by_magento_title_mode', $post['search_by_magento_title_mode'])->save();

        $listingProduct->save();

        $this->_forward('viewSearchSettings');

        return;
    }

    //########################################

    public function checkSearchResultsAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = $this->getListing()->getSetting('additional_data', 'adding_listing_products_ids');

        if (empty($listingId) || empty($listingProductsIds)) {
            $this->_forward('index');
        }

        $listingProductsIds = $this->filterProductsForNewAsin($listingProductsIds);

        if (empty($listingProductsIds) ||
            !$this->getListing()->getMarketplace()->getChildObject()->isNewAsinAvailable()) {
            $redirectUrl = $this->getUrl(
                '*/*/index', array(
                'step' => 5,
                'id' => $this->getRequest()->getParam('id')
                )
            );
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array('redirect' => $redirectUrl)
                )
            );
        }

        $this->getListing()->setSetting('additional_data', 'adding_new_asin_listing_products_ids', $listingProductsIds);
        $this->getListing()->save();

        $showNewAsinStep = $this->getListing()->getSetting('additional_data', 'show_new_asin_step');
        if (isset($showNewAsinStep)) {
            return $this->getResponse()->setBody(
                Mage::helper('M2ePro')->jsonEncode(
                    array(
                    'redirect' => $this->getUrl(
                        '*/*/index', array(
                        'id' => $this->getRequest()->getParam('id'),
                        'step' => $showNewAsinStep ? 4 : 5
                        )
                    )
                    )
                )
            );
        }

        $newAsinPopup = $this->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_add_searchAsin_newAsinPopup');

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array('data' => $newAsinPopup->toHtml())
            )
        );
    }

    //########################################

    public function showNewAsinStepAction()
    {
        $showNewAsinStep = (int)$this->getRequest()->getParam('show_new_asin_step', 1);

        $remember = $this->getRequest()->getParam('remember');

        if ($remember) {
            $this->getListing()->setSetting('additional_data', 'show_new_asin_step', $showNewAsinStep);

            $this->getListing()->save();
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'redirect' => $this->getUrl(
                    '*/*/index', array(
                    'id' => $this->getRequest()->getParam('id'),
                    'step' => $showNewAsinStep ? 4 : 5
                    )
                )
                )
            )
        );
    }

    //########################################

    public function viewTemplateDescriptionPopupAction()
    {
        $mainBlock = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_template_description_main');

        return $this->getResponse()->setBody($mainBlock->toHtml());
    }

    public function viewTemplateDescriptionsGridAction()
    {
        $listingProductsIds    = $this->getRequest()->getParam('products_ids');
        $magentoCategoryIds    = $this->getRequest()->getParam('magento_categories_ids');
        $createNewTemplateJsFn = $this->getRequest()->getParam('create_new_template_js_function');

        if (empty($listingProductsIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        !is_array($listingProductsIds) && $listingProductsIds = array_filter(explode(',', $listingProductsIds));
        !is_array($magentoCategoryIds) && $magentoCategoryIds = array_filter(explode(',', $magentoCategoryIds));

        /** @var Ess_M2ePro_Block_Adminhtml_Amazon_Listing_Template_Description_Grid $grid */
        $grid = $this->loadLayout()->getLayout()->createBlock(
            'M2ePro/adminhtml_amazon_listing_template_description_grid'
        );
        $grid->setCheckNewAsinAccepted(true);
        $grid->setProductsIds($listingProductsIds);
        $grid->setMagentoCategoryIds($magentoCategoryIds);
        $grid->setMapToTemplateJsFn('selectTemplateDescription');
        $createNewTemplateJsFn !== null && $grid->setCreateNewTemplateJsFn($createNewTemplateJsFn);

        return $this->getResponse()->setBody($grid->toHtml());
    }

    //########################################

    public function descriptionTemplateAssignTypeAction()
    {
        $listingId = $this->getRequest()->getParam('id');
        $listingProductsIds = $this->getRequest()->getParam('products_ids');

        $mode = $this->getRequest()->getParam('mode');
        $descriptionTemplateId = $this->getRequest()->getParam('description_template_id');

        if (empty($listingId) || empty($mode)) {
            $this->_forward('index');
            return;
        }

        if (!is_array($listingProductsIds)) {
            $listingProductsIds = explode(',', $listingProductsIds);
        }

        $listing = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $listingId);
        $listingAdditionalData = $listing->getData('additional_data');
        $listingAdditionalData = Mage::helper('M2ePro')->jsonDecode($listingAdditionalData);

        $listingAdditionalData['new_asin_mode'] = $mode;

        $listing->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($listingAdditionalData))->save();

        if ($mode == NewAsinTemplateBlock::MODE_SAME && !empty($descriptionTemplateId)) {
            /** @var Ess_M2ePro_Model_Amazon_Template_Description $descriptionTemplate */
            $descriptionTemplate = Mage::helper('M2ePro/Component_Amazon')
                ->getModel('Template_Description')->load($descriptionTemplateId);

            if (!$descriptionTemplate->isEmpty()) {
                if (!empty($listingProductsIds)) {
                    $this->setDescriptionTemplate($listingProductsIds, $descriptionTemplateId);
                    $this->_forward('mapToNewAsin', 'adminhtml_amazon_listing');
                }

                return $this->_redirect(
                    '*/adminhtml_amazon_listing_productAdd/index', array(
                    '_current' => true,
                    'step' => 5
                    )
                );
            }

            unset($listingAdditionalData['new_asin_mode']);

            $listing->setData('additional_data', Mage::helper('M2ePro')->jsonEncode($listingAdditionalData))->save();
        } else if ($mode == NewAsinTemplateBlock::MODE_CATEGORY) {
            return $this->_redirect(
                '*/*/descriptionTemplateAssignByMagentoCategory', array(
                '_current' => true,
                )
            );
        } else if ($mode == NewAsinTemplateBlock::MODE_MANUALLY) {
            return $this->_redirect(
                '*/*/descriptionTemplateAssignManually', array(
                '_current' => true,
                )
            );
        }

        $this->_forward('index');
    }

    public function descriptionTemplateAssignByMagentoCategoryAction()
    {
        $listingProductsIds = $this->getListing()->getSetting(
            'additional_data', 'adding_new_asin_listing_products_ids'
        );

        if (empty($listingProductsIds)) {
            $this->_forward('index');
            return;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_newAsin_category_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $block = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_newAsin_category');

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/zYgVAQ");

        $this->_addContent($block)->renderLayout();
    }

    public function descriptionTemplateAssignManuallyAction()
    {
        $listingProductsIds = $this->getListing()->getSetting(
            'additional_data', 'adding_new_asin_listing_products_ids'
        );

        if (empty($listingProductsIds)) {
            $this->_forward('index');
            return;
        }

        if ($this->getRequest()->isXmlHttpRequest()) {
            $grid = $this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_newAsin_manual_grid');
            return $this->getResponse()->setBody($grid->toHtml());
        }

        $this->_initAction();

        $this->setPageHelpLink(null, null, "x/zYgVAQ");

        $this->_addContent($this->getLayout()->createBlock('M2ePro/adminhtml_amazon_listing_add_newAsin_manual'))
             ->renderLayout();
    }

    //########################################

    public function assignByMagentoCategorySaveCategoryAction()
    {
        $templateId = $this->getRequest()->getParam('template_id');
        $magentoCategoryIds = $this->getRequest()->getParam('magento_categories_ids');

        if (empty($templateId) || empty($magentoCategoryIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        !is_array($magentoCategoryIds) && $magentoCategoryIds = array_filter(explode(',', $magentoCategoryIds));
        $templatesData = $this->getListing()->getSetting('additional_data', 'adding_category_templates_data', array());

        foreach ($magentoCategoryIds as $magentoCategoryId) {
            $templatesData[$magentoCategoryId] = $templateId;
        }

        $this->getListing()->setSetting('additional_data', 'adding_category_templates_data', $templatesData);
        $this->getListing()->save();

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    public function assignByMagentoCategoryDeleteCategoryAction()
    {
        $magentoCategoryIds = $this->getRequest()->getParam('magento_categories_ids');

        if (empty($magentoCategoryIds)) {
            return $this->getResponse()->setBody('You should provide correct parameters.');
        }

        !is_array($magentoCategoryIds) && $magentoCategoryIds = array_filter(explode(',', $magentoCategoryIds));
        $templatesData = $this->getListing()->getSetting('additional_data', 'adding_category_templates_data', array());

        foreach ($magentoCategoryIds as $magentoCategoryId) {
            unset($templatesData[$magentoCategoryId]);
        }

        $this->getListing()->setSetting('additional_data', 'adding_category_templates_data', $templatesData);
        $this->getListing()->save();

        return $this->getResponse()->setBody(Mage::helper('M2ePro')->jsonEncode(array('result' => true)));
    }

    //########################################

    public function checkNewAsinProductsAction()
    {
        $listingProductsIds = $this->getListing()->getSetting(
            'additional_data', 'adding_new_asin_listing_products_ids'
        );

        /** @var Ess_M2ePro_Model_Resource_Amazon_Listing_Product_Collection $collection */
        $collection = Mage::helper('M2ePro/Component_Amazon')->getCollection('Listing_Product');
        $collection->getSelect()->where(
            "`main_table`.`id` IN (?) AND `second_table`.`template_description_id` IS NULL", $listingProductsIds
        );

        $data = $collection->getData();
        if (empty($data)) {
            return $this->getResponse()->setBody(1);
        }

        $popup = $this->loadLayout()->getLayout()
            ->createBlock('M2ePro/adminhtml_amazon_listing_add_newAsin_warningPopup');

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'total_count'  => count($listingProductsIds),
                'failed_count' => count($data),
                'html'         => $popup->toHtml()
                )
            )
        );
    }

    //########################################

    public function resetNewAsinAction()
    {
        $listingProductsIds = $this->getListing()->getSetting(
            'additional_data', 'adding_new_asin_listing_products_ids'
        );
        $listingProductsIds = Mage::helper('M2ePro/Component_Amazon_Variation')
            ->filterLockedProducts($listingProductsIds);

        if (!empty($listingProductsIds)) {
            foreach ($listingProductsIds as $productId) {

                /** @var $listingProduct Ess_M2ePro_Model_Listing_Product */
                $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);

                $runListingProductProcessor = false;
                if ($listingProduct->getChildObject()->getVariationManager()->isLogicalUnit()) {
                    $parentType = $listingProduct->getChildObject()->getVariationManager()->getTypeModel();

                    $parentType->setMatchedAttributes(array(), false);
                    $parentType->setChannelAttributesSets(array(), false);
                    $parentType->setChannelVariations(array(), false);
                    $parentType->setVirtualProductAttributes(array(), false);
                    $parentType->setVirtualChannelAttributes(array(), false);

                    $runListingProductProcessor = true;
                }

                $listingProduct->setData('general_id', null);
                $listingProduct->setData('general_id_search_info', null);
                $listingProduct->setData(
                    'is_general_id_owner',
                    Ess_M2ePro_Model_Amazon_Listing_Product::IS_GENERAL_ID_OWNER_NO
                );
                $listingProduct->setData('search_settings_status', null);
                $listingProduct->setData('search_settings_data', null);

                $listingProduct->save();

                if ($runListingProductProcessor) {
                    $parentType->getProcessor()->process();
                }
            }

            $this->setDescriptionTemplate($listingProductsIds, null);
        }

        $this->getListing()->setSetting('additional_data', 'adding_category_templates_data', array());
        $this->getListing()->save();

        return $this->_redirect(
            '*/adminhtml_amazon_listing_productAdd/index', array(
            '_current' => true,
            'step' => 4
            )
        );
    }

    //########################################

    protected function setDescriptionTemplate($productsIds, $templateId)
    {
        $connWrite = Mage::getSingleton('core/resource')->getConnection('core_write');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $productsIds = array_chunk($productsIds, 1000);
        foreach ($productsIds as $productsIdsChunk) {
            $connWrite->update(
                $tableAmazonListingProduct, array(
                    'template_description_id' => $templateId
                ), '`listing_product_id` IN ('.implode(',', $productsIdsChunk).')'
            );
        }
    }

    //########################################

    protected function runProcessorForParents($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableAmazonListingProduct = Mage::helper('M2ePro/Module_Database_Structure')
            ->getTableNameWithPrefix('m2epro_amazon_listing_product');

        $select = $connRead->select();
        $select->from(array('alp' => $tableAmazonListingProduct), array('listing_product_id'))
            ->where('listing_product_id IN (?)', $productsIds)
            ->where('is_variation_parent = ?', 1);

        $productsIds = Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);

        foreach ($productsIds as $productId) {
            $listingProduct = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing_Product', $productId);
            $listingProduct->getChildObject()->getVariationManager()->getTypeModel()->getProcessor()->process();
        }
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

    // ---------------------------------------

    protected function clearSession()
    {
        Mage::helper('M2ePro/Data_Session')->setValue($this->_sessionKey, null);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Amazon_Listing
     * @throws Ess_M2ePro_Model_Exception
     */
    public function getListing()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        if ($this->_listing === null) {
            $this->_listing = Mage::helper('M2ePro/Component_Amazon')->getObject('Listing', $listingId);
        }

        return $this->_listing;
    }

    //########################################

    protected function filterProductsForSearch($productsIds)
    {
        $variationHelper = Mage::helper('M2ePro/Component_Amazon_Variation');

        $productsIds = $variationHelper->filterProductsByStatus($productsIds);

        $unsetProducts = $this->getLockedProductsInAction($productsIds);
        $unsetProducts = array_unique($unsetProducts);

        foreach ($unsetProducts as $id) {
            $key = array_search($id, $productsIds);
            unset($productsIds[$key]);
        }

        return $productsIds;
    }

    protected function filterProductsForNewAsin($productsIds)
    {
        return Mage::helper('M2ePro/Component_Amazon_Variation')->filterProductsNotMatchingForNewAsin($productsIds);
    }

    //########################################

    protected function getLockedProductsInAction($productsIds)
    {
        $connRead = Mage::getSingleton('core/resource')->getConnection('core_read');
        $table = Mage::helper('M2ePro/Module_Database_Structure')->getTableNameWithPrefix('m2epro_processing_lock');

        $select = $connRead->select();
        $select->from(array('lo' => $table), array('object_id'))
            ->where('model_name = "M2ePro/Listing_Product"')
            ->where('object_id IN (?)', $productsIds)
            ->where('tag = "in_action"');

        return Mage::getResourceModel('core/config')
            ->getReadConnection()
            ->fetchCol($select);
    }

    //########################################

    /** @return Ess_M2ePro_Model_Amazon_Listing
     * @throws Exception
     */
    protected function getListingFromRequest()
    {
        if (!$listingId = $this->getRequest()->getParam('id')) {
            throw new Ess_M2ePro_Model_Exception('Listing is not defined');
        }

        return Mage::helper('M2ePro/Component')->getCachedUnknownObject('Listing', $listingId)->getChildObject();
    }

    //########################################

    public function clear()
    {
        $this->clearSession();

        if ($additionalData = $this->getListing()->getSettings('additional_data')) {
            $additionalData['adding_listing_products_ids'] = array();
            $additionalData['adding_new_asin_listing_products_ids'] = array();
            $additionalData['auto_search_was_performed'] = 0;
            unset($additionalData['adding_category_templates_data']);
            unset($additionalData['source']);
            $this->getListing()->setSettings('additional_data', $additionalData)->save();
        }
    }

    //########################################
}
