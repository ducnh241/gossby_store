CREATE TABLE `osc_collection_product_rel`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `collection_id` int(11) NOT NULL DEFAULT 0,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `added_timestamp` int(11) NOT NULL DEFAULT 0,
  `modified_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique` (`collection_id`,`product_id`)
)  ENGINE=InnoDB DEFAULT CHARSET=utf8;