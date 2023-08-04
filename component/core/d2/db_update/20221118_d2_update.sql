

/* define resource order design */
DROP TABLE IF EXISTS `osc_d2_resource`;
CREATE TABLE `osc_d2_resource`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `design_id`          int(10) unsigned NOT NULL default 0,
    `resource_url`       varchar(255) NOT NULL default '',
    `member_id`          int(11) unsigned NOT NULL default 0,
    `added_timestamp`    int(11) NOT NULL default 0,
    `modified_timestamp` int(11) NOT NULL default 0,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `design_id_INDEX` (`design_id`)
) ENGINE=InnoDB;

/* condition define resource order design */
DROP TABLE IF EXISTS `osc_d2_condition`;
CREATE TABLE `osc_d2_condition`
(
    `id`                 int(11) NOT NULL AUTO_INCREMENT,
    `resource_id`        int(10) unsigned NOT NULL default 0,
    `condition_key`                varchar(255),
    `condition_value`              varchar(255),
    `member_id`          int(11) unsigned NOT NULL default 0,
    `added_timestamp`    int(11) NOT NULL default 0,
    `modified_timestamp` int(11) NOT NULL default 0,
    PRIMARY KEY (`id`) USING BTREE,
    KEY `resource_id_INDEX` (`resource_id`)
) ENGINE=InnoDB;
