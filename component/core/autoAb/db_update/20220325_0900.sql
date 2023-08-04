ALTER TABLE `osc_auto_ab_product_price_config`
    ADD COLUMN `fixed_product_ids` VARCHAR(255) NULL DEFAULT NULL AFTER `variant_data`;