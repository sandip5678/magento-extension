<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Block_Adminhtml_Wizard_Welcome extends Ess_M2ePro_Block_Adminhtml_Wizard_MainAbstract
{
    //########################################

    protected function getHeaderTextHtml()
    {
        return 'Welcome';
    }

    //########################################

    protected function _toHtml()
    {
        /** @var Ess_M2ePro_Helper_Module_Wizard $wizardHelper */
        $wizardHelper = $this->helper('M2ePro/Module_Wizard');

        return parent::_toHtml() .
               $wizardHelper->createBlock('welcome_content', $this->getNick())->toHtml();
    }

    //########################################
}