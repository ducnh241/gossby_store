CREATE TABLE `osc_dmca` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `ukey` VARCHAR(32) NOT NULL,
  `data` MEDIUMTEXT NOT NULL,
  `form` VARCHAR(45) NOT NULL,
  `added_timestamp` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC));
