CREATE TABLE `osc_catalog_order_pre_fulfillment` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `queue_flag` tinyint(1) NOT NULL DEFAULT '1',
  `error_message` varchar(255) DEFAULT NULL,
  `tracking_number` varchar(255) DEFAULT NULL,
  `shipping_carrier` varchar(255) DEFAULT NULL,
  `tracking_url` varchar(255) DEFAULT NULL,
  `line_items` text NOT NULL,
  `quantity` int(11) NOT NULL,
  `shipping_method` tinyint(1) NOT NULL DEFAULT '0',
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`order_id`),
  KEY `tracking_number` (`tracking_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;