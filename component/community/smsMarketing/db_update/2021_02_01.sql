-- ----------------------------
-- Table structure for osc_catalog_klaviyo_sms_queue
-- ----------------------------
DROP TABLE IF EXISTS `osc_catalog_klaviyo_sms_queue`;
CREATE TABLE `osc_catalog_klaviyo_sms_queue`  (
  `record_id` int NOT NULL AUTO_INCREMENT,
  `queue_flag` tinyint(1) NOT NULL DEFAULT 1,
  `error_message` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `data` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `added_timestamp` int NOT NULL,
  `modified_timestamp` int NOT NULL,
  PRIMARY KEY (`record_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;


ALTER TABLE osc_twilio_sms_sent ADD COLUMN country_code varchar(3) DEFAULT NULL AFTER mobile_number;