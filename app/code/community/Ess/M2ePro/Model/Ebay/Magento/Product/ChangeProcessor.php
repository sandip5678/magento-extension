<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Ebay_Magento_Product_ChangeProcessor
    extends Ess_M2ePro_Model_Magento_Product_ChangeProcessor_Abstract
{
    const INSTRUCTION_TYPE_TITLE_DATA_CHANGED       = 'magento_product_title_data_changed';
    const INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED    = 'magento_product_subtitle_data_changed';
    const INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED = 'magento_product_description_data_changed';
    const INSTRUCTION_TYPE_IMAGES_DATA_CHANGED      = 'magento_product_images_data_changed';
    const INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED  = 'magento_product_categories_data_changed';
    const INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED    = 'magento_product_shipping_data_changed';
    const INSTRUCTION_TYPE_OTHER_DATA_CHANGED       = 'magento_product_other_data_changed';

    //########################################

    public function getTrackingAttributes()
    {
        return array_unique(
            array_merge(
                $this->getTitleTrackingAttributes(),
                $this->getSubtitleTrackingAttributes(),
                $this->getDescriptionTrackingAttributes(),
                $this->getImagesTrackingAttributes(),
                $this->getCategoriesTrackingAttributes(),
                $this->getShippingTrackingAttributes(),
                $this->getOtherTrackingAttributes()
            )
        );
    }

    public function getInstructionsDataByAttributes(array $attributes)
    {
        $data = array();

        if (array_intersect($attributes, $this->getTitleTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_TITLE_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getSubtitleTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_SUBTITLE_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getDescriptionTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_DESCRIPTION_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getImagesTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_IMAGES_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getCategoriesTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_CATEGORIES_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getShippingTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_SHIPPING_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        if (array_intersect($attributes, $this->getOtherTrackingAttributes())) {
            $priority = 5;

            if ($this->getListingProduct()->isListed()) {
                $priority = 30;
            }

            $data[] = array(
                'type'     => self::INSTRUCTION_TYPE_OTHER_DATA_CHANGED,
                'priority' => $priority,
            );
        }

        return $data;
    }

    //########################################

    public function getTitleTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();
        return array_unique($ebayDescriptionTemplate->getTitleAttributes());
    }

    public function getSubtitleTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();
        return array_unique($ebayDescriptionTemplate->getSubTitleAttributes());
    }

    public function getDescriptionTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();
        return array_unique($ebayDescriptionTemplate->getDescriptionAttributes());
    }

    public function getImagesTrackingAttributes()
    {
        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();

        $trackingAttributes = array_merge(
            $ebayDescriptionTemplate->getImageMainAttributes(),
            $ebayDescriptionTemplate->getGalleryImagesAttributes(),
            $ebayDescriptionTemplate->getVariationImagesAttributes()
        );

        return array_unique($trackingAttributes);
    }

    public function getCategoriesTrackingAttributes()
    {
        if (!$this->getEbayListingProduct()->isSetCategoryTemplate()) {
            return array();
        }

        $categoryTemplate = $this->getEbayListingProduct()->getCategoryTemplate();

        return array_unique($categoryTemplate->getMainCategoryAttributes());
    }

    public function getShippingTrackingAttributes()
    {
        $shippingTemplate = $this->getEbayListingProduct()->getShippingTemplate();

        $attributes = array_merge(
            $shippingTemplate->getCountryAttributes(),
            $shippingTemplate->getAddressAttributes(),
            $shippingTemplate->getPostalCodeAttributes(),
            $shippingTemplate->getDispatchTimeAttributes()
        );

        $calculatedShippingObject = $shippingTemplate->getCalculatedShipping();
        if ($calculatedShippingObject !== null) {
            $attributes = array_merge(
                $attributes, array_merge(
                    $calculatedShippingObject->getPackageSizeAttributes(),
                    $calculatedShippingObject->getDimensionAttributes(),
                    $calculatedShippingObject->getWeightAttributes()
                )
            );
        }

        /** @var Ess_M2ePro_Model_Ebay_Template_Shipping_Service[] $services */
        $services = $shippingTemplate->getServices(true);
        foreach ($services as $service) {
            $attributes = array_merge(
                $attributes, array_merge(
                    $service->getCostAttributes(),
                    $service->getCostAdditionalAttributes(),
                    $service->getCostSurchargeAttributes()
                )
            );
        }

        return array_unique($attributes);
    }

    public function getOtherTrackingAttributes()
    {
        $trackingAttributes = array();

        $ebaySellingFormatTemplate = $this->getEbayListingProduct()->getEbaySellingFormatTemplate();
        $trackingAttributes = array_merge(
            $trackingAttributes,
            $ebaySellingFormatTemplate->getBestOfferAcceptAttributes(),
            $ebaySellingFormatTemplate->getBestOfferRejectAttributes(),
            $ebaySellingFormatTemplate->getTaxCategoryAttributes()
        );

        $ebayDescriptionTemplate = $this->getEbayListingProduct()->getEbayDescriptionTemplate();
        $trackingAttributes = array_merge(
            $trackingAttributes,
            $ebayDescriptionTemplate->getConditionAttributes(),
            $ebayDescriptionTemplate->getConditionNoteAttributes()
        );

        return array_unique($trackingAttributes);
    }

    //########################################

    /**
     * @return Ess_M2ePro_Model_Ebay_Listing_Product
     */
    protected function getEbayListingProduct()
    {
        return $this->getListingProduct()->getChildObject();
    }

    //########################################
}
