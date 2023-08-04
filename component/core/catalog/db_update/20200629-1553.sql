CREATE TABLE `osc_catalog_item_overflow_queue` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_flag` tinyint(1) NOT NULL DEFAULT '1',
  `error_message` varchar(255) DEFAULT NULL,
  `data` text,
  `added_timestamp` int(10) NOT NULL,
  `modified_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `osc_catalog_order_item` ADD COLUMN `design_alert_flag` INT(1) NULL DEFAULT 0 AFTER `design_url`;