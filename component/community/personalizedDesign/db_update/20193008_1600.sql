CREATE TABLE `osc_personalized_design` (
  `design_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `ukey` VARCHAR(27) NOT NULL,
  `title` VARCHAR(45) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin' NOT NULL,
  `design_data` LONGTEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin' NOT NULL,
  `added_timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `modified_timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`design_id`),
  UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC));

