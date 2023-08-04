DROP TABLE IF EXISTS `osc_product_type_variant_location_price`;
CREATE TABLE `osc_product_type_variant_location_price` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_type_variant_id` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `location_data` varchar(255) NOT NULL DEFAULT '',
  `price` int(11) UNSIGNED NOT NULL DEFAULT 0,
  `compare_at_price` int(11) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
