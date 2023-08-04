CREATE TABLE `osc_cross_sell_config_segments`
(
    `id`                 int     NOT NULL,
    `product_type_id`    int     NOT NULL DEFAULT 0,
    `segments`           varchar(2000),
    `segments_type`      tinyint NOT NULL DEFAULT 1,
    `added_timestamp`    int     NOT NULL DEFAULT 0,
    `modified_timestamp` int     NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)
);

CREATE TABLE `osc_cross_sell_image`
(
    `id`                 int          NOT NULL AUTO_INCREMENT,
    `design_id`          int          NOT NULL DEFAULT 0,
    `ukey`               varchar(255) NOT NULL DEFAULT '',
    `position`           int NULL,
    `flag_main`          tinyint(1) NOT NULL DEFAULT 0,
    `is_default_mockup`  tinyint(1) DEFAULT 0,
    `filename`           varchar(255) NULL,
    `filename_s3`        varchar(255) NULL,
    `added_timestamp`    int NULL,
    `modified_timestamp` int NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ukey` (`ukey`),
    KEY                  `design_id` (`design_id`)
);

CREATE TABLE `osc_cross_sell_print_template_maps`
(
    `id`                      int NOT NULL AUTO_INCREMENT,
    `product_type_variant_id` int NULL,
    `print_template_id`       int NULL,
    `group_product_variant`   varchar(45) NULL,
    `total_mockup`            int NULL,
    `added_timestamp`         int NULL,
    `modified_timestamp`      int NULL,
    PRIMARY KEY (`id`),
    KEY                       `product_type_variant_id` (`product_type_variant_id`)
);

CREATE TABLE `osc_cross_sell_push_mockup_queue`
(
    `id`                      int         NOT NULL AUTO_INCREMENT,
    `ukey`                    varchar(45) NOT NULL DEFAULT '',
    `design_id`               int         NOT NULL DEFAULT 0,
    `product_type_variant_id` int         NOT NULL DEFAULT 0,
    `queue_flag`              tinyint(1) NOT NULL DEFAULT 0,
    `count_mockup`            int                  DEFAULT 0,
    `total_mockup`            int                  DEFAULT 0,
    `error_message`           text,
    `data`                    text,
    `added_timestamp`         int NULL,
    `modified_timestamp`      int NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `ukey_UNIQUE` (`ukey`),
    KEY                       `design_id` (`design_id`)
);