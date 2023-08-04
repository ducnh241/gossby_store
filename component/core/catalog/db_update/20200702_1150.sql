ALTER TABLE `osc_catalog_product_review` ADD COLUMN `list_photo` text COMMENT 'List review photo' AFTER `photo_extension`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `has_comment` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Check review has comment or not' AFTER `review`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `has_photo` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Check review has photo or not' AFTER `has_comment`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `parent_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'Review parent_id' AFTER `ukey`;
ALTER TABLE `osc_catalog_product_review` ADD COLUMN `role` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '0: Normal user, 1: Admin' AFTER `parent_id`;
ALTER TABLE `osc_catalog_product_review_request` ADD COLUMN `review_id` INT(11) NOT NULL DEFAULT 0 COMMENT 'review_id > 0: request reply to a review' AFTER `ukey`;

ALTER TABLE `osc_catalog_product_review` ADD INDEX `comment` (`parent_id`, `state`, `has_comment`);
ALTER TABLE `osc_catalog_product_review` ADD INDEX `photo` (`parent_id`, `state`, `has_photo`);
ALTER TABLE `osc_catalog_product_review` ADD INDEX `vote` (`parent_id`, `state`, `vote_value`);

CREATE TABLE `osc_catalog_product_review_image` (
  `image_id` int NOT NULL AUTO_INCREMENT,
  `review_id` int NOT NULL,
  `position` int NOT NULL DEFAULT '0',
  `extension` varchar(3) NOT NULL,
  `width` int NOT NULL,
  `height` int NOT NULL,
  `alt` varchar(255) NOT NULL,
  `filename` varchar(255) NOT NULL,
  `added_timestamp` int NOT NULL,
  `modified_timestamp` int NOT NULL,
  PRIMARY KEY (`image_id`),
  UNIQUE KEY `filename_unique` (`review_id`,`filename`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;