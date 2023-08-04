-- ----------------------------
-- Table structure for osc_catalog_product_feed
-- ----------------------------
DROP TABLE IF EXISTS `osc_catalog_product_feed`;
CREATE TABLE `osc_catalog_product_feed`  (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `social_chanel` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `country_code` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `added_timestamp` int UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  INDEX `product_id`(`product_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = DYNAMIC;

ALTER TABLE `osc_catalog_product_feed` ADD COLUMN `group_mode` TINYINT(1) NOT NULL DEFAULT 0 AFTER `country_code`;

DELETE FROM `osc_core_setting` WHERE `setting_key` = 'catalog/google_feed/table';

INSERT INTO `osc_core_setting`(`record_id`, `setting_key`, `setting_value`, `added_timestamp`, `modified_timestamp`)
VALUES
    (NULL, 'catalog/google_feed/table', '{\"ceramic_mug\":{\"google_product_cat_id\":\"2169\",\"shipping_label\":\"\"},\"enamel_campfire_mug\":{\"google_product_cat_id\":\"2169\",\"shipping_label\":\"\"},\"insulated_coffee_mug\":{\"google_product_cat_id\":\"2169\",\"shipping_label\":\"\"},\"two_tone_mug\":{\"google_product_cat_id\":\"2169\",\"shipping_label\":\"\"},\"wrapped_canvas\":{\"google_product_cat_id\":\"505397\",\"shipping_label\":\"\"},\"fullPrints_ceramic_mug\":{\"google_product_cat_id\":\"2169\",\"shipping_label\":\"\"},\"fullPrints_two_tone_mug\":{\"google_product_cat_id\":\"2169\",\"shipping_label\":\"\"},\"fullPrints_insulated_coffee_mug\":{\"google_product_cat_id\":\"2169\",\"shipping_label\":\"\"},\"desktop_plaque\":{\"google_product_cat_id\":\"708\",\"shipping_label\":\"\"},\"fleece_blanket\":{\"google_product_cat_id\":\"1985\",\"shipping_label\":\"\"},\"facemask_with_filter\":{\"google_product_cat_id\":\"5194\",\"shipping_label\":\"\"},\"facemask_without_filter\":{\"google_product_cat_id\":\"5194\",\"shipping_label\":\"\"},\"pillow\":{\"google_product_cat_id\":\"4454\",\"shipping_label\":\"\"},\"gildan_g500_classic_tee\":{\"google_product_cat_id\":\"212\",\"shipping_label\":\"\"},\"bella_canvas_3001c_unisex_jersey_short_sleeve\":{\"google_product_cat_id\":\"212\",\"shipping_label\":\"\"},\"next_level_nl3600_premium_short_sleeve\":{\"google_product_cat_id\":\"212\",\"shipping_label\":\"\"},\"ornament_heart\":{\"google_product_cat_id\":\"3144\",\"shipping_label\":\"\"},\"ornament_medallion\":{\"google_product_cat_id\":\"3144\",\"shipping_label\":\"\"},\"ornament_scalloped\":{\"google_product_cat_id\":\"3144\",\"shipping_label\":\"\"},\"ornament_circle\":{\"google_product_cat_id\":\"3144\",\"shipping_label\":\"\"},\"sherpa_flannel_blanket\":{\"google_product_cat_id\":\"1985\",\"shipping_label\":\"\"},\"candlle holder\":{\"google_product_cat_id\":\"588\",\"shipping_label\":\"\"},\"poster\":{\"google_product_cat_id\":\"500044\",\"shipping_label\":\"\"}}', 1606385964, 1617016062);
