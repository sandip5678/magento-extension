<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Walmart_Log extends Ess_M2ePro_Block_Adminhtml_Widget_Container
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        $this->setId('walmartLog');

        if (!Mage::helper('M2ePro/Component')->isSingleActiveComponent()) {
            $componentName = Mage::helper('M2ePro/Component_Walmart')->getTitle();
            $this->_headerText = Mage::helper('M2ePro')->__('%component_name% / Logs & Events', $componentName);
        } else {
            $this->_headerText = Mage::helper('M2ePro')->__('Logs & Events');
        }

        $this->removeButton('back');
        $this->removeButton('delete');
        $this->removeButton('add');
        $this->removeButton('save');
        $this->removeButton('edit');

        $this->setTemplate('M2ePro/walmart/log/log.phtml');
    }

    //########################################

    protected function _toHtml()
    {
        $css = <<<HTML

<style type="text/css">
    #listing_switcher_add_new_drop_down ul li {
        padding: 2px 5px 2px 10px !important;
    }
    #listing-profile-title_drop_down ul li {
        font-size: 12px !important;
    }
</style>

HTML;

        $javascript = <<<JAVASCIRPT

<script type="text/javascript">

    Event.observe(window, 'load', function() {
        CommonHandlerObj = new CommonHandler();
        LogHandlerObj = new LogHandler();
    });

</script>

JAVASCIRPT;

        $activeTab = $this->getData('active_tab') !== null ? $this->getData('active_tab')
            : Ess_M2ePro_Block_Adminhtml_Walmart_Log_Tabs::TAB_ID_LISTING;
        $tabsBlock = $this->getLayout()->createBlock(
            'M2ePro/adminhtml_walmart_log_tabs', '', array('active_tab' => $activeTab)
        );

        return $css . $javascript .
            parent::_toHtml() .
            $tabsBlock->toHtml() .
            '<div id="tabs_container"></div>';
    }

    //########################################
}
