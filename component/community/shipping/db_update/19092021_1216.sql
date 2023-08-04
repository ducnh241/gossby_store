CREATE TABLE `osc_shipping_methods`
(
    `id`                 INT          NOT NULL AUTO_INCREMENT,
    `shipping_name`      VARCHAR(255) NOT NULL DEFAULT '',
    `shipping_key`       VARCHAR(255) NOT NULL DEFAULT '',
    `shipping_status`    TINYINT(1) NOT NULL DEFAULT 1,
    `is_default`         TINYINT(1) NOT NULL DEFAULT 0,
    `added_timestamp`    INT NULL,
    `modified_timestamp` INT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `key_UNIQUE` (`shipping_key` ASC)
);



CREATE TABLE `osc_shipping_rate`
(
    `id`                      INT NOT NULL AUTO_INCREMENT,
    `shipping_method_id`      INT UNSIGNED NULL,
    `group_id`                VARCHAR(255) NULL,
    `child_group`             VARCHAR(255) NULL,
    `location_data`           VARCHAR(2000) NULL,
    `location_parsed`         TEXT NULL,
    `product_type_id`         INT NULL,
    `product_type_variant_id` INT NULL,
    `rate_type`               TINYINT(1) NOT NULL DEFAULT 1,
    `quantity_rate`           VARCHAR(2000) NULL,
    `dynamic_rate`            VARCHAR(2000) NULL,
    `added_timestamp`         INT NULL,
    `modified_timestamp`      INT NULL,
    PRIMARY KEY (`id`),
    INDEX                     `producty_type_id` (`product_type_id` ASC),
    INDEX                     `producty_type_variantr_id` (`product_type_variant_id` ASC)
);


CREATE TABLE `osc_shipping_delivery_time`
(
    `id`                      INT  NOT NULL AUTO_INCREMENT,
    `shipping_method_id`      INT UNSIGNED NULL,
    `group_id`                VARCHAR(255) NULL,
    `child_group`             VARCHAR(255) NULL,
    `location_data`           VARCHAR(2000) NULL,
    `location_parsed`         TEXT NOT NULL,
    `product_type_id`         INT NULL,
    `product_type_variant_id` INT NULL,
    `process_time`            INT NULL,
    `estimate_time`           INT NULL,
    `added_timestamp`         INT NULL,
    `modified_timestamp`      INT NULL,
    PRIMARY KEY (`id`),
    INDEX                     `product_type_id` (`product_type_id` ASC),
    INDEX                     `product_type_variant_id` (`product_type_variant_id` ASC)
);


CREATE TABLE `osc_shipping_pack_rate`
(
    `id`                      INT NOT NULL AUTO_INCREMENT,
    `shipping_method_id`      INT UNSIGNED NULL,
    `pack_key`                VARCHAR(255) NULL,
    `group_id`                VARCHAR(255) NULL,
    `child_group`             VARCHAR(255) NULL,
    `location_data`           VARCHAR(2000) NULL,
    `location_parsed`         TEXT NULL,
    `product_type_id`         INT NULL,
    `product_type_variant_id` INT NULL,
    `rate_type`               TINYINT(1) NOT NULL DEFAULT 1,
    `quantity_rate`           VARCHAR(2000) NULL,
    `dynamic_rate`            VARCHAR(2000) NULL,
    `added_timestamp`         INT NULL,
    `modified_timestamp`      INT NULL,
    PRIMARY KEY (`id`),
    INDEX                     `producty_type_id` (`product_type_id` ASC),
    INDEX                     `producty_type_variantr_id` (`product_type_variant_id` ASC)
);