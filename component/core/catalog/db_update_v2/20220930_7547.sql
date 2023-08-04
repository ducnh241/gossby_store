ALTER TABLE `osc_catalog_product_image`
    ADD `thumbnail` VARCHAR(255) NULL
    AFTER `filename`,
    ADD `duration` INT(11) NOT NULL DEFAULT 0
    AFTER `height`;

ALTER TABLE `osc_product_variant`
    ADD `video_id` VARCHAR(255) NULL
	AFTER `image_id`;
