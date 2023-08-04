/*
 Query Store DB
 */

ALTER TABLE osc_catalog_product_feed
    RENAME TO osc_feed_product;

ALTER TABLE osc_google_product_category
    RENAME TO osc_feed_google_category;

DROP TABLE IF EXISTS `osc_feed_block`;
CREATE TABLE `osc_feed_block`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `product_id`         int(11) NOT NULL DEFAULT 0,
    `sku`                VARCHAR(15) NOT NULL DEFAULT '',
    `collection_id`      int(11),
    `country_code`       VARCHAR(2),
    `member_id`           int(11) NOT NULL DEFAULT 0,
    `added_timestamp`    int(11) NOT NULL DEFAULT 0,
    `modified_timestamp` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `collection_id_INDEX` (`collection_id`) USING BTREE,
    KEY `country_code_INDEX` (`country_code`) USING BTREE,
    UNIQUE KEY `unique` (`sku`,`collection_id`,`country_code`)
) ENGINE=InnoDB;

DROP TABLE IF EXISTS `osc_feed_traffic_product`;
CREATE TABLE `osc_feed_traffic_product`
(
    `id`          int(11) NOT NULL AUTO_INCREMENT,
    `product_sku`        VARCHAR(15) NOT NULL DEFAULT '',
    `total`      int(11) NOT NULL DEFAULT 1,
    `date`       int(8) NOT NULL DEFAULT 0,
    `added_timestamp`    int(11) NOT NULL DEFAULT 0,
    `modified_timestamp` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `unique` (`product_sku`,`date`)
) ENGINE=InnoDB;
