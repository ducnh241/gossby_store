CREATE TABLE `osc_personalized_design_config` (
  `config_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `url` VARCHAR(255) CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin' NOT NULL,
  `slice_data` LONGTEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `meta_data` LONGTEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin',
  `added_timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `modified_timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`config_id`),
  UNIQUE INDEX `url_UNIQUE` (`url` ASC)
);

CREATE TABLE `osc_personalized_design_tmp` (
  `record_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `design_id` INT(10) UNSIGNED,
  `design_data` LONGTEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin',
  `status` tinyint(1) NOT NULL DEFAULT 0,
  `meta_data` TEXT CHARACTER SET 'utf8mb4' COLLATE 'utf8mb4_bin',
  `added_timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  `modified_timestamp` INT(10) UNSIGNED NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`)
);