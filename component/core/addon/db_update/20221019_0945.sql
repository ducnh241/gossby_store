ALTER TABLE `osc_addon_service`
    ADD COLUMN `auto_apply_for_product_type` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `product_type_id`;

ALTER TABLE `osc_addon_service` ADD COLUMN `display_area` TINYINT(1) NOT NULL DEFAULT 0 AFTER `product_type_id`;

TRUNCATE osc_addon_service_report; -- Reset lại data report

ALTER TABLE `osc_addon_service` DROP COLUMN `auto_apply_for_product_type`;
ALTER TABLE `osc_addon_service`
    ADD COLUMN `auto_apply_for_product_type_variants` VARCHAR(2000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL AFTER `product_type_id`;
