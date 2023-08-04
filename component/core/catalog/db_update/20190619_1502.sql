ALTER TABLE `osc_setting`
CHANGE COLUMN `input_type` `input_type` ENUM('input', 'onoff', 'textarea', 'editor', 'select', 'multi_select', 'checkbox', 'radio', 'line', 'desc', 'heading', 'address', 'image', 'navigation', 'file', 'collection', '') NOT NULL DEFAULT '' ;

INSERT INTO `osc_setting`
(`tab_key`,`group_key`,`item_key`,`setting_key`,`setting_value`,`input_type`,`input_data`,`empty_allowed_flag`,`validate_callback`,`validate_callback_params`,`label`,`description`,`theme_new_row_flag`,`position`)
VALUES
('feed', '', '', '', '', '', '', '0', '', '', 'Feed', '', '1', '0');

INSERT INTO `osc_setting`
(`tab_key`,`group_key`,`item_key`,`setting_key`,`setting_value`,`input_type`,`input_data`,`empty_allowed_flag`,`validate_callback`,`validate_callback_params`,`label`,`description`,`theme_new_row_flag`,`position`)
VALUES
('feed', 'feed_facebook', '', '', '', '', '', '0', '', '', 'Feed Facebook', '', '1', '1');

INSERT INTO `osc_setting`
(`tab_key`,`group_key`,`item_key`,`setting_key`,`setting_value`,`input_type`,`input_data`,`empty_allowed_flag`,`validate_callback`,`validate_callback_params`,`label`,`description`,`theme_new_row_flag`,`position`)
VALUES
('feed', 'feed_facebook', 'feed_facebook_rss', 'feed/facebook_rss', '1', 'collection', '', '0', '', '', 'Select Collection', 'Select a Collection for feed facebook', '1', '1');


INSERT INTO `osc_setting`
(`tab_key`,`group_key`,`item_key`,`setting_key`,`setting_value`,`input_type`,`input_data`,`empty_allowed_flag`,`validate_callback`,`validate_callback_params`,`label`,`description`,`theme_new_row_flag`,`position`)
VALUES
('feed', 'feed_google', '', '', '', '', '', '0', '', '', 'Feed Google', '', '1', '1');

INSERT INTO `osc_setting`
(`tab_key`,`group_key`,`item_key`,`setting_key`,`setting_value`,`input_type`,`input_data`,`empty_allowed_flag`,`validate_callback`,`validate_callback_params`,`label`,`description`,`theme_new_row_flag`,`position`)
VALUES
('feed', 'feed_google', 'feed_google_rss', 'feed/google_rss', '1', 'collection', '', '0', '', '', 'Select Collection', 'Select a Collection for feed google', '1', '1');