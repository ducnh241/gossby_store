ALTER TABLE `store`.`osc_catalog_product_bulk_queue`
    ADD COLUMN `extra_data` LONGTEXT NULL DEFAULT NULL AFTER `queue_data`;
