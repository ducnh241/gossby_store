ALTER TABLE `osc_catalog_order`
CHANGE COLUMN `shipping_zip` `shipping_zip` VARCHAR(100) NOT NULL DEFAULT '',
CHANGE COLUMN `billing_zip` `billing_zip` VARCHAR(100) NOT NULL DEFAULT '',
CHANGE COLUMN `billing_province_code` `billing_province_code` VARCHAR(25) NULL DEFAULT NULL;


ALTER TABLE `osc_catalog_cart` 
CHANGE COLUMN `shipping_zip` `shipping_zip` VARCHAR(100) NOT NULL DEFAULT '',
CHANGE COLUMN `billing_zip` `billing_zip` VARCHAR(100) NOT NULL DEFAULT '';

ALTER TABLE `osc_catalog_customer` CHANGE COLUMN `zip` `zip` VARCHAR(100) NOT NULL DEFAULT '';

ALTER TABLE `osc_tracking` ADD COLUMN `unique_timestamp` INT(10) NOT NULL DEFAULT 0 AFTER `client_info`;
