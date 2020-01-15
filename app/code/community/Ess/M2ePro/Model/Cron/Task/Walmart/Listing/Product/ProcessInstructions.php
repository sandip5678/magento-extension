<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2015 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Cron_Task_Walmart_Listing_Product_ProcessInstructions
    extends Ess_M2ePro_Model_Cron_Task_Abstract
{
    const NICK = 'walmart/listing/product/process_instructions';

    //####################################

    protected function performActions()
    {
        $processor = Mage::getModel('M2ePro/Listing_Product_Instruction_Processor');
        $processor->setComponent(Ess_M2ePro_Helper_Component_Walmart::NICK);
        $processor->setMaxListingsProductsCount(
            (int)Mage::helper('M2ePro/Module')->getConfig()->getGroupValue(
                '/walmart/listing/product/instructions/cron/', 'listings_products_per_one_time'
            )
        );
        $processor->registerHandler(
            Mage::getModel('M2ePro/Walmart_Listing_Product_Instruction_AutoActions_Handler')
        );
        $processor->registerHandler(
            Mage::getModel('M2ePro/Walmart_Listing_Product_Instruction_SynchronizationTemplate_Handler')
        );

        $processor->process();
    }

    //########################################
}
