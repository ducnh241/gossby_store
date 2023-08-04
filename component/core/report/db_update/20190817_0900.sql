ALTER TABLE `osc_report_record` ADD COLUMN `extra_key_1` VARCHAR(255) NULL DEFAULT NULL AFTER `report_key`,
ADD COLUMN `extra_key_2` VARCHAR(255) NULL DEFAULT NULL AFTER `extra_key_1`,
ADD COLUMN `extra_key_3` VARCHAR(255) NULL DEFAULT NULL AFTER `extra_key_2`,
ADD INDEX `extra_key_1` (`report_key` ASC, `extra_key_1` ASC),
ADD INDEX `extra_key_2` (`report_key` ASC, `extra_key_2` ASC),
ADD INDEX `extra_key_3` (`report_key` ASC, `extra_key_3` ASC);

UPDATE osc_report_record SET extra_key_3 = report_key, extra_key_1 = SUBSTRING(report_key, 14, LOCATE('/', report_key, 15) - 14), report_key = CONCAT('catalog/item', SUBSTRING(report_key, LOCATE('/', report_key, 15))) WHERE report_key LIKE 'catalog/item/%';
