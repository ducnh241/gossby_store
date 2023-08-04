ALTER TABLE `osc_product_variant` ADD COLUMN `product_type_variant_id` INT(10) NOT NULL DEFAULT 0 AFTER `product_id`;

CREATE TABLE `osc_supply_variant` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
    `country_code` varchar(25) NOT NULL DEFAULT '',
    `province_code` varchar(25) NOT NULL DEFAULT '',
    `product_type_variant_id` int(11) NOT NULL DEFAULT 0,
    `supplier_id` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `country_province_variant_supplier_UNIQUE` (`country_code`, `province_code`, `product_type_variant_id`, `supplier_id`) USING BTREE
) ENGINE=InnoDB;
