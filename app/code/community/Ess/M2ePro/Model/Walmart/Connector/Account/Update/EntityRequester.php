<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Account_Update_EntityRequester
    extends Ess_M2ePro_Model_Walmart_Connector_Command_Pending_Requester
{
    //########################################

    public function getRequestData()
    {
        return $this->_params;
    }

    protected function getCommand()
    {
        return array('account','update','entity');
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Walmart_Connector_Account_Update_ProcessingRunner';
    }

    //########################################
}
