CREATE TABLE `osc_catalog_order_process` (
  `record_id` INT(11) NOT NULL AUTO_INCREMENT,
  `member_id` INT(11) NOT NULL,
  `order_id` INT(11) NOT NULL,
  `line_items` TEXT NOT NULL,
  `quantity` INT(11) NOT NULL,
  `added_timestamp` INT(10) NOT NULL,
  `modified_timestamp` INT(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  INDEX `order_id` (`order_id` ASC));


ALTER TABLE `osc_catalog_order_item`
ADD COLUMN `process_quantity` INT(11) NOT NULL DEFAULT '0' AFTER `refunded_quantity`;