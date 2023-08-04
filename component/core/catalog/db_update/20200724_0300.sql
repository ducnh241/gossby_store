ALTER TABLE `osc_catalog_product_variant` ADD COLUMN `design_id` varchar(255) DEFAULT NULL AFTER `sku`;

ALTER TABLE `osc_catalog_order_item` ADD COLUMN `fulfill_lock` int(1) DEFAULT 0 AFTER `additional_data`;
ALTER TABLE `osc_catalog_cart_item` ADD COLUMN `fulfill_lock` int(1) DEFAULT 0 AFTER `custom_data`;
