DROP TABLE IF EXISTS `osc_facebook_pixel_log`;
CREATE TABLE `osc_facebook_pixel_log`
(
    `id`                 int NOT NULL AUTO_INCREMENT,
    `pixel_id`           varchar(20) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
    `order_id`           int NULL DEFAULT NULL,
    `type`               tinyint(1) NULL DEFAULT NULL COMMENT '0:before fire event/1:after fire event',
    `data`               text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
    `added_timestamp`    int NOT NULL,
    `modified_timestamp` int NOT NULL,
    PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;