
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `order_item_id` int(10) UNSIGNED NULL DEFAULT NULL AFTER `order_id`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `variant_id` int(10) UNSIGNED NULL DEFAULT 0 AFTER `product_id`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `product_type_variant_id` int(10) UNSIGNED NULL DEFAULT 0 AFTER `variant_id`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `product_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `product_type_variant_id`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `country_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL AFTER `product_type`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `helpful` int(10) NULL DEFAULT 0 AFTER `has_photo`;

CREATE TABLE `osc_catalog_product_review_helpful`
(
    `id`         int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `review_id`  int(10) NULL DEFAULT NULL,
    `ip_address` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `unique_helpfull`(`review_id`, `ip_address`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci ROW_FORMAT = Dynamic;

ALTER TABLE `osc_catalog_product_review_request` ADD COLUMN `order_item_id` int(10) UNSIGNED NULL DEFAULT NULL AFTER `order_id`;
ALTER TABLE `osc_catalog_product_review_request` ADD COLUMN `variant_id` int(10) UNSIGNED NULL DEFAULT NULL AFTER `product_id`;
ALTER TABLE `osc_catalog_product_review_request` ADD COLUMN `product_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' AFTER `variant_id`;
ALTER TABLE `osc_catalog_product_review_request` ADD COLUMN `country_code` varchar(2) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT '' AFTER `product_type`;
ALTER TABLE `osc_catalog_product_review_request` ADD COLUMN `product_type_variant_id` int(10) UNSIGNED NULL DEFAULT 0 AFTER `country_code`;

ALTER TABLE `osc_catalog_product_review` DROP COLUMN `helpful`;
DROP TABLE IF EXISTS `osc_catalog_product_review_helpful`;