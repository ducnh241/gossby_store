CREATE TABLE `osc_catalog_product_review` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(10) unsigned DEFAULT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `vote_value` tinyint(1) NOT NULL DEFAULT '5',
  `photo_filename` varchar(255) CHARACTER SET latin1 DEFAULT NULL,
  `photo_width` int(10) unsigned DEFAULT NULL,
  `photo_height` int(10) unsigned DEFAULT NULL,
  `photo_extension` varchar(4) CHARACTER SET latin1 DEFAULT NULL,
  `review` text NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`record_id`),
  KEY `product_id` (`product_id`,`vote_value`),
  KEY `order_id` (`order_id`,`product_id`,`vote_value`),
  KEY `customer_id` (`customer_id`),
  KEY `vote_value` (`vote_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE `osc_catalog_product_review_request` (
  `request_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(27) CHARACTER SET latin1 NOT NULL,
  `customer_id` int(10) unsigned DEFAULT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_email` varchar(255) NOT NULL,
  `order_id` int(10) unsigned DEFAULT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `added_timestamp` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`request_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  UNIQUE KEY `order_id` (`order_id`,`product_id`),
  KEY `customer_id` (`customer_id`,`order_id`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
