ALTER TABLE `osc_catalog_product` ADD COLUMN `meta_data` TEXT AFTER `meta_tags`;
ALTER TABLE `osc_catalog_cart_item` ADD COLUMN `custom_price_data` TEXT AFTER `custom_data`;
ALTER TABLE `osc_catalog_cart` ADD COLUMN `custom_price_data` TEXT AFTER `abandoned_email_sents`;
ALTER TABLE `osc_catalog_order` ADD COLUMN `custom_price` INT(11) AFTER `tax_price`;
ALTER TABLE `osc_catalog_order` ADD COLUMN `custom_price_data` TEXT AFTER `note`;
ALTER TABLE `osc_catalog_order_item` ADD COLUMN `custom_price_data` TEXT AFTER `custom_data`;

