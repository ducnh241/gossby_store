ALTER TABLE `osc_catalog_product_review` CHANGE COLUMN `state` `state` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0:hidden|1:pending|2:approved' ;
