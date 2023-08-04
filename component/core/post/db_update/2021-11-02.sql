DROP TABLE IF EXISTS `osc_post_author`;
CREATE TABLE `osc_post_author`  (
  `author_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `slug` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `meta_tags` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `added_timestamp` int(11) NOT NULL DEFAULT 0,
  `modified_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`author_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

ALTER TABLE `osc_post` ADD `author_id` int AFTER `post_id`;