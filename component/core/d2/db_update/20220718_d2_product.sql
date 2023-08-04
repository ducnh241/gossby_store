/*
 Query Store DB
 */
DROP TABLE IF EXISTS `osc_catalog_d2_products`;
CREATE TABLE `osc_catalog_d2_products`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `product_id`         int(11) NOT NULL,
    `added_by`           int(11) NOT NULL,
    `modified_by`        int(11) NOT NULL,
    `added_timestamp`    int(11) NOT NULL,
    `modified_timestamp` int(11) NOT NULL,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE KEY `product_id` (`product_id`) USING BTREE
) ENGINE=InnoDB;

ALTER TABLE `osc_catalog_d2_products`
    ADD COLUMN `title` VARCHAR(255) NULL DEFAULT NULL AFTER `product_id`;
