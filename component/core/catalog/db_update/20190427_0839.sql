ALTER TABLE `osc_catalog_cart_item` ADD INDEX `variant_id_idx` (`variant_id` ASC) VISIBLE;
DELETE FROM osc_catalog_cart_item WHERE variant_id NOT IN(SELECT variant_id FROM osc_catalog_product_variant) AND item_id > 0;
ALTER TABLE `osc_catalog_cart_item` ADD CONSTRAINT `variant_id_2132` FOREIGN KEY (`variant_id`) REFERENCES `osc_catalog_product_variant` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE `osc_catalog_order` ADD COLUMN `code` VARCHAR(45) NULL DEFAULT NULL, ADD UNIQUE INDEX `code_UNIQUE` (`code` ASC);
UPDATE osc_catalog_order SET `code` = REPLACE(`code_pattern`, '{{number}}', LPAD(LPAD(order_id, 4, '0'), 5, '1')) WHERE order_id > 0;
ALTER TABLE `osc_catalog_order` DROP COLUMN `code_pattern`;