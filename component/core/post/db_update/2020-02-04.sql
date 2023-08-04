ALTER TABLE osc_post ADD COLUMN visits int(11) NOT NULL DEFAULT 0 AFTER published_flag, ADD COLUMN unique_visits int(11) NOT NULL DEFAULT 0  AFTER visits;

DROP TABLE IF EXISTS `osc_post_unique_visit`;
CREATE TABLE `osc_post_unique_visit`  (
  `record_id` int NOT NULL AUTO_INCREMENT,
  `track_key` varchar(27) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `post_id` int NOT NULL,
  `added_timestamp` int NOT NULL,
  `visited_timestamp` int NOT NULL,
  PRIMARY KEY (`record_id`) USING BTREE,
  UNIQUE INDEX `unique`(`track_key`, `post_id`)
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `osc_post_referer`;
CREATE TABLE `osc_post_referer`  (
  `record_id` bigint(0) UNSIGNED NOT NULL AUTO_INCREMENT,
  `post_id` int(0) UNSIGNED NOT NULL,
  `referer` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `report_value` decimal(11, 2) NOT NULL,
  `added_timestamp` int(0) UNSIGNED NOT NULL,
  PRIMARY KEY (`record_id`) USING BTREE,
  UNIQUE INDEX `unique`(`post_id`, `referer`)
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;