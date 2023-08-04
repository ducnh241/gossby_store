ALTER TABLE `osc_catalog_order` 
ADD COLUMN `ab_test` TEXT NULL DEFAULT NULL AFTER `client_info`,
ADD COLUMN `client_referer` VARCHAR(255) NULL DEFAULT NULL AFTER `client_info`,
ADD COLUMN `client_country` VARCHAR(100) NULL DEFAULT NULL AFTER `client_referer`,
ADD COLUMN `client_device_type` VARCHAR(100) NULL DEFAULT NULL AFTER `client_country`,
ADD COLUMN `client_browser` VARCHAR(100) NULL DEFAULT NULL AFTER `client_device_type`;