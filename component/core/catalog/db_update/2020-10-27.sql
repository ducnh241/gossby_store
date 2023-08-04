ALTER TABLE osc_product_type ADD COLUMN description_id int(11) DEFAULT 0 AFTER product_type_option_ids;
ALTER TABLE osc_product_type_variant ADD COLUMN description_id int(11) DEFAULT 0 AFTER status;

-- ----------------------------
-- Table structure for osc_product_type_description
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_description`;
CREATE TABLE `osc_product_type_description`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `description` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  `added_timestamp` int NULL DEFAULT NULL,
  `modified_timestamp` int NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;