ALTER TABLE `osc_catalog_product_variant` CHANGE COLUMN `image_id` `image_id` VARCHAR(255) NOT NULL;
UPDATE osc_catalog_product_variant SET image_id = '' WHERE image_id = '0' AND variant_id > 0;