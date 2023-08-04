ALTER TABLE `osc_catalog_order`
ADD COLUMN `master_lock_flag` TINYINT(1) NOT NULL DEFAULT 0 AFTER `code`;


ALTER TABLE `osc_catalog_product`
ADD COLUMN `master_lock_flag` TINYINT(1) NOT NULL DEFAULT 0 AFTER `modified_timestamp`;

