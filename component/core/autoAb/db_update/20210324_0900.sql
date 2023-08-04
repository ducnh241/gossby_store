ALTER TABLE osc_product_type_variant ADD COLUMN base_cost_configs varchar(1000) DEFAULT NULL AFTER compare_at_price;
ALTER TABLE osc_product_type_variant_location_price ADD COLUMN base_cost_configs varchar(1000) DEFAULT NULL AFTER compare_at_price;

DROP TABLE IF EXISTS osc_auto_ab_product_price_group;

DROP TABLE IF EXISTS osc_auto_ab_product_price_condition;
CREATE TABLE `osc_auto_ab_product_price` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `country_code` VARCHAR(25) NOT NULL DEFAULT '',
    `product_type_variant_id` INT(11) unsigned NOT NULL DEFAULT 0,
    `config_id` INT(11) unsigned NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `item_INDEX` (`country_code`,`product_type_variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS osc_auto_ab_product_price_condition_config;
CREATE TABLE `osc_auto_ab_product_price_config` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `title` VARCHAR(255) NOT NULL DEFAULT '',
    `location_data` VARCHAR(2000) NOT NULL DEFAULT '',
    `variant_data` VARCHAR(2000) NOT NULL DEFAULT '',
    `fee` SMALLINT(3) NOT NULL DEFAULT 0,
    `condition_type` TINYINT(1) NULL DEFAULT 0,
    `begin_at` INT(10) NOT NULL DEFAULT 0,
    `finish_at` INT(10) NOT NULL DEFAULT 0,
    `price_range` VARCHAR(255) NOT NULL DEFAULT '',
    `added_timestamp` INT(10) NOT NULL DEFAULT 0,
    `modified_timestamp` INT(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS osc_auto_ab_product_price_tracking;
CREATE TABLE `osc_auto_ab_product_price_tracking` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `config_id` INT(11) NOT NULL DEFAULT 0,
    `product_type_variant_id` INT(11) NOT NULL DEFAULT 0,
    `product_variant_id` INT(11) NOT NULL DEFAULT 0,
    `product_id` INT(11) NOT NULL DEFAULT 0,
    `order_item_id` INT(11) unsigned NOT NULL,
    `price_ab_test` INT(11) NOT NULL DEFAULT 0,
    `base_cost` INT(11) unsigned NOT NULL DEFAULT 0,
    `revenue` INT(11) unsigned NOT NULL DEFAULT 0,
    `quantity` INT(10) unsigned NOT NULL DEFAULT 0,
    `added_timestamp` INT(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `item_INDEX` (`config_id`,`product_type_variant_id`,`product_variant_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS osc_auto_ab_product_price_log;
CREATE TABLE `osc_auto_ab_product_price_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `product_id` INT(11) NOT NULL DEFAULT 0,
    `product_variant_id` INT(11) NOT NULL DEFAULT 0,
    `product_type_variant_id` INT(11) NOT NULL DEFAULT 0,
    `note` varchar(1000) DEFAULT NULL,
    `added_timestamp` INT(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    KEY `item_INDEX` (`product_variant_id`,`product_type_variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8
