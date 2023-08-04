CREATE TABLE `osc_customize_printersync_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `order_line_id` int(11) NOT NULL,
  `url` varchar(255) DEFAULT NULL,
  `sync_data` longtext,
  `syncing_flag` tinyint(1) NOT NULL DEFAULT '0',
  `error_message` varchar(255) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `line_UNIQUE` (`order_line_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

