ALTER TABLE `osc_catalog_order_item`
ADD COLUMN `other_quantity` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `fulfilled_quantity`;