DROP TABLE IF EXISTS `osc_facebook_api_queue`;
CREATE TABLE `osc_facebook_api_queue`  (
  `queue_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `queue_flag` tinyint(1) NOT NULL DEFAULT 0,
  `pixel_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `data_events` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `error_message` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `added_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

TRUNCATE TABLE osc_facebook_api_queue;
--------------------------------------------------------------------

DROP TABLE IF EXISTS `osc_facebook_pixel`;
CREATE TABLE `osc_facebook_pixel`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `pixel_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `added_timestamp` int NOT NULL DEFAULT 0,
  `modified_timestamp` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

DROP TABLE IF EXISTS `osc_facebook_pixel_product_type_rel`;
CREATE TABLE `osc_facebook_pixel_product_type_rel`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type_id` int NULL DEFAULT NULL,
  `pixel_id` varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `added_timestamp` int NOT NULL DEFAULT 0,
  `modified_timestamp` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;