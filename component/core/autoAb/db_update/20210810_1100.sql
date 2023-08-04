
-- store
ALTER TABLE `osc_auto_ab_product_price_tracking`
    ADD COLUMN `order_id` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `order_item_id`;


ALTER TABLE `osc_auto_ab_product_price_config`
    ADD COLUMN `status` TINYINT(1) NOT NULL DEFAULT 1 AFTER `price_range`;


-- master + store
ALTER TABLE `osc_product_type_variant`
    ADD COLUMN `best_price` VARCHAR(1000) NULL DEFAULT NULL AFTER `price`;
