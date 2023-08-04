ALTER TABLE `osc_catalog_order`
ADD COLUMN `process_status` ENUM('unprocess', 'process', 'partially_process', 'processed') NOT NULL DEFAULT 'unprocess' AFTER `fulfillment_status`;

ALTER TABLE `osc_catalog_order_index`
ADD COLUMN `process_status` TINYINT(4) UNSIGNED NOT NULL AFTER `fulfillment_status`;