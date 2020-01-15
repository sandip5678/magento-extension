<?php

/*
 * @author     M2E Pro Developers Team
 * @copyright  2011-2017 ESS-UA [M2E Pro]
 * @license    Commercial use is forbidden
 */

class Ess_M2ePro_Model_Walmart_Template_SellingFormat_SnapshotBuilder
    extends Ess_M2ePro_Model_Template_SnapshotBuilder_Abstract
{
    //########################################

    public function getSnapshot()
    {
        $data = $this->_model->getData();
        if (empty($data)) {
            return array();
        }

        /** @var Ess_M2ePro_Model_Walmart_Template_SellingFormat $childModel */
        $childModel = $this->_model->getChildObject();

        $ignoredKeys = array(
            'id',
            'template_selling_format_id',
        );

        // ---------------------------------------
        $data['shipping_overrides'] = $childModel->getShippingOverrides();

        if ($data['shipping_overrides'] !== null) {
            foreach ($data['shipping_overrides'] as &$shippingOverride) {
                foreach ($shippingOverride as $key => &$value) {
                    if (in_array($key, $ignoredKeys)) {
                        unset($shippingOverride[$key]);
                        continue;
                    }

                    $value !== null && !is_array($value) && $value = (string)$value;
                }

                unset($value);
            }

            unset($shippingOverride);
        }

        // ---------------------------------------

        // ---------------------------------------
        $data['promotions'] = $childModel->getPromotions();

        if ($data['promotions'] !== null) {
            foreach ($data['promotions'] as &$promotion) {
                foreach ($promotion as $key => &$value) {
                    if (in_array($key, $ignoredKeys)) {
                        unset($promotion[$key]);
                        continue;
                    }

                    $value !== null && !is_array($value) && $value = (string)$value;
                }

                unset($value);
            }

            unset($promotion);
        }

        // ---------------------------------------

        return $data;
    }

    //########################################
}
