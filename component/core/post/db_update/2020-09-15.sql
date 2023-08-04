-- ----------------------------
-- Table structure for osc_post
-- ----------------------------
DROP TABLE IF EXISTS `osc_post`;
CREATE TABLE `osc_post`  (
  `post_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `priority` int NOT NULL DEFAULT 0,
  `collection_id` int NOT NULL DEFAULT 0,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `content` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `meta_tags` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `published_flag` tinyint(1) NOT NULL DEFAULT 1,
  `added_timestamp` int NOT NULL,
  `modified_timestamp` int NOT NULL,
  PRIMARY KEY (`post_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Table structure for osc_post_collection
-- ----------------------------
DROP TABLE IF EXISTS `osc_post_collection`;
CREATE TABLE `osc_post_collection`  (
  `collection_id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `image` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `meta_tags` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `added_timestamp` int NOT NULL,
  `modified_timestamp` int NOT NULL,
  PRIMARY KEY (`collection_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;