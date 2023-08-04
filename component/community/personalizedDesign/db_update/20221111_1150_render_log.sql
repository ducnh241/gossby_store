DROP TABLE IF EXISTS `osc_personalized_design_rerender_log`;
CREATE TABLE `osc_personalized_design_rerender_log` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `member_id` INT(11) NOT NULL DEFAULT 0,
    `order_id` INT(11) NOT NULL DEFAULT 0,
    `order_item_id` INT(11) NOT NULL DEFAULT 0,
    `design_id` INT(11) NOT NULL DEFAULT 0,
    `status` TINYINT(1) NOT NULL DEFAULT 0,
    `message` TEXT,
    `added_timestamp` INT(11) NOT NULL DEFAULT 0,
    `modified_timestamp` INT(11) NOT NULL DEFAULT 0,
    PRIMARY KEY (`id`),
    UNIQUE KEY `rerender_order_id_UNIQUE` (`order_id`,`order_item_id`,`design_id`)
) ENGINE=InnoDB;