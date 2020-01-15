<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_OperationHistory extends Ess_M2ePro_Model_OperationHistory
{
    protected $_timePoints   = array();
    protected $_leftPadding  = 0;
    protected $_bufferString = '';

    //########################################

    public function addEol()
    {
        $this->appendEol();
        $this->saveBufferString();
    }

    public function appendEol()
    {
        $this->appendText();
    }

    // ---------------------------------------

    public function addLine($char = '-')
    {
        $this->appendLine($char);
        $this->saveBufferString();
    }

    public function appendLine($char = '-')
    {
        $this->appendText(str_repeat($char, 30));
    }

    // ---------------------------------------

    public function addText($text = null)
    {
        $this->appendText($text);
        $this->saveBufferString();
    }

    public function appendText($text = null)
    {
        $text && $text = str_repeat(' ', $this->_leftPadding) . $text;
        $this->_bufferString .= (string)$text . PHP_EOL;
    }

    // ---------------------------------------

    public function saveBufferString()
    {
        $profilerData = (string)$this->getContentData('profiler');
        $this->setContentData('profiler', $profilerData.$this->_bufferString);
        $this->_bufferString = '';
    }

    //########################################

    public function addTimePoint($id, $title)
    {
        foreach ($this->_timePoints as &$point) {
            if ($point['id'] == $id) {
                $this->updateTimePoint($id);
                return true;
            }
        }

        $this->_timePoints[] = array(
            'id' => $id,
            'title' => $title,
            'time' => microtime(true)
        );

        return true;
    }

    public function updateTimePoint($id)
    {
        foreach ($this->_timePoints as $point) {
            if ($point['id'] == $id) {
                $point['time'] = microtime(true);
                return true;
            }
        }

        return false;
    }

    public function saveTimePoint($id, $immediatelySave = true)
    {
        foreach ($this->_timePoints as $point) {
            if ($point['id'] == $id) {
                $this->appendText(
                    $point['title'].': '.round(microtime(true) - $point['time'], 2).' sec.'
                );

                $immediatelySave && $this->saveBufferString();
                return true;
            }
        }

        return false;
    }

    //########################################

    public function increaseLeftPadding($count = 5)
    {
        $this->_leftPadding += (int)$count;
    }

    public function decreaseLeftPadding($count = 5)
    {
        $this->_leftPadding -= (int)$count;
        $this->_leftPadding < 0 && $this->_leftPadding = 0;
    }

    //########################################

    public function getProfilerInfo($nestingLevel = 0)
    {
        if ($this->getObject() === null) {
            return null;
        }

        $offset = str_repeat(' ', $nestingLevel * 7);
        $separationLine = str_repeat('#', 80 - strlen($offset));

        $nick = strtoupper($this->getObject()->getData('nick'));
        strpos($nick, '_') !== false && $nick = str_replace('SYNCHRONIZATION_', '', $nick);

        $profilerData = preg_replace('/^/m', "{$offset}", $this->getContentData('profiler'));

        $info = <<<INFO
{$offset}{$nick}
{$offset}Start Date: {$this->getObject()->getData('start_date')}
{$offset}End Date: {$this->getObject()->getData('end_date')}
{$offset}Total Time: {$this->getTotalTime()}

{$offset}{$separationLine}
{$profilerData}
INFO;

        if ($fatalInfo = $this->getContentData('fatal_error')) {
            $info .= <<<INFO

{$offset}<span style="color: red; font-weight: bold;">Fatal: {$fatalInfo['message']}</span>
{$offset}<span style="color: red; font-weight: bold;">File: {$fatalInfo['file']}::{$fatalInfo['line']}</span>

INFO;
        }

        if ($exceptions = $this->getContentData('exceptions')) {
            foreach ($exceptions as $exception) {
                $info .= <<<INFO

{$offset}<span style="color: red; font-weight: bold;">Exception: {$exception['message']}</span>
{$offset}<span style="color: red; font-weight: bold;">File: {$exception['file']}::{$exception['line']}</span>

INFO;
            }
        }

        return <<<INFO
{$info}
{$offset}{$separationLine}

INFO;
    }

    public function getFullProfilerInfo($nestingLevel = 0)
    {
        if ($this->getObject() === null) {
            return null;
        }

        $profilerInfo = $this->getProfilerInfo($nestingLevel);

        $childObjects = Mage::getModel('M2ePro/OperationHistory')->getCollection()
                                ->addFieldToFilter('parent_id', $this->getObject()->getId())
                                ->setOrder('start_date', 'ASC');

        $childObjects->getSize() > 0 && $nestingLevel++;

        foreach ($childObjects as $item) {
            $object = Mage::getModel('M2ePro/Synchronization_OperationHistory');
            $object->setObject($item);

            $profilerInfo .= $object->getFullProfilerInfo($nestingLevel);
        }

        return $profilerInfo;
    }

    //########################################
}
