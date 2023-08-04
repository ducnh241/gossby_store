ALTER TABLE `osc_catalog_order` 
CHANGE COLUMN `shipping_province` `shipping_province` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `shipping_province_code` `shipping_province_code` VARCHAR(5) NULL DEFAULT NULL  ,
CHANGE COLUMN `billing_province` `billing_province` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `billing_province_code` `billing_province_code` VARCHAR(5) NULL DEFAULT NULL ;

ALTER TABLE `osc_catalog_cart` 
CHANGE COLUMN `shipping_province` `shipping_province` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `shipping_province_code` `shipping_province_code` VARCHAR(5) NULL DEFAULT NULL  ,
CHANGE COLUMN `billing_province` `billing_province` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `billing_province_code` `billing_province_code` VARCHAR(5) NULL DEFAULT NULL ;

ALTER TABLE `osc_catalog_customer` 
CHANGE COLUMN `province` `province` VARCHAR(255) NULL DEFAULT NULL ,
CHANGE COLUMN `province_code` `province_code` VARCHAR(5) NULL DEFAULT NULL;
