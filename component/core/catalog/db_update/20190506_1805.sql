ALTER TABLE `osc_catalog_discount_code` ADD COLUMN `auto_generated` TINYINT(1) NOT NULL DEFAULT 0 AFTER `discount_code`;
ALTER TABLE `osc_catalog_discount_code_usage` ADD COLUMN `code_auto_generated` TINYINT(1) NOT NULL DEFAULT 0 AFTER `discount_code`;
