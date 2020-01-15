<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_OperationHistory extends Ess_M2ePro_Model_Abstract
{
    const MAX_LIFETIME_INTERVAL = 864000; // 10 days

    /**
     * @var Ess_M2ePro_Model_OperationHistory
     */
    protected $_object = null;

    //########################################

    public function _construct()
    {
        parent::_construct();
        $this->_init('M2ePro/OperationHistory');
    }

    //########################################

    public function setObject($value)
    {
        if (is_object($value)) {
            $this->_object = $value;
        } else {
            $this->_object = Mage::getModel('M2ePro/OperationHistory')->load($value);
            !$this->_object->getId() && $this->_object = null;
        }

        return $this;
    }

    /**
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getObject()
    {
        return $this->_object;
    }

    //########################################

    /**
     * @param $nick string
     * @return Ess_M2ePro_Model_OperationHistory
     */
    public function getParentObject($nick = null)
    {
        if ($this->getObject()->getData('parent_id') === null) {
            return null;
        }

        $parentId     = (int)$this->getObject()->getData('parent_id');
        $parentObject = Mage::getModel('M2ePro/OperationHistory')->load($parentId);

        if ($nick === null) {
            return $parentObject;
        }

        while ($parentObject->getData('nick') != $nick) {
            $parentId = $parentObject->getData('parent_id');
            if ($parentId === null) {
                return null;
            }

            $parentObject = Mage::getModel('M2ePro/OperationHistory')->load($parentId);
        }

        return $parentObject;
    }

    //########################################

    public function start(
        $nick,
        $parentId = null,
        $initiator = Ess_M2ePro_Helper_Data::INITIATOR_UNKNOWN,
        $data = array()
    ) {
        $data = array(
            'nick'       => $nick,
            'parent_id'  => $parentId,
            'data'       => Mage::helper('M2ePro')->jsonEncode($data),
            'initiator'  => $initiator,
            'start_date' => Mage::helper('M2ePro')->getCurrentGmtDate()
        );

        $this->_object = Mage::getModel('M2ePro/OperationHistory')->setData($data)->save();

        return true;
    }

    public function stop()
    {
        if ($this->_object === null || $this->_object->getData('end_date')) {
            return false;
        }

        $this->_object->setData('end_date', Mage::helper('M2ePro')->getCurrentGmtDate())->save();

        return true;
    }

    //########################################

    public function setContentData($key, $value)
    {
        if ($this->_object === null) {
            return false;
        }

        $data = array();
        if ($this->_object->getData('data') != '') {
            $data = Mage::helper('M2ePro')->jsonDecode($this->_object->getData('data'));
        }

        $data[$key] = $value;
        $this->_object->setData('data', Mage::helper('M2ePro')->jsonEncode($data))->save();

        return true;
    }

    public function addContentData($key, $value)
    {
        $existedData = $this->getContentData($key);

        if ($existedData === null) {
            is_array($value) ? $existedData = array($value) : $existedData = $value;
            return $this->setContentData($key, $existedData);
        }

        is_array($existedData) ? $existedData[] = $value : $existedData .= $value;
        return $this->setContentData($key, $existedData);
    }

    public function getContentData($key)
    {
        if ($this->_object === null) {
            return null;
        }

        if ($this->_object->getData('data') == '') {
            return null;
        }

        $data = Mage::helper('M2ePro')->jsonDecode($this->_object->getData('data'));

        if (isset($data[$key])) {
            return $data[$key];
        }

        return null;
    }

    //########################################

    public function cleanOldData()
    {
        $minDate = new DateTime('now', new DateTimeZone('UTC'));
        $minDate->modify('-'.self::MAX_LIFETIME_INTERVAL.' seconds');

        Mage::getSingleton('core/resource')->getConnection('core_write')
                ->delete(
                    Mage::helper('M2ePro/Module_Database_Structure')
                        ->getTableNameWithPrefix('m2epro_operation_history'),
                    array(
                        '`create_date` <= ?' => $minDate->format('Y-m-d H:i:s')
                    )
                );
    }

    public function makeShutdownFunction()
    {
        if ($this->_object === null) {
            return false;
        }

        $objectId = $this->_object->getId();
        register_shutdown_function(
            function() use ($objectId)
            {
            $error = error_get_last();
            if ($error === null || !in_array((int)$error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
                return;
            }

            $object = Mage::getModel('M2ePro/OperationHistory');
            $object->setObject($objectId);

            if (!$object->stop()) {
                return;
            }

            $collection = $object->getCollection()->addFieldToFilter('parent_id', $objectId);
            if ($collection->getSize()) {
                return;
            }

            $stackTrace = @debug_backtrace(false);
            $object->setContentData(
                'fatal_error', array(
                'message' => $error['message'],
                'file'    => $error['file'],
                'line'    => $error['line'],
                'trace'   => Mage::helper('M2ePro/Module_Exception')->getFatalStackTraceInfo($stackTrace)
                )
            );
            }
        );

        return true;
    }

    //########################################

    public function getDataInfo($nestingLevel = 0)
    {
        if ($this->_object === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 7);
        $separationLine = str_repeat('#', 80 - strlen($offset));

        $nick = strtoupper($this->getObject()->getData('nick'));

        $contentData = (array)Mage::helper('M2ePro')->jsonDecode($this->getObject()->getData('data'));
        $contentData = preg_replace('/^/m', "{$offset}", print_r($contentData, true));

        return <<<INFO
{$offset}{$nick}
{$offset}Start Date: {$this->getObject()->getData('start_date')}
{$offset}End Date: {$this->getObject()->getData('end_date')}
{$offset}Total Time: {$this->getTotalTime()}

{$offset}{$separationLine}
{$contentData}
{$offset}{$separationLine}

INFO;
    }

    public function getFullDataInfo($nestingLevel = 0)
    {
        if ($this->_object === null) {
            return null;
        }

        $dataInfo = $this->getDataInfo($nestingLevel);

        $childObjects = $this->getCollection()
                             ->addFieldToFilter('parent_id', $this->getObject()->getId())
                             ->setOrder('start_date', 'ASC');

        $childObjects->getSize() > 0 && $nestingLevel++;

        foreach ($childObjects as $item) {
            $object = Mage::getModel('M2ePro/OperationHistory');
            $object->setObject($item);

            $dataInfo .= $object->getFullDataInfo($nestingLevel);
        }

        return $dataInfo;
    }

    // ---------------------------------------

    public function getExecutionInfo($nestingLevel = 0)
    {
        if ($this->_object === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 5);

        return <<<INFO
{$offset}<b>{$this->getObject()->getData('nick')} ## {$this->getObject()->getData('id')}</b>
{$offset}start date: {$this->getObject()->getData('start_date')}
{$offset}end date:   {$this->getObject()->getData('end_date')}
{$offset}total time: {$this->getTotalTime()}
<br>
INFO;
    }

    public function getExecutionTreeUpInfo()
    {
        if ($this->_object === null) {
            return null;
        }

        $extraParent = $this->getObject();
        $executionTree[] = $extraParent;

        while ($parentId = $extraParent->getData('parent_id')) {
            $extraParent = Mage::getModel('M2ePro/OperationHistory')->load($parentId);
            $executionTree[] = $extraParent;
        }

        $info = '';
        $executionTree = array_reverse($executionTree);

        foreach ($executionTree as $nestingLevel => $item) {
            $object = Mage::getModel('M2ePro/OperationHistory');
            $object->setObject($item);

            $info .= $object->getExecutionInfo($nestingLevel);
        }

        return $info;
    }

    public function getExecutionTreeDownInfo($nestingLevel = 0)
    {
        if ($this->_object === null) {
            return null;
        }

        $info = $this->getExecutionInfo($nestingLevel);

        $childObjects = $this->getCollection()
            ->addFieldToFilter('parent_id', $this->getObject()->getId())
            ->setOrder('start_date', 'ASC');

        $childObjects->getSize() > 0 && $nestingLevel++;

        foreach ($childObjects as $item) {
            $object = Mage::getModel('M2ePro/OperationHistory');
            $object->setObject($item);

            $info .= $object->getExecutionTreeDownInfo($nestingLevel);
        }

        return $info;
    }

    // ---------------------------------------

    protected function getTotalTime()
    {
        $totalTime = strtotime($this->getObject()->getData('end_date')) -
                     strtotime($this->getObject()->getData('start_date'));

        if ($totalTime < 0) {
            return 'n/a';
        }

        $minutes = (int)($totalTime / 60);
        $minutes < 10 && $minutes = '0'.$minutes;

        $seconds = $totalTime - $minutes * 60;
        $seconds < 10 && $seconds = '0'.$seconds;

        return "{$minutes}:{$seconds}";
    }

    //########################################
}
