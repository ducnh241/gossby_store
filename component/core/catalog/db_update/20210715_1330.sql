ALTER TABLE `osc_catalog_collection`
ADD COLUMN `show_review_mode` TINYINT(1) NOT NULL DEFAULT '0' AFTER `sort_option`;
