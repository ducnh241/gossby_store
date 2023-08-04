CREATE TABLE `osc_personalized_design_analytic_process_queue` (
  `record_id` bigint(22) unsigned NOT NULL AUTO_INCREMENT,
  `queue_data` longtext NOT NULL,
  `locked_key` varchar(50) NOT NULL,
  `locked_timestamp` int(10) unsigned NOT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`record_id`)
) ENGINE=InnoDB;

CREATE TABLE `osc_personalized_design_analytic` (
  `record_id` BIGINT(22) UNSIGNED NOT NULL AUTO_INCREMENT,
  `design_id` BIGINT(22) UNSIGNED NOT NULL,
  `option_key` VARCHAR(45) NOT NULL,
  `value_key` VARCHAR(45) NOT NULL,
  `value_hash` VARCHAR(255) NOT NULL,
  `layer_name` VARCHAR(255) NOT NULL,
  `form_name` VARCHAR(255) NOT NULL,
  `parsed_value` TEXT NOT NULL,
  `counter` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`record_id`),
  UNIQUE INDEX `unique` (`design_id` ASC, `option_key` ASC, `value_key` ASC));
