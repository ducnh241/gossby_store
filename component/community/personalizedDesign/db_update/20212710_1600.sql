ALTER TABLE `osc_personalized_design`
    ADD COLUMN `type_flag` TINYINT(1) NULL DEFAULT 0 AFTER `member_id`;
ALTER TABLE `osc_catalog_product`
    ADD COLUMN `type_flag` TINYINT(1) NULL DEFAULT 0 AFTER `master_lock_flag`;
ALTER TABLE `osc_catalog_product`
    ADD COLUMN `asin` VARCHAR(45) NULL AFTER `type_flag`;


CREATE TABLE `osc_product_export_design_amazon`
(
    `id`                 int          NOT NULL AUTO_INCREMENT,
    `ukey`               varchar(100) NOT NULL DEFAULT '',
    `product_id`         int          NOT NULL DEFAULT 0,
    `export_id`          varchar(100) NULL,
    `error`              text NULL,
    `link_download`      varchar(1000) NULL,
    `size`               varchar(45) NULL,
    `queue_flag`         tinyint(1) NOT NULL DEFAULT 0,
    `queue_data`         text NULL,
    `added_timestamp`    int NULL,
    `modified_timestamp` int NULL,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX                `index_product`(`product_id`) USING BTREE,
    INDEX                `index_export_id`(`export_id`) USING BTREE
) ENGINE = InnoDB;
