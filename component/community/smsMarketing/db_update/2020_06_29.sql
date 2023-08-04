DROP TABLE IF EXISTS `osc_twilio_sms_click`;
CREATE TABLE `osc_twilio_sms_click`  (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `country_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `province_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `osc_twilio_sms_queue`;
CREATE TABLE `osc_twilio_sms_queue`  (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `data_id` int(11) NULL DEFAULT NULL,
  `data_request` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `queue_flag` int(2) NOT NULL,
  `error_message` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `running_timestamp` int(10) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`queue_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `osc_twilio_sms_sent`;
CREATE TABLE `osc_twilio_sms_sent`  (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `mobile_number` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;