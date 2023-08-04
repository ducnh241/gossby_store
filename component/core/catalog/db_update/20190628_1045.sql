ALTER TABLE `osc_catalog_product_review` ADD COLUMN `state` VARCHAR(45) NULL DEFAULT 'pending' COMMENT 'pending|approved|hidden' AFTER `modified_timestamp`,
    ADD COLUMN `ukey` VARCHAR(27) NOT NULL AFTER `record_id`,
    ADD UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC);
