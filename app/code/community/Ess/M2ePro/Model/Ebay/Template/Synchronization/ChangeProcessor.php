<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Template_Synchronization_ChangeProcessor
    extends Ess_M2ePro_Model_Template_Synchronization_ChangeProcessor_Abstract
{
    const INSTRUCTION_TYPE_REVISE_QTY_ENABLED            = 'template_synchronization_revise_qty_enabled';
    const INSTRUCTION_TYPE_REVISE_QTY_DISABLED           = 'template_synchronization_revise_qty_disabled';
    const INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED   = 'template_synchronization_revise_qty_settings_changed';

    const INSTRUCTION_TYPE_REVISE_PRICE_ENABLED          = 'template_synchronization_revise_price_enabled';
    const INSTRUCTION_TYPE_REVISE_PRICE_DISABLED         = 'template_synchronization_revise_price_disabled';
    const INSTRUCTION_TYPE_REVISE_PRICE_SETTINGS_CHANGED = 'template_synchronization_revise_price_settings_changed';

    const INSTRUCTION_TYPE_REVISE_TITLE_ENABLED          = 'template_synchronization_revise_title_enabled';
    const INSTRUCTION_TYPE_REVISE_TITLE_DISABLED         = 'template_synchronization_revise_title_disabled';

    const INSTRUCTION_TYPE_REVISE_SUBTITLE_ENABLED       = 'template_synchronization_revise_subtitle_enabled';
    const INSTRUCTION_TYPE_REVISE_SUBTITLE_DISABLED      = 'template_synchronization_revise_subtitle_disabled';

    const INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED    = 'template_synchronization_revise_description_enabled';
    const INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED   = 'template_synchronization_revise_description_disabled';

    const INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED         = 'template_synchronization_revise_images_enabled';
    const INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED        = 'template_synchronization_revise_images_disabled';

    const INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED     = 'template_synchronization_revise_categories_enabled';
    const INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED    = 'template_synchronization_revise_categories_disabled';

    const INSTRUCTION_TYPE_REVISE_PAYMENT_ENABLED        = 'template_synchronization_revise_payment_enabled';
    const INSTRUCTION_TYPE_REVISE_PAYMENT_DISABLED       = 'template_synchronization_revise_payment_disabled';

    const INSTRUCTION_TYPE_REVISE_SHIPPING_ENABLED       = 'template_synchronization_revise_shipping_enabled';
    const INSTRUCTION_TYPE_REVISE_SHIPPING_DISABLED      = 'template_synchronization_revise_shipping_disabled';

    const INSTRUCTION_TYPE_REVISE_RETURN_ENABLED         = 'template_synchronization_revise_return_enabled';
    const INSTRUCTION_TYPE_REVISE_RETURN_DISABLED        = 'template_synchronization_revise_return_disabled';

    const INSTRUCTION_TYPE_REVISE_OTHER_ENABLED          = 'template_synchronization_revise_other_enabled';
    const INSTRUCTION_TYPE_REVISE_OTHER_DISABLED         = 'template_synchronization_revise_other_disabled';

    //########################################

    protected function getInstructionsData(Ess_M2ePro_Model_Template_Diff_Abstract $diff, $status)
    {
        /** @var Ess_M2ePro_Model_Ebay_Template_Synchronization_Diff $diff */

        $data = parent::getInstructionsData($diff, $status);

        if ($diff->isReviseQtyEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 80;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_QTY_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseQtyDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_QTY_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseQtySettingsChanged()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 80;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_QTY_SETTINGS_CHANGED,
                'priority'  => $priority,
            );
        }

        if ($diff->isRevisePriceEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 60;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_PRICE_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isRevisePriceDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_PRICE_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isRevisePriceSettingsChanged()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_PRICE_SETTINGS_CHANGED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseTitleEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_TITLE_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseTitleDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_TITLE_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseSubtitleEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_SUBTITLE_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseSubtitleDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_SUBTITLE_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseDescriptionEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_DESCRIPTION_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseDescriptionDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_DESCRIPTION_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseImagesEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_IMAGES_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseImagesDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_IMAGES_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseCategoriesEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_CATEGORIES_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseCategoriesDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_CATEGORIES_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isRevisePaymentEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_PAYMENT_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isRevisePaymentDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_PAYMENT_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseShippingEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_SHIPPING_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseShippingDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_SHIPPING_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseReturnEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_RETURN_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseReturnDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_RETURN_DISABLED,
                'priority'  => 5,
            );
        }

        if ($diff->isReviseOtherEnabled()) {
            $priority = 5;

            if ($status == Ess_M2ePro_Model_Listing_Product::STATUS_LISTED) {
                $priority = 30;
            }

            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_OTHER_ENABLED,
                'priority'  => $priority,
            );
        }

        if ($diff->isReviseOtherDisabled()) {
            $data[] = array(
                'type'      => self::INSTRUCTION_TYPE_REVISE_OTHER_DISABLED,
                'priority'  => 5,
            );
        }

        return $data;
    }

    //########################################
}
