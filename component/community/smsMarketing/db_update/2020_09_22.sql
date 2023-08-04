ALTER TABLE `osc_sms_marketing_campaign` ADD COLUMN `sending_timestamp` INT(10) NOT NULL DEFAULT 0 AFTER `destination_url`;

-- ----------------------------
-- Table structure for osc_sms_marketing_campaign_queue
-- ----------------------------
DROP TABLE IF EXISTS `osc_sms_marketing_campaign_queue`;
CREATE TABLE `osc_sms_marketing_campaign_queue`  (
  `record_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `phone` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `country_code` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT '0',
  `campaign_id` int NULL DEFAULT 0,
  PRIMARY KEY (`record_id`) USING BTREE,
  UNIQUE INDEX `ukey`(`phone`, `campaign_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;