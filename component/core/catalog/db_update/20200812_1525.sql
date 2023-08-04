ALTER TABLE osc_catalog_cart_item ADD COLUMN `tax_value` SMALLINT(3) NOT NULL DEFAULT 0 AFTER `price`;
ALTER TABLE osc_catalog_order_item ADD COLUMN `tax_value` SMALLINT(3) NOT NULL DEFAULT 0 AFTER `price`;
