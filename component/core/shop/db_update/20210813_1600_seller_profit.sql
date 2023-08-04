
ALTER TABLE `osc_catalog_order_item` ADD COLUMN `product_type_variant_id` int(11) NOT NULL DEFAULT 0 AFTER `variant_id`;

ALTER TABLE `osc_product_type_variant` ADD COLUMN `seller_base_cost` varchar(1000) DEFAULT NULL AFTER `base_cost_configs`;