ALTER TABLE `osc_filter_tag`
    ADD COLUMN `is_break_down_keyword` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_show_filter`;

INSERT INTO `osc_core_setting` (`setting_key`,`setting_value`,`added_timestamp`,`modified_timestamp`) VALUES ('filter/autoTag/settingField','{\"product_type\":{\"value\":\"0\",\"text\":\"Product type\"},\"variant\":{\"value\":\"0\",\"text\":\"Variant\"},\"quote\":{\"value\":\"0\",\"text\":\"Quote\"},\"topic\":{\"value\":\"0\",\"text\":\"Topic\"},\"collections\":{\"value\":\"0\",\"text\":\"Collections\"},\"description\":{\"value\":\"0\",\"text\":\"Description\"},\"meta_title\":{\"value\":\"0\",\"text\":\"Meta title\"},\"meta_slug\":{\"value\":\"0\",\"text\":\"Meta slug\"},\"meta_description\":{\"value\":\"0\",\"text\":\"Meta description\"},\"meta_keyword\":{\"value\":\"0\",\"text\":\"Meta Title\"}}',UNIX_TIMESTAMP(),UNIX_TIMESTAMP());

CREATE TABLE `osc_filter_auto_tag`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `product_id`         int(11) NOT NULL DEFAULT 0,
    `auto_tag`           varchar(255) NOT NULL DEFAULT '',
    `deleted_tag`         varchar(255),
    `new_tag`            varchar(255),
    `added_by`           int(11) NOT NULL DEFAULT 0,
    `modified_by`        int(11) NOT NULL DEFAULT 0,
    `added_timestamp`    int(10) NOT NULL DEFAULT 0,
    `modified_timestamp` int(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`)   USING BTREE,
    INDEX                `auto_tag_product_id`(`product_id`) USING BTREE
) ENGINE = InnoDB;