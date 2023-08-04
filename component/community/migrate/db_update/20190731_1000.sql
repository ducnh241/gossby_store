CREATE TABLE `osc_migrate_gearlaunch` (
  `queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `member_id` int(11) NOT NULL,
  `queue_key` int(10) unsigned NOT NULL,
  `queue_flag` tinyint(1) NOT NULL DEFAULT '1',
  `error_flag` tinyint(1) NOT NULL DEFAULT '0',
  `error_message` varchar(255) DEFAULT NULL,
  `action_key` varchar(100) NOT NULL,
  `action_data` text NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  `modified_timestamp` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`queue_id`),
  KEY `queue_key` (`queue_key`,`queue_flag`,`added_timestamp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
