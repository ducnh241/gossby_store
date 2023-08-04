ALTER TABLE osc_catalog_product ADD COLUMN selling_type TINYINT(1) NULL DEFAULT 1 AFTER product_type, ADD INDEX selling_type (selling_type ASC);

UPDATE osc_catalog_product SET	selling_type = 2 WHERE meta_data NOT LIKE '%campaign_config%';