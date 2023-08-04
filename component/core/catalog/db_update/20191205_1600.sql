CREATE TABLE `osc_core_image_optimize` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `process_key` varchar(27) NOT NULL,
  `original_path` varchar(255) NOT NULL,
  `optimized_path` varchar(255) NOT NULL,
  `extension` varchar(5) NOT NULL,
  `width` int(10) unsigned NOT NULL,
  `height` int(10) unsigned NOT NULL,
  `crop_flag` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `webp_flag` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `added_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `optimized_path_UNIQUE` (`optimized_path`),
  KEY `process_key` (`process_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
