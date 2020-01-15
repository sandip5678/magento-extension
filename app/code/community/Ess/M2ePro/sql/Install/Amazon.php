<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Install_Amazon extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->run(
            <<<SQL

DROP TABLE IF EXISTS `m2epro_amazon_account`;
CREATE TABLE `m2epro_amazon_account` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `merchant_id` VARCHAR(255) NOT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  `related_store_id` INT(11) NOT NULL DEFAULT 0,
  `other_listings_synchronization` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `other_listings_mapping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `other_listings_mapping_settings` VARCHAR(255) DEFAULT NULL,
  `magento_orders_settings` TEXT NOT NULL,
  `is_vat_calculation_service_enabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_magento_invoice_creation_disabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `info` TEXT DEFAULT NULL,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_account_repricing`;
CREATE TABLE `m2epro_amazon_account_repricing` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `token` VARCHAR(255) DEFAULT NULL,
  `total_products` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `regular_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `regular_price_attribute` VARCHAR(255) NOT NULL,
  `regular_price_coefficient` VARCHAR(255) NOT NULL,
  `regular_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `min_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `min_price_value` DECIMAL(14, 2) UNSIGNED DEFAULT NULL,
  `min_price_percent` INT(11) UNSIGNED DEFAULT NULL,
  `min_price_attribute` VARCHAR(255) NOT NULL,
  `min_price_coefficient` VARCHAR(255) NOT NULL,
  `min_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `max_price_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `max_price_value` DECIMAL(14, 2) UNSIGNED DEFAULT NULL,
  `max_price_percent` INT(11) UNSIGNED DEFAULT NULL,
  `max_price_attribute` VARCHAR(255) NOT NULL,
  `max_price_coefficient` VARCHAR(255) NOT NULL,
  `max_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `disable_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `disable_mode_attribute` VARCHAR(255) NOT NULL,
  `last_checked_listing_product_update_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_category`;
CREATE TABLE `m2epro_amazon_dictionary_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `parent_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `browsenode_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `product_data_nicks` VARCHAR(500) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `path` VARCHAR(500) DEFAULT NULL,
  `keywords` TEXT DEFAULT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `browsenode_id` (`browsenode_id`),
  INDEX `category_id` (`category_id`),
  INDEX `is_leaf` (`is_leaf`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `path` (`path`),
  INDEX `parent_category_id` (`parent_category_id`),
  INDEX `title` (`title`),
  INDEX `product_data_nicks` (`product_data_nicks`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_category_product_data`;
CREATE TABLE `m2epro_amazon_dictionary_category_product_data` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `browsenode_id` INT(11) UNSIGNED NOT NULL,
  `product_data_nick` VARCHAR(255) NOT NULL,
  `is_applicable` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `required_attributes` TEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `browsenode_id` (`browsenode_id`),
  INDEX `product_data_nick` (`product_data_nick`),
  INDEX `is_applicable` (`is_applicable`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_marketplace`;
CREATE TABLE `m2epro_amazon_dictionary_marketplace` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `client_details_last_update_date` DATETIME DEFAULT NULL,
  `server_details_last_update_date` DATETIME DEFAULT NULL,
  `product_data` LONGTEXT DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_dictionary_specific`;
CREATE TABLE `m2epro_amazon_dictionary_specific` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `specific_id` INT(11) UNSIGNED NOT NULL,
  `parent_specific_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_data_nick` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `xml_tag` VARCHAR(255) NOT NULL,
  `xpath` VARCHAR(255) NOT NULL,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `values` TEXT DEFAULT NULL,
  `recommended_values` TEXT DEFAULT NULL,
  `params` TEXT DEFAULT NULL,
  `data_definition` TEXT DEFAULT NULL,
  `min_occurs` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
  `max_occurs` TINYINT(4) UNSIGNED NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `max_occurs` (`max_occurs`),
  INDEX `min_occurs` (`min_occurs`),
  INDEX `parent_specific_id` (`parent_specific_id`),
  INDEX `title` (`title`),
  INDEX `type` (`type`),
  INDEX `specific_id` (`specific_id`),
  INDEX `xml_tag` (`xml_tag`),
  INDEX `xpath` (`xpath`),
  INDEX `product_data_nick` (`product_data_nick`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_item`;
CREATE TABLE `m2epro_amazon_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `variation_product_options` TEXT DEFAULT NULL,
  `variation_channel_options` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `product_id` (`product_id`),
  INDEX `sku` (`sku`),
  INDEX `store_id` (`store_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing`;
CREATE TABLE `m2epro_amazon_listing` (
  `listing_id` INT(11) UNSIGNED NOT NULL,
  `auto_global_adding_description_template_id` int(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_description_template_id` int(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `sku_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sku_custom_attribute` VARCHAR(255) NOT NULL,
  `sku_modification_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sku_modification_custom_value` VARCHAR(255) NOT NULL,
  `generate_sku_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `general_id_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `general_id_custom_attribute` VARCHAR(255) NOT NULL,
  `worldwide_id_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `worldwide_id_custom_attribute` VARCHAR(255) NOT NULL,
  `search_by_magento_title_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `condition_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_value` VARCHAR(255) NOT NULL,
  `condition_custom_attribute` VARCHAR(255) NOT NULL,
  `condition_note_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_note_value` VARCHAR(2000) NOT NULL,
  `image_main_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_main_attribute` varchar(255) NOT NULL,
  `gallery_images_mode` tinyint(2) UNSIGNED NOT NULL,
  `gallery_images_limit` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `gallery_images_attribute` varchar(255) NOT NULL,
  `gift_wrap_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `gift_wrap_attribute` VARCHAR(255) NOT NULL,
  `gift_message_mode` TINYINT(2) UNSIGNED NOT NULL,
  `gift_message_attribute` VARCHAR(255) NOT NULL,
  `handling_time_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `handling_time_value` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `handling_time_custom_attribute` VARCHAR(255) NOT NULL,
  `restock_date_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `restock_date_value` DATETIME NOT NULL,
  `restock_date_custom_attribute` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`listing_id`),
  INDEX `auto_global_adding_description_template_id` (`auto_global_adding_description_template_id`),
  INDEX `auto_website_adding_description_template_id` (`auto_website_adding_description_template_id`),
  INDEX `generate_sku_mode` (`generate_sku_mode`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_auto_category_group`;
CREATE TABLE `m2epro_amazon_listing_auto_category_group` (
    `listing_auto_category_group_id` int(11) UNSIGNED NOT NULL,
    `adding_description_template_id` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`listing_auto_category_group_id`),
    INDEX `adding_description_template_id` (`adding_description_template_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_other`;
CREATE TABLE `m2epro_amazon_listing_other` (
  `listing_other_id` INT(11) UNSIGNED NOT NULL,
  `general_id` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `is_afn_channel` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_isbn_general_id` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing_disabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing_inactive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`listing_other_id`),
  INDEX `general_id` (`general_id`),
  INDEX `is_afn_channel` (`is_afn_channel`),
  INDEX `is_isbn_general_id` (`is_isbn_general_id`),
  INDEX `is_repricing` (`is_repricing`),
  INDEX `is_repricing_disabled` (`is_repricing_disabled`),
  INDEX `is_repricing_inactive` (`is_repricing_inactive`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product`;
CREATE TABLE `m2epro_amazon_listing_product` (
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `template_description_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_product_tax_code_id` INT(11) UNSIGNED DEFAULT NULL,
  `is_variation_product` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_product_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_channel_matched` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_variation_parent` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_parent_id` INT(11) UNSIGNED DEFAULT NULL,
  `variation_parent_need_processor` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_child_statuses` TEXT DEFAULT NULL,
  `general_id` VARCHAR(255) DEFAULT NULL,
  `general_id_search_info` TEXT DEFAULT NULL,
  `search_settings_status` TINYINT(2) UNSIGNED DEFAULT NULL,
  `search_settings_data` LONGTEXT DEFAULT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `online_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_regular_sale_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_regular_sale_price_start_date` DATETIME DEFAULT NULL,
  `online_regular_sale_price_end_date` DATETIME DEFAULT NULL,
  `online_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_business_discounts` TEXT DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_handling_time` INT(11) UNSIGNED DEFAULT NULL,
  `online_restock_date` DATETIME DEFAULT NULL,
  `online_details_data` LONGTEXT DEFAULT NULL,
  `online_images_data` LONGTEXT DEFAULT NULL,
  `is_details_data_changed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_images_data_changed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_repricing` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_afn_channel` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_isbn_general_id` TINYINT(2) UNSIGNED DEFAULT NULL,
  `is_general_id_owner` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `variation_parent_afn_state` SMALLINT(4) UNSIGNED DEFAULT NULL,
  `variation_parent_repricing_state` SMALLINT(4) UNSIGNED DEFAULT NULL,
  `defected_messages` TEXT DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  INDEX `general_id` (`general_id`),
  INDEX `search_settings_status` (`search_settings_status`),
  INDEX `is_repricing` (`is_repricing`),
  INDEX `is_afn_channel` (`is_afn_channel`),
  INDEX `is_isbn_general_id` (`is_isbn_general_id`),
  INDEX `is_variation_product_matched` (`is_variation_product_matched`),
  INDEX `is_variation_channel_matched` (`is_variation_channel_matched`),
  INDEX `is_variation_product` (`is_variation_product`),
  INDEX `online_regular_price` (`online_regular_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_regular_sale_price` (`online_regular_sale_price`),
  INDEX `online_business_price` (`online_business_price`),
  INDEX `sku` (`sku`),
  INDEX `is_variation_parent` (`is_variation_parent`),
  INDEX `variation_parent_need_processor` (`variation_parent_need_processor`),
  INDEX `variation_parent_id` (`variation_parent_id`),
  INDEX `is_general_id_owner` (`is_general_id_owner`),
  INDEX `variation_parent_afn_state` (`variation_parent_afn_state`),
  INDEX `variation_parent_repricing_state` (`variation_parent_repricing_state`),
  INDEX `template_shipping_id` (`template_shipping_id`),
  INDEX `template_product_tax_code_id` (`template_product_tax_code_id`),
  INDEX `template_description_id` (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_repricing`;
CREATE TABLE `m2epro_amazon_listing_product_repricing` (
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `is_online_disabled` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_online_inactive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_min_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_max_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_min_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_max_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `last_updated_is_disabled` TINYINT(2) UNSIGNED DEFAULT NULL,
  `is_process_required` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `last_synchronization_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  INDEX `is_online_disabled` (`is_online_disabled`),
  INDEX `is_online_inactive` (`is_online_inactive`),
  INDEX `is_process_required` (`is_process_required`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_variation`;
CREATE TABLE `m2epro_amazon_listing_product_variation` (
  `listing_product_variation_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_variation_option`;
CREATE TABLE `m2epro_amazon_listing_product_variation_option` (
  `listing_product_variation_option_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_option_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_action_processing`;
CREATE TABLE `m2epro_amazon_listing_product_action_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `listing_product_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `is_prepared` TINYINT(2) NOT NULL DEFAULT 0,
  `group_hash` VARCHAR(255) DEFAULT NULL,
  `request_data` LONGTEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`),
  INDEX `type` (`type`),
  INDEX `is_prepared` (`is_prepared`),
  INDEX `group_hash` (`group_hash`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_listing_product_action_processing_list_sku`;
CREATE TABLE `m2epro_amazon_listing_product_action_processing_list_sku` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) NOT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `account_id__sku` (`account_id`, `sku`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_indexer_listing_product_parent`;
CREATE TABLE `m2epro_amazon_indexer_listing_product_parent` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `min_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_regular_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `min_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `max_business_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_marketplace`;
CREATE TABLE `m2epro_amazon_marketplace` (
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `developer_key` VARCHAR(255) DEFAULT NULL,
  `default_currency` VARCHAR(255) NOT NULL,
  `is_new_asin_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `is_merchant_fulfillment_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_business_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_vat_calculation_service_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_product_tax_code_policy_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_automatic_token_retrieving_available` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`marketplace_id`),
  INDEX `is_new_asin_available` (`is_new_asin_available`),
  INDEX `is_merchant_fulfillment_available` (`is_merchant_fulfillment_available`),
  INDEX `is_business_available` (`is_business_available`),
  INDEX `is_vat_calculation_service_available` (`is_vat_calculation_service_available`),
  INDEX `is_product_tax_code_policy_available` (`is_product_tax_code_policy_available`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_order`;
CREATE TABLE `m2epro_amazon_order` (
  `order_id` INT(11) UNSIGNED NOT NULL,
  `amazon_order_id` VARCHAR(255) NOT NULL,
  `seller_order_id` varchar(255) DEFAULT NULL,
  `is_afn_channel` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_prime` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_business` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_email` VARCHAR(255) DEFAULT NULL,
  `shipping_service` VARCHAR(255) DEFAULT NULL,
  `shipping_address` TEXT NOT NULL,
  `shipping_price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `shipping_dates` TEXT DEFAULT NULL,
  `paid_amount` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `discount_details` TEXT DEFAULT NULL,
  `qty_shipped` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `qty_unshipped` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `currency` VARCHAR(10) NOT NULL,
  `purchase_update_date` DATETIME DEFAULT NULL,
  `purchase_create_date` DATETIME DEFAULT NULL,
  `merchant_fulfillment_data` TEXT NULL DEFAULT NULL,
  `merchant_fulfillment_label` BLOB NULL DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `amazon_order_id` (`amazon_order_id`),
  INDEX `seller_order_id` (`seller_order_id`),
  INDEX `is_prime` (`is_prime`),
  INDEX `is_business` (`is_business`),
  INDEX `buyer_email` (`buyer_email`),
  INDEX `buyer_name` (`buyer_name`),
  INDEX `paid_amount` (`paid_amount`),
  INDEX `purchase_create_date` (`purchase_create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_order_item`;
CREATE TABLE `m2epro_amazon_order_item` (
  `order_item_id` INT(11) UNSIGNED NOT NULL,
  `amazon_order_item_id` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `general_id` VARCHAR(255) DEFAULT NULL,
  `is_isbn_general_id` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `price` DECIMAL(12, 4) UNSIGNED NOT NULL,
  `gift_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `gift_message` VARCHAR(500) DEFAULT NULL,
  `gift_type` VARCHAR(255) DEFAULT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `discount_details` TEXT DEFAULT NULL,
  `currency` VARCHAR(10) NOT NULL,
  `qty_purchased` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  INDEX `general_id` (`general_id`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_order_action_processing`;
CREATE TABLE `m2epro_amazon_order_action_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED DEFAULT NULL,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `request_pending_single_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_data` LONGTEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `request_pending_single_id` (`request_pending_single_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_shipping`;
CREATE TABLE `m2epro_amazon_template_shipping` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `template_name_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
    `template_name_value` VARCHAR(255) NOT NULL,
    `template_name_attribute` VARCHAR(255) NOT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_product_tax_code`;
CREATE TABLE `m2epro_amazon_template_product_tax_code` (
    `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL,
    `product_tax_code_mode` TINYINT(2) NOT NULL,
    `product_tax_code_value` VARCHAR(255) DEFAULT NULL,
    `product_tax_code_attribute` VARCHAR(255) DEFAULT NULL,
    `update_date` DATETIME DEFAULT NULL,
    `create_date` DATETIME DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_description`;
CREATE TABLE `m2epro_amazon_template_description` (
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `is_new_asin_accepted` TINYINT(2) UNSIGNED DEFAULT 0,
  `product_data_nick` VARCHAR(255) DEFAULT NULL,
  `category_path` VARCHAR(255) DEFAULT NULL,
  `browsenode_id` DECIMAL(20, 0) UNSIGNED DEFAULT NULL,
  `registered_parameter` VARCHAR(25) DEFAULT NULL,
  `worldwide_id_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `worldwide_id_custom_attribute` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`template_description_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `is_new_asin_accepted` (`is_new_asin_accepted`),
  INDEX `product_data_nick` (`product_data_nick`),
  INDEX `browsenode_id` (`browsenode_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_description_definition`;
CREATE TABLE `m2epro_amazon_template_description_definition` (
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `title_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `title_template` VARCHAR(255) NOT NULL,
  `brand_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `brand_custom_value` VARCHAR(255) DEFAULT NULL,
  `brand_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `manufacturer_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `manufacturer_custom_value` VARCHAR(255) DEFAULT NULL,
  `manufacturer_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `manufacturer_part_number_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `manufacturer_part_number_custom_value` VARCHAR(255) NOT NULL,
  `manufacturer_part_number_custom_attribute` VARCHAR(255) NOT NULL,
  `item_package_quantity_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_package_quantity_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_package_quantity_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `number_of_items_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `number_of_items_custom_value` VARCHAR(255) DEFAULT NULL,
  `number_of_items_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `msrp_rrp_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `msrp_rrp_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_volume_length_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_width_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_height_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_length_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_width_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_height_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_volume_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_volume_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_weight_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  `item_dimensions_weight_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_weight_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `item_dimensions_weight_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `item_dimensions_weight_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `package_dimensions_volume_length_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_width_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_height_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_length_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_width_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_height_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `package_dimensions_volume_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_dimensions_volume_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `shipping_weight_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `shipping_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  `shipping_weight_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `shipping_weight_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 1,
  `shipping_weight_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `shipping_weight_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_weight_mode` TINYINT(2) UNSIGNED DEFAULT 0,
  `package_weight_custom_value` DECIMAL(10, 2) UNSIGNED DEFAULT NULL,
  `package_weight_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `package_weight_unit_of_measure_mode` TINYINT(2) UNSIGNED DEFAULT 1,
  `package_weight_unit_of_measure_custom_value` VARCHAR(255) DEFAULT NULL,
  `package_weight_unit_of_measure_custom_attribute` VARCHAR(255) DEFAULT NULL,
  `target_audience_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `target_audience` TEXT NOT NULL,
  `search_terms_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `search_terms` TEXT NOT NULL,
  `bullet_points_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `bullet_points` TEXT NOT NULL,
  `description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `description_template` LONGTEXT NOT NULL,
  `image_main_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_main_attribute` VARCHAR(255) NOT NULL,
  `image_variation_difference_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_variation_difference_attribute` VARCHAR(255) NOT NULL,
  `gallery_images_mode` TINYINT(2) UNSIGNED NOT NULL,
  `gallery_images_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `gallery_images_attribute` VARCHAR(255) NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_description_specific`;
CREATE TABLE `m2epro_amazon_template_description_specific` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `xpath` VARCHAR(255) NOT NULL,
  `mode` VARCHAR(25) NOT NULL,
  `is_required` TINYINT(2) UNSIGNED DEFAULT 0,
  `recommended_value` VARCHAR(255) DEFAULT NULL,
  `custom_value` VARCHAR(255) DEFAULT NULL,
  `custom_attribute` VARCHAR(255) DEFAULT NULL,
  `type` VARCHAR(25) DEFAULT NULL,
  `attributes` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_description_id` (`template_description_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_selling_format`;
CREATE TABLE `m2epro_amazon_template_selling_format` (
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `qty_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_custom_value` INT(11) UNSIGNED NOT NULL,
  `qty_custom_attribute` VARCHAR(255) NOT NULL,
  `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100,
  `qty_modification_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_min_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `qty_max_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `is_regular_customer_allowed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `is_business_customer_allowed` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `regular_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_price_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_price_coefficient` VARCHAR(255) NOT NULL,
  `regular_map_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_map_price_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_sale_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_sale_price_coefficient` VARCHAR(255) NOT NULL,
  `regular_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_start_date_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_start_date_value` DATETIME NOT NULL,
  `regular_sale_price_start_date_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_sale_price_end_date_mode` TINYINT(2) UNSIGNED NOT NULL,
  `regular_sale_price_end_date_value` DATETIME NOT NULL,
  `regular_sale_price_end_date_custom_attribute` VARCHAR(255) NOT NULL,
  `regular_price_vat_percent` FLOAT UNSIGNED DEFAULT NULL,
  `business_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `business_price_custom_attribute` VARCHAR(255) NOT NULL,
  `business_price_coefficient` VARCHAR(255) NOT NULL,
  `business_price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `business_price_vat_percent` FLOAT UNSIGNED DEFAULT NULL,
  `business_discounts_mode` TINYINT(2) UNSIGNED NOT NULL,
  `business_discounts_tier_coefficient` VARCHAR(255) NOT NULL,
  `business_discounts_tier_customer_group_id` INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_selling_format_business_discount`;
CREATE TABLE `m2epro_amazon_template_selling_format_business_discount` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
    `qty` INT(11) UNSIGNED NOT NULL,
    `mode` TINYINT(2) UNSIGNED NOT NULL,
    `attribute` VARCHAR(255) DEFAULT NULL,
    `coefficient` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`),
    INDEX `template_selling_format_id` (`template_selling_format_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_amazon_template_synchronization`;
CREATE TABLE `m2epro_amazon_template_synchronization` (
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `list_mode` TINYINT(2) UNSIGNED NOT NULL,
  `list_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `list_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `list_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `list_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `list_advanced_rules_mode` TINYINT(2) UNSIGNED NOT NULL,
  `list_advanced_rules_filters` TEXT DEFAULT NULL,
  `revise_update_qty` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_qty_max_applied_value` INT(11) UNSIGNED DEFAULT NULL,
  `revise_update_price` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_price_max_allowed_deviation` INT(11) UNSIGNED DEFAULT NULL,
  `relist_mode` TINYINT(2) UNSIGNED NOT NULL,
  `relist_filter_user_lock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_status_enabled` TINYINT(2) UNSIGNED NOT NULL,
  `relist_is_in_stock` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `relist_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `relist_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `relist_advanced_rules_mode` TINYINT(2) UNSIGNED NOT NULL,
  `relist_advanced_rules_filters` TEXT DEFAULT NULL,
  `stop_mode` TINYINT(2) UNSIGNED NOT NULL,
  `stop_status_disabled` TINYINT(2) UNSIGNED NOT NULL,
  `stop_out_off_stock` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_magento_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_magento_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated` TINYINT(2) UNSIGNED NOT NULL,
  `stop_qty_calculated_value` INT(11) UNSIGNED NOT NULL,
  `stop_qty_calculated_value_max` INT(11) UNSIGNED NOT NULL,
  `stop_advanced_rules_mode` TINYINT(2) UNSIGNED NOT NULL,
  `stop_advanced_rules_filters` TEXT DEFAULT NULL,
  PRIMARY KEY (`template_synchronization_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );

        $this->_installer->run(
            <<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`update_date`,`create_date`) VALUES
  ('/amazon/', 'application_name', 'M2ePro - Amazon Magento Integration', NOW(), NOW()),
  ('/component/amazon/', 'mode', '1', NOW(), NOW()),
  ('/listing/product/inspector/amazon/', 'max_allowed_instructions_count', '2000', NOW(), NOW()),
  ('/listing/product/revise/total/amazon/', 'mode', '0', NOW(), NOW()),
  ('/listing/product/revise/total/amazon/', 'max_allowed_instructions_count', '2000', NOW(), NOW()),
  ('/amazon/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NOW(), NOW()),
  ('/amazon/listing/product/action/scheduled_data/', 'limit', '20000', NOW(), NOW()),
  ('/amazon/listing/product/action/processing/prepare/', 'max_listings_products_count', '2000', NOW(), NOW()),
  ('/amazon/listing/product/action/list/', 'priority_coefficient', '25', NOW(), NOW()),
  ('/amazon/listing/product/action/list/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/list/', 'min_allowed_wait_interval', '3600', NOW(), NOW()),
  ('/amazon/listing/product/action/relist/', 'priority_coefficient', '125', NOW(), NOW()),
  ('/amazon/listing/product/action/relist/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/relist/', 'min_allowed_wait_interval', '1800', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_qty/', 'priority_coefficient', '500', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_qty/', 'min_allowed_wait_interval', '900', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_price/', 'priority_coefficient', '250', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_price/', 'min_allowed_wait_interval', '1800', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_details/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_details/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_details/', 'min_allowed_wait_interval', '7200', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_images/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/revise_images/', 'min_allowed_wait_interval', '7200', NOW(), NOW()),
  ('/amazon/listing/product/action/stop/', 'priority_coefficient', '1000', NOW(), NOW()),
  ('/amazon/listing/product/action/stop/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/stop/', 'min_allowed_wait_interval', '600', NOW(), NOW()),
  ('/amazon/listing/product/action/delete/', 'priority_coefficient', '1000', NOW(), NOW()),
  ('/amazon/listing/product/action/delete/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/amazon/listing/product/action/delete/', 'min_allowed_wait_interval', '600', NOW(), NOW()),
  ('/amazon/order/settings/marketplace_25/', 'use_first_street_line_as_company', '1', NOW(), NOW()),
  ('/amazon/repricing/', 'mode', '1', NOW(), NOW()),
  ('/amazon/repricing/', 'base_url', 'https://repricer.m2epro.com/connector/m2epro/', NOW(), NOW()),
  ('/amazon/business/', 'mode', '0', NOW(), NOW());

INSERT INTO `m2epro_marketplace` VALUES
  (24, 4, 'Canada', 'CA', 'amazon.ca', 0, 4, 'America', 'amazon', NOW(), NOW()),
  (25, 3, 'Germany', 'DE', 'amazon.de', 0, 3, 'Europe', 'amazon', NOW(), NOW()),
  (26, 5, 'France', 'FR', 'amazon.fr', 0, 7, 'Europe', 'amazon', NOW(), NOW()),
  (27, 6, 'Japan', 'JP', 'amazon.co.jp', 0, 6, 'Asia / Pacific', 'amazon', NOW(), NOW()),
  (28, 2, 'United Kingdom', 'UK', 'amazon.co.uk', 0, 2, 'Europe', 'amazon', NOW(), NOW()),
  (29, 1, 'United States', 'US', 'amazon.com', 0, 1, 'America', 'amazon', NOW(), NOW()),
  (30, 7, 'Spain', 'ES', 'amazon.es', 0, 8, 'Europe', 'amazon', NOW(), NOW()),
  (31, 8, 'Italy', 'IT', 'amazon.it', 0, 5, 'Europe', 'amazon', NOW(), NOW()),
  (32, 9, 'China', 'CN', 'amazon.cn', 0, 9, 'Asia / Pacific', 'amazon', NOW(), NOW()),
  (34, 9, 'Mexico', 'MX', 'amazon.com.mx', 0, 10, 'America', 'amazon', NOW(), NOW()),
  (35, 10, 'Australia', 'AU', 'amazon.com.au', 0, 11, 'Australia Region', 'amazon', NOW(), NOW()),
  (36, 0, 'India', 'IN', 'amazon.in', 0, 12, 'Asia / Pacific', 'amazon', NOW(), NOW());

INSERT INTO `m2epro_amazon_marketplace` VALUES
  (24, '8636-1433-4377', 'CAD',1,0,0,0,0,1),
  (25, '7078-7205-1944', 'EUR',1,1,1,1,1,1),
  (26, '7078-7205-1944', 'EUR',1,0,1,1,1,1),
  (27, NULL, '',0,0,0,0,0,0),
  (28, '7078-7205-1944', 'GBP',1,1,1,1,1,1),
  (29, '8636-1433-4377', 'USD',1,1,1,0,0,1),
  (30, '7078-7205-1944', 'EUR',1,0,1,1,1,1),
  (31, '7078-7205-1944', 'EUR',1,0,1,1,1,1),
  (32, NULL, '',0,0,0,0,0,0),
  (34, '8636-1433-4377', 'MXN',1,0,0,0,0,1),
  (35, '2770-5005-3793', 'AUD',1,0,0,0,0,1),
  (36, NULL, '',0,0,0,0,0,0);

SQL
        );
    }

    //########################################
}
