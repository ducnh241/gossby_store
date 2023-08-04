CREATE TABLE `osc_post_office_subscriber` (
  `subscriber_id` INT NOT NULL AUTO_INCREMENT,
  `email` VARCHAR(255) NOT NULL,
  `token` VARCHAR(27) NOT NULL,
  `newsletter` TINYINT(1) NOT NULL DEFAULT 1,
  `added_timestamp` INT(10) NOT NULL DEFAULT 0,
  `modified_timestamp` INT(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`subscriber_id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC),
  UNIQUE INDEX `token_UNIQUE` (`token` ASC));
