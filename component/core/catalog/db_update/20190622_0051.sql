ALTER TABLE `osc_catalog_order_bulk_queue` CHANGE COLUMN `action` `action` ENUM('capture', 'fulfill', 'fulfillment_replace') NOT NULL ;
