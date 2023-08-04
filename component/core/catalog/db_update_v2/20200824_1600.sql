-- ----------------------------
-- Table structure for osc_product_type
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type`;
CREATE TABLE `osc_product_type` (
  `id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `product_type_option_ids` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT 'Luu lai array option cua product type nay',
  `status` tinyint DEFAULT '1',
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_product_type_option_value
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_option_value`;
CREATE TABLE `osc_product_type_option_value` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type_option_id` int DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `meta_data` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_product_type_variant
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_variant`;
CREATE TABLE `osc_product_type_variant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type_id` int DEFAULT NULL,
  `title` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `key` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_product_variant
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_variant`;
CREATE TABLE `osc_product_variant` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `image_id` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `sku` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `options` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `price` int NOT NULL,
  `compare_at_price` int NOT NULL DEFAULT '0',
  `cost` int NOT NULL DEFAULT '0',
  `track_quantity` tinyint(1) NOT NULL DEFAULT '1',
  `overselling` tinyint(1) NOT NULL DEFAULT '0',
  `quantity` int NOT NULL DEFAULT '0',
  `require_shipping` tinyint(1) NOT NULL DEFAULT '0',
  `require_packing` tinyint(1) NOT NULL DEFAULT '0',
  `weight` int NOT NULL DEFAULT '0',
  `weight_unit` enum('kg','g','oz','lb') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'g',
  `keep_flat` tinyint(1) NOT NULL DEFAULT '1',
  `dimension_width` int NOT NULL DEFAULT '0',
  `dimension_height` int NOT NULL DEFAULT '0',
  `dimension_length` int NOT NULL DEFAULT '0',
  `added_timestamp` int NOT NULL,
  `modified_timestamp` int NOT NULL,
  `meta_data` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `sku_UNIQUE` (`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ----------------------------
-- Table structure for osc_supplier
-- ----------------------------
DROP TABLE IF EXISTS `osc_supplier`;
CREATE TABLE `osc_supplier` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `short_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_supplier_variant_rel
-- ----------------------------
DROP TABLE IF EXISTS `osc_supplier_variant_rel`;
CREATE TABLE `osc_supplier_variant_rel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `product_type_variant_id` int DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `print_template_id` int DEFAULT NULL,
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_mockup
-- ----------------------------
DROP TABLE IF EXISTS `osc_mockup`;
CREATE TABLE `osc_mockup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT NULL,
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_print_template
-- ----------------------------
DROP TABLE IF EXISTS `osc_print_template`;
CREATE TABLE `osc_print_template` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `short_title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `config` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `merge_config` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `mockup_config` text CHARACTER SET utf8 COLLATE utf8_general_ci,
  `status` tinyint DEFAULT '1',
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_print_template_mockup_rel
-- ----------------------------
DROP TABLE IF EXISTS `osc_print_template_mockup_rel`;
CREATE TABLE `osc_print_template_mockup_rel` (
  `id` int NOT NULL AUTO_INCREMENT,
  `print_template_id` int DEFAULT NULL,
  `mockup_id` int DEFAULT NULL,
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  `is_default_mockup` tinyint(1) DEFAULT '0' COMMENT 'Danh dau day la mockup mac dinh cua print template',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `print_template_mockup_index` (`print_template_id`,`mockup_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_product_type_option
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_option`;
CREATE TABLE `osc_product_type_option` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `type` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL COMMENT '1: checkbox, 2: selector...',
  `key` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `description` varchar(1000) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `status` tinyint DEFAULT '1',
  `added_timestamp` int DEFAULT NULL,
  `modified_timestamp` int DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;