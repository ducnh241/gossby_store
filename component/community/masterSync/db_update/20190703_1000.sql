CREATE TABLE `osc_mastersync_queue` (
  `queue_id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) DEFAULT NULL,
  `sync_key` varchar(255) NOT NULL,
  `sync_data` longtext,
  `syncing_flag` tinyint(1) NOT NULL DEFAULT '0',
  `error_message` varchar(255) DEFAULT NULL,
  `running_timestamp` int(10) NOT NULL DEFAULT '0',
  `added_timestamp` int(10) NOT NULL DEFAULT '0',
  `modified_timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
