CREATE TABLE `osc_report_record_new` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_report_record_new_ab` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `ab_key` varchar(255) NOT NULL,
  `ab_value` varchar(255) NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`,`ab_key`,`ab_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_report_record_new_referer` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`,`referer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_report_record_new_referer_ab` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `ab_key` varchar(255) NOT NULL,
  `ab_value` varchar(255) NOT NULL,
  `referer` varchar(255) NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`,`referer`,`ab_key`,`ab_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_report_record_product` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_report_record_product_ab` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `ab_key` varchar(255) NOT NULL,
  `ab_value` varchar(255) NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`,`product_id`,`ab_key`,`ab_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_report_record_product_referer` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `referer` varchar(255) NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`,`product_id`,`referer`),
  KEY `extra_key` (`report_key`,`added_timestamp`,`referer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_report_record_product_referer_ab` (
  `record_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `report_key` varchar(255) NOT NULL,
  `ab_key` varchar(255) NOT NULL,
  `ab_value` varchar(255) NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `referer` varchar(255) NOT NULL,
  `report_value` decimal(11,2) NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`report_key`,`added_timestamp`,`product_id`,`referer`,`ab_key`,`ab_value`),
  KEY `extra_key` (`report_key`,`added_timestamp`,`referer`,`ab_key`,`ab_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `osc_catalog_order_transaction` ADD INDEX `transaction_type` (`transaction_type` ASC, `added_timestamp` ASC, `order_id` ASC);
