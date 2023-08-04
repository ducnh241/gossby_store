ALTER TABLE `osc_catalog_discount_code` ADD COLUMN `campaign` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE `osc_catalog_discount_code_usage` ADD COLUMN `campaign` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
