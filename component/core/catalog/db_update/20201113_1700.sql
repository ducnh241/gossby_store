ALTER TABLE osc_catalog_discount_code ADD COLUMN `maximum_amount` INT(11) NOT NULL DEFAULT 0 AFTER `discount_value`;