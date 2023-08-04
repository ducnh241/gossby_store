CREATE TABLE `osc_print_template_beta` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` VARCHAR(500) NOT NULL DEFAULT '',
  `config` TEXT NULL DEFAULT NULL,
  `description` VARCHAR(1000) NULL DEFAULT NULL,
  `member_id` INT(11) NOT NULL DEFAULT 0,
  `added_timestamp` INT(11) UNSIGNED NOT NULL,
  `modified_timestamp` INT(11) UNSIGNED NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB;

--run store and master
ALTER TABLE `osc_supplier` ADD COLUMN `member_id` int NOT NULL DEFAULT 0 AFTER `status`;