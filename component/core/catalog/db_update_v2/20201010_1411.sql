ALTER TABLE `osc_catalog_product_image`
ADD COLUMN `ukey` VARCHAR(100) NULL DEFAULT NULL AFTER `product_id`,
ADD UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC);



ALTER TABLE `osc_catalog_product_image`
ADD COLUMN `flag_main` TINYINT(1) NULL DEFAULT 0 AFTER `position`;