ALTER TABLE `osc_tracking_footprint` ADD COLUMN `ab_value` VARCHAR(45) NULL DEFAULT NULL AFTER `referer`, ADD COLUMN `ab_key` VARCHAR(45) NULL DEFAULT NULL AFTER `referer`;
ALTER TABLE `osc_post_office_subscriber` ADD COLUMN `ab_value` VARCHAR(45) NULL DEFAULT NULL AFTER `confirm`, ADD COLUMN `ab_key` VARCHAR(45) NULL DEFAULT NULL AFTER `confirm`;

DROP TABLE IF EXISTS `osc_behavior_recorded`;
CREATE TABLE `osc_behavior_recorded`  (
  `record_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `section_key` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `section_value` varchar(45) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ab_key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `ab_value` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `added_timestamp` int UNSIGNED NOT NULL,
  PRIMARY KEY (`record_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;