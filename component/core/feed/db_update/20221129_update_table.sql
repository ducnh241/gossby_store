/* category: google, bing*/
ALTER TABLE `osc_feed_block`
    ADD COLUMN `category` VARCHAR(45) NOT NULL DEFAULT 'google' AFTER `country_code`;

DROP INDEX `unique` ON osc_feed_block;

CREATE UNIQUE INDEX `unique` ON osc_feed_block (`sku`,`collection_id`,`country_code`, `category`);


