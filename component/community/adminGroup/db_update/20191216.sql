CREATE TABLE `osc_member_groups_admin` (
  `member_groups_id` int(11) NOT NULL AUTO_INCREMENT,
  `member_id` int(1) NOT NULL DEFAULT '0',
  `group_ids` varchar(255) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`member_groups_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;