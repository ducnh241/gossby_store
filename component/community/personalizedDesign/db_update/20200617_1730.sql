CREATE TABLE IF NOT EXISTS `osc_personalized_design_version` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `design_id` int(10) DEFAULT NULL,
  `title_design` varchar(120) CHARACTER SET utf8 DEFAULT NULL,
  `design_data` longtext CHARACTER SET utf8,
  `meta_data` longtext CHARACTER SET utf8,
  `user_id` int(11) DEFAULT '0',
  `active` tinyint(1) DEFAULT '0',
  `added_timestamp` int(10) UNSIGNED DEFAULT '0',
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;