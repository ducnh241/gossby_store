CREATE TABLE `osc_catalog_2d_image_library` (
  `item_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(10) unsigned NOT NULL,
  `locked_flag` tinyint(1) NOT NULL DEFAULT 0,
  `item_type` enum('file','directory') NOT NULL DEFAULT 'file',
  `name` varchar(255) NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `extension` varchar(4) DEFAULT NULL,
  `width` int(10) unsigned DEFAULT NULL,
  `height` int(10) unsigned DEFAULT NULL,
  `size` int(10) unsigned DEFAULT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`item_id`),
  KEY `name` (`name`,`extension`,`item_type`,`added_timestamp`,`modified_timestamp`,`member_id`),
  KEY `item_type` (`item_type`,`added_timestamp`,`modified_timestamp`,`member_id`),
  KEY `extension` (`extension`,`added_timestamp`,`modified_timestamp`,`member_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
