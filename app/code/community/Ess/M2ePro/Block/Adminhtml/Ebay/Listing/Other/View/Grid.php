<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Ebay_Listing_Other_View_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        /** @var $this->connRead Varien_Db_Adapter_Pdo_Mysql */
        $this->connRead = Mage::getSingleton('core/resource')->getConnection('core_read');

        $this->setId('ebayListingOtherViewGrid');

        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(true);
    }

    public function getMassactionBlockName()
    {
        return 'M2ePro/adminhtml_grid_massaction';
    }

    //########################################

    protected function _prepareCollection()
    {
        $collection = Mage::helper('M2ePro/Component_Ebay')->getCollection('Listing_Other');

        $collection->getSelect()->joinLeft(
            array('mp' => Mage::getResourceModel('M2ePro/Marketplace')->getMainTable()),
            'mp.id = main_table.marketplace_id',
            array('marketplace_title' => 'mp.title')
        );

        $collection->getSelect()->joinLeft(
            array('mea' => Mage::getResourceModel('M2ePro/Ebay_Account')->getMainTable()),
            'mea.account_id = main_table.account_id',
            array('account_mode' => 'mea.mode')
        );

        // Add Filter By Account
        if ($accountId = $this->getRequest()->getParam('account')) {
            $collection->addFieldToFilter('main_table.account_id', $accountId);
        }

        // Add Filter By Marketplace
        if ($marketplaceId = $this->getRequest()->getParam('marketplace')) {
            $collection->addFieldToFilter('main_table.marketplace_id', $marketplaceId);
        }

        $collection->getSelect()->reset(Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(
            array(
                'id'                    => 'main_table.id',
                'account_id'            => 'main_table.account_id',
                'marketplace_id'        => 'main_table.marketplace_id',
                'product_id'            => 'main_table.product_id',
                'title'                 => 'second_table.title',
                'sku'                   => 'second_table.sku',
                'item_id'               => 'second_table.item_id',
                'online_qty'            => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'online_qty_sold'       => 'second_table.online_qty_sold',
                'online_price'          => 'second_table.online_price',
                'status'                => 'main_table.status',
                'start_date'            => 'second_table.start_date',
                'end_date'              => 'second_table.end_date',
                'currency'              => 'second_table.currency',
                'account_mode'          => 'mea.mode'
            )
        );

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id', array(
                'header'                    => Mage::helper('M2ePro')->__('Product ID'),
                'align'                     => 'left',
                'type'                      => 'number',
                'width'                     => '20px',
                'index'                     => 'product_id',
                'filter_index'              => 'main_table.product_id',
                'frame_callback'            => array($this, 'callbackColumnProductId'),
                'filter'                    => 'M2ePro/adminhtml_grid_column_filter_productId',
                'filter_condition_callback' => array($this, 'callbackFilterProductId')
            )
        );

        $this->addColumn(
            'title', array(
                'header'                    => Mage::helper('M2ePro')->__('Title / SKU'),
                'align'                     => 'left',
                'type'                      => 'text',
                'index'                     => 'title',
                'filter_index'              => 'second_table.title',
                'frame_callback'            => array($this, 'callbackColumnProductTitle'),
                'filter_condition_callback' => array($this, 'callbackFilterTitle')
            )
        );

        $this->addColumn(
            'item_id', array(
                'header'         => Mage::helper('M2ePro')->__('Item ID'),
                'align'          => 'left',
                'width'          => '120px',
                'type'           => 'text',
                'index'          => 'item_id',
                'filter_index'   => 'second_table.item_id',
                'frame_callback' => array($this, 'callbackColumnItemId')
            )
        );

        $this->addColumn(
            'online_qty', array(
                'header'         => Mage::helper('M2ePro')->__('Available QTY'),
                'align'          => 'right',
                'width'          => '50px',
                'type'           => 'number',
                'index'          => 'online_qty',
                'filter_index'   => new Zend_Db_Expr('(second_table.online_qty - second_table.online_qty_sold)'),
                'frame_callback' => array($this, 'callbackColumnOnlineAvailableQty')
            )
        );

        $this->addColumn(
            'online_qty_sold', array(
                'header'         => Mage::helper('M2ePro')->__('Sold QTY'),
                'align'          => 'right',
                'width'          => '50px',
                'type'           => 'number',
                'index'          => 'online_qty_sold',
                'filter_index'   => 'second_table.online_qty_sold',
                'frame_callback' => array($this, 'callbackColumnOnlineQtySold')
            )
        );

        $this->addColumn(
            'online_price', array(
                'header'         => Mage::helper('M2ePro')->__('Price'),
                'align'          => 'right',
                'width'          => '50px',
                'type'           => 'number',
                'index'          => 'online_price',
                'filter_index'   => 'second_table.online_price',
                'frame_callback' => array($this, 'callbackColumnOnlinePrice')
            )
        );

        $this->addColumn(
            'status', array(
                'header' => Mage::helper('M2ePro')->__('Status'),
                'width' => '100px',
                'index' => 'status',
                'filter_index' => 'main_table.status',
                'type' => 'options',
                'sortable' => false,
                'options' => array(
                    Ess_M2ePro_Model_Listing_Product::STATUS_LISTED   => Mage::helper('M2ePro')->__('Listed'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN   => Mage::helper('M2ePro')->__('Listed (Hidden)'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_SOLD     => Mage::helper('M2ePro')->__('Sold'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED  => Mage::helper('M2ePro')->__('Stopped'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED => Mage::helper('M2ePro')->__('Finished'),
                    Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED  => Mage::helper('M2ePro')->__('Pending')
                ),
                'frame_callback' => array($this, 'callbackColumnStatus')
            )
        );

        $this->addColumn(
            'end_date', array(
                'header'         => Mage::helper('M2ePro')->__('End Date'),
                'align'          => 'right',
                'width'          => '160px',
                'type'           => 'datetime',
                'format'         => Mage::app()->getLocale()
                                        ->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM),
                'index'          => 'end_date',
                'filter_index'   => 'second_table.end_date',
                'frame_callback' => array($this, 'callbackColumnEndTime')
            )
        );

        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        // Set mass-action identifiers
        // ---------------------------------------
        $this->setMassactionIdField('main_table.id');
        $this->getMassactionBlock()->setFormFieldName('ids');
        // ---------------------------------------

        $this->getMassactionBlock()->setGroups(
            array(
            'mapping' => Mage::helper('M2ePro')->__('Mapping'),
            'other'   => Mage::helper('M2ePro')->__('Other')
            )
        );

        // Set mass-action
        // ---------------------------------------
        $this->getMassactionBlock()->addItem(
            'autoMapping', array(
            'label'   => Mage::helper('M2ePro')->__('Map Item(s) Automatically'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'mapping'
        );

        $this->getMassactionBlock()->addItem(
            'moving', array(
            'label'   => Mage::helper('M2ePro')->__('Move Item(s) to Listing'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'other'
        );
        $this->getMassactionBlock()->addItem(
            'removing', array(
            'label'   => Mage::helper('M2ePro')->__('Remove Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'other'
        );
        $this->getMassactionBlock()->addItem(
            'unmapping', array(
            'label'   => Mage::helper('M2ePro')->__('Unmap Item(s)'),
            'url'     => '',
            'confirm' => Mage::helper('M2ePro')->__('Are you sure?')
            ), 'mapping'
        );
        // ---------------------------------------

        return parent::_prepareMassaction();
    }

    //########################################

    public function callbackColumnProductId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            $productTitle = Mage::helper('M2ePro')->escapeHtml($row->getData('title'));
            $productTitle = Mage::helper('M2ePro')->escapeJs($productTitle);
            if (strlen($productTitle) > 60) {
                $productTitle = substr($productTitle, 0, 60) . '...';
            }

            $htmlValue = '&nbsp;<a href="javascript:void(0);"
                                    onclick="EbayListingOtherMappingHandlerObj.openPopUp(\''.
                                        $productTitle.
                                        '\','.
                                        (int)$row->getId().
                                    ');">' . Mage::helper('M2ePro')->__('Map') . '</a>';

            return $htmlValue;
        }

        $htmlValue = '&nbsp<a href="'
            .$this->getUrl(
                'adminhtml/catalog_product/edit',
                array('id' => $row->getData('product_id'))
            )
            .'" target="_blank">'
            .$row->getData('product_id')
            .'</a>';

        $htmlValue .= '&nbsp&nbsp&nbsp<a href="javascript:void(0);"'
            .' onclick="EbayListingOtherGridHandlerObj.movingHandler.getGridHtml('
            .Mage::helper('M2ePro')->jsonEncode(array((int)$row->getData('id')))
            .')">'
            .Mage::helper('M2ePro')->__('Move')
            .'</a>';

        return $htmlValue;
    }

    public function callbackColumnProductTitle($value, $row, $column, $isExport)
    {
        $value = '<span>' . Mage::helper('M2ePro')->escapeHtml($value) . '</span>';

        $tempSku = $row->getData('sku');

        if ($tempSku === null) {
            $tempSku = '<i style="color:gray;">receiving...</i>';
        } elseif ($tempSku == '') {
            $tempSku = '<i style="color:gray;">none</i>';
        } else {
            $tempSku = Mage::helper('M2ePro')->escapeHtml($tempSku);
        }

        $value .= '<br/><strong>'
                  .Mage::helper('M2ePro')->__('SKU')
                  .':</strong> '
                  .$tempSku;

        return $value;
    }

    public function callbackColumnItemId($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        $url = Mage::helper('M2ePro/Component_Ebay')->getItemUrl(
            $row->getData('item_id'),
            $row->getData('account_mode'),
            $row->getData('marketplace_id')
        );
        $value = '<a href="' . $url . '" target="_blank">' . $value . '</a>';

        return $value;
    }

    public function callbackColumnOnlineAvailableQty($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        if ($row->getData('status') != Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
            return '<span style="color: gray; text-decoration: line-through;">' . $value . '</span>';
        }

        return $value;
    }

    public function callbackColumnOnlineQtySold($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ($value <= 0) {
            return '<span style="color: red;">0</span>';
        }

        return $value;
    }

    public function callbackColumnOnlinePrice($value, $row, $column, $isExport)
    {
        if ($value === null || $value === '') {
            return Mage::helper('M2ePro')->__('N/A');
        }

        if ((float)$value <= 0) {
            return '<span style="color: #f00;">0</span>';
        }

        return Mage::app()->getLocale()->currency($row->getData('currency'))->toCurrency($value);
    }

    public function callbackColumnStatus($value, $row, $column, $isExport)
    {
        switch ($row->getData('status')) {
            case Ess_M2ePro_Model_Listing_Product::STATUS_LISTED:
                $value = '<span style="color: green;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_HIDDEN:
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_SOLD:
                $value = '<span style="color: brown;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_STOPPED:
                $value = '<span style="color: red;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_FINISHED:
                $value = '<span style="color: blue;">' . $value . '</span>';
                break;

            case Ess_M2ePro_Model_Listing_Product::STATUS_BLOCKED:
                $value = '<span style="color: orange;">'.$value.'</span>';
                break;

            default:
                break;
        }

        return $value;
    }

    public function callbackColumnStartTime($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    public function callbackColumnEndTime($value, $row, $column, $isExport)
    {
        if (empty($value)) {
            return Mage::helper('M2ePro')->__('N/A');
        }

        return $value;
    }

    protected function callbackFilterProductId($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if (empty($value)) {
            return;
        }

        $where = '';

        if (isset($value['from']) && $value['from'] != '') {
            $where .= 'product_id >= ' . (int)$value['from'];
        }

        if (isset($value['to']) && $value['to'] != '') {
            if (isset($value['from']) && $value['from'] != '') {
                $where .= ' AND ';
            }

            $where .= 'product_id <= ' . (int)$value['to'];
        }

        if (isset($value['is_mapped']) && $value['is_mapped'] !== '') {
            if (!empty($where)) {
                $where = '(' . $where . ') AND ';
            }

            if ($value['is_mapped']) {
                $where .= 'product_id IS NOT NULL';
            } else {
                $where .= 'product_id IS NULL';
            }
        }

        $collection->getSelect()->where($where);
    }

    protected function callbackFilterTitle($collection, $column)
    {
        $value = $column->getFilter()->getValue();

        if ($value == null) {
            return;
        }

        $collection->getSelect()->where('second_table.title LIKE ? OR second_table.sku LIKE ?', '%'.$value.'%');
    }

    //########################################

    protected function _toHtml()
    {
        $javascriptsMain = <<<HTML
<script type="text/javascript">

    if (typeof EbayListingOtherGridHandlerObj != 'undefined') {
        EbayListingOtherGridHandlerObj.afterInitPage();
    }

    Event.observe(window, 'load', function() {
        setTimeout(function() {
            EbayListingOtherGridHandlerObj.afterInitPage();
        }, 350);
    });

</script>
HTML;

        return parent::_toHtml().$javascriptsMain;
    }

    //########################################

    public function getGridUrl()
    {
        return $this->getUrl('*/adminhtml_ebay_listing_other/viewGrid', array('_current' => true));
    }

    public function getRowUrl($row)
    {
        return false;
    }

    //########################################
}
