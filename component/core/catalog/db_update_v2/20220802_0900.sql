RENAME TABLE osc_catalog_tax TO osc_catalog_tax_backup, osc_catalog_tax_convert TO osc_catalog_tax;
CREATE TABLE osc_catalog_tax_convert LIKE osc_catalog_tax;
ALTER TABLE `osc_catalog_tax_convert` ADD COLUMN `exclude_product_type_ids` VARCHAR(100) NULL DEFAULT '[]' AFTER `product_type_id`;
