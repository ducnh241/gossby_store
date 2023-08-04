ALTER TABLE `osc_product_type` ADD COLUMN `default_for_light_design` varchar(1000) NULL;
ALTER TABLE `osc_product_type` ADD COLUMN `default_for_dark_design` varchar(1000) NULL;
ALTER TABLE `osc_product_type` ADD COLUMN `is_cross_sell` tinyint(1) NOT NULL DEFAULT 0;

UPDATE `osc_product_type` SET `is_cross_sell` = 1 WHERE `ukey` IN (
    'bella_canvas_3001c_unisex_jersey_short_sleeve',
    'gildan_g500_classic_tee',
    'youth_t_shirt'
);

CREATE TABLE `osc_cross_sell_design_color`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `product_type_variant_id` int(11) NOT NULL DEFAULT 0,
  `is_light_design` tinyint(1) NOT NULL DEFAULT 0,
  `is_dark_design` tinyint(1) NOT NULL DEFAULT 0,
  `added_timestamp` int(11) NOT NULL DEFAULT 0,
  `modified_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1;

CREATE TABLE `osc_cross_sell`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cart',
  `product_type_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `product_type_variant_id` int(11) NOT NULL DEFAULT 0,
  `discount_type` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `discount_value` int(11) UNSIGNED NULL DEFAULT 0,
  `added_timestamp` int(11) NOT NULL DEFAULT 0,
  `modified_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1;
