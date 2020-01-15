<?php

// @codingStandardsIgnoreFile

class Ess_M2ePro_Sql_Install_Ebay extends Ess_M2ePro_Model_Upgrade_Feature_AbstractFeature
{
    //########################################

    public function execute()
    {
        $this->_installer->run(
            <<<SQL

DROP TABLE IF EXISTS `m2epro_ebay_account`;
CREATE TABLE `m2epro_ebay_account` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `mode` TINYINT(2) UNSIGNED NOT NULL,
  `server_hash` VARCHAR(255) NOT NULL,
  `user_id` VARCHAR(255) NOT NULL,
  `token_session` VARCHAR(255) NOT NULL,
  `token_expired_date` DATETIME NOT NULL,
  `sell_api_token_session` VARCHAR(255) DEFAULT NULL,
  `sell_api_token_expired_date` DATETIME DEFAULT NULL,
  `marketplaces_data` TEXT DEFAULT NULL,
  `defaults_last_synchronization` datetime DEFAULT NULL,
  `other_listings_synchronization` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `other_listings_mapping_mode` TINYINT(2) NOT NULL DEFAULT 0,
  `other_listings_mapping_settings` VARCHAR(255) DEFAULT NULL,
  `other_listings_last_synchronization` DATETIME DEFAULT NULL,
  `feedbacks_receive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_auto_response` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_auto_response_only_positive` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `feedbacks_last_used_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `ebay_store_title` VARCHAR(255) NOT NULL,
  `ebay_store_url` TEXT NOT NULL,
  `ebay_store_subscription_level` VARCHAR(255) NOT NULL,
  `ebay_store_description` TEXT NOT NULL,
  `info` TEXT DEFAULT NULL,
  `user_preferences` TEXT DEFAULT NULL,
  `rate_tables` TEXT DEFAULT NULL,
  `ebay_shipping_discount_profiles` TEXT DEFAULT NULL,
  `job_token` VARCHAR(255) DEFAULT NULL,
  `orders_last_synchronization` DATETIME DEFAULT NULL,
  `magento_orders_settings` TEXT NOT NULL,
  `messages_receive` TINYINT(2) NOT NULL DEFAULT 0,
  PRIMARY KEY (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_account_store_category`;
CREATE TABLE `m2epro_ebay_account_store_category` (
  `account_id` INT(11) UNSIGNED NOT NULL,
  `category_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `parent_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `title` VARCHAR(200) NOT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `sorder` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`account_id`, `category_id`),
  INDEX `parent_id` (`parent_id`),
  INDEX `sorder` (`sorder`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_account_pickup_store`;
CREATE TABLE `m2epro_ebay_account_pickup_store` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `location_id` VARCHAR(255) DEFAULT NULL,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `phone` VARCHAR(255) NOT NULL,
  `postal_code` VARCHAR(50) NOT NULL,
  `url` VARCHAR(255) NOT NULL,
  `utc_offset` VARCHAR(50) NOT NULL ,
  `country` VARCHAR(255) NOT NULL,
  `region` VARCHAR(255) NOT NULL,
  `city` VARCHAR(255) NOT NULL,
  `address_1` VARCHAR(255) NOT NULL,
  `address_2` VARCHAR(255) NOT NULL,
  `latitude` FLOAT,
  `longitude` FLOAT,
  `business_hours` TEXT NOT NULL,
  `special_hours` TEXT NOT NULL,
  `pickup_instruction` TEXT NOT NULL,
  `qty_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_custom_value` INT(11) UNSIGNED NOT NULL,
  `qty_custom_attribute` VARCHAR(255) NOT NULL,
  `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100,
  `qty_modification_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_min_posted_value` int(11) UNSIGNED DEFAULT NULL,
  `qty_max_posted_value` int(11) UNSIGNED DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `name` (`name`),
  INDEX `location_id` (`location_id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_account_pickup_store_state`;
CREATE TABLE `m2epro_ebay_account_pickup_store_state` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_pickup_store_id` INT(11) UNSIGNED NOT NULL,
  `is_in_processing` TINYINT(2) UNSIGNED DEFAULT 0,
  `sku` VARCHAR(255) NOT NULL,
  `online_qty` INT(11) NOT NULL,
  `target_qty` INT(11) NOT NULL,
  `is_added` TINYINT(2) NOT NULL DEFAULT 0,
  `is_deleted` TINYINT(2) NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_pickup_store_id` (`account_pickup_store_id`),
  INDEX `is_in_processing` (`is_in_processing`),
  INDEX `sku` (`sku`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_account_pickup_store_log`;
CREATE TABLE `m2epro_ebay_account_pickup_store_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_pickup_store_state_id` INT(11) UNSIGNED DEFAULT NULL,
  `location_id` VARCHAR(255) NOT NULL,
  `location_title` VARCHAR(255) DEFAULT NULL,
  `action_id` INT(11) UNSIGNED NOT NULL,
  `action` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `priority` TINYINT(2) UNSIGNED NOT NULL DEFAULT 3,
  `description` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_pickup_store_state_id` (`account_pickup_store_state_id`),
  INDEX `location_id` (`location_id`),
  INDEX `location_title` (`location_title`),
  INDEX `action` (`action`),
  INDEX `action_id` (`action_id`),
  INDEX `priority` (`priority`),
  INDEX `type` (`type`),
  INDEX `create_date` (`create_date`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_category`;
CREATE TABLE `m2epro_ebay_dictionary_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_id` INT(11) UNSIGNED NOT NULL,
  `parent_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `path` VARCHAR(500) DEFAULT NULL,
  `features` LONGTEXT DEFAULT NULL,
  `item_specifics` LONGTEXT DEFAULT NULL,
  `is_leaf` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `category_id` (`category_id`),
  INDEX `is_leaf` (`is_leaf`),
  INDEX `parent_category_id` (`parent_category_id`),
  INDEX `title` (`title`),
  INDEX `path` (`path`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_marketplace`;
CREATE TABLE `m2epro_ebay_dictionary_marketplace` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `client_details_last_update_date` DATETIME DEFAULT NULL,
  `server_details_last_update_date` DATETIME DEFAULT NULL,
  `dispatch` LONGTEXT NOT NULL,
  `packages` LONGTEXT NOT NULL,
  `return_policy` LONGTEXT NOT NULL,
  `listing_features` LONGTEXT NOT NULL,
  `payments` LONGTEXT NOT NULL,
  `shipping_locations` LONGTEXT NOT NULL,
  `shipping_locations_exclude` LONGTEXT NOT NULL,
  `additional_data` LONGTEXT DEFAULT NULL,
  `tax_categories` LONGTEXT NOT NULL,
  `charities` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_shipping`;
CREATE TABLE `m2epro_ebay_dictionary_shipping` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `ebay_id` VARCHAR(255) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `category` VARCHAR(255) NOT NULL,
  `is_flat` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_calculated` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_international` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `data` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `category` (`category`),
  INDEX `ebay_id` (`ebay_id`),
  INDEX `is_calculated` (`is_calculated`),
  INDEX `is_flat` (`is_flat`),
  INDEX `is_international` (`is_international`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_feedback`;
CREATE TABLE `m2epro_ebay_feedback` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `ebay_item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `ebay_item_title` VARCHAR(255) NOT NULL,
  `ebay_transaction_id` VARCHAR(20) NOT NULL,
  `buyer_name` VARCHAR(200) NOT NULL,
  `buyer_feedback_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `buyer_feedback_text` VARCHAR(255) NOT NULL,
  `buyer_feedback_date` DATETIME NOT NULL,
  `buyer_feedback_type` VARCHAR(20) NOT NULL,
  `seller_feedback_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `seller_feedback_text` VARCHAR(255) NOT NULL,
  `seller_feedback_date` DATETIME NOT NULL,
  `seller_feedback_type` VARCHAR(20) NOT NULL,
  `last_response_attempt_date` DATETIME DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `buyer_feedback_id` (`buyer_feedback_id`),
  INDEX `ebay_item_id` (`ebay_item_id`),
  INDEX `ebay_transaction_id` (`ebay_transaction_id`),
  INDEX `seller_feedback_id` (`seller_feedback_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_feedback_template`;
CREATE TABLE `m2epro_ebay_feedback_template` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `body` TEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_item`;
CREATE TABLE `m2epro_ebay_item` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `account_id` INT(11) UNSIGNED NOT NULL,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `product_id` INT(11) UNSIGNED NOT NULL,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `variations` TEXT DEFAULT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `item_id` (`item_id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `product_id` (`product_id`),
  INDEX `store_id` (`store_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing`;
CREATE TABLE `m2epro_ebay_listing` (
  `listing_id` INT(11) UNSIGNED NOT NULL,
  `products_sold_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `items_sold_count` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `auto_global_adding_template_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `auto_global_adding_template_other_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_template_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `auto_website_adding_template_other_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_payment_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_payment_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_payment_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_shipping_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_return_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_description_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_selling_format_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `template_synchronization_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `product_add_ids` TEXT DEFAULT NULL,
  `parts_compatibility_mode` VARCHAR(10) DEFAULT NULL,
  PRIMARY KEY (`listing_id`),
  INDEX `auto_global_adding_template_category_id` (`auto_global_adding_template_category_id`),
  INDEX `auto_global_adding_template_other_category_id` (`auto_global_adding_template_other_category_id`),
  INDEX `auto_website_adding_template_category_id` (`auto_website_adding_template_category_id`),
  INDEX `auto_website_adding_template_other_category_id` (`auto_website_adding_template_other_category_id`),
  INDEX `items_sold_count` (`items_sold_count`),
  INDEX `products_sold_count` (`products_sold_count`),
  INDEX `template_description_custom_id` (`template_description_custom_id`),
  INDEX `template_description_id` (`template_description_id`),
  INDEX `template_description_mode` (`template_description_mode`),
  INDEX `template_payment_custom_id` (`template_payment_custom_id`),
  INDEX `template_payment_id` (`template_payment_id`),
  INDEX `template_payment_mode` (`template_payment_mode`),
  INDEX `template_return_custom_id` (`template_return_custom_id`),
  INDEX `template_return_id` (`template_return_id`),
  INDEX `template_return_mode` (`template_return_mode`),
  INDEX `template_selling_format_custom_id` (`template_selling_format_custom_id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_selling_format_mode` (`template_selling_format_mode`),
  INDEX `template_shipping_custom_id` (`template_shipping_custom_id`),
  INDEX `template_shipping_id` (`template_shipping_id`),
  INDEX `template_shipping_mode` (`template_shipping_mode`),
  INDEX `template_synchronization_custom_id` (`template_synchronization_custom_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`),
  INDEX `template_synchronization_mode` (`template_synchronization_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_auto_category_group`;
CREATE TABLE `m2epro_ebay_listing_auto_category_group` (
    `listing_auto_category_group_id` int(11) UNSIGNED NOT NULL,
    `adding_template_category_id` int(11) UNSIGNED DEFAULT NULL,
    `adding_template_other_category_id` int(11) UNSIGNED DEFAULT NULL,
    PRIMARY KEY (`listing_auto_category_group_id`),
    INDEX `adding_template_category_id` (`adding_template_category_id`),
    INDEX `adding_template_other_category_id` (`adding_template_other_category_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_other`;
CREATE TABLE `m2epro_ebay_listing_other` (
  `listing_other_id` INT(11) UNSIGNED NOT NULL,
  `item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `sku` VARCHAR(255) DEFAULT NULL,
  `title` VARCHAR(255) NOT NULL,
  `currency` VARCHAR(255) DEFAULT NULL,
  `online_duration` INT(11) UNSIGNED DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `online_qty` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `online_qty_sold` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `online_bids` INT(11) UNSIGNED DEFAULT NULL,
  `start_date` DATETIME NOT NULL,
  `end_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`listing_other_id`),
  INDEX `currency` (`currency`),
  INDEX `end_date` (`end_date`),
  INDEX `item_id` (`item_id`),
  INDEX `online_bids` (`online_bids`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_qty_sold` (`online_qty_sold`),
  INDEX `sku` (`sku`),
  INDEX `start_date` (`start_date`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product`;
CREATE TABLE `m2epro_ebay_listing_product` (
  `listing_product_id` INT(11) UNSIGNED NOT NULL,
  `template_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_other_category_id` INT(11) UNSIGNED DEFAULT NULL,
  `ebay_item_id` INT(11) UNSIGNED DEFAULT NULL,
  `item_uuid` VARCHAR(32) DEFAULT NULL,
  `is_duplicate` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_is_variation` TINYINT(2) UNSIGNED DEFAULT NULL,
  `online_is_auction_type` TINYINT(2) UNSIGNED DEFAULT NULL,
  `online_sku` VARCHAR(255) DEFAULT NULL,
  `online_title` VARCHAR(255) DEFAULT NULL,
  `online_sub_title` VARCHAR(255) DEFAULT NULL,
  `online_description` LONGTEXT DEFAULT NULL,
  `online_images` LONGTEXT DEFAULT NULL,
  `online_duration` INT(11) UNSIGNED DEFAULT NULL,
  `online_current_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_start_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_reserve_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_buyitnow_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_qty_sold` INT(11) UNSIGNED DEFAULT NULL,
  `online_bids` INT(11) UNSIGNED DEFAULT NULL,
  `online_main_category` VARCHAR(255) DEFAULT NULL,
  `online_categories_data` LONGTEXT DEFAULT NULL,
  `online_shipping_data` LONGTEXT DEFAULT NULL,
  `online_payment_data` LONGTEXT DEFAULT NULL,
  `online_return_data` LONGTEXT DEFAULT NULL,
  `online_other_data` LONGTEXT DEFAULT NULL,
  `start_date` DATETIME DEFAULT NULL,
  `end_date` DATETIME DEFAULT NULL,
  `template_payment_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_payment_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_payment_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_shipping_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_shipping_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_return_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_return_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_description_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_description_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_selling_format_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_selling_format_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `template_synchronization_id` INT(11) UNSIGNED DEFAULT NULL,
  `template_synchronization_custom_id` INT(11) UNSIGNED DEFAULT NULL,
  PRIMARY KEY (`listing_product_id`),
  INDEX `ebay_item_id` (`ebay_item_id`),
  INDEX `item_uuid` (`item_uuid`),
  INDEX `is_duplicate` (`is_duplicate`),
  INDEX `online_is_variation` (`online_is_variation`),
  INDEX `online_is_auction_type` (`online_is_auction_type`),
  INDEX `end_date` (`end_date`),
  INDEX `online_bids` (`online_bids`),
  INDEX `online_buyitnow_price` (`online_buyitnow_price`),
  INDEX `online_main_category` (`online_main_category`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_qty_sold` (`online_qty_sold`),
  INDEX `online_reserve_price` (`online_reserve_price`),
  INDEX `online_sku` (`online_sku`),
  INDEX `online_current_price` (`online_current_price`),
  INDEX `online_start_price` (`online_start_price`),
  INDEX `online_title` (`online_title`),
  INDEX `start_date` (`start_date`),
  INDEX `template_category_id` (`template_category_id`),
  INDEX `template_description_custom_id` (`template_description_custom_id`),
  INDEX `template_description_id` (`template_description_id`),
  INDEX `template_description_mode` (`template_description_mode`),
  INDEX `template_other_category_id` (`template_other_category_id`),
  INDEX `template_payment_custom_id` (`template_payment_custom_id`),
  INDEX `template_payment_id` (`template_payment_id`),
  INDEX `template_payment_mode` (`template_payment_mode`),
  INDEX `template_return_custom_id` (`template_return_custom_id`),
  INDEX `template_return_id` (`template_return_id`),
  INDEX `template_return_mode` (`template_return_mode`),
  INDEX `template_selling_format_custom_id` (`template_selling_format_custom_id`),
  INDEX `template_selling_format_id` (`template_selling_format_id`),
  INDEX `template_selling_format_mode` (`template_selling_format_mode`),
  INDEX `template_shipping_custom_id` (`template_shipping_custom_id`),
  INDEX `template_shipping_id` (`template_shipping_id`),
  INDEX `template_shipping_mode` (`template_shipping_mode`),
  INDEX `template_synchronization_custom_id` (`template_synchronization_custom_id`),
  INDEX `template_synchronization_id` (`template_synchronization_id`),
  INDEX `template_synchronization_mode` (`template_synchronization_mode`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_pickup_store`;
CREATE TABLE `m2epro_ebay_listing_product_pickup_store` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `listing_product_id` INT(11) UNSIGNED,
  `account_pickup_store_id` INT(11) UNSIGNED,
  `is_process_required` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `account_pickup_store_id` (`account_pickup_store_id`),
  INDEX `is_process_required` (`is_process_required`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_variation`;
CREATE TABLE `m2epro_ebay_listing_product_variation` (
  `listing_product_variation_id` INT(11) UNSIGNED NOT NULL,
  `add` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `delete` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `online_sku` VARCHAR(255) DEFAULT NULL,
  `online_price` DECIMAL(12, 4) UNSIGNED DEFAULT NULL,
  `online_qty` INT(11) UNSIGNED DEFAULT NULL,
  `online_qty_sold` INT(11) UNSIGNED DEFAULT NULL,
  `status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`listing_product_variation_id`),
  INDEX `add` (`add`),
  INDEX `delete` (`delete`),
  INDEX `online_sku` (`online_sku`),
  INDEX `online_price` (`online_price`),
  INDEX `online_qty` (`online_qty`),
  INDEX `online_qty_sold` (`online_qty_sold`),
  INDEX `status` (`status`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_variation_option`;
CREATE TABLE `m2epro_ebay_listing_product_variation_option` (
  `listing_product_variation_option_id` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`listing_product_variation_option_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_listing_product_action_processing`;
CREATE TABLE `m2epro_ebay_listing_product_action_processing` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `processing_id` INT(11) UNSIGNED NOT NULL,
  `listing_product_id` INT(11) UNSIGNED DEFAULT NULL,
  `type` VARCHAR(12) NOT NULL,
  `request_timeout` INT(11) UNSIGNED DEFAULT NULL,
  `request_data` LONGTEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `listing_product_id` (`listing_product_id`),
  INDEX `processing_id` (`processing_id`),
  INDEX `type` (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_indexer_listing_product_parent`;
CREATE TABLE `m2epro_ebay_indexer_listing_product_parent` (
    `listing_product_id` INT(11) UNSIGNED NOT NULL,
    `listing_id` INT(11) UNSIGNED NOT NULL,
    `min_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `max_price` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
    `create_date` DATETIME NOT NULL,
    PRIMARY KEY (`listing_product_id`),
    INDEX `listing_id` (`listing_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_marketplace`;
CREATE TABLE `m2epro_ebay_marketplace` (
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `currency` VARCHAR(70) NOT NULL DEFAULT 'USD',
  `origin_country` VARCHAR(255) DEFAULT NULL,
  `language_code` VARCHAR(255) DEFAULT NULL,
  `is_multivariation` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_freight_shipping` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_calculated_shipping` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_tax_table` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_vat` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_stp` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_stp_advanced` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_map` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_local_shipping_rate_table` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_international_shipping_rate_table` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_english_measurement_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_metric_measurement_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_cash_on_delivery` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_global_shipping_program` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_charity` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_click_and_collect` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_in_store_pickup` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_return_description` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_epid` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `is_ktype` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`marketplace_id`),
  INDEX `is_calculated_shipping` (`is_calculated_shipping`),
  INDEX `is_cash_on_delivery` (`is_cash_on_delivery`),
  INDEX `is_charity` (`is_charity`),
  INDEX `is_english_measurement_system` (`is_english_measurement_system`),
  INDEX `is_freight_shipping` (`is_freight_shipping`),
  INDEX `is_international_shipping_rate_table` (`is_international_shipping_rate_table`),
  INDEX `is_local_shipping_rate_table` (`is_local_shipping_rate_table`),
  INDEX `is_metric_measurement_system` (`is_metric_measurement_system`),
  INDEX `is_tax_table` (`is_tax_table`),
  INDEX `is_vat` (`is_vat`),
  INDEX `is_stp` (`is_stp`),
  INDEX `is_stp_advanced` (`is_stp_advanced`),
  INDEX `is_map` (`is_map`),
  INDEX `is_click_and_collect` (`is_click_and_collect`),
  INDEX `is_in_store_pickup` (`is_in_store_pickup`),
  INDEX `is_return_description` (`is_return_description`),
  INDEX `is_epid` (`is_epid`),
  INDEX `is_ktype` (`is_ktype`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_motor_epid`;
CREATE TABLE `m2epro_ebay_dictionary_motor_epid` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `epid` VARCHAR(255) NOT NULL,
  `product_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `make` VARCHAR(255) NOT NULL,
  `model` VARCHAR(255) NOT NULL,
  `year` SMALLINT(4) UNSIGNED NOT NULL,
  `trim` VARCHAR(255) DEFAULT NULL,
  `engine` VARCHAR(255) DEFAULT NULL,
  `submodel` VARCHAR(255) DEFAULT NULL,
  `street_name` VARCHAR(255) DEFAULT NULL,
  `is_custom` TINYINT(2) UNSIGNED NOT NULL,
  `scope` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `epid` (`epid`),
  INDEX `engine` (`engine`),
  INDEX `make` (`make`),
  INDEX `model` (`model`),
  INDEX `product_type` (`product_type`),
  INDEX `submodel` (`submodel`),
  INDEX `trim` (`trim`),
  INDEX `year` (`year`),
  INDEX `is_custom` (`is_custom`),
  INDEX `scope` (`scope`),
  INDEX `street_name` (`street_name`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_dictionary_motor_ktype`;
CREATE TABLE `m2epro_ebay_dictionary_motor_ktype` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `ktype` int(11) UNSIGNED NOT NULL,
  `make` varchar(255) DEFAULT NULL,
  `model` varchar(255) DEFAULT NULL,
  `variant` varchar(255) DEFAULT NULL,
  `body_style` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `from_year` int(11) DEFAULT NULL,
  `to_year` int(11) DEFAULT NULL,
  `engine` varchar(255) DEFAULT NULL,
  `is_custom` TINYINT(2) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `body_style` (`body_style`),
  INDEX `engine` (`engine`),
  INDEX `from_year` (`from_year`),
  INDEX `ktype` (`ktype`),
  INDEX `make` (`make`),
  INDEX `model` (`model`),
  INDEX `to_year` (`to_year`),
  INDEX `type` (`type`),
  INDEX `variant` (`variant`),
  INDEX `is_custom` (`is_custom`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_motor_filter`;
CREATE TABLE `m2epro_ebay_motor_filter` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `type` TINYINT(2) UNSIGNED NOT NULL,
    `conditions` TEXT NOT NULL,
    `note` TEXT DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX type (`type`)
)
ENGINE = MYISAM
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_motor_group`;
CREATE TABLE `m2epro_ebay_motor_group` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `title` varchar(255) NOT NULL,
    `mode` TINYINT(2) UNSIGNED NOT NULL,
    `type` TINYINT(2) UNSIGNED NOT NULL,
    `items_data` TEXT DEFAULT NULL,
    `update_date` datetime DEFAULT NULL,
    `create_date` datetime DEFAULT NULL,
    PRIMARY KEY (id),
    INDEX mode (`mode`),
    INDEX type (`type`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_motor_filter_to_group`;
CREATE TABLE `m2epro_ebay_motor_filter_to_group` (
    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    `filter_id` INT(11) UNSIGNED NOT NULL,
    `group_id` INT(11) UNSIGNED NOT NULL,
    PRIMARY KEY (id),
    INDEX filter_id (`filter_id`),
    INDEX group_id (`group_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_order`;
CREATE TABLE `m2epro_ebay_order` (
  `order_id` INT(11) UNSIGNED NOT NULL,
  `ebay_order_id` VARCHAR(255) NOT NULL,
  `selling_manager_id` INT(11) UNSIGNED DEFAULT NULL,
  `buyer_name` VARCHAR(255) NOT NULL,
  `buyer_email` VARCHAR(255) NOT NULL,
  `buyer_user_id` VARCHAR(255) NOT NULL,
  `buyer_message` VARCHAR(500) DEFAULT NULL,
  `buyer_tax_id` VARCHAR(64) DEFAULT NULL,
  `paid_amount` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `saved_amount` DECIMAL(12, 4) UNSIGNED NOT NULL DEFAULT 0.0000,
  `currency` VARCHAR(10) NOT NULL,
  `checkout_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `payment_status` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_details` TEXT DEFAULT NULL,
  `payment_details` TEXT DEFAULT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `purchase_update_date` DATETIME DEFAULT NULL,
  `purchase_create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`order_id`),
  INDEX `ebay_order_id` (`ebay_order_id`),
  INDEX `selling_manager_id` (`selling_manager_id`),
  INDEX `buyer_email` (`buyer_email`),
  INDEX `buyer_name` (`buyer_name`),
  INDEX `buyer_user_id` (`buyer_user_id`),
  INDEX `paid_amount` (`paid_amount`),
  INDEX `checkout_status` (`checkout_status`),
  INDEX `payment_status` (`payment_status`),
  INDEX `shipping_status` (`shipping_status`),
  INDEX `purchase_create_date` (`purchase_create_date`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_order_external_transaction`;
CREATE TABLE `m2epro_ebay_order_external_transaction` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) UNSIGNED NOT NULL,
  `transaction_id` VARCHAR(255) NOT NULL,
  `fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `sum` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `is_refund` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `transaction_date` DATETIME NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `order_id` (`order_id`),
  INDEX `transaction_id` (`transaction_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_order_item`;
CREATE TABLE `m2epro_ebay_order_item` (
  `order_item_id` INT(11) UNSIGNED NOT NULL,
  `transaction_id` VARCHAR(20) NOT NULL,
  `selling_manager_id` INT(11) UNSIGNED DEFAULT NULL,
  `item_id` DECIMAL(20, 0) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `sku` VARCHAR(64) DEFAULT NULL,
  `price` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `qty_purchased` INT(11) UNSIGNED NOT NULL,
  `tax_details` TEXT DEFAULT NULL,
  `final_fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `waste_recycling_fee` DECIMAL(12, 4) NOT NULL DEFAULT 0.0000,
  `variation_details` TEXT DEFAULT NULL,
  `tracking_details` TEXT DEFAULT NULL,
  `unpaid_item_process_state` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`order_item_id`),
  INDEX `transaction_id` (`transaction_id`),
  INDEX `selling_manager_id` (`selling_manager_id`),
  INDEX `item_id` (`item_id`),
  INDEX `sku` (`sku`),
  INDEX `title` (`title`),
  INDEX `unpaid_item_process_state` (`unpaid_item_process_state`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_category`;
CREATE TABLE `m2epro_ebay_template_category` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `category_main_id` INT(11) UNSIGNED NOT NULL,
  `category_main_path` VARCHAR(255) DEFAULT NULL,
  `category_main_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 2,
  `category_main_attribute` VARCHAR(255) NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_category_specific`;
CREATE TABLE `m2epro_ebay_template_category_specific` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_category_id` INT(11) UNSIGNED NOT NULL,
  `mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `attribute_title` VARCHAR(255) NOT NULL,
  `value_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `value_ebay_recommended` LONGTEXT DEFAULT NULL,
  `value_custom_value` VARCHAR(255) DEFAULT NULL,
  `value_custom_attribute` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_category_id` (`template_category_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_description`;
CREATE TABLE `m2epro_ebay_template_description` (
  `template_description_id` INT(11) UNSIGNED NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `title_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `title_template` VARCHAR(255) NOT NULL,
  `subtitle_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `subtitle_template` VARCHAR(255) NOT NULL,
  `description_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `description_template` LONGTEXT NOT NULL,
  `condition_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_value` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `condition_attribute` VARCHAR(255) NOT NULL,
  `condition_note_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `condition_note_template` TEXT NOT NULL,
  `product_details` TEXT DEFAULT NULL,
  `cut_long_titles` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `hit_counter` VARCHAR(255) NOT NULL,
  `editor_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `enhancement` VARCHAR(255) NOT NULL,
  `gallery_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 4,
  `image_main_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `image_main_attribute` VARCHAR(255) NOT NULL,
  `gallery_images_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `gallery_images_limit` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `gallery_images_attribute` VARCHAR(255) NOT NULL,
  `variation_images_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `variation_images_limit` tinyint(2) UNSIGNED NOT NULL DEFAULT 1,
  `variation_images_attribute` varchar(255) NOT NULL,
  `default_image_url` VARCHAR(255) DEFAULT NULL,
  `variation_configurable_images` TEXT DEFAULT NULL,
  `use_supersize_images` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `watermark_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `watermark_image` LONGBLOB DEFAULT NULL,
  `watermark_settings` TEXT DEFAULT NULL,
  PRIMARY KEY (`template_description_id`),
  INDEX `is_custom_template` (`is_custom_template`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_other_category`;
CREATE TABLE `m2epro_ebay_template_other_category` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` int(11) UNSIGNED NOT NULL,
  `account_id` int(11) UNSIGNED NOT NULL,
  `category_secondary_id` int(11) UNSIGNED NOT NULL,
  `category_secondary_path` varchar(255) DEFAULT NULL,
  `category_secondary_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 2,
  `category_secondary_attribute` varchar(255) NOT NULL,
  `store_category_main_id` decimal(20, 0) UNSIGNED NOT NULL,
  `store_category_main_path` varchar(255) DEFAULT NULL,
  `store_category_main_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `store_category_main_attribute` varchar(255) NOT NULL,
  `store_category_secondary_id` decimal(20, 0) UNSIGNED NOT NULL,
  `store_category_secondary_path` varchar(255) DEFAULT NULL,
  `store_category_secondary_mode` tinyint(2) UNSIGNED NOT NULL DEFAULT 0,
  `store_category_secondary_attribute` varchar(255) NOT NULL,
  `update_date` datetime DEFAULT NULL,
  `create_date` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `account_id` (`account_id`),
  INDEX `marketplace_id` (`marketplace_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_payment`;
CREATE TABLE `m2epro_ebay_template_payment` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `pay_pal_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `pay_pal_email_address` VARCHAR(255) NOT NULL,
  `pay_pal_immediate_payment` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `is_custom_template` (`is_custom_template`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_payment_service`;
CREATE TABLE `m2epro_ebay_template_payment_service` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_payment_id` INT(11) UNSIGNED NOT NULL,
  `code_name` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `template_payment_id` (`template_payment_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_return`;
CREATE TABLE `m2epro_ebay_template_return` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `accepted` VARCHAR(255) NOT NULL,
  `option` VARCHAR(255) NOT NULL,
  `within` VARCHAR(255) NOT NULL,
  `shipping_cost` VARCHAR(255) NOT NULL,
  `international_accepted` VARCHAR(255) NOT NULL,
  `international_option` VARCHAR(255) NOT NULL,
  `international_within` VARCHAR(255) NOT NULL,
  `international_shipping_cost` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `is_custom_template` (`is_custom_template`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_selling_format`;
CREATE TABLE `m2epro_ebay_template_selling_format` (
  `template_selling_format_id` INT(11) UNSIGNED NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `listing_type` TINYINT(2) UNSIGNED NOT NULL,
  `listing_type_attribute` VARCHAR(255) NOT NULL,
  `listing_is_private` TINYINT(2) UNSIGNED NOT NULL,
  `restricted_to_business` TINYINT(2) UNSIGNED DEFAULT 0,
  `duration_mode` TINYINT(4) UNSIGNED NOT NULL,
  `duration_attribute` VARCHAR(255) NOT NULL,
  `out_of_stock_control` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `qty_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_custom_value` INT(11) UNSIGNED NOT NULL,
  `qty_custom_attribute` VARCHAR(255) NOT NULL,
  `qty_percentage` INT(11) UNSIGNED NOT NULL DEFAULT 100,
  `qty_modification_mode` TINYINT(2) UNSIGNED NOT NULL,
  `qty_min_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `qty_max_posted_value` INT(11) UNSIGNED DEFAULT NULL,
  `lot_size_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `lot_size_custom_value` INT(11) UNSIGNED DEFAULT NULL,
  `lot_size_attribute` VARCHAR(255) DEFAULT NULL,
  `vat_percent` FLOAT UNSIGNED NOT NULL DEFAULT 0,
  `tax_table_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `tax_category_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `tax_category_value` VARCHAR(255) NOT NULL,
  `tax_category_attribute` VARCHAR(255) NOT NULL,
  `price_increase_vat_percent` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `price_variation_mode` TINYINT(2) UNSIGNED NOT NULL,
  `fixed_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `fixed_price_coefficient` VARCHAR(255) NOT NULL,
  `fixed_price_custom_attribute` VARCHAR(255) NOT NULL,
  `start_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `start_price_coefficient` VARCHAR(255) NOT NULL,
  `start_price_custom_attribute` VARCHAR(255) NOT NULL,
  `reserve_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `reserve_price_coefficient` VARCHAR(255) NOT NULL,
  `reserve_price_custom_attribute` VARCHAR(255) NOT NULL,
  `buyitnow_price_mode` TINYINT(2) UNSIGNED NOT NULL,
  `buyitnow_price_coefficient` VARCHAR(255) NOT NULL,
  `buyitnow_price_custom_attribute` VARCHAR(255) NOT NULL,
  `price_discount_stp_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_discount_stp_attribute` VARCHAR(255) NOT NULL,
  `price_discount_stp_type` TINYINT(2) UNSIGNED NOT NULL,
  `price_discount_map_mode` TINYINT(2) UNSIGNED NOT NULL,
  `price_discount_map_attribute` VARCHAR(255) NOT NULL,
  `price_discount_map_exposure_type` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_mode` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_accept_mode` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_accept_value` VARCHAR(255) NOT NULL,
  `best_offer_accept_attribute` VARCHAR(255) NOT NULL,
  `best_offer_reject_mode` TINYINT(2) UNSIGNED NOT NULL,
  `best_offer_reject_value` VARCHAR(255) NOT NULL,
  `best_offer_reject_attribute` VARCHAR(255) NOT NULL,
  `charity` VARCHAR(255) DEFAULT NULL,
  `ignore_variations` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`template_selling_format_id`),
  INDEX `is_custom_template` (`is_custom_template`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_shipping`;
CREATE TABLE `m2epro_ebay_template_shipping` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `marketplace_id` INT(11) UNSIGNED NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `country_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `country_custom_value` VARCHAR(255) NOT NULL,
  `country_custom_attribute` VARCHAR(255) NOT NULL,
  `postal_code_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `postal_code_custom_value` VARCHAR(255) NOT NULL,
  `postal_code_custom_attribute` VARCHAR(255) NOT NULL,
  `address_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `address_custom_value` VARCHAR(255) NOT NULL,
  `address_custom_attribute` VARCHAR(255) NOT NULL,
  `dispatch_time_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `dispatch_time_value` INT(11) UNSIGNED NOT NULL DEFAULT 1,
  `dispatch_time_attribute` VARCHAR(255) DEFAULT NULL,
  `local_shipping_rate_table` TEXT DEFAULT NULL,
  `international_shipping_rate_table` TEXT DEFAULT NULL,
  `local_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `local_shipping_discount_promotional_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `local_shipping_discount_combined_profile_id` TEXT DEFAULT NULL,
  `click_and_collect_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `cash_on_delivery_cost` VARCHAR(255) DEFAULT NULL,
  `international_shipping_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `international_shipping_discount_promotional_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `international_shipping_discount_combined_profile_id` TEXT DEFAULT NULL,
  `excluded_locations` TEXT DEFAULT NULL,
  `cross_border_trade` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `global_shipping_program` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `update_date` DATETIME DEFAULT NULL,
  `create_date` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `is_custom_template` (`is_custom_template`),
  INDEX `marketplace_id` (`marketplace_id`),
  INDEX `title` (`title`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_shipping_calculated`;
CREATE TABLE `m2epro_ebay_template_shipping_calculated` (
  `template_shipping_id` INT(11) UNSIGNED NOT NULL,
  `measurement_system` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `package_size_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
  `package_size_value` VARCHAR(500) NOT NULL,
  `package_size_attribute` VARCHAR(255) NOT NULL,
  `dimension_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `dimension_width_value` VARCHAR(500) NOT NULL,
  `dimension_width_attribute` VARCHAR(255) NOT NULL,
  `dimension_length_value` VARCHAR(500) NOT NULL,
  `dimension_length_attribute` VARCHAR(255) NOT NULL,
  `dimension_depth_value` VARCHAR(500) NOT NULL,
  `dimension_depth_attribute` VARCHAR(255) NOT NULL,
  `weight_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `weight_minor` VARCHAR(500) NOT NULL,
  `weight_major` VARCHAR(500) NOT NULL,
  `weight_attribute` VARCHAR(255) NOT NULL,
  `local_handling_cost` VARCHAR(255) DEFAULT NULL,
  `international_handling_cost` VARCHAR(255) DEFAULT NULL,
  PRIMARY KEY (`template_shipping_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_shipping_service`;
CREATE TABLE `m2epro_ebay_template_shipping_service` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_shipping_id` INT(11) UNSIGNED NOT NULL,
  `shipping_type` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `shipping_value` VARCHAR(255) NOT NULL,
  `cost_mode` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  `cost_value` VARCHAR(255) NOT NULL,
  `cost_additional_value` VARCHAR(255) NOT NULL,
  `cost_surcharge_value` VARCHAR(255) NOT NULL,
  `locations` TEXT NOT NULL,
  `priority` TINYINT(2) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `priority` (`priority`),
  INDEX `template_shipping_id` (`template_shipping_id`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

DROP TABLE IF EXISTS `m2epro_ebay_template_synchronization`;
CREATE TABLE `m2epro_ebay_template_synchronization` (
  `template_synchronization_id` INT(11) UNSIGNED NOT NULL,
  `is_custom_template` TINYINT(2) UNSIGNED NOT NULL DEFAULT 1,
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
  `revise_update_title` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_sub_title` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_description` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_images` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_categories` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_shipping` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_payment` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_return` TINYINT(2) UNSIGNED NOT NULL,
  `revise_update_other` TINYINT(2) UNSIGNED NOT NULL,
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
  PRIMARY KEY (`template_synchronization_id`),
  INDEX `is_custom_template` (`is_custom_template`)
)
ENGINE = INNODB
CHARACTER SET utf8
COLLATE utf8_general_ci;

SQL
        );

        $this->_installer->run(
            <<<SQL

INSERT INTO `m2epro_config` (`group`,`key`,`value`,`update_date`,`create_date`) VALUES
  ('/component/ebay/', 'mode', '1', NOW(), NOW()),
  ('/listing/product/inspector/ebay/', 'max_allowed_instructions_count', '2000', NOW(), NOW()),
  ('/listing/product/revise/total/ebay/', 'mode', '0', NOW(), NOW()),
  ('/listing/product/revise/total/ebay/', 'max_allowed_instructions_count', '2000', NOW(), NOW()),
  ('/ebay/listing/product/instructions/cron/', 'listings_products_per_one_time', '1000', NOW(), NOW()),
  ('/ebay/listing/product/action/list/', 'priority_coefficient', '25', NOW(), NOW()),
  ('/ebay/listing/product/action/list/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/relist/', 'priority_coefficient', '125', NOW(), NOW()),
  ('/ebay/listing/product/action/relist/', 'wait_increase_coefficient',  '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_qty/', 'priority_coefficient', '500', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_qty/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_price/', 'priority_coefficient', '250', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_price/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_title/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_title/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_subtitle/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_subtitle/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_description/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_description/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_images/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_images/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_categories/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_categories/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_payment/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_payment/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_shipping/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_shipping/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_return/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_return/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_other/', 'priority_coefficient', '50', NOW(), NOW()),
  ('/ebay/listing/product/action/revise_other/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/action/stop/', 'priority_coefficient', '1000', NOW(), NOW()),
  ('/ebay/listing/product/action/stop/', 'wait_increase_coefficient', '100', NOW(), NOW()),
  ('/ebay/listing/product/scheduled_actions/', 'max_prepared_actions_count', '3000', NOW(), NOW()),
  ('/ebay/order/settings/marketplace_8/', 'use_first_street_line_as_company', '1', NOW(), NOW()),
  ('/ebay/connector/listing/', 'check_the_same_product_already_listed', '1', NOW(), NOW()),
  ('/component/ebay/variation/', 'mpn_can_be_changed', '0', NOW(), NOW()),
  ('/view/ebay/template/category/', 'use_last_specifics', '0', NOW(), NOW()),
  ('/ebay/motors/', 'epids_motor_attribute', NULL, NOW(), NOW()),
  ('/ebay/motors/', 'epids_uk_attribute', NULL, NOW(), NOW()),
  ('/ebay/motors/', 'epids_de_attribute', NULL, NOW(), NOW()),
  ('/ebay/motors/', 'epids_au_attribute', NULL, NOW(), NOW()),
  ('/ebay/motors/', 'ktypes_attribute', NULL, NOW(), NOW()),
  ('/ebay/sell_on_another_marketplace/', 'tutorial_shown', '0', NOW(), NOW()),
  ('/ebay/description/', 'upload_images_mode', 2, NOW(),NOW()),
  ('/ebay/description/', 'should_be_ulrs_secure', 0, NOW(),NOW());

INSERT INTO `m2epro_marketplace` VALUES
  (1, 0, 'United States', 'US', 'ebay.com', 0, 1, 'America', 'ebay', NOW(), NOW()),
  (2, 2, 'Canada', 'Canada', 'ebay.ca', 0, 8, 'America', 'ebay', NOW(), NOW()),
  (3, 3, 'United Kingdom', 'UK', 'ebay.co.uk', 0, 2, 'Europe', 'ebay', NOW(), NOW()),
  (4, 15, 'Australia', 'Australia', 'ebay.com.au', 0, 4, 'Australia Region', 'ebay', NOW(), NOW()),
  (5, 16, 'Austria', 'Austria', 'ebay.at', 0, 5, 'Europe', 'ebay', NOW(), NOW()),
  (6, 23, 'Belgium (French)', 'Belgium_French', 'befr.ebay.be', 0, 7, 'Europe', 'ebay', NOW(), NOW()),
  (7, 71, 'France', 'France', 'ebay.fr', 0, 10, 'Europe', 'ebay', NOW(), NOW()),
  (8, 77, 'Germany', 'Germany', 'ebay.de', 0, 3, 'Europe', 'ebay', NOW(), NOW()),
  (9, 100, 'eBay Motors', 'eBayMotors', 'ebay.com/motors', 0, 23, 'Other', 'ebay', NOW(), NOW()),
  (10, 101, 'Italy', 'Italy', 'ebay.it', 0, 14, 'Europe', 'ebay', NOW(), NOW()),
  (11, 123, 'Belgium (Dutch)', 'Belgium_Dutch', 'benl.ebay.be', 0, 6, 'Europe', 'ebay', NOW(), NOW()),
  (12, 146, 'Netherlands', 'Netherlands', 'ebay.nl', 0, 16, 'Europe', 'ebay', NOW(), NOW()),
  (13, 186, 'Spain', 'Spain', 'ebay.es', 0, 19, 'Europe', 'ebay', NOW(), NOW()),
  (14, 193, 'Switzerland', 'Switzerland', 'ebay.ch', 0, 22, 'Europe', 'ebay', NOW(), NOW()),
  (15, 201, 'Hong Kong', 'HongKong', 'ebay.com.hk', 0, 11, 'Asia / Pacific', 'ebay', NOW(), NOW()),
  (16, 203, 'India', 'India', 'ebay.in', 0, 12, 'Asia / Pacific', 'ebay', NOW(), NOW()),
  (17, 205, 'Ireland', 'Ireland', 'ebay.ie', 0, 13, 'Europe', 'ebay', NOW(), NOW()),
  (18, 207, 'Malaysia', 'Malaysia', 'ebay.com.my', 0, 15, 'Asia / Pacific', 'ebay', NOW(), NOW()),
  (19, 210, 'Canada (French)', 'CanadaFrench', 'cafr.ebay.ca', 0, 9, 'America', 'ebay', NOW(), NOW()),
  (20, 211, 'Philippines', 'Philippines', 'ebay.ph', 0, 17, 'Asia / Pacific', 'ebay', NOW(), NOW()),
  (21, 212, 'Poland', 'Poland', 'ebay.pl', 0, 18, 'Europe', 'ebay', NOW(), NOW()),
  (22, 216, 'Singapore', 'Singapore', 'ebay.com.sg', 0, 20, 'Asia / Pacific', 'ebay', NOW(), NOW());

INSERT INTO `m2epro_ebay_marketplace` VALUES
    (1, 'USD', 'us', 'en_US', 1, 1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 0, 1, 1, 0, 1, 0, 0, 0),
    (2, 'CAD', 'ca', 'en_CA', 1, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (3, 'GBP', 'gb', 'en_GB', 1, 1, 0, 0, 1, 1, 1, 0, 1, 1, 0, 1, 0, 1, 1, 1, 1, 0, 1, 1),
    (4, 'AUD', 'au', 'en_AU', 1, 1, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0, 1, 1, 1, 0, 1, 1),
    (5, 'EUR', 'at', 'de_AT', 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 0),
    (6, 'EUR', 'be', 'nl_BE', 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (7, 'EUR', 'fr', 'fr_FR', 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 1),
    (8, 'EUR', 'de', 'de_DE', 1, 0, 0, 0, 1, 1, 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 0, 1, 1, 1),
    (9, 'USD', 'us', 'en_US', 1, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1, 0, 0, 1, 1, 0, 0, 0, 1, 0),
    (10, 'EUR', 'it', 'it_IT', 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 1, 0, 1, 0, 0, 1, 0, 1),
    (11, 'EUR', 'be', 'fr_BE', 0, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (12, 'EUR', 'nl', 'nl_NL', 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (13, 'EUR', 'es', 'es_ES', 1, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 1, 0, 1),
    (14, 'CHF', 'ch', 'fr_CH', 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (15, 'HKD', 'hk', 'zh_HK', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (16, 'INR', 'in', 'hi_IN', 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (17, 'EUR', 'ie', 'en_IE', 1, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (18, 'MYR', 'my', 'ms_MY', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (19, 'CAD', 'ca', 'fr_CA', 0, 1, 1, 1, 0, 1, 0, 0, 0, 0, 1, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (20, 'PHP', 'ph', 'fil_PH', 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (21, 'PLN', 'pl', 'pl_PL', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0),
    (22, 'SGD', 'sg', 'zh_SG', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 0, 0, 1, 0, 0, 0, 0, 0);

SQL
        );
    }

    //########################################
}
