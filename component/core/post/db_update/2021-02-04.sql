-- ----------------------------
-- Table structure for osc_post_collection_rel
-- ----------------------------
DROP TABLE IF EXISTS `osc_post_collection_rel`;
CREATE TABLE `osc_post_collection_rel`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `collection_id` int NOT NULL,
  `post_id` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

-- convert data
INSERT INTO `osc_post_collection_rel` (`collection_id`, `post_id`)
SELECT `collection_id`, `post_id`
FROM `osc_post`;

ALTER TABLE `osc_post_collection` ADD COLUMN `priority` int NOT NULL DEFAULT 0 AFTER image;

ALTER TABLE `osc_post` DROP COLUMN `collection_id`;