ALTER TABLE `osc_catalog_order_export_draft` CHANGE COLUMN `export_key` `export_key` VARCHAR(32) NOT NULL,
ADD COLUMN `secondary_key` VARCHAR(255) NULL DEFAULT NULL AFTER `export_key`,
DROP INDEX `ukey` ,
ADD UNIQUE INDEX `ukey` (`export_key` ASC, `secondary_key` ASC, `line_item_id` ASC);