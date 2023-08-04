ALTER TABLE `osc_catalog_order_fulfillment`
ADD COLUMN `service` VARCHAR(45) NULL DEFAULT NULL AFTER `quantity`;
ALTER TABLE osc_catalog_order_fulfillment ADD COLUMN additional_data TEXT NULL DEFAULT NULL AFTER service;

ALTER TABLE `osc_catalog_order_process`
ADD COLUMN `service` VARCHAR(45) NULL DEFAULT NULL AFTER `quantity`;