-- Run all store
ALTER TABLE `osc_members` ADD COLUMN `has_order` tinyint(1) DEFAULT '0' AFTER `sref_type`;