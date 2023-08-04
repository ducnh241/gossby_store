
ALTER TABLE `osc_twilio_sms_click` ADD COLUMN `campaign_id` INT(11) NULL DEFAULT 0 AFTER `province_code`;

ALTER TABLE `osc_catalog_order` ADD COLUMN `sms_campaign_id` INT(11) NULL DEFAULT 0 AFTER `sref_id`;

-- ----------------------------
-- Table structure for osc_sms_marketing_export_phone_draft
-- ----------------------------
DROP TABLE IF EXISTS `osc_sms_marketing_export_phone_draft`;
CREATE TABLE `osc_sms_marketing_export_phone_draft`  (
  `record_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `export_key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `phone` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `export_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `added_timestamp` int UNSIGNED NOT NULL,
  PRIMARY KEY (`record_id`) USING BTREE,
  UNIQUE INDEX `ukey`(`export_key`, `phone`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for osc_sms_marketing_campaign
-- ----------------------------
DROP TABLE IF EXISTS `osc_sms_marketing_campaign`;
CREATE TABLE `osc_sms_marketing_campaign`  (
  `campaign_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `file_data` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `content_sms` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `destination_url` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `added_timestamp` int NOT NULL,
  `modified_timestamp` int NOT NULL,
  `state` tinyint NULL DEFAULT 0,
  PRIMARY KEY (`campaign_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;