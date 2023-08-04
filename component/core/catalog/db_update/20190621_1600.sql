CREATE TABLE `osc_catalog_product_unique_visit` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `track_key` varchar(27) NOT NULL,
  `product_id` int(11) NOT NULL,
  `unique_timestamp` int(10) NOT NULL,
  `visit_timestamp` int(10) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `unique` (`track_key`,`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;