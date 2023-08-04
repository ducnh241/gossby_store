CREATE TABLE `osc_catalog_item_customize_order_map` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `design_id` int(10) unsigned NOT NULL,
  `order_line_id` int(10) unsigned NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `design_id` (`design_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_catalog_item_customize_design` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(100) NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `product_title` varchar(255) NOT NULL,
  `product_image_url` varchar(255) NOT NULL,
  `customize_id` int(10) unsigned NOT NULL,
  `customize_title` varchar(255) NOT NULL,
  `customize_info` longtext NOT NULL,
  `customize_data` longtext NOT NULL,
  `design_image_url` varchar(255) DEFAULT NULL,
  `state` tinyint(1) unsigned NOT NULL COMMENT '1: pending\\n2: processing\\n3: completed',
  `member_id` int(11) DEFAULT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `member_id` (`member_id`),
  KEY `state` (`state`,`member_id`),
  KEY `ukey_index` (`ukey`,`state`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
