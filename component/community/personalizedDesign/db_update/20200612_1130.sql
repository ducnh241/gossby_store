CREATE TABLE `osc_personalized_design_version` (
  `record_id` int NOT NULL AUTO_INCREMENT,
  `design_id` int NOT NULL DEFAULT '0',
  `title_design` varchar(120) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `parent_id` int DEFAULT NULL,
  `data_design` longtext CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `user_id` int DEFAULT NULL,
  `added_timestamp` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;