CREATE TABLE `osc_personalized_design_sync` (
  `record_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `syncing_flag` tinyint(1) NOT NULL DEFAULT '0',
  `sync_type` enum('font','image','design') COLLATE utf8mb4_bin NOT NULL,
  `sync_data` longtext COLLATE utf8mb4_bin NOT NULL,
  `sync_error` varchar(255) COLLATE utf8mb4_bin DEFAULT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;
