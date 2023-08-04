CREATE TABLE `osc_marketing_point` (
  `record_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `order_id` int(11) unsigned NOT NULL DEFAULT '0',
  `order_line_item_id` int(11) unsigned NOT NULL DEFAULT '0',
  `product_id` int(11) unsigned NOT NULL DEFAULT '0',
  `variant_id` int(11) unsigned NOT NULL DEFAULT '0',
  `member_id` int(11) unsigned NOT NULL DEFAULT '0',
  `point` bigint(20) unsigned NOT NULL DEFAULT '0',
  `meta_data` text,
  `added_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  `modified_timestamp` int(11) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;