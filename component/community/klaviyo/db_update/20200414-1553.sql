CREATE TABLE `osc_catalog_klaviyo_queue` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_flag` tinyint(1) NOT NULL DEFAULT '1',
  `error_message` varchar(255) DEFAULT NULL,
  `data` text,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

