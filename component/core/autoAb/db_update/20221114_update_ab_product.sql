ALTER TABLE `osc_auto_ab_product_map`
    ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `acquisition`;
