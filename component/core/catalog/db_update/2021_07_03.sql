ALTER TABLE osc_alias CHANGE COLUMN lang_key lang_key VARCHAR(5) NOT NULL ;
UPDATE `osc_alias` SET `lang_key` = 'en-us'