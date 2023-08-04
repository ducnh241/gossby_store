ALTER TABLE `osc_tracking` CHANGE COLUMN `ukey` `ukey` VARCHAR(27) NOT NULL;
ALTER TABLE `osc_tracking_footprint` ADD COLUMN `track_ukey` VARCHAR(27) NOT NULL AFTER `footprint_id`, ADD INDEX `track_ukey` (`track_ukey` ASC);
ALTER TABLE `osc_tracking_footprint` CHANGE COLUMN `referer` `referer` VARCHAR(255) NULL DEFAULT NULL ;
UPDATE `osc_tracking_footprint` f SET `track_ukey` = (SELECT `ukey` FROM `osc_tracking` t WHERE t.track_id = f.track_id) WHERE f.footprint_id > 0;
ALTER TABLE `osc_tracking_footprint` DROP COLUMN `track_id`;
