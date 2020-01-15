<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  M2E LTD
 * @license    Commercial use is forbidden
 */

abstract class Ess_M2ePro_Controller_Adminhtml_WizardController
    extends Ess_M2ePro_Controller_Adminhtml_MainController
{
    /** @var Ess_M2ePro_Helper_Module_Wizard|null  */
    protected $_wizardHelper = null;

    //########################################

    abstract protected function getNick();

    abstract protected function getMenuRootNodeNick();

    abstract protected function getMenuRootNodeLabel();

    //########################################

    protected function _initAction()
    {
        $this->loadLayout();

        // Popup
        // ---------------------------------------
        $this->_initPopUp();
        // ---------------------------------------

        Mage::helper('M2ePro/Module_Wizard')->addWizardHandlerJs();

        return $this;
    }

    //########################################

    public function indexAction()
    {
        if ($this->isNotStarted()) {
            return $this->_redirect('*/*/welcome');
        }

        if ($this->isActive()) {
            return $this->_redirect('*/*/installation');
        }

        $this->_redirect('*/*/congratulation', array('wizard'=>true));
    }

    // ---------------------------------------

    public function welcomeAction()
    {
        if (!$this->isNotStarted()) {
            return $this->_redirect('*/*/index');
        }

        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_ACTIVE)
             ->setStep($this->getFirstStep());

        $this->_redirect('*/*/index');
    }

    public function installationAction()
    {
        if ($this->isFinished() || $this->isNotStarted()) {
            return $this->_redirect('*/*/index');
        }

        if (!$this->getCurrentStep()) {
            $this->setStep($this->getFirstStep());
        }

        return $this->_initAction()
                    ->_addContent($this->getWizardHelper()->createBlock('installation', $this->getNick()))
                    ->renderLayout();
    }

    public function congratulationAction()
    {
        if (!$this->isFinished()) {
            $this->_redirect('*/*/index');
            return;
        }

        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->_initAction();
        $this->_addContent($this->getWizardHelper()->createBlock('congratulation', $this->getNick()));
        $this->_addNextWizardPresentation();
        $this->renderLayout();
    }

    //########################################

    public function skipAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_SKIPPED);

        $this->_redirect('*/*/index');
    }

    public function completeAction()
    {
        Mage::helper('M2ePro/Magento')->clearMenuCache();

        $this->setStatus(Ess_M2ePro_Helper_Module_Wizard::STATUS_COMPLETED);

        $this->_redirect('*/*/index');
    }

    //########################################

    protected function getWizardHelper()
    {
        if ($this->_wizardHelper === null) {
            $this->_wizardHelper = Mage::helper('M2ePro/Module_Wizard');
        }

        return $this->_wizardHelper;
    }

    // ---------------------------------------

    protected function setStatus($status)
    {
        $this->getWizardHelper()->setStatus($this->getNick(), $status);
        return $this;
    }

    protected function getStatus()
    {
        return $this->getWizardHelper()->getStatus($this->getNick());
    }

    // ---------------------------------------

    protected function setStep($step)
    {
        $this->getWizardHelper()->setStep($this->getNick(), $step);
        return $this;
    }

    protected function getSteps()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getSteps();
    }

    protected function getFirstStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getFirstStep();
    }

    protected function getPrevStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getPrevStep();
    }

    protected function getCurrentStep()
    {
        return $this->getWizardHelper()->getStep($this->getNick());
    }

    protected function getNextStep()
    {
        return $this->getWizardHelper()->getWizard($this->getNick())->getNextStep();
    }

    // ---------------------------------------

    protected function isNotStarted()
    {
        return $this->getWizardHelper()->isNotStarted($this->getNick());
    }

    protected function isActive()
    {
        return $this->getWizardHelper()->isActive($this->getNick());
    }

    public function isCompleted()
    {
        return $this->getWizardHelper()->isCompleted($this->getNick());
    }

    public function isSkipped()
    {
        return $this->getWizardHelper()->isSkipped($this->getNick());
    }

    protected function isFinished()
    {
        return $this->getWizardHelper()->isFinished($this->getNick());
    }

    //########################################

    public function setStepAction()
    {
        $step = $this->getRequest()->getParam('step');

        if ($step === null) {
            return $this->getResponse()->setBody(
                json_encode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Step is invalid')
                    )
                )
            );
        }

        $this->setStep($step);

        return $this->getResponse()->setBody(
            json_encode(
                array(
                'type' => 'success'
                )
            )
        );
    }

    public function setStatusAction()
    {
        $status = $this->getRequest()->getParam('status');

        if ($status === null) {
            return $this->getResponse()->setBody(
                json_encode(
                    array(
                    'type' => 'error',
                    'message' => Mage::helper('M2ePro')->__('Status is invalid')
                    )
                )
            );
        }

        $this->setStatus($status);

        return $this->getResponse()->setBody(
            json_encode(
                array(
                'type' => 'success'
                )
            )
        );
    }

    //########################################

    protected function _addNextWizardPresentation()
    {
        $nextWizard = $this->getWizardHelper()->getActiveWizard($this->getCustomViewNick());
        if ($nextWizard) {
            $presentationBlock = $this->getWizardHelper()->createBlock(
                'presentation', $this->getWizardHelper()->getNick($nextWizard)
            );
            $presentationBlock && $this->_addContent($presentationBlock);
        }

        return $this;
    }

    //########################################

    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed($this->getMenuRootNodeNick());
    }

    public function loadLayout($ids=null, $generateBlocks=true, $generateXml=true)
    {
        $tempResult = parent::loadLayout($ids, $generateBlocks, $generateXml);
        $tempResult->_setActiveMenu($this->getMenuRootNodeNick());
        $tempResult->_title($this->getMenuRootNodeLabel());
        return $tempResult;
    }

    //########################################
}
