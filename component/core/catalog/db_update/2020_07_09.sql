ALTER TABLE osc_catalog_order ADD COLUMN member_hold INT(11) NOT NULL DEFAULT 0 AFTER master_lock_flag;