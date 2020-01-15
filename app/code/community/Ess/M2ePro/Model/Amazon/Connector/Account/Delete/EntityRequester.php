<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Amazon_Connector_Account_Delete_EntityRequester
    extends Ess_M2ePro_Model_Amazon_Connector_Command_Pending_Requester
{
    //########################################

    public function getRequestData()
    {
        return array();
    }

    protected function getCommand()
    {
        return array('account','delete','entity');
    }

    //########################################

    protected function getProcessingRunnerModelName()
    {
        return 'Amazon_Connector_Account_Delete_ProcessingRunner';
    }

    //########################################
}
