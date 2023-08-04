-- insert catalog product
INSERT INTO `{{data_config:database_new_store}}`.`osc_catalog_product`
SELECT *
FROM `{{data_config:database_current_store}}`.`osc_catalog_product` where `{{data_config:database_current_store}}`.`osc_catalog_product`.`product_id` in ({{data_product:list_product_id}});

UPDATE `{{data_config:database_new_store}}`.`osc_catalog_product` SET `solds` = 0, `views` = 0 where `{{data_config:database_current_store}}`.`osc_catalog_product`.`product_id` in ({{data_product:list_product_id}});

-- insert catalog product variant
INSERT INTO `{{data_config:database_new_store}}`.`osc_product_variant`
SELECT *
FROM `{{data_config:database_current_store}}`.`osc_product_variant` where `{{data_config:database_current_store}}`.`osc_product_variant`.`product_id` in ({{data_product:list_product_id}});

UPDATE `{{data_config:database_new_store}}`.`osc_product_variant` SET `quantity` = 0 where `{{data_config:database_current_store}}`.`osc_catalog_product`.`product_id` in ({{data_product:list_product_id}});
-- insert catalog product
INSERT INTO `{{data_config:database_new_store}}`.`osc_catalog_product_image`
SELECT *
FROM `{{data_config:database_current_store}}`.`osc_catalog_product_image` where `{{data_config:database_current_store}}`.`osc_catalog_product_image`.`product_id` in ({{data_product:list_product_id}});

-- insert catalog product review
INSERT INTO `{{data_config:database_new_store}}`.`osc_catalog_product_review`
SELECT *
FROM `{{data_config:database_current_store}}`.`osc_catalog_product_review` where `{{data_config:database_current_store}}`.`osc_catalog_product_review`.`product_id` in ({{data_product:list_product_id}});

-- insert catalog product
INSERT INTO `{{data_config:database_new_store}}`.`osc_catalog_product_review_image`
SELECT *
FROM `{{data_config:database_current_store}}`.`osc_catalog_product_review_image`;

-- insert catalog product
DELETE FROM `{{data_config:database_new_store}}`.`osc_catalog_product_review_image`
WHERE `{{data_config:database_new_store}}`.`osc_catalog_product_review_image`.`review_id` NOT IN
(SELECT `{{data_config:database_new_store}}`.`osc_catalog_product_review`.`record_id` FROM `{{data_config:database_new_store}}`.`osc_catalog_product_review`);

INSERT INTO `{{data_config:database_new_store}}`.`osc_permission_analytics` SELECT * FROM `{{data_config:database_current_store}}`.`osc_permission_analytics`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_permission_masks` SELECT * FROM `{{data_config:database_current_store}}`.`osc_permission_masks`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_member_groups_admin` SELECT * FROM `{{data_config:database_current_store}}`.`osc_member_groups_admin`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_members`  SELECT * FROM `{{data_config:database_current_store}}`.`osc_members` where (`{{data_config:database_current_store}}`.`osc_members`.`member_id` <> '1' and `{{data_config:database_current_store}}`.`osc_members`.`email` <> '{{data_member:email_seller}}');

INSERT INTO `{{data_config:database_new_store}}`.`osc_homepage` SELECT * FROM `{{data_config:database_current_store}}`.`osc_homepage`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_alias` SELECT * FROM `{{data_config:database_current_store}}`.`osc_alias`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_navigation` SELECT * FROM `{{data_config:database_current_store}}`.`osc_navigation`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_catalog_collection` SELECT * FROM `{{data_config:database_current_store}}`.`osc_catalog_collection`;

UPDATE `{{data_config:database_new_store}}`.`osc_homepage` SET `{{data_config:database_new_store}}`.`osc_homepage`.`value` = REPLACE(`{{data_config:database_new_store}}`.`osc_homepage`.`value`, '{{data_config:domain_current_store}}', '{{data_config:domain_new_store}}');

UPDATE `{{data_config:database_new_store}}`.`osc_navigation` SET `{{data_config:database_new_store}}`.`osc_navigation`.items = REPLACE(`{{data_config:database_new_store}}`.`osc_navigation`.items, '{{data_config:domain_current_store}}', '{{data_config:domain_new_store}}') where `{{data_config:database_new_store}}`.`osc_navigation`.`items` LIKE '%{{data_config:domain_current_store}}%';

UPDATE `{{data_config:database_new_store}}`.`osc_catalog_collection` SET `{{data_config:database_new_store}}`.`osc_catalog_collection`.`description` = REPLACE(`{{data_config:database_new_store}}`.`osc_catalog_collection`.`description`, '{{data_config:domain_current_store}}', '{{data_config:domain_new_store}}') where `{{data_config:database_new_store}}`.`osc_catalog_collection`.`description` LIKE '%{{data_config:domain_current_store}}%';

INSERT INTO `{{data_config:database_new_store}}`.`osc_product_type` SELECT * FROM `{{data_config:database_current_store}}`.`osc_product_type`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_product_type_description` SELECT * FROM `{{data_config:database_current_store}}`.`osc_product_type_description`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_product_type_option` SELECT * FROM `{{data_config:database_current_store}}`.`osc_product_type_option`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_product_type_option_value` SELECT * FROM `{{data_config:database_current_store}}`.`osc_product_type_option_value`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_product_type_variant` SELECT * FROM `{{data_config:database_current_store}}`.`osc_product_type_variant`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_product_type_variant_location_price` SELECT * FROM `{{data_config:database_current_store}}`.`osc_product_type_variant_location_price`;

INSERT INTO `{{data_config:database_new_store}}`.`osc_supplier` SELECT * FROM `{{data_config:database_current_store}}`.`osc_supplier`;


-- insert catalog personalized ** Lấy id từ danh sách product
INSERT INTO `{{data_config:database_new_store}}`.`osc_personalized_design`
SELECT *
FROM `{{data_config:database_current_store}}`.`osc_personalized_design` where `{{data_config:database_current_store}}`.`osc_personalized_design`.`design_id` in ({{data_product:list_design_id}});

UPDATE `{{data_config:database_new_store}}`.`osc_personalized_design` SET `{{data_config:database_new_store}}`.`osc_personalized_design`.`design_data` = REPLACE(`{{data_config:database_new_store}}`.`osc_personalized_design`.`design_data`, '{{data_config:domain_current_store}}', '{{data_config:domain_new_store}}');

-- insert library image 2d
INSERT INTO `{{data_config:database_new_store}}`.`osc_catalog_2d_image_library` SELECT * FROM `{{data_config:database_current_store}}`.`osc_catalog_2d_image_library`;


-- copy folder storage
cp -R ..../{{data_config:domain_current_store}}/storage/page .../{{data_config:domain_new_store}}/storage/page
cp -R ..../{{data_config:domain_current_store}}/storage/collection .../{{data_config:domain_new_store}}/storage/collection
cp -R ..../{{data_config:domain_current_store}}/storage/section .../{{data_config:domain_new_store}}/storage/section
cp -R ..../{{data_config:domain_current_store}}/storage/catalog/campaign/imgLib .../{{data_config:domain_new_store}}/storage/catalog/campaign/imgLib
cp -R ..../{{data_config:domain_current_store}}/storage/personalizedDesign/fonts .../{{data_config:domain_new_store}}/storage/personalizedDesign/fonts
