CREATE TABLE `osc_catalog_product_tabs` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` int(10) NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

UPDATE osc_catalog_product SET description = NULL, tags = CONCAT(tags, IF(tags != '', ',', ''), 'meta:tab:description,meta:tab:shipping,meta:tab:return_refund') WHERE product_id > 0;

ALTER TABLE `osc_catalog_product_tabs` DROP COLUMN `product_tags`;
ALTER TABLE `osc_catalog_product_tabs` ADD COLUMN `apply_all` INT(1) NOT NULL DEFAULT 0 AFTER `priority`;