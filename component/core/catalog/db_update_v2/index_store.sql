ALTER TABLE `osc_catalog_order_item` ADD INDEX `order_item_meta_id` (`order_item_meta_id`);
ALTER TABLE `osc_product_type` ADD INDEX `ukey_index` (`ukey`);
ALTER TABLE `osc_catalog_product_pack` ADD INDEX `product_type_id_index` (`product_type_id`);
ALTER TABLE `osc_product_type_variant_location_price` ADD INDEX `product_type_variant_id_index` (`product_type_variant_id`);

ALTER TABLE `osc_product_type_variant` ADD INDEX `product_type_id_index` (`product_type_id`), ADD INDEX `ukey_index` (`ukey`);
ALTER TABLE `osc_product_type_option` ADD INDEX `ukey_index` (`ukey`);
ALTER TABLE `osc_product_type_option_value` ADD INDEX `ukey_index` (`ukey`);
ALTER TABLE `osc_supplier_variant_rel` ADD INDEX `index_1` (`product_type_variant_id`, `supplier_id`), ADD UNIQUE INDEX `index_2` (`product_type_variant_id`, `supplier_id`, `print_template_id`);