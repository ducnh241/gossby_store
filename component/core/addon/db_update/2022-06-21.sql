ALTER TABLE `osc_catalog_product`
    ADD COLUMN `addon_service_data` VARCHAR(500) NULL AFTER `collection_ids`;

DROP TABLE IF EXISTS `osc_addon_service`;
CREATE TABLE `osc_addon_service`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `title`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `type`               tinyint(3) NULL DEFAULT 0 COMMENT '0: single choice; 1:groups; 2: variant',
    `product_type_id`    int(11) NULL DEFAULT 0,
    `data`               mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
    `added_timestamp`    int(11) NULL DEFAULT 0,
    `modified_timestamp` int(11) NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


DROP TABLE IF EXISTS `osc_addon_service_report`;
CREATE TABLE `osc_addon_service_report`
(
    `id`               int(11) NOT NULL AUTO_INCREMENT,
    `shop_id`          int(11) NOT NULL DEFAULT 0,
    `addon_service_id` int(11) NOT NULL DEFAULT 0,
    `product_id`       int(11) NOT NULL DEFAULT 0,
    `order_id`         int(11) NOT NULL DEFAULT 0,
    `item_id`          int(11) NOT NULL DEFAULT 0,
    `added_timestamp`  int(11) NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;
