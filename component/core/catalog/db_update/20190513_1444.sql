ALTER TABLE `osc_catalog_cart` 
ADD COLUMN `shipping_full_name` VARCHAR(255) NOT NULL AFTER `shipping_last_name`,
ADD COLUMN `billing_full_name` VARCHAR(255) NOT NULL AFTER `billing_last_name`;

UPDATE osc_catalog_cart SET shipping_full_name = CONCAT(shipping_first_name, ' ', shipping_last_name), billing_full_name = CONCAT(billing_first_name, ' ', billing_last_name) WHERE cart_id > 0;

ALTER TABLE `osc_catalog_cart` 
DROP COLUMN `billing_last_name`,
DROP COLUMN `billing_first_name`,
DROP COLUMN `shipping_last_name`,
DROP COLUMN `shipping_first_name`;


ALTER TABLE `osc_catalog_order` 
ADD COLUMN `shipping_full_name` VARCHAR(255) NOT NULL AFTER `shipping_last_name`,
ADD COLUMN `billing_full_name` VARCHAR(255) NOT NULL AFTER `billing_last_name`;

UPDATE osc_catalog_order SET shipping_full_name = CONCAT(shipping_first_name, ' ', shipping_last_name), billing_full_name = CONCAT(billing_first_name, ' ', billing_last_name) WHERE order_id > 0;

ALTER TABLE `osc_catalog_order` 
DROP COLUMN `billing_last_name`,
DROP COLUMN `billing_first_name`,
DROP COLUMN `shipping_last_name`,
DROP COLUMN `shipping_first_name`;


ALTER TABLE `osc_catalog_customer` 
ADD COLUMN `full_name` VARCHAR(255) NOT NULL AFTER `last_name`;

UPDATE osc_catalog_customer SET full_name = CONCAT(first_name, ' ', last_name) WHERE customer_id > 0;

ALTER TABLE `osc_catalog_customer` 
DROP COLUMN `last_name`,
DROP COLUMN `first_name`;
