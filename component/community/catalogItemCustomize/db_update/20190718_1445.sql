CREATE TABLE `osc_catalog_item_customize` (
  `item_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ukey` VARCHAR(27) NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `config` TEXT NOT NULL,
  `added_timestamp` INT(10) NOT NULL,
  `modified_timestamp` INT(10) NOT NULL,
  PRIMARY KEY (`item_id`),
  UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC));