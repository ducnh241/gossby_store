CREATE TABLE `osc_payment_account_sync` (
  `queue_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `queue_flag` tinyint(1) unsigned NOT NULL DEFAULT '1',
  `error_message` varchar(255) DEFAULT NULL,
  `footprint_key` varchar(45) NOT NULL,
  `log_id` int(10) unsigned NOT NULL,
  `order_id` int(10) unsigned NOT NULL,
  `account_id` int(10) unsigned NOT NULL,
  `amount` decimal(20,2) unsigned NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
