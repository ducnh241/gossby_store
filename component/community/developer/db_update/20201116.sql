-- Note: Chạy query truoc khi merge vao live
-- Them status cho osc_location_country chi lay country status =1;
ALTER TABLE `osc_location_country` ADD `status` ENUM('0','1') DEFAULT '1';
-- Them cac nuoc con thieu vao osc_location_country
INSERT INTO osc_location_country(`country_code`, `country_name`, `zip_formats`, `phone_prefix`, `status`) VALUES ('CU', 'Cuba', NULL, '[\"53\"]', '0');
INSERT INTO osc_location_country(`country_code`, `country_name`, `zip_formats`, `phone_prefix`, `status`) VALUES ('KP', 'North Korea', NULL, '[\"850\"]', '0');
INSERT INTO osc_location_country(`country_code`, `country_name`, `zip_formats`, `phone_prefix`, `status`) VALUES ('CS', 'Serbia And Montenegro', NULL, '[\"381\"]', '0');
INSERT INTO osc_location_country(`country_code`, `country_name`, `zip_formats`, `phone_prefix`, `status`) VALUES ('SY', 'Syria', NULL, '[\"963\"]', '0');
INSERT INTO osc_location_country(`country_code`, `country_name`, `zip_formats`, `phone_prefix`, `status`) VALUES ('UA', 'Ukraine', NULL, '[\"380\"]', '0');
INSERT INTO osc_location_country(`country_code`, `country_name`, `zip_formats`, `phone_prefix`, `status`) VALUES ('AP', 'Asia Pacific Region', NULL, NULL, '0');
INSERT INTO osc_location_country(`country_code`, `country_name`, `zip_formats`, `phone_prefix`, `status`) VALUES ('AC', 'Ascention Island', NULL, '[\"247\"]', '0');
-- Cap nhat ten cac nuoc
UPDATE osc_location_country SET country_name="Eswatini" WHERE country_name ="Swaziland";
UPDATE osc_location_country SET country_name="Caribbean Netherlands" WHERE country_name ="Bonaire, Saint-Eustache et Saba";