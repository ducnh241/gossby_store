CREATE TABLE `osc_catalog_product_auto_listing_discard` (
  `record_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `product_id` int(0) NOT NULL,
  `type` enum('listing','discard') NOT NULL,
  `added_timestamp` INT(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE INDEX `product_UNIQUE` (`product_id` ASC, `type` ASC)
);