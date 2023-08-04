ALTER TABLE `osc_catalog_discount_code` ADD COLUMN `prerequisite_shipping` VARCHAR(255) NULL,
ADD COLUMN `max_item_allow` INT ( 11 ) NULL AFTER `once_per_customer`;