<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Helper_Module_Exception extends Mage_Core_Helper_Abstract
{
    const FILTER_TYPE_TYPE    = 1;
    const FILTER_TYPE_INFO    = 2;
    const FILTER_TYPE_MESSAGE = 3;

    //########################################

    public function process(Exception $exception, $sendToServer = true)
    {
        try {
            $type = get_class($exception);

            $info = $this->getExceptionInfo($exception, $type);
            $info .= $this->getExceptionStackTraceInfo($exception);
            $info .= $this->getCurrentUserActionInfo();
            $info .= $this->getAdditionalActionInfo();
            $info .= Mage::helper('M2ePro/Module_Support_Form')->getSummaryInfo();

            $this->log($info, $type);

            if (!$sendToServer ||
                ($exception instanceof Ess_M2ePro_Model_Exception && !$exception->isSendToServer()) ||
                !(bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/debug/exceptions/', 'send_to_server') ||
                $this->isExceptionFiltered($info, $exception->getMessage(), $type)) {
                return;
            }

            $temp = Mage::helper('M2ePro/Data_Global')->getValue('send_exception_to_server');
            if (!empty($temp)) {
                return;
            }

            Mage::helper('M2ePro/Data_Global')->setValue('send_exception_to_server', true);

            $this->send($info, $exception->getMessage(), $type);

            Mage::helper('M2ePro/Data_Global')->unsetValue('send_exception_to_server');
        } catch (Exception $exceptionTemp) {
        }
    }

    public function processFatal($error, $traceInfo)
    {
        try {
            $type = 'Fatal Error';

            $info = $this->getFatalInfo($error, $type);
            $info .= $traceInfo;
            $info .= $this->getCurrentUserActionInfo();
            $info .= $this->getAdditionalActionInfo();
            $info .= Mage::helper('M2ePro/Module_Support_Form')->getSummaryInfo();

            $this->log($info, $type);

            if (!(bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                                ->getGroupValue('/debug/fatal_error/', 'send_to_server') ||
                $this->isExceptionFiltered($info, $error['message'], $type)) {
                return;
            }

            $temp = Mage::helper('M2ePro/Data_Global')->getValue('send_exception_to_server');
            if (!empty($temp)) {
                return;
            }

            Mage::helper('M2ePro/Data_Global')->setValue('send_exception_to_server', true);

            $this->send($info, $error['message'], $type);

            Mage::helper('M2ePro/Data_Global')->unsetValue('send_exception_to_server');
        } catch (Exception $exceptionTemp) {
        }
    }

    // ---------------------------------------

    public function setFatalErrorHandler()
    {
        $temp = Mage::helper('M2ePro/Data_Global')->getValue('set_fatal_error_handler');

        if (!empty($temp)) {
            return;
        }

        Mage::helper('M2ePro/Data_Global')->setValue('set_fatal_error_handler', true);

        register_shutdown_function(
            function(){

            $error = error_get_last();
            if ($error === null) {
                return;
            }

            if (!in_array((int)$error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR))) {
                return;
            }

            $trace = @debug_backtrace(false);
            $traceInfo = Mage::helper('M2ePro/Module_Exception')->getFatalStackTraceInfo($trace);

            Mage::helper('M2ePro/Module_Exception')->processFatal($error, $traceInfo);
            }
        );
    }

    public function getUserMessage(Exception $exception)
    {
        return Mage::helper('M2ePro')->__('Fatal error occurred').': "'.$exception->getMessage().'".';
    }

    //########################################

    protected function log($message, $type)
    {
        /** @var Ess_M2ePro_Model_Log_System $log */
        $log = Mage::getModel('M2ePro/Log_System');

        $log->setType($type);
        $log->setDescription($message);

        $trace = debug_backtrace();
        $file = isset($trace[1]['file']) ? $trace[1]['file'] : 'not set';;
        $line = isset($trace[1]['line']) ? $trace[1]['line'] : 'not set';

        $additionalData = array(
            'called-from' => $file .' : '. $line
        );
        $log->setData('additional_data', print_r($additionalData, true));

        $log->save();
    }

    //########################################

    protected function getExceptionInfo(Exception $exception, $type)
    {
        $additionalData = $exception instanceof Ess_M2ePro_Model_Exception ? $exception->getAdditionalData()
                                                                           : '';

        is_array($additionalData) && $additionalData = print_r($additionalData, true);

        $exceptionInfo = <<<EXCEPTION
-------------------------------- EXCEPTION INFO ----------------------------------
Type: {$type}
File: {$exception->getFile()}
Line: {$exception->getLine()}
Code: {$exception->getCode()}
Message: {$exception->getMessage()}
Additional Data: {$additionalData}

EXCEPTION;

        return $exceptionInfo;
    }

    protected function getExceptionStackTraceInfo(Exception $exception)
    {
        $stackTraceInfo = <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$exception->getTraceAsString()}


TRACE;

        return $stackTraceInfo;
    }

    // ---------------------------------------

    protected function getFatalInfo($error, $type)
    {
        $fatalInfo = <<<FATAL
-------------------------------- FATAL ERROR INFO --------------------------------
Type: {$type}
File: {$error['file']}
Line: {$error['line']}
Message: {$error['message']}


FATAL;

        return $fatalInfo;
    }

    public function getFatalStackTraceInfo($stackTrace)
    {
        if (!is_array($stackTrace)) {
            $stackTrace = array();
        }

        $stackTrace = array_reverse($stackTrace);
        $info = '';

        if (count($stackTrace) > 1) {
            foreach ($stackTrace as $key => $trace) {
                $info .= "#{$key} {$trace['file']}({$trace['line']}):";
                $info .= " {$trace['class']}{$trace['type']}{$trace['function']}(";

                if (count($trace['args'])) {
                    foreach ($trace['args'] as $key => $arg) {
                        $key != 0 && $info .= ',';

                        if (is_object($arg)) {
                            $info .= get_class($arg);
                        } else {
                            $info .= $arg;
                        }
                    }
                }

                $info .= ")\n";
            }
        }

        if ($info == '') {
            $info = 'Unavailable';
        }

        $stackTraceInfo = <<<TRACE
-------------------------------- STACK TRACE INFO --------------------------------
{$info}


TRACE;

        return $stackTraceInfo;
    }

    // ---------------------------------------

    protected function getCurrentUserActionInfo()
    {
        $server = print_r(Mage::app()->getRequest()->getServer(), true);
        $get = print_r(Mage::app()->getRequest()->getQuery(), true);
        $post = print_r(Mage::app()->getRequest()->getPost(), true);

        $actionInfo = <<<ACTION
-------------------------------- ACTION INFO -------------------------------------
SERVER: {$server}
GET: {$get}
POST: {$post}

ACTION;

        return $actionInfo;
    }

    protected function getAdditionalActionInfo()
    {
        $currentStoreId = Mage::app()->getStore()->getId();

        $actionInfo = <<<ACTION
-------------------------------- ADDITIONAL INFO -------------------------------------
Current Store: {$currentStoreId}

ACTION;

        return $actionInfo;
    }

    //########################################

    protected function send($info, $message, $type)
    {
        $dispatcherObject = Mage::getModel('M2ePro/M2ePro_Connector_Dispatcher');
        $connectorObj = $dispatcherObject->getVirtualConnector(
            'exception', 'add', 'entity',
            array('info'    => $info,
                                                                     'message' => $message,
            'type'    => $type)
        );

        $dispatcherObject->process($connectorObj);
    }

    protected function isExceptionFiltered($info, $message, $type)
    {
        if (!(bool)(int)Mage::helper('M2ePro/Module')->getConfig()
                            ->getGroupValue('/debug/exceptions/', 'filters_mode')) {
            return false;
        }

        $exceptionFilters = Mage::getModel('M2ePro/Registry')->load('/exceptions_filters/', 'key')
                                                             ->getValueFromJson();

        foreach ($exceptionFilters as $exceptionFilter) {
            try {
                $searchSubject = '';
                $exceptionFilter['type'] == self::FILTER_TYPE_TYPE    && $searchSubject = $type;
                $exceptionFilter['type'] == self::FILTER_TYPE_MESSAGE && $searchSubject = $message;
                $exceptionFilter['type'] == self::FILTER_TYPE_INFO    && $searchSubject = $info;

                $tempResult = preg_match($exceptionFilter['preg_match'], $searchSubject);
            } catch (Exception $exception) {
                return false;
            }

            if ($tempResult) {
                return true;
            }
        }

        return false;
    }

    //########################################
}
