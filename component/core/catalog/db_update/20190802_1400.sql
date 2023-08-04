CREATE TABLE `osc_catalog_order_template_export` (
  `template_id` INT NOT NULL AUTO_INCREMENT,
  `template_name` VARCHAR(45) NOT NULL,
  `list_key` JSON NOT NULL,
  `added_timestamp` INT(11) NOT NULL,
  PRIMARY KEY (`template_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;