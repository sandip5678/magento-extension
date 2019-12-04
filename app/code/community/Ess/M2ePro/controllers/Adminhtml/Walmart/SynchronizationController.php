<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Adminhtml_Walmart_SynchronizationController
    extends Ess_M2ePro_Controller_Adminhtml_Walmart_MainController
{
    //########################################

    protected function _initAction()
    {
        $this->loadLayout()
             ->_title(Mage::helper('M2ePro')->__('Configuration'))
             ->_title(Mage::helper('M2ePro')->__('Synchronization'));

        $this->getLayout()->getBlock('head')
             ->addJs('M2ePro/Plugin/ProgressBar.js')
             ->addCss('M2ePro/css/Plugin/ProgressBar.css')
             ->addJs('M2ePro/Plugin/AreaWrapper.js')
             ->addCss('M2ePro/css/Plugin/AreaWrapper.css')
             ->addJs('M2ePro/SynchProgressHandler.js')
             ->addJs('M2ePro/SynchronizationHandler.js');

        $this->_initPopUp();

        $this->setPageHelpLink(null, null, "x/L4taAQ");

        return $this;
    }

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed(
            Ess_M2ePro_Helper_View_Walmart::MENU_ROOT_NODE_NICK . '/configuration'
        );
    }

    //########################################

    public function indexAction()
    {
        $this->_initAction()
            ->_addContent(
                $this->getLayout()->createBlock(
                    'M2ePro/adminhtml_walmart_configuration', '',
                    array(
                        'active_tab' => Ess_M2ePro_Block_Adminhtml_Walmart_Configuration_Tabs::TAB_ID_SYNCHRONIZATION
                    )
                )
            )->renderLayout();
    }

    //########################################

    public function saveAction()
    {
        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/cron/task/walmart/listing/product/process_instructions/', 'mode',
            (int)$this->getRequest()->getParam('walmart_instructions_mode')
        );
    }

    //########################################

    public function synchCheckProcessingNowAction()
    {
        $warningMessages = array();

        $walmartProcessing = Mage::getModel('M2ePro/Lock_Item')->getCollection()
            ->addFieldToFilter('nick', array('like' => 'synchronization_walmart%'))
            ->getSize();

        if ($walmartProcessing > 0) {
            $warningMessages[] = Mage::helper('M2ePro')->__(
                'Data has been sent on Walmart. It is being processed now. You can continue working with M2E Pro.'
            );
        }

        return $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'messages' => $warningMessages
                )
            )
        );
    }

    //########################################

    public function runReviseAllAction()
    {
        $startDate = Mage::helper('M2ePro')->getCurrentGmtDate();

        Mage::helper('M2ePro/Module')->getConfig()->setGroupValue(
            '/listing/product/revise/total/walmart/', 'mode', '1'
        );

        $startDateRegistry = Mage::getModel('M2ePro/Registry')
            ->load('/listing/product/revise/total/walmart/start_date/', 'key');
        $startDateRegistry->setData('key', '/listing/product/revise/total/walmart/start_date/');
        $startDateRegistry->setData('value', $startDate);
        $startDateRegistry->save();

        $endDateRegistry = Mage::getModel('M2ePro/Registry')
            ->load('/listing/product/revise/total/walmart/end_date/', 'key');
        if ($endDateRegistry->getId()) {
            $endDateRegistry->delete();
        }

        $lastListingProductIdRegistry = Mage::getModel('M2ePro/Registry')
            ->load('/listing/product/revise/total/walmart/last_listing_product_id/', 'key');
        $lastListingProductIdRegistry->setData('key', '/listing/product/revise/total/walmart/last_listing_product_id/');
        $lastListingProductIdRegistry->setData('value', 0);
        $lastListingProductIdRegistry->save();

        $format = Mage::app()->getLocale()->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);

        $this->getResponse()->setBody(
            Mage::helper('M2ePro')->jsonEncode(
                array(
                'start_date' => Mage::app()->getLocale()->date(strtotime($startDate))->toString($format)
                )
            )
        );
    }

    //########################################
}
