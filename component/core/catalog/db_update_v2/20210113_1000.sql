ALTER TABLE `osc_catalog_order` ADD COLUMN `is_upsale` tinyint(1) DEFAULT 0 AFTER `payment_data`;
ALTER TABLE `osc_catalog_order_item` ADD COLUMN `payment_data` text DEFAULT NULL AFTER `additional_data`,
ADD COLUMN `fraud_data` varchar(255) DEFAULT NULL AFTER `payment_data`,
ADD COLUMN `fraud_risk_level` varchar(45) DEFAULT NULL AFTER `fraud_data`,
ADD COLUMN `payment_status` varchar(45) DEFAULT NULL AFTER `fraud_risk_level`,
ADD COLUMN `upsale_data` text DEFAULT NULL AFTER `options`,
ADD COLUMN `reference_total_price` INT(11) NOT NULL DEFAULT 0 AFTER `price`;
ALTER TABLE `osc_catalog_order_transaction` ADD COLUMN `order_item_id` INT(11) UNSIGNED NOT NULL DEFAULT 0 AFTER `order_id`;