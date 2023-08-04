
DROP TABLE IF EXISTS `osc_auto_ab_product_config`;
CREATE TABLE osc_auto_ab_product_config
(
    id                 INT NOT NULL AUTO_INCREMENT,
    ukey               VARCHAR(15) NOT NULL DEFAULT '',
    title              VARCHAR(500) NOT NULL DEFAULT '',
    begin_time         INT NOT NULL DEFAULT 0,
    finish_time        INT NOT NULL DEFAULT 0,
    status             TINYINT NULL DEFAULT 0,
    added_timestamp    INT NOT NULL DEFAULT 0,
    modified_timestamp INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY `ab_product_config_ukey` (`ukey`)
);

DROP TABLE IF EXISTS `osc_auto_ab_product_map`;
CREATE TABLE osc_auto_ab_product_map
(
    id                 INT NOT NULL AUTO_INCREMENT,
    config_id          INT NOT NULL DEFAULT 0,
    product_id         INT NOT NULL DEFAULT 0,
    acquisition        INT NOT NULL DEFAULT 0,
    added_timestamp    INT NOT NULL DEFAULT 0,
    modified_timestamp INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    INDEX              config_id (config_id ASC)
);

DROP TABLE IF EXISTS `osc_auto_ab_product_view_tracking`;
CREATE TABLE osc_auto_ab_product_view_tracking
(
    id                 INT NOT NULL AUTO_INCREMENT,
    config_id          INT NOT NULL DEFAULT 0,
    track_ukey         VARCHAR(27) NOT NULL DEFAULT '',
    product_id         INT NOT NULL DEFAULT 0,
    date               INT(8) NOT NULL DEFAULT 0,
    added_timestamp    INT NOT NULL DEFAULT 0,
    modified_timestamp INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    UNIQUE KEY `product_tracking_unique` (`config_id`,`track_ukey`,`product_id`,`date`)
);

DROP TABLE IF EXISTS `osc_auto_ab_product_order_tracking`;
CREATE TABLE osc_auto_ab_product_order_tracking
(
    id                      INT NOT NULL AUTO_INCREMENT,
    config_id               INT NOT NULL DEFAULT 0,
    product_type_variant_id INT NOT NULL DEFAULT 0,
    product_variant_id      INT NOT NULL DEFAULT 0,
    product_id              INT NOT NULL DEFAULT 0,
    order_item_id           INT NOT NULL DEFAULT 0,
    order_id                INT NOT NULL DEFAULT 0,
    revenue                 INT NOT NULL DEFAULT 0,
    quantity                INT NOT NULL DEFAULT 0,
    added_timestamp         INT NOT NULL DEFAULT 0,
    modified_timestamp      INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY                       `item_INDEX` (`config_id`,`product_type_variant_id`,`product_variant_id`,`product_id`)
);

DROP TABLE IF EXISTS `osc_auto_ab_product_report`;
CREATE TABLE osc_auto_ab_product_report
(
    id                      INT NOT NULL AUTO_INCREMENT,
    config_id               INT NOT NULL DEFAULT 0,
    product_id              INT NOT NULL DEFAULT 0,
    unique_visitor          INT NOT NULL DEFAULT 0,
    page_view               INT NOT NULL DEFAULT 0,
    total_order             INT NOT NULL DEFAULT 0,
    quantity                INT NOT NULL DEFAULT 0,
    revenue                 INT NOT NULL DEFAULT 0,
    date                    INT(8) NOT NULL DEFAULT 0,
    added_timestamp         INT NOT NULL DEFAULT 0,
    modified_timestamp      INT NOT NULL DEFAULT 0,
    PRIMARY KEY (id),
    KEY `config_id_INDEX` (`config_id`) USING BTREE,
    KEY `product_id_INDEX` (`product_id`) USING BTREE
);

