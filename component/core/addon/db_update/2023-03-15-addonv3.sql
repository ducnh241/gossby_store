DROP TABLE IF EXISTS `osc_addon_service`;
CREATE TABLE `osc_addon_service`
(
    `id`                                   int(11) NOT NULL AUTO_INCREMENT,
    `title`                                varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `type`                                 tinyint(3) NULL DEFAULT 0 COMMENT '0: single choice; 1:groups; 2: variant',
    `product_type_id`                      int(11) NULL DEFAULT 0,
    `auto_apply_for_product_type_variants` varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `status`                               tinyint(1) NULL DEFAULT 0,
    `ab_test_enable`                       tinyint(1) NULL DEFAULT 0,
    `ab_test_start_timestamp`              int(10) NULL DEFAULT NULL,
    `ab_test_end_timestamp`                int(10) NULL DEFAULT NULL,
    `start_timestamp`                      int(11) NULL DEFAULT 0,
    `end_timestamp`                        int(11) NULL DEFAULT 0,
    `added_timestamp`                      int(11) NULL DEFAULT 0,
    `modified_timestamp`                   int(11) NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `osc_addon_service_report`;
CREATE TABLE `osc_addon_service_report`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `addon_id`           int(11) NOT NULL DEFAULT 0,
    `addon_version_id`   int(11) NOT NULL DEFAULT 0,
    `version_name`       varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
    `traffic`            int(11) NOT NULL DEFAULT 0,
    `unique_visitor`     int(11) NOT NULL DEFAULT 0,
    `page_view`          int(11) NOT NULL DEFAULT 0,
    `total_order`        int(11) NOT NULL DEFAULT 0,
    `total_sale`         int(11) NOT NULL DEFAULT 0,
    `total_quantity`     int(11) NOT NULL DEFAULT 0,
    `revenue`            int(11) NOT NULL DEFAULT 0,
    `date`               int(8) NOT NULL DEFAULT 0,
    `added_timestamp`    int(11) NOT NULL DEFAULT 0,
    `modified_timestamp` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX                `addon__id_INDEX`(`addon_id`) USING BTREE,
    INDEX                `addon_version_INDEX`(`addon_version_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `osc_addon_service_report_order`;
CREATE TABLE `osc_addon_service_report_order`
(
    `id`                      int(11) NOT NULL AUTO_INCREMENT,
    `addon_id`                int(11) NOT NULL DEFAULT 0,
    `addon_version_id`        int(11) NOT NULL DEFAULT 0,
    `product_id`              int(11) NOT NULL DEFAULT 0,
    `product_type_variant_id` int(11) NOT NULL DEFAULT 0,
    `product_variant_id`      int(11) NOT NULL DEFAULT 0,
    `order_item_id`           int(11) NOT NULL DEFAULT 0,
    `order_id`                int(11) NOT NULL DEFAULT 0,
    `revenue`                 int(11) NOT NULL DEFAULT 0,
    `quantity`                int(11) NOT NULL DEFAULT 0,
    `sale`                    int(11) NOT NULL DEFAULT 0,
    `date`                    int(8) NOT NULL DEFAULT 0,
    `added_timestamp`         int(11) NOT NULL DEFAULT 0,
    `modified_timestamp`      int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX                     `item_INDEX`(`addon_id`, `addon_version_id`, `product_type_variant_id`, `product_id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `osc_addon_service_report_view`;
CREATE TABLE `osc_addon_service_report_view`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `track_ukey`         varchar(27) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '',
    `addon_id`           int(11) NOT NULL DEFAULT 0,
    `addon_version_id`   int(11) NOT NULL DEFAULT 0,
    `date`               int(8) NOT NULL DEFAULT 0,
    `added_timestamp`    int(11) NOT NULL DEFAULT 0,
    `modified_timestamp` int(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE,
    UNIQUE INDEX `addon_tracking_unique`(`addon_id`, `track_ukey`, `addon_version_id`, `date`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

DROP TABLE IF EXISTS `osc_addon_service_version`;
CREATE TABLE `osc_addon_service_version`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `addon_id`           int(11) NULL DEFAULT 0,
    `title`              varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `traffic`            int(11) NULL DEFAULT 0,
    `display_area`       tinyint(1) NOT NULL DEFAULT 0,
    `is_default_version` tinyint(1) NOT NULL DEFAULT 0,
    `is_hide`            int(1) NOT NULL DEFAULT 0,
    `images`             varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `videos`             varchar(2000) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `data`               mediumtext CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
    `added_timestamp`    int(11) NULL DEFAULT 0,
    `modified_timestamp` int(11) NULL DEFAULT 0,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;


ALTER TABLE `osc_addon_service_report` ADD COLUMN `distributed` int(11) NOT NULL DEFAULT 0 AFTER `version_name`;

ALTER TABLE `osc_addon_service_report` CHANGE unique_visitor approached_unique int(11) NOT NULL DEFAULT 0;
ALTER TABLE `osc_addon_service_report` CHANGE page_view approached int(11) NOT NULL DEFAULT 0;

ALTER TABLE `osc_addon_service_report` DROP COLUMN `traffic`;
ALTER TABLE `osc_addon_service_report` DROP COLUMN `total_quantity`;
ALTER TABLE `osc_addon_service_report_order` DROP COLUMN `quantity`;
ALTER TABLE `osc_addon_service_report_view` ADD COLUMN `increment_unique` tinyint(1) NOT NULL DEFAULT 0 AFTER `date`;