CREATE TABLE `osc_catalog_order_thankyou_email` (
  `email_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(27) NOT NULL,
  `order_id` int(11) NOT NULL,
  `discount_code_id` int(11) NOT NULL,
  `email_state` enum('queue','sending','sent','error') NOT NULL DEFAULT 'queue',
  `error` varchar(255) DEFAULT NULL,
  `opened_flag` tinyint(1) NOT NULL DEFAULT '0',
  `added_timestamp` int(10) NOT NULL DEFAULT '0',
  `sent_timestamp` int(10) NOT NULL DEFAULT '0',
  PRIMARY KEY (`email_id`),
  UNIQUE KEY `order_id_UNIQUE` (`order_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `discount_code_id_UNIQUE` (`discount_code_id`),
  CONSTRAINT `discount_code_id_1234` FOREIGN KEY (`discount_code_id`) REFERENCES `osc_catalog_discount_code` (`discount_code_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `order_id_1234` FOREIGN KEY (`order_id`) REFERENCES `osc_catalog_order` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

ALTER TABLE `osc_catalog_checkout_abandoned_email` 
CHANGE COLUMN `discount_code` `discount_code_id` INT(11) NULL DEFAULT NULL ,
DROP INDEX `discount_code_UNIQUE` ,
ADD UNIQUE INDEX `discount_code_id_UNIQUE` (`discount_code_id` ASC);

ALTER TABLE `osc_catalog_checkout_abandoned_email` 
ADD CONSTRAINT `discount_code_id_q324412`
  FOREIGN KEY (`discount_code_id`)
  REFERENCES `osc_catalog_discount_code` (`discount_code_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;