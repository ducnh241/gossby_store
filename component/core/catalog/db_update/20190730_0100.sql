ALTER TABLE `osc_catalog_cart_item` ADD COLUMN `ukey` VARCHAR(52) NULL DEFAULT NULL AFTER `item_id`, CHANGE COLUMN `custom_data` `custom_data` LONGTEXT NULL DEFAULT NULL, DROP INDEX `cart_variant_id`, ADD UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC);
UPDATE osc_catalog_cart_item SET ukey = CONCAT(cart_id, ':', variant_id, ':d751713988987e9331980363e24189ce') WHERE item_id > 0;
ALTER TABLE `osc_catalog_cart_item` CHANGE COLUMN `ukey` `ukey` VARCHAR(52) NOT NULL ;
ALTER TABLE `osc_catalog_order_item` ADD COLUMN `ukey` VARCHAR(52) NULL DEFAULT NULL AFTER `item_id`, CHANGE COLUMN `custom_data` `custom_data` LONGTEXT NULL DEFAULT NULL, ADD UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC), DROP INDEX `order_variant_id`;
UPDATE osc_catalog_order_item SET ukey = CONCAT(order_id, ':', variant_id, ':d751713988987e9331980363e24189ce') WHERE item_id > 0;
ALTER TABLE `osc_catalog_order_item` CHANGE COLUMN `ukey` `ukey` VARCHAR(52) NOT NULL ;