<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Block_Adminhtml_Wizard_InstallationAmazon_Installation_Account extends Mage_Adminhtml_Block_Template
{
    //########################################

    public function __construct()
    {
        parent::__construct();

        // Initialization block
        // ---------------------------------------
        $this->setId('wizardInstallationAccount');
        // ---------------------------------------

        $this->setTemplate('M2ePro/wizard/installationAmazon/installation/account.phtml');
    }

    protected function _beforeToHtml()
    {
        // ---------------------------------------
        $url = $this->getUrl('*/adminhtml_amazon_account/new', array('wizard'=>true));
        $step = 'account';
        $callback = 'function() {
            $(\'wizard_complete\').show()
        }';
        // ---------------------------------------

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Proceed'),
                'onclick' => 'WizardHandlerObj.processStep(\''.$url.'\',\''.$step.'\','.$callback.');',
                'class'   => 'process_account_button'
                )
            );
        $this->setChild('process_account_button', $buttonBlock);

        $buttonBlock = $this->getLayout()
            ->createBlock('adminhtml/widget_button')
            ->setData(
                array(
                'label'   => Mage::helper('M2ePro')->__('Skip'),
                'onclick' => 'WizardHandlerObj.skipStep(\''.$step.'\','.$callback.');',
                'class'   => 'skip_account_button'
                )
            );
        $this->setChild('skip_account_button', $buttonBlock);
        // ---------------------------------------

        return parent::_beforeToHtml();
    }

    //########################################
}
