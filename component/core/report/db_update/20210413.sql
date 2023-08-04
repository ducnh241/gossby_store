CREATE TABLE `osc_browser_behavior_recorded` (
  `record_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `track_ukey` VARCHAR(27) NOT NULL,
  `page_url` VARCHAR(1000) NOT NULL DEFAULT '',
  `event` VARCHAR(45) NOT NULL DEFAULT '',
  `target` VARCHAR(255) NOT NULL DEFAULT '',
  `pointer` VARCHAR(125) NOT NULL DEFAULT '{}',
  `history` INT UNSIGNED DEFAULT 0 NOT NULL,
  `added_timestamp` INT(10) UNSIGNED NOT NULL,
  PRIMARY KEY (`record_id`),
  INDEX `track_ukey` (`track_ukey` ASC));
