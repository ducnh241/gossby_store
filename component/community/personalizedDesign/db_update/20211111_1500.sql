ALTER TABLE `osc_personalized_design`
    ADD COLUMN `is_draft` TINYINT(1) NULL DEFAULT 0 AFTER `type_flag`;
