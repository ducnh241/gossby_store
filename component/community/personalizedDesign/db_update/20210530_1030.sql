ALTER TABLE `osc_personalized_design_sync`
ADD COLUMN `requeue` TINYINT(1) NULL DEFAULT '0' AFTER `sync_error`,
ADD COLUMN `next_timestamp` INT NULL DEFAULT '0' AFTER `requeue`;
