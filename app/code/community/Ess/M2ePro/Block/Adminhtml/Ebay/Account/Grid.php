<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Account_Grid extends Ess_M2ePro_Block_Adminhtml_Account_Grid
{
    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Account');
        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'id', array(
                'header'       => Mage::helper('M2ePro')->__('ID'),
                'align'        => 'right',
                'width'        => '100px',
                'type'         => 'number',
                'index'        => 'id',
                'filter_index' => 'main_table.id'
            )
        );

        $this->addColumn(
            'title', array(
                'header'                    => Mage::helper('M2ePro')->__('Title / Info'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'title',
                'escape'                    => true,
                'filter_index'              => 'main_table.title',
                'frame_callback'            => array($this, 'callbackColumnTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        if (Mage::helper('M2ePro/View_Ebay')->isFeedbacksShouldBeShown()) {
            $this->addColumn(
                'feedbacks', array(
                    'header'         => Mage::helper('M2ePro')->__('Feedback'),
                    'align'          => 'center',
                    'width'          => '120px',
                    'type'           => 'text',
                    'sortable'       => false,
                    'filter'         => false,
                    'frame_callback' => array($this, 'callbackColumnFeedbacks')
                )
            );
        }

        return parent::_prepareColumns();
    }

    //########################################

    public function callbackColumnTitle($value, $row, $column, $isExport)
    {
        /** @var Ess_M2ePro_Model_Account $row */
        $userIdLabel = Mage::helper('M2ePro')->__('eBay User ID');
        $userId = $row->getData('user_id');

        $userIdHtml = '';
        if (!empty($userId)) {
            $userIdHtml = <<<HTML
            <span style="font-weight: bold">{$userIdLabel}</span>:
            <span style="color: #505050">{$userId}</span>
            <br/>
HTML;
        }

        $environmentLabel = Mage::helper('M2ePro')->__('Environment');
        $environment = (int)$row->getData('mode') == Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX
            ? 'Sandbox (Test)'
            : 'Production (Live)';
        $environment = Mage::helper('M2ePro')->__($environment);

        return <<<HTML
<div>
    {$value}<br/>
    {$userIdHtml}
    <span style="font-weight: bold">{$environmentLabel}</span>:
    <span style="color: #505050">{$environment}</span>
    <br/>
</div>
HTML;
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $mode = null;
        if (strpos('sandbox (test)', strtolower($value)) !== false) {
            $mode = Ess_M2ePro_Model_Ebay_Account::MODE_SANDBOX;
        } elseif (strpos('production (live)', strtolower($value)) !== false) {
            $mode = Ess_M2ePro_Model_Ebay_Account::MODE_PRODUCTION;
        }

        $modeWhere = '';
        if ($mode !== null) {
            $modeWhere = ' OR second_table.mode = ' . $mode;
        }

        $collection->getSelect()->where(
            'main_table.title LIKE ? OR second_table.user_id LIKE ?' . $modeWhere,
            '%'. $value .'%'
        );
    }

    public function callbackColumnFeedbacks($value, $row, $column, $isExport)
    {
        if (Mage::helper('M2ePro/View_Ebay')->isFeedbacksShouldBeShown($row->getData('id'))) {
            $url = $this->getUrl('*/adminhtml_ebay_feedback/index', array('account' => $row->getId()));
            $link = '<a href="' . $url . '" target="_blank">'. Mage::helper('M2ePro')->__("Feedback") . '</a>';
        } else {
            $link = '<strong style="color: gray;">'
                        . Mage::helper('M2ePro')->__("Disabled") .
                    '</strong>';
        }

        return $link;
    }

    //########################################
}
