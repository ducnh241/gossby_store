CREATE TABLE `osc_filter_tag`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `title`              varchar(255) UNIQUE NOT NULL,
    `other_title`        varchar(500) DEFAULT NULL,
    `type`               tinyint(1) NULL DEFAULT 0,
    `parent_id`          int(11) NULL DEFAULT 0,
    `lock_flag`          tinyint(1) NOT NULL DEFAULT 0,
    `status`             tinyint(1) NOT NULL DEFAULT 1,
    `is_show_filter`     tinyint(1) NULL DEFAULT NULL,
    `position`           int(10) NULL DEFAULT NULL,
    `added_timestamp`    int(10) NOT NULL DEFAULT 0,
    `modified_timestamp` int(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX                `tag_parent_id`(`parent_id`) USING BTREE
) ENGINE = InnoDB;

CREATE TABLE `osc_filter_tag_product_rel`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `tag_id`             int(11) NOT NULL DEFAULT 0,
    `product_id`         int(11) NOT NULL DEFAULT 0,
    `added_timestamp`    int(11) NOT NULL DEFAULT 0,
    `modified_timestamp` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `unique` (`tag_id`,`product_id`),
    INDEX                `product_id` (`product_id`),
    INDEX                `tag_id` (`tag_id`)
) ENGINE=InnoDB;


CREATE TABLE `osc_filter_collection`
(
    `id`                 INT NOT NULL AUTO_INCREMENT,
    `collection_id`      INT NOT NULL DEFAULT 0,
    `filter_setting`     TEXT NULL,
    `added_timestamp`    INT NULL,
    `modified_timestamp` INT NULL,
    PRIMARY KEY (`id`),
    INDEX                `index_collection` (`collection_id`)
);

ALTER TABLE `osc_filter_tag` ADD COLUMN `required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `lock_flag`;


