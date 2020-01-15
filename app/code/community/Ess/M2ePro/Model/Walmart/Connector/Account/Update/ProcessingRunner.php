<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Connector_Account_Update_ProcessingRunner
    extends Ess_M2ePro_Model_Connector_Command_Pending_Processing_Single_Runner
{
    //########################################

    protected function setLocks()
    {
        parent::setLocks();

        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Account', $params['account_id']);

        $account->addProcessingLock(null, $this->getProcessingObject()->getId());
        $account->addProcessingLock('server_synchronize', $this->getProcessingObject()->getId());
        $account->addProcessingLock('adding_to_server', $this->getProcessingObject()->getId());
    }

    protected function unsetLocks()
    {
        parent::unsetLocks();

        $params = $this->getParams();

        /** @var Ess_M2ePro_Model_Account $account */
        $account = Mage::helper('M2ePro/Component_Walmart')->getCachedObject('Account', $params['account_id']);

        $account->deleteProcessingLocks(null, $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks('server_synchronize', $this->getProcessingObject()->getId());
        $account->deleteProcessingLocks('adding_to_server', $this->getProcessingObject()->getId());
    }

    //########################################
}