CREATE TABLE `osc_catalog_product_block` (
  `record_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `country_code` varchar(255) NOT NULL,
  `product_ids` varchar(255),
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE INDEX `country_code_UNIQUE` (`country_code` ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
