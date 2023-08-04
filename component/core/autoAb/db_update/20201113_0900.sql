DROP TABLE IF EXISTS osc_auto_ab_product_price_group;
CREATE TABLE `osc_auto_ab_product_price_group` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_country` varchar(1000) NOT NULL,
  `fees` int(3) unsigned NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `group_country_UNIQUE` (`group_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS osc_auto_ab_product_price_condition;
CREATE TABLE `osc_auto_ab_product_price_condition` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL DEFAULT 0,
  `product_type_variant_id` int(11) unsigned NOT NULL DEFAULT 0,
  `condition_config_id` int(11) unsigned NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `item_INDEX` (`group_id`,`product_type_variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS osc_auto_ab_product_price_condition_config;
CREATE TABLE `osc_auto_ab_product_price_condition_config` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `group_id` int(11) unsigned NOT NULL DEFAULT 0,
  `product_type_ids` varchar(255) NOT NULL DEFAULT '',
  `product_type_variant_ids` varchar(500) NOT NULL DEFAULT '',
  `condition_start` varchar(50) NOT NULL DEFAULT '',
  `condition_end` varchar(50) NOT NULL DEFAULT '',
  `price_range` varchar(2000) NOT NULL DEFAULT '',
  `base_cost` int(10) unsigned NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`),
  KEY `group_id_INDEX` (`group_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS osc_auto_ab_product_price_tracking;
CREATE TABLE `osc_auto_ab_product_price_tracking` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `condition_config_id` int(11) NOT NULL DEFAULT 0,
  `product_variant_id` int(11) NOT NULL DEFAULT 0,
  `order_item_id` int(11) unsigned NOT NULL,
  `product_type_variant_id` int(11) NOT NULL DEFAULT 0,
  `price` int(11) unsigned NOT NULL DEFAULT 0,
  `quantity` int(10) unsigned NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`),
  KEY `item_INDEX` (`condition_config_id`,`product_type_variant_id`,`product_variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS osc_auto_ab_product_price_log;
CREATE TABLE `osc_auto_ab_product_price_log` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_variant_id` int(11) NOT NULL DEFAULT 0,
  `product_type_variant_id` int(11) NOT NULL DEFAULT 0,
  `note` varchar(1000) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`),
  KEY `item_INDEX` (`product_variant_id`,`product_type_variant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8

ALTER TABLE osc_product_variant ADD COLUMN best_price_data varchar(1000) DEFAULT NULL AFTER compare_at_price;
