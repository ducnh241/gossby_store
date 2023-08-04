ALTER TABLE `osc_product_variant`
ADD COLUMN `position` SMALLINT (6) NOT NULL DEFAULT 0 AFTER `modified_timestamp`,
ADD INDEX `position_index` (`position`);

ALTER TABLE `osc_product_type_option`
ADD COLUMN `is_reorder` TINYINT (1) NOT NULL DEFAULT 0 AFTER `is_show_option`;