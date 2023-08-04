ALTER TABLE `osc_post`
    ADD COLUMN `footer_banner_image` VARCHAR(255) AFTER `description`;

ALTER TABLE `osc_post`
    ADD COLUMN `footer_banner_url` VARCHAR(255) AFTER `description`;
