ALTER TABLE `osc_catalog_order_thankyou_email` CHANGE COLUMN `error` `error_message` VARCHAR(255) NULL DEFAULT NULL;
ALTER TABLE `osc_catalog_order_thankyou_email` ADD COLUMN `clicks` INT NOT NULL DEFAULT 0 AFTER `opened_flag`;
CREATE TABLE `osc_catalog_order_thankyou_email_clicked` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_id` int(11) NOT NULL,
  `referer_url` varchar(255) NULL DEFAULT NULL,
  `clicked_url` varchar(255) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `email_id` (`email_id`),
  CONSTRAINT `email_id_4324asdads` FOREIGN KEY (`email_id`) REFERENCES `osc_catalog_order_thankyou_email` (`email_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


