ALTER TABLE `osc_catalog_product_image`
ADD COLUMN `ukey` VARCHAR(100) NULL DEFAULT NULL AFTER `product_id`,
ADD UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC);

ALTER TABLE `osc_catalog_product_image` ADD COLUMN `flag_main` TINYINT(1) NULL DEFAULT 0 AFTER `position`;

ALTER TABLE osc_mastersync_queue ADD COLUMN priority tinyint(1) DEFAULT 0 AFTER syncing_flag;

ALTER TABLE osc_catalog_order_item MODIFY COLUMN tax_value smallint(3) NULL DEFAULT NULL AFTER price;
ALTER TABLE osc_catalog_order_item MODIFY COLUMN design_url varchar(1000) NULL DEFAULT NULL AFTER price;
ALTER TABLE osc_catalog_order_item ADD COLUMN order_item_meta_id int(11) NOT NULL DEFAULT 0 AFTER variant_id;

ALTER TABLE osc_catalog_cart_item MODIFY COLUMN tax_value smallint(3) NULL DEFAULT NULL AFTER price;

-- ----------------------------
-- Table structure for osc_catalog_order_item_meta
-- ----------------------------
DROP TABLE IF EXISTS `osc_catalog_order_item_meta`;
CREATE TABLE `osc_catalog_order_item_meta` (
  `meta_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `custom_data` longtext DEFAULT NULL,
  `added_timestamp` int(10) unsigned NOT NULL,
  `modified_timestamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`meta_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for osc_catalog_product
-- ----------------------------
DROP TABLE IF EXISTS `osc_catalog_product_v2`;
CREATE TABLE `osc_catalog_product_v2` (
  `product_id` int(11) NOT NULL AUTO_INCREMENT,
  `upc` varchar(15) DEFAULT NULL,
  `sku` varchar(15) DEFAULT NULL,
  `member_id` int(11) NOT NULL,
  `position_index` int(10) DEFAULT 0,
  `slug` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `topic` varchar(500) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `content` mediumtext DEFAULT NULL,
  `product_type` varchar(255) DEFAULT NULL,
  `vendor` varchar(255) DEFAULT NULL,
  `price` int(11) NOT NULL,
  `compare_at_price` int(11) NOT NULL,
  `discarded` tinyint(1) NOT NULL DEFAULT 0,
  `listing` tinyint(1) unsigned NOT NULL DEFAULT 1,
  `solds` int(11) NOT NULL DEFAULT 0,
  `views` int(11) NOT NULL DEFAULT 0,
  `tags` text DEFAULT NULL,
  `meta_tags` text DEFAULT NULL,
  `meta_data` longtext DEFAULT NULL,
  `options` text DEFAULT NULL,
  `collection_ids` varchar(255) DEFAULT NULL,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  `master_lock_flag` tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`product_id`) USING BTREE,
  UNIQUE KEY `sku_UNIQUE` (`sku`) USING BTREE,
  UNIQUE KEY `upc_UNIQUE` (`upc`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Table structure for osc_product_variant
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_variant`;
CREATE TABLE `osc_product_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `image_id` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `sku` varchar(255) DEFAULT NULL,
  `design_id` varchar(255) DEFAULT NULL,
  `options` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `price` int(11) DEFAULT NULL,
  `compare_at_price` int(11) DEFAULT 0,
  `cost` int(11) DEFAULT 0,
  `track_quantity` tinyint(1) DEFAULT 1,
  `overselling` tinyint(1) DEFAULT 0,
  `quantity` int(11) DEFAULT 0,
  `require_shipping` tinyint(1) DEFAULT 0,
  `require_packing` tinyint(1) DEFAULT 0,
  `weight` int(11) DEFAULT 0,
  `weight_unit` enum('kg','g','oz','lb') CHARACTER SET utf8 DEFAULT 'g',
  `keep_flat` tinyint(1) DEFAULT 1,
  `dimension_width` int(11) DEFAULT 0,
  `dimension_height` int(11) DEFAULT 0,
  `dimension_length` int(11) DEFAULT 0,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  `meta_data` text DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `sku_UNIQUE` (`sku`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


INSERT INTO osc_product_variant (product_id, image_id, sku,design_id, options,price, compare_at_price,cost, track_quantity, overselling, quantity, require_shipping, require_packing, weight, weight_unit, keep_flat, dimension_width, dimension_height , dimension_length, added_timestamp, modified_timestamp)
SELECT product_id, image_id, sku,design_id, CONCAT('{','"option1":"',option1,'","option2":"',option2,'","option3":"',option3,'"}'),price, compare_at_price,cost, track_quantity, overselling, quantity, require_shipping, require_packing, weight, weight_unit, keep_flat, dimension_width, dimension_height , dimension_length, added_timestamp, modified_timestamp
FROM osc_catalog_product_variant WHERE design_id != '' AND design_id is not null;

-- ----------------------------
-- Table structure for osc_product_type_variant_location_price
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_variant_location_price`;
CREATE TABLE `osc_product_type_variant_location_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `product_type_variant_id` int(11) unsigned NOT NULL DEFAULT 0,
  `location_data` varchar(255) NOT NULL DEFAULT '',
  `price` int(11) unsigned NOT NULL DEFAULT 0,
  `compare_at_price` int(11) unsigned NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ----------------------------
-- Table structure for osc_catalog_product_pack
-- ----------------------------
DROP TABLE IF EXISTS `osc_catalog_product_pack`;
CREATE TABLE `osc_catalog_product_pack` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type_id` int(11) unsigned NOT NULL DEFAULT 0,
  `title` varchar(255) NOT NULL DEFAULT '',
  `quantity` int(11) unsigned NOT NULL DEFAULT 0,
  `discount_type` tinyint(1) NOT NULL DEFAULT 0,
  `discount_value` int(11) unsigned NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of osc_catalog_product_pack
-- ----------------------------
BEGIN;
INSERT INTO `osc_catalog_product_pack` VALUES (1,30,'Pack 1',1,0,0,1603111347,1603111347),(2,30,'Pack 3',3,0,1000,1603111357,1603111357),(3,30,'Pack 5',5,0,1500,1603111365,1603111365),(4,30,'Pack 10',10,0,1500,1603111373,1603111373),(5,31,'Pack 1',1,0,0,1603111403,1603111403),(6,31,'Pack 3',3,0,1000,1603111411,1603111411),(7,31,'Pack 5',5,0,1500,1603111419,1603111419),(8,31,'Pack 10',10,0,1500,1603111430,1603111430);
COMMIT;

-- ----------------------------
-- Table structure for osc_catalog_tax
-- ----------------------------
DROP TABLE IF EXISTS `osc_catalog_tax`;
CREATE TABLE `osc_catalog_tax` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type_id` int(11) unsigned NOT NULL DEFAULT 0,
  `departure_location_data` varchar(255) NOT NULL DEFAULT '',
  `destination_location_data` varchar(255) NOT NULL DEFAULT '',
  `tax_value` int(11) unsigned NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`),
  UNIQUE KEY `item_INDEX` (`product_type_id`,`departure_location_data`,`destination_location_data`)
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of osc_catalog_tax
-- ----------------------------
BEGIN;
INSERT INTO `osc_catalog_tax` VALUES (1,1,'','g1',2000,1603111714,1603111714),(2,2,'','g1',2000,1603111714,1603111714),(3,3,'','g1',2000,1603111714,1603111714),(4,4,'','g1',2000,1603111714,1603111714),(5,5,'','g1',2000,1603111714,1603111714),(6,6,'','g1',2000,1603111714,1603111714),(7,7,'','g1',2000,1603111714,1603111714),(8,8,'','g1',2000,1603111714,1603111714),(9,9,'','g1',2000,1603111714,1603111714),(10,10,'','g1',2000,1603111714,1603111714),(11,11,'','g1',2000,1603111714,1603111714),(12,12,'','g1',2000,1603111714,1603111714),(13,13,'','g1',2000,1603111714,1603111714),(14,14,'','g1',2000,1603111714,1603111714),(15,15,'','g1',2000,1603111714,1603111714),(16,16,'','g1',2000,1603111714,1603111714),(17,17,'','g1',2000,1603111714,1603111714),(18,18,'','g1',2000,1603111714,1603111714),(19,19,'','g1',2000,1603111714,1603111714),(20,20,'','g1',2000,1603111714,1603111714),(21,21,'','g1',2000,1603111714,1603111714),(22,22,'','g1',2000,1603111714,1603111714),(23,23,'','g1',2000,1603111714,1603111714),(24,24,'','g1',2000,1603111714,1603111714),(25,25,'','g1',2000,1603111714,1603111714),(26,26,'','g1',2000,1603111714,1603111714),(27,27,'','g1',2000,1603111714,1603111714),(28,28,'','g1',2000,1603111714,1603111714),(29,29,'','g1',2000,1603111714,1603111714),(30,30,'','g1',2000,1603111714,1603111714),(31,31,'','g1',2000,1603111714,1603111714),(32,32,'','g1',2000,1603111714,1603111714),(33,33,'','g1',2000,1603111714,1603111714),(34,34,'','g1',2000,1603111714,1603111714),(35,35,'','g1',2000,1603111714,1603111714),(36,36,'','g1',2000,1603111714,1603111714),(37,37,'','g1',2000,1603111714,1603111714),(38,38,'','g1',2000,1603111714,1603111714),(39,39,'','g1',2000,1603111714,1603111714),(40,1,'','g2',0,1603111714,1603111714),(41,2,'','g2',0,1603111714,1603111714),(42,3,'','g2',0,1603111714,1603111714),(43,4,'','g2',0,1603111714,1603111714),(44,5,'','g2',0,1603111714,1603111714),(45,6,'','g2',0,1603111714,1603111714),(46,7,'','g2',0,1603111714,1603111714),(47,8,'','g2',0,1603111714,1603111714),(48,9,'','g2',0,1603111714,1603111714),(49,10,'','g2',0,1603111714,1603111714),(50,11,'','g2',0,1603111714,1603111714),(51,12,'','g2',0,1603111714,1603111714),(52,13,'','g2',0,1603111714,1603111714),(53,14,'','g2',0,1603111714,1603111714),(54,15,'','g2',0,1603111714,1603111714),(55,16,'','g2',0,1603111714,1603111714),(56,17,'','g2',0,1603111714,1603111714),(57,18,'','g2',0,1603111714,1603111714),(58,19,'','g2',0,1603111714,1603111714),(59,20,'','g2',0,1603111714,1603111714),(60,21,'','g2',0,1603111714,1603111714),(61,22,'','g2',0,1603111714,1603111714),(62,23,'','g2',0,1603111714,1603111714),(63,24,'','g2',0,1603111714,1603111714),(64,25,'','g2',0,1603111714,1603111714),(65,26,'','g2',0,1603111714,1603111714),(66,27,'','g2',0,1603111714,1603111714),(67,28,'','g2',0,1603111714,1603111714),(68,29,'','g2',0,1603111714,1603111714),(69,30,'','g2',0,1603111714,1603111714),(70,31,'','g2',0,1603111714,1603111714),(71,32,'','g2',0,1603111714,1603111714),(72,33,'','g2',0,1603111714,1603111714),(73,34,'','g2',0,1603111714,1603111714),(74,35,'','g2',0,1603111714,1603111714),(75,36,'','g2',0,1603111714,1603111714),(76,37,'','g2',0,1603111714,1603111714),(77,38,'','g2',0,1603111714,1603111714),(78,39,'','g2',0,1603111714,1603111714);
COMMIT;

-- ----------------------------
-- Table structure for osc_location_country
-- ----------------------------
DROP TABLE IF EXISTS `osc_location_country`;
CREATE TABLE `osc_location_country` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_code` varchar(2) NOT NULL,
  `country_name` varchar(100) NOT NULL,
  `zip_formats` varchar(255) DEFAULT NULL,
  `phone_prefix` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `code_UNIQUE` (`country_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=247 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_location_country
-- ----------------------------
BEGIN;
INSERT INTO `osc_location_country` VALUES (1, 'AF', 'Afghanistan', '[\"####\"]', '[\"93\"]');
INSERT INTO `osc_location_country` VALUES (2, 'AX', 'Aland Islands', '[\"#####\",\"AX-#####\"]', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (3, 'AL', 'Albania', '[\"####\"]', '[\"355\"]');
INSERT INTO `osc_location_country` VALUES (4, 'DZ', 'Algeria', '[\"#####\"]', '[\"213\"]');
INSERT INTO `osc_location_country` VALUES (5, 'AS', 'American Samoa', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (6, 'AD', 'Andorra', '[\"AD###\",\"#####\"]', '[\"376\"]');
INSERT INTO `osc_location_country` VALUES (7, 'AO', 'Angola', '', '[\"244\"]');
INSERT INTO `osc_location_country` VALUES (8, 'AI', 'Anguilla', '[\"AI-2640\"]', '[\"1-264\"]');
INSERT INTO `osc_location_country` VALUES (9, 'AQ', 'Antarctica', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (10, 'AG', 'Antigua And Barbuda', '', '[\"1-268\"]');
INSERT INTO `osc_location_country` VALUES (11, 'AR', 'Argentina', '[\"####\",\"@####@@@\"]', '[\"54\"]');
INSERT INTO `osc_location_country` VALUES (12, 'AM', 'Armenia', '[\"####\"]', '[\"374\"]');
INSERT INTO `osc_location_country` VALUES (13, 'AW', 'Aruba', '', '[\"297\"]');
INSERT INTO `osc_location_country` VALUES (14, 'AU', 'Australia', '[\"####\"]', '[\"61\"]');
INSERT INTO `osc_location_country` VALUES (15, 'AT', 'Austria', '[\"####\"]', '[\"43\"]');
INSERT INTO `osc_location_country` VALUES (16, 'AZ', 'Azerbaijan', '[\"AZ ####\"]', '[\"994\"]');
INSERT INTO `osc_location_country` VALUES (17, 'BS', 'Bahamas', '', '[\"1-242\"]');
INSERT INTO `osc_location_country` VALUES (18, 'BH', 'Bahrain', '[\"###\",\"####\"]', '[\"973\"]');
INSERT INTO `osc_location_country` VALUES (19, 'BD', 'Bangladesh', '[\"####\"]', '[\"880\"]');
INSERT INTO `osc_location_country` VALUES (20, 'BB', 'Barbados', '[\"BB#####\"]', '[\"1-246\"]');
INSERT INTO `osc_location_country` VALUES (21, 'BY', 'Belarus', '[\"######\"]', '[\"375\"]');
INSERT INTO `osc_location_country` VALUES (22, 'BE', 'Belgium', '[\"####\"]', '[\"32\"]');
INSERT INTO `osc_location_country` VALUES (23, 'BZ', 'Belize', '', '[\"501\"]');
INSERT INTO `osc_location_country` VALUES (24, 'BJ', 'Benin', '', '[\"229\"]');
INSERT INTO `osc_location_country` VALUES (25, 'BM', 'Bermuda', '[\"@@ ##\",\"@@ @@\"]', '[\"1-441\"]');
INSERT INTO `osc_location_country` VALUES (26, 'BT', 'Bhutan', '[\"#####\"]', '[\"975\"]');
INSERT INTO `osc_location_country` VALUES (27, 'BO', 'Bolivia', '', '[\"591\"]');
INSERT INTO `osc_location_country` VALUES (28, 'BQ', 'Bonaire, Saint-Eustache et Saba', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (29, 'BA', 'Bosnia And Herzegovina', '[\"#####\"]', '[\"387\"]');
INSERT INTO `osc_location_country` VALUES (30, 'BW', 'Botswana', '', '[\"267\"]');
INSERT INTO `osc_location_country` VALUES (31, 'BV', 'Bouvet Island', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (32, 'BR', 'Brazil', '[\"#####-###\",\"#####\"]', '[\"55\"]');
INSERT INTO `osc_location_country` VALUES (33, 'IO', 'British Indian Ocean Territory', '[\"BBND 1ZZ\"]', '[\"246\"]');
INSERT INTO `osc_location_country` VALUES (34, 'BN', 'Brunei', '[\"@@####\"]', '[\"673\"]');
INSERT INTO `osc_location_country` VALUES (35, 'BG', 'Bulgaria', '[\"####\"]', '[\"359\"]');
INSERT INTO `osc_location_country` VALUES (36, 'BF', 'Burkina Faso', '', '[\"226\"]');
INSERT INTO `osc_location_country` VALUES (37, 'BI', 'Burundi', '', '[\"257\"]');
INSERT INTO `osc_location_country` VALUES (38, 'KH', 'Cambodia', '[\"#####\"]', '[\"855\"]');
INSERT INTO `osc_location_country` VALUES (39, 'CM', 'Cameroon', '', '[\"237\"]');
INSERT INTO `osc_location_country` VALUES (40, 'CA', 'Canada', '[\"@#@ #@#\"]', '[\"1\"]');
INSERT INTO `osc_location_country` VALUES (41, 'CV', 'Cape Verde', '[\"####\"]', '[\"238\"]');
INSERT INTO `osc_location_country` VALUES (42, 'KY', 'Cayman Islands', '[\"KY#-####\"]', '[\"1-345\"]');
INSERT INTO `osc_location_country` VALUES (43, 'CF', 'Central African Republic', '', '[\"236\"]');
INSERT INTO `osc_location_country` VALUES (44, 'TD', 'Chad', '', '[\"235\"]');
INSERT INTO `osc_location_country` VALUES (45, 'CL', 'Chile', '[\"#######\",\"###-####\"]', '[\"56\"]');
INSERT INTO `osc_location_country` VALUES (46, 'CN', 'China', '[\"######\"]', '[\"86\"]');
INSERT INTO `osc_location_country` VALUES (47, 'CX', 'Christmas Island', '[\"####\"]', '[\"61\"]');
INSERT INTO `osc_location_country` VALUES (48, 'CC', 'Cocos (Keeling) Islands', '[\"####\"]', '[\"61\"]');
INSERT INTO `osc_location_country` VALUES (49, 'CO', 'Colombia', '[\"######\"]', '[\"57\"]');
INSERT INTO `osc_location_country` VALUES (50, 'KM', 'Comoros', '', '[\"269\"]');
INSERT INTO `osc_location_country` VALUES (51, 'CG', 'Congo', '', '[\"242\"]');
INSERT INTO `osc_location_country` VALUES (52, 'CD', 'Congo, The Democratic Republic Of The', '', '[\"243\"]');
INSERT INTO `osc_location_country` VALUES (53, 'CK', 'Cook Islands', '', '[\"682\"]');
INSERT INTO `osc_location_country` VALUES (54, 'CR', 'Costa Rica', '[\"#####\",\"#####-####\"]', '[\"506\"]');
INSERT INTO `osc_location_country` VALUES (55, 'HR', 'Croatia', '[\"#####\"]', '[\"385\"]');
INSERT INTO `osc_location_country` VALUES (56, 'CW', 'Curacao', '', '[\"599\"]');
INSERT INTO `osc_location_country` VALUES (57, 'CY', 'Cyprus', '[\"####\"]', '[\"357\"]');
INSERT INTO `osc_location_country` VALUES (58, 'CZ', 'Czech Republic', '[\"### ##\"]', '[\"420\"]');
INSERT INTO `osc_location_country` VALUES (59, 'CI', 'Côte d\'Ivoire', '', '[\"225\"]');
INSERT INTO `osc_location_country` VALUES (60, 'DK', 'Denmark', '[\"####\"]', '[\"45\"]');
INSERT INTO `osc_location_country` VALUES (61, 'DJ', 'Djibouti', '', '[\"253\"]');
INSERT INTO `osc_location_country` VALUES (62, 'DM', 'Dominica', '', '[\"1-767\"]');
INSERT INTO `osc_location_country` VALUES (63, 'DO', 'Dominican Republic', '[\"#####\"]', '[\"1-809\",\"1-829\",\"1-849\"]');
INSERT INTO `osc_location_country` VALUES (64, 'TL', 'East Timor', '', '[\"670\"]');
INSERT INTO `osc_location_country` VALUES (65, 'EC', 'Ecuador', '[\"######\"]', '[\"593\"]');
INSERT INTO `osc_location_country` VALUES (66, 'EG', 'Egypt', '[\"#####\"]', '[\"20\"]');
INSERT INTO `osc_location_country` VALUES (67, 'SV', 'El Salvador', '[\"####\"]', '[\"503\"]');
INSERT INTO `osc_location_country` VALUES (68, 'GQ', 'Equatorial Guinea', '', '[\"240\"]');
INSERT INTO `osc_location_country` VALUES (69, 'ER', 'Eritrea', '', '[\"291\"]');
INSERT INTO `osc_location_country` VALUES (70, 'EE', 'Estonia', '[\"#####\"]', '[\"372\"]');
INSERT INTO `osc_location_country` VALUES (71, 'ET', 'Ethiopia', '[\"####\"]', '[\"251\"]');
INSERT INTO `osc_location_country` VALUES (72, 'FK', 'Falkland Islands (Malvinas)', '[\"FIQQ 1ZZ\"]', '[\"500\"]');
INSERT INTO `osc_location_country` VALUES (73, 'FO', 'Faroe Islands', '[\"###\"]', '[\"298\"]');
INSERT INTO `osc_location_country` VALUES (74, 'FJ', 'Fiji', '', '[\"679\"]');
INSERT INTO `osc_location_country` VALUES (75, 'FI', 'Finland', '[\"#####\"]', '[\"358\"]');
INSERT INTO `osc_location_country` VALUES (76, 'FR', 'France', '[\"#####\"]', '[\"33\"]');
INSERT INTO `osc_location_country` VALUES (77, 'GF', 'French Guiana', '[\"973##\"]', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (78, 'PF', 'French Polynesia', '[\"987##\"]', '[\"689\"]');
INSERT INTO `osc_location_country` VALUES (79, 'TF', 'French Southern Territories', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (80, 'GA', 'Gabon', '', '[\"241\"]');
INSERT INTO `osc_location_country` VALUES (81, 'GM', 'Gambia', '', '[\"220\"]');
INSERT INTO `osc_location_country` VALUES (82, 'GE', 'Georgia', '[\"####\"]', '[\"995\"]');
INSERT INTO `osc_location_country` VALUES (83, 'DE', 'Germany', '[\"#####\"]', '[\"49\"]');
INSERT INTO `osc_location_country` VALUES (84, 'GH', 'Ghana', '', '[\"233\"]');
INSERT INTO `osc_location_country` VALUES (85, 'GI', 'Gibraltar', '[\"GX11 1AA\"]', '[\"350\"]');
INSERT INTO `osc_location_country` VALUES (86, 'GR', 'Greece', '[\"### ##\"]', '[\"30\"]');
INSERT INTO `osc_location_country` VALUES (87, 'GL', 'Greenland', '[\"####\"]', '[\"299\"]');
INSERT INTO `osc_location_country` VALUES (88, 'GD', 'Grenada', '', '[\"1-473\"]');
INSERT INTO `osc_location_country` VALUES (89, 'GP', 'Guadeloupe', '[\"971##\"]', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (90, 'GU', 'Guam', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (91, 'GT', 'Guatemala', '[\"#####\"]', '[\"502\"]');
INSERT INTO `osc_location_country` VALUES (92, 'GG', 'Guernsey', '[\"GY# #@@\",\"GY## #@@\"]', '[\"44-1481\"]');
INSERT INTO `osc_location_country` VALUES (93, 'GN', 'Guinea', '[\"###\"]', '[\"224\"]');
INSERT INTO `osc_location_country` VALUES (94, 'GW', 'Guinea Bissau', '[\"####\"]', '[\"245\"]');
INSERT INTO `osc_location_country` VALUES (95, 'GY', 'Guyana', '', '[\"592\"]');
INSERT INTO `osc_location_country` VALUES (96, 'HT', 'Haiti', '[\"####\"]', '[\"509\"]');
INSERT INTO `osc_location_country` VALUES (97, 'HM', 'Heard Island And Mcdonald Islands', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (98, 'VA', 'Holy See (Vatican City State)', '[\"00120\"]', '[\"379\"]');
INSERT INTO `osc_location_country` VALUES (99, 'HN', 'Honduras', '[\"@@####\",\"#####\"]', '[\"504\"]');
INSERT INTO `osc_location_country` VALUES (100, 'HK', 'Hong Kong', '', '[\"852\"]');
INSERT INTO `osc_location_country` VALUES (101, 'HU', 'Hungary', '[\"####\"]', '[\"36\"]');
INSERT INTO `osc_location_country` VALUES (102, 'IS', 'Iceland', '[\"###\"]', '[\"354\"]');
INSERT INTO `osc_location_country` VALUES (103, 'IN', 'India', '[\"######\",\"### ###\"]', '[\"91\"]');
INSERT INTO `osc_location_country` VALUES (104, 'ID', 'Indonesia', '[\"#####\"]', '[\"62\"]');
INSERT INTO `osc_location_country` VALUES (105, 'IR', 'Iran', '[\"##########\",\"#####-#####\"]', '[\"98\"]');
INSERT INTO `osc_location_country` VALUES (106, 'IQ', 'Iraq', '[\"#####\"]', '[\"964\"]');
INSERT INTO `osc_location_country` VALUES (107, 'IE', 'Ireland', '[\"@** ****\",\"@##\"]', '[\"353\"]');
INSERT INTO `osc_location_country` VALUES (108, 'IM', 'Isle Of Man', '[\"IM# #@@\",\"IM## #@@\"]', '[\"44-1624\"]');
INSERT INTO `osc_location_country` VALUES (109, 'IL', 'Israel', '[\"#######\"]', '[\"972\"]');
INSERT INTO `osc_location_country` VALUES (110, 'IT', 'Italy', '[\"#####\"]', '[\"39\"]');
INSERT INTO `osc_location_country` VALUES (111, 'JM', 'Jamaica', '[\"##\"]', '[\"1-876\"]');
INSERT INTO `osc_location_country` VALUES (112, 'JP', 'Japan', '[\"###-####\",\"###\"]', '[\"81\"]');
INSERT INTO `osc_location_country` VALUES (113, 'JE', 'Jersey', '[\"JE# #@@\",\"JE## #@@\"]', '[\"44-1534\"]');
INSERT INTO `osc_location_country` VALUES (114, 'JO', 'Jordan', '[\"#####\"]', '[\"962\"]');
INSERT INTO `osc_location_country` VALUES (115, 'KZ', 'Kazakhstan', '[\"######\"]', '[\"7\"]');
INSERT INTO `osc_location_country` VALUES (116, 'KE', 'Kenya', '[\"#####\"]', '[\"254\"]');
INSERT INTO `osc_location_country` VALUES (117, 'KI', 'Kiribati', '', '[\"686\"]');
INSERT INTO `osc_location_country` VALUES (118, 'XK', 'Kosovo', '', '[\"383\"]');
INSERT INTO `osc_location_country` VALUES (119, 'KW', 'Kuwait', '[\"#####\"]', '[\"965\"]');
INSERT INTO `osc_location_country` VALUES (120, 'KG', 'Kyrgyzstan', '[\"######\"]', '[\"996\"]');
INSERT INTO `osc_location_country` VALUES (121, 'LA', 'Laos', '[\"#####\"]', '[\"856\"]');
INSERT INTO `osc_location_country` VALUES (122, 'LV', 'Latvia', '[\"LV-####\"]', '[\"371\"]');
INSERT INTO `osc_location_country` VALUES (123, 'LB', 'Lebanon', '[\"#####\",\"#### ####\"]', '[\"961\"]');
INSERT INTO `osc_location_country` VALUES (124, 'LS', 'Lesotho', '[\"###\"]', '[\"266\"]');
INSERT INTO `osc_location_country` VALUES (125, 'LR', 'Liberia', '[\"####\"]', '[\"231\"]');
INSERT INTO `osc_location_country` VALUES (126, 'LY', 'Libya', '', '[\"218\"]');
INSERT INTO `osc_location_country` VALUES (127, 'LI', 'Liechtenstein', '[\"####\"]', '[\"423\"]');
INSERT INTO `osc_location_country` VALUES (128, 'LT', 'Lithuania', '[\"LT-#####\"]', '[\"370\"]');
INSERT INTO `osc_location_country` VALUES (129, 'LU', 'Luxembourg', '[\"####\"]', '[\"352\"]');
INSERT INTO `osc_location_country` VALUES (130, 'MO', 'Macao', '', '[\"853\"]');
INSERT INTO `osc_location_country` VALUES (131, 'MK', 'Macedonia', '[\"####\"]', '[\"389\"]');
INSERT INTO `osc_location_country` VALUES (132, 'MG', 'Madagascar', '[\"###\"]', '[\"261\"]');
INSERT INTO `osc_location_country` VALUES (133, 'MW', 'Malawi', '', '[\"265\"]');
INSERT INTO `osc_location_country` VALUES (134, 'MY', 'Malaysia', '[\"#####\"]', '[\"60\"]');
INSERT INTO `osc_location_country` VALUES (135, 'MV', 'Maldives', '[\"#####\"]', '[\"960\"]');
INSERT INTO `osc_location_country` VALUES (136, 'ML', 'Mali', '', '[\"223\"]');
INSERT INTO `osc_location_country` VALUES (137, 'MT', 'Malta', '[\"@@@ ####\"]', '[\"356\"]');
INSERT INTO `osc_location_country` VALUES (138, 'MH', 'Marshall Islands', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (139, 'MQ', 'Martinique', '[\"972##\"]', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (140, 'MR', 'Mauritania', '', '[\"222\"]');
INSERT INTO `osc_location_country` VALUES (141, 'MU', 'Mauritius', '[\"#####\"]', '[\"230\"]');
INSERT INTO `osc_location_country` VALUES (142, 'YT', 'Mayotte', '[\"976##\"]', '[\"262\"]');
INSERT INTO `osc_location_country` VALUES (143, 'MX', 'Mexico', '[\"#####\"]', '[\"52\"]');
INSERT INTO `osc_location_country` VALUES (144, 'FM', 'Micronesia', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (145, 'MD', 'Moldova', '[\"MD####\",\"MD-####\"]', '[\"373\"]');
INSERT INTO `osc_location_country` VALUES (146, 'MC', 'Monaco', '[\"980##\"]', '[\"377\"]');
INSERT INTO `osc_location_country` VALUES (147, 'MN', 'Mongolia', '[\"#####\"]', '[\"976\"]');
INSERT INTO `osc_location_country` VALUES (148, 'ME', 'Montenegro', '[\"#####\"]', '[\"382\"]');
INSERT INTO `osc_location_country` VALUES (149, 'MS', 'Montserrat', '', '[\"1-664\"]');
INSERT INTO `osc_location_country` VALUES (150, 'MA', 'Morocco', '[\"#####\"]', '[\"212\"]');
INSERT INTO `osc_location_country` VALUES (151, 'MZ', 'Mozambique', '[\"####\"]', '[\"258\"]');
INSERT INTO `osc_location_country` VALUES (152, 'MM', 'Myanmar', '[\"#####\"]', '[\"95\"]');
INSERT INTO `osc_location_country` VALUES (153, 'NA', 'Namibia', '', '[\"264\"]');
INSERT INTO `osc_location_country` VALUES (154, 'NR', 'Nauru', '', '[\"674\"]');
INSERT INTO `osc_location_country` VALUES (155, 'NP', 'Nepal', '[\"#####\"]', '[\"977\"]');
INSERT INTO `osc_location_country` VALUES (156, 'NL', 'Netherlands', '[\"#### @@\"]', '[\"31\"]');
INSERT INTO `osc_location_country` VALUES (157, 'AN', 'Netherlands Antilles', '', '[\"599\"]');
INSERT INTO `osc_location_country` VALUES (158, 'NC', 'New Caledonia', '[\"988##\"]', '[\"687\"]');
INSERT INTO `osc_location_country` VALUES (159, 'NZ', 'New Zealand', '[\"####\"]', '[\"64\"]');
INSERT INTO `osc_location_country` VALUES (160, 'NI', 'Nicaragua', '[\"#####\"]', '[\"505\"]');
INSERT INTO `osc_location_country` VALUES (161, 'NE', 'Niger', '[\"####\"]', '[\"227\"]');
INSERT INTO `osc_location_country` VALUES (162, 'NG', 'Nigeria', '[\"######\"]', '[\"234\"]');
INSERT INTO `osc_location_country` VALUES (163, 'NU', 'Niue', '', '[\"683\"]');
INSERT INTO `osc_location_country` VALUES (164, 'NF', 'Norfolk Island', '[\"####\"]', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (165, 'MP', 'Northern Mariana Islands', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (166, 'NO', 'Norway', '[\"####\"]', '[\"47\"]');
INSERT INTO `osc_location_country` VALUES (167, 'OM', 'Oman', '[\"###\"]', '[\"968\"]');
INSERT INTO `osc_location_country` VALUES (168, 'PK', 'Pakistan', '[\"#####\"]', '[\"92\"]');
INSERT INTO `osc_location_country` VALUES (169, 'PW', 'Palau', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (170, 'PS', 'Palestinian Territory, Occupied', '[\"###\"]', '[\"970\"]');
INSERT INTO `osc_location_country` VALUES (171, 'PA', 'Panama', '[\"####\"]', '[\"507\"]');
INSERT INTO `osc_location_country` VALUES (172, 'PG', 'Papua New Guinea', '[\"###\"]', '[\"675\"]');
INSERT INTO `osc_location_country` VALUES (173, 'PY', 'Paraguay', '[\"####\"]', '[\"595\"]');
INSERT INTO `osc_location_country` VALUES (174, 'PE', 'Peru', '[\"#####\",\"PE #####\"]', '[\"51\"]');
INSERT INTO `osc_location_country` VALUES (175, 'PH', 'Philippines', '[\"####\"]', '[\"63\"]');
INSERT INTO `osc_location_country` VALUES (176, 'PN', 'Pitcairn', '[\"PCRN 1ZZ\"]', '[\"64\"]');
INSERT INTO `osc_location_country` VALUES (177, 'PL', 'Poland', '[\"##-###\"]', '[\"48\"]');
INSERT INTO `osc_location_country` VALUES (178, 'PT', 'Portugal', '[\"####-###\"]', '[\"351\"]');
INSERT INTO `osc_location_country` VALUES (179, 'QA', 'Qatar', '', '[\"974\"]');
INSERT INTO `osc_location_country` VALUES (180, 'RE', 'Reunion', '[\"974##\"]', '[\"262\"]');
INSERT INTO `osc_location_country` VALUES (181, 'RO', 'Romania', '[\"######\"]', '[\"40\"]');
INSERT INTO `osc_location_country` VALUES (182, 'RU', 'Russia', '[\"######\"]', '[\"7\"]');
INSERT INTO `osc_location_country` VALUES (183, 'RW', 'Rwanda', '', '[\"250\"]');
INSERT INTO `osc_location_country` VALUES (184, 'BL', 'Saint Barth&eacute;lemy', '[\"#####\"]', '[\"590\"]');
INSERT INTO `osc_location_country` VALUES (185, 'SH', 'Saint Helena', '[\"@@@@ 1ZZ\"]', '[\"290\"]');
INSERT INTO `osc_location_country` VALUES (186, 'KN', 'Saint Kitts And Nevis', '', '[\"1-869\"]');
INSERT INTO `osc_location_country` VALUES (187, 'LC', 'Saint Lucia', '[\"LC## ###\"]', '[\"1-758\"]');
INSERT INTO `osc_location_country` VALUES (188, 'MF', 'Saint Martin', '[\"97150\"]', '[\"590\"]');
INSERT INTO `osc_location_country` VALUES (189, 'PM', 'Saint Pierre And Miquelon', '[\"97500\"]', '[\"508\"]');
INSERT INTO `osc_location_country` VALUES (190, 'WS', 'Samoa', '[\"WS####\"]', '[\"685\"]');
INSERT INTO `osc_location_country` VALUES (191, 'SM', 'San Marino', '[\"4789#\"]', '[\"378\"]');
INSERT INTO `osc_location_country` VALUES (192, 'ST', 'Sao Tome And Principe', '', '[\"239\"]');
INSERT INTO `osc_location_country` VALUES (193, 'SA', 'Saudi Arabia', '[\"#####\",\"#####-####\"]', '[\"966\"]');
INSERT INTO `osc_location_country` VALUES (194, 'SN', 'Senegal', '[\"#####\"]', '[\"221\"]');
INSERT INTO `osc_location_country` VALUES (195, 'RS', 'Serbia', '[\"#####\"]', '[\"381\"]');
INSERT INTO `osc_location_country` VALUES (196, 'SC', 'Seychelles', '', '[\"248\"]');
INSERT INTO `osc_location_country` VALUES (197, 'SL', 'Sierra Leone', '', '[\"232\"]');
INSERT INTO `osc_location_country` VALUES (198, 'SG', 'Singapore', '[\"######\"]', '[\"65\"]');
INSERT INTO `osc_location_country` VALUES (199, 'SX', 'Sint Maarten', '', '[\"1-721\"]');
INSERT INTO `osc_location_country` VALUES (200, 'SK', 'Slovakia', '[\"### ##\"]', '[\"421\"]');
INSERT INTO `osc_location_country` VALUES (201, 'SI', 'Slovenia', '[\"####\",\"SI-####\"]', '[\"386\"]');
INSERT INTO `osc_location_country` VALUES (202, 'SB', 'Solomon Islands', '', '[\"677\"]');
INSERT INTO `osc_location_country` VALUES (203, 'SO', 'Somalia', '[\"@@ #####\"]', '[\"252\"]');
INSERT INTO `osc_location_country` VALUES (204, 'ZA', 'South Africa', '[\"####\"]', '[\"27\"]');
INSERT INTO `osc_location_country` VALUES (205, 'GS', 'South Georgia And The South Sandwich Islands', '[\"SIQQ 1ZZ\"]', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (206, 'KR', 'South Korea', '[\"###-###\",\"#####\"]', '[\"82\"]');
INSERT INTO `osc_location_country` VALUES (207, 'SS', 'South Sudan', '[\"#####\"]', '[\"211\"]');
INSERT INTO `osc_location_country` VALUES (208, 'ES', 'Spain', '[\"#####\"]', '[\"34\"]');
INSERT INTO `osc_location_country` VALUES (209, 'LK', 'Sri Lanka', '[\"#####\"]', '[\"94\"]');
INSERT INTO `osc_location_country` VALUES (210, 'VC', 'St. Vincent', '[\"VC####\"]', '[\"1-784\"]');
INSERT INTO `osc_location_country` VALUES (211, 'SD', 'Sudan', '[\"#####\"]', '[\"249\"]');
INSERT INTO `osc_location_country` VALUES (212, 'SR', 'Suriname', '', '[\"597\"]');
INSERT INTO `osc_location_country` VALUES (213, 'SJ', 'Svalbard And Jan Mayen', '[\"####\"]', '[\"47\"]');
INSERT INTO `osc_location_country` VALUES (214, 'SZ', 'Swaziland', '[\"@###\"]', '[\"268\"]');
INSERT INTO `osc_location_country` VALUES (215, 'SE', 'Sweden', '[\"### ##\"]', '[\"46\"]');
INSERT INTO `osc_location_country` VALUES (216, 'CH', 'Switzerland', '[\"####\"]', '[\"41\"]');
INSERT INTO `osc_location_country` VALUES (217, 'TW', 'Taiwan', '[\"###\",\"###-##\"]', '[\"886\"]');
INSERT INTO `osc_location_country` VALUES (218, 'TJ', 'Tajikistan', '[\"######\"]', '[\"992\"]');
INSERT INTO `osc_location_country` VALUES (219, 'TZ', 'Tanzania', '[\"#####\"]', '[\"255\"]');
INSERT INTO `osc_location_country` VALUES (220, 'TH', 'Thailand', '[\"#####\"]', '[\"66\"]');
INSERT INTO `osc_location_country` VALUES (221, 'TG', 'Togo', '', '[\"228\"]');
INSERT INTO `osc_location_country` VALUES (222, 'TK', 'Tokelau', '', '[\"690\"]');
INSERT INTO `osc_location_country` VALUES (223, 'TO', 'Tonga', '', '[\"676\"]');
INSERT INTO `osc_location_country` VALUES (224, 'TT', 'Trinidad And Tobago', '[\"######\"]', '[\"1-868\"]');
INSERT INTO `osc_location_country` VALUES (225, 'TN', 'Tunisia', '[\"####\"]', '[\"216\"]');
INSERT INTO `osc_location_country` VALUES (226, 'TR', 'Turkey', '[\"#####\"]', '[\"90\"]');
INSERT INTO `osc_location_country` VALUES (227, 'TM', 'Turkmenistan', '[\"######\"]', '[\"993\"]');
INSERT INTO `osc_location_country` VALUES (228, 'TC', 'Turks and Caicos Islands', '[\"TKCA 1ZZ\"]', '[\"1-649\"]');
INSERT INTO `osc_location_country` VALUES (229, 'TV', 'Tuvalu', '', '[\"688\"]');
INSERT INTO `osc_location_country` VALUES (230, 'VI', 'U.S. Virgin Islands', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (231, 'UG', 'Uganda', '', '[\"256\"]');
INSERT INTO `osc_location_country` VALUES (232, 'AE', 'United Arab Emirates', '', '[\"971\"]');
INSERT INTO `osc_location_country` VALUES (233, 'GB', 'United Kingdom', '[\"@@## #@@\",\"@#@ #@@\",\"@@# #@@\",\"@@#@ #@@\",\"@## #@@\",\"@# #@@\"]', '[\"44\"]');
INSERT INTO `osc_location_country` VALUES (234, 'US', 'United States', '[\"#####\",\"#####-####\"]', '[\"1\"]');
INSERT INTO `osc_location_country` VALUES (235, 'UM', 'United States Minor Outlying Islands', '', '\"[]\"');
INSERT INTO `osc_location_country` VALUES (236, 'UY', 'Uruguay', '[\"#####\"]', '[\"598\"]');
INSERT INTO `osc_location_country` VALUES (237, 'UZ', 'Uzbekistan', '[\"######\"]', '[\"998\"]');
INSERT INTO `osc_location_country` VALUES (238, 'VU', 'Vanuatu', '', '[\"678\"]');
INSERT INTO `osc_location_country` VALUES (239, 'VE', 'Venezuela', '[\"####\",\"####-@\"]', '[\"58\"]');
INSERT INTO `osc_location_country` VALUES (240, 'VN', 'Vietnam', '[\"######\"]', '[\"84\"]');
INSERT INTO `osc_location_country` VALUES (241, 'VG', 'Virgin Islands, British', '[\"VG####\"]', '[\"1-284\"]');
INSERT INTO `osc_location_country` VALUES (242, 'WF', 'Wallis And Futuna', '[\"986##\"]', '[\"681\"]');
INSERT INTO `osc_location_country` VALUES (243, 'EH', 'Western Sahara', '', '[\"212\"]');
INSERT INTO `osc_location_country` VALUES (244, 'YE', 'Yemen', '', '[\"967\"]');
INSERT INTO `osc_location_country` VALUES (245, 'ZM', 'Zambia', '[\"#####\"]', '[\"260\"]');
INSERT INTO `osc_location_country` VALUES (246, 'ZW', 'Zimbabwe', '', '[\"263\"]');
COMMIT;

-- ----------------------------
-- Table structure for osc_location_group
-- ----------------------------
DROP TABLE IF EXISTS `osc_location_group`;
CREATE TABLE `osc_location_group` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `group_data` longtext NOT NULL,
  `parsed_data` longtext DEFAULT NULL,
  `system_flag` tinyint(1) unsigned NOT NULL DEFAULT 0,
  `added_timestamp` int(10) NOT NULL DEFAULT 0,
  `modified_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `group_name` (`group_name`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=latin1 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_location_group
-- ----------------------------
BEGIN;
INSERT INTO `osc_location_group` VALUES (1, 'TAX - 20%', '{\"include\":\"c108_c122_c128_c129_c137_c233\",\"exclude\":\"\"}', '[\"c108\",\"c122\",\"c128\",\"c129\",\"c137\",\"c233\"]', 0, 1602265939, 1602265939);
INSERT INTO `osc_location_group` VALUES (2, 'TAX - 0%', '{\"include\":\"c66_c119_c127_c166\",\"exclude\":\"\"}', '[\"c66\",\"c119\",\"c127\",\"c166\"]', 0, 1602266027, 1602266027);
INSERT INTO `osc_location_group` VALUES (3, '[SUPPY PRODUCT] - EU', '{\"include\":\"c66_c108_c119_c122_c127_c128_c129_c137_c166_c233\",\"exclude\":\"\"}', '[\"c66\",\"c108\",\"c119\",\"c122\",\"c127\",\"c128\",\"c129\",\"c137\",\"c166\",\"c233\"]', 0, 1602266383, 1602266383);
INSERT INTO `osc_location_group` VALUES (4, '[SUPPLY PRODUCT] - DPI', '{\"include\":\"*\",\"exclude\":\"g3\"}', '[\"c1\",\"c2\",\"c3\",\"c4\",\"c5\",\"c6\",\"c7\",\"c8\",\"c9\",\"c10\",\"c11\",\"c12\",\"c13\",\"c14\",\"c15\",\"c16\",\"c17\",\"c18\",\"c19\",\"c20\",\"c21\",\"c22\",\"c23\",\"c24\",\"c25\",\"c26\",\"c27\",\"c28\",\"c29\",\"c30\",\"c31\",\"c32\",\"c33\",\"c34\",\"c35\",\"c36\",\"c37\",\"c38\",\"c39\",\"c40\",\"c41\",\"c42\",\"c43\",\"c44\",\"c45\",\"c46\",\"c47\",\"c48\",\"c49\",\"c50\",\"c51\",\"c52\",\"c53\",\"c54\",\"c55\",\"c56\",\"c57\",\"c58\",\"c59\",\"c60\",\"c61\",\"c62\",\"c63\",\"c64\",\"c65\",\"c67\",\"c68\",\"c69\",\"c70\",\"c71\",\"c72\",\"c73\",\"c74\",\"c75\",\"c76\",\"c77\",\"c78\",\"c79\",\"c80\",\"c81\",\"c82\",\"c83\",\"c84\",\"c85\",\"c86\",\"c87\",\"c88\",\"c89\",\"c90\",\"c91\",\"c92\",\"c93\",\"c94\",\"c95\",\"c96\",\"c97\",\"c98\",\"c99\",\"c100\",\"c101\",\"c102\",\"c103\",\"c104\",\"c105\",\"c106\",\"c107\",\"c109\",\"c110\",\"c111\",\"c112\",\"c113\",\"c114\",\"c115\",\"c116\",\"c117\",\"c118\",\"c120\",\"c121\",\"c123\",\"c124\",\"c125\",\"c126\",\"c130\",\"c131\",\"c132\",\"c133\",\"c134\",\"c135\",\"c136\",\"c138\",\"c139\",\"c140\",\"c141\",\"c142\",\"c143\",\"c144\",\"c145\",\"c146\",\"c147\",\"c148\",\"c149\",\"c150\",\"c151\",\"c152\",\"c153\",\"c154\",\"c155\",\"c156\",\"c157\",\"c158\",\"c159\",\"c160\",\"c161\",\"c162\",\"c163\",\"c164\",\"c165\",\"c167\",\"c168\",\"c169\",\"c170\",\"c171\",\"c172\",\"c173\",\"c174\",\"c175\",\"c176\",\"c177\",\"c178\",\"c179\",\"c180\",\"c181\",\"c182\",\"c183\",\"c184\",\"c185\",\"c186\",\"c187\",\"c188\",\"c189\",\"c190\",\"c191\",\"c192\",\"c193\",\"c194\",\"c195\",\"c196\",\"c197\",\"c198\",\"c199\",\"c200\",\"c201\",\"c202\",\"c203\",\"c204\",\"c205\",\"c206\",\"c207\",\"c208\",\"c209\",\"c210\",\"c211\",\"c212\",\"c213\",\"c214\",\"c215\",\"c216\",\"c217\",\"c218\",\"c219\",\"c220\",\"c221\",\"c222\",\"c223\",\"c224\",\"c225\",\"c226\",\"c227\",\"c228\",\"c229\",\"c230\",\"c231\",\"c232\",\"c234\",\"c235\",\"c236\",\"c237\",\"c238\",\"c239\",\"c240\",\"c241\",\"c242\",\"c243\",\"c244\",\"c245\",\"c246\",\"p1\",\"p2\",\"p3\",\"p4\",\"p5\",\"p6\",\"p7\",\"p8\",\"p9\",\"p10\",\"p11\",\"p12\",\"p13\",\"p14\",\"p15\",\"p16\",\"p17\",\"p18\",\"p19\",\"p20\",\"p21\",\"p22\",\"p23\",\"p24\",\"p25\",\"p26\",\"p27\",\"p28\",\"p29\",\"p30\",\"p31\",\"p32\",\"p33\",\"p34\",\"p35\",\"p36\",\"p37\",\"p38\",\"p39\",\"p40\",\"p41\",\"p42\",\"p43\",\"p44\",\"p45\",\"p46\",\"p47\",\"p48\",\"p49\",\"p50\",\"p51\",\"p52\",\"p53\",\"p54\",\"p55\",\"p56\",\"p57\",\"p58\",\"p59\",\"p60\",\"p61\",\"p62\",\"p63\",\"p64\",\"p65\",\"p66\",\"p67\",\"p68\",\"p69\",\"p70\",\"p71\",\"p72\",\"p73\",\"p74\",\"p75\",\"p76\",\"p77\",\"p78\",\"p79\",\"p80\",\"p81\",\"p82\",\"p83\",\"p84\",\"p85\",\"p86\",\"p87\",\"p88\",\"p89\",\"p90\",\"p91\",\"p92\",\"p93\",\"p94\",\"p95\",\"p96\",\"p97\",\"p98\",\"p99\",\"p100\",\"p101\",\"p102\",\"p103\",\"p104\",\"p105\",\"p106\",\"p107\",\"p108\",\"p109\",\"p110\",\"p111\",\"p112\",\"p113\",\"p114\",\"p115\",\"p116\",\"p117\",\"p118\",\"p119\",\"p120\",\"p121\",\"p122\",\"p123\",\"p124\",\"p125\",\"p126\",\"p127\",\"p128\",\"p129\",\"p130\",\"p131\",\"p132\",\"p133\",\"p134\",\"p135\",\"p136\",\"p137\",\"p138\",\"p139\",\"p140\",\"p141\",\"p142\",\"p143\",\"p144\",\"p145\",\"p146\",\"p147\",\"p148\",\"p149\",\"p150\",\"p151\",\"p152\",\"p153\",\"p154\",\"p155\",\"p156\",\"p157\",\"p158\",\"p159\",\"p160\",\"p161\",\"p162\",\"p163\",\"p164\",\"p165\",\"p166\",\"p167\",\"p168\",\"p169\",\"p170\",\"p171\",\"p172\",\"p173\",\"p174\",\"p175\",\"p176\",\"p177\",\"p178\",\"p179\",\"p180\",\"p181\",\"p182\",\"p183\",\"p184\",\"p185\",\"p186\",\"p187\",\"p188\",\"p189\",\"p190\",\"p191\",\"p192\",\"p193\",\"p194\",\"p195\",\"p196\",\"p197\",\"p198\",\"p199\",\"p200\",\"p201\",\"p202\",\"p203\",\"p204\",\"p205\",\"p206\",\"p207\",\"p208\",\"p209\",\"p210\",\"p211\",\"p212\",\"p213\",\"p214\",\"p215\",\"p216\",\"p217\",\"p218\",\"p219\",\"p220\",\"p221\",\"p222\",\"p223\",\"p224\",\"p225\",\"p226\",\"p227\",\"p228\",\"p229\",\"p230\",\"p231\",\"p232\",\"p233\",\"p234\",\"p235\",\"p236\",\"p237\",\"p238\",\"p239\",\"p240\",\"p241\",\"p242\",\"p243\",\"p244\",\"p245\",\"p246\",\"p247\",\"p248\",\"p249\",\"p250\",\"p251\",\"p252\"]', 0, 1602266535, 1602266535);
INSERT INTO `osc_location_group` VALUES (5, '[SUPPLY PRODUCT] - CW', '{\"include\":\"g3_c14_c234\",\"exclude\":\"\"}', '[\"c66\",\"c108\",\"c119\",\"c122\",\"c127\",\"c128\",\"c129\",\"c137\",\"c166\",\"c233\",\"c14\",\"p103\",\"p104\",\"p105\",\"p106\",\"p107\",\"p108\",\"p109\",\"p110\",\"c234\",\"p1\",\"p2\",\"p3\",\"p4\",\"p5\",\"p6\",\"p7\",\"p8\",\"p9\",\"p10\",\"p11\",\"p12\",\"p13\",\"p14\",\"p15\",\"p16\",\"p17\",\"p18\",\"p19\",\"p20\",\"p21\",\"p22\",\"p23\",\"p24\",\"p25\",\"p26\",\"p27\",\"p28\",\"p29\",\"p30\",\"p31\",\"p32\",\"p33\",\"p34\",\"p35\",\"p36\",\"p37\",\"p38\",\"p39\",\"p40\",\"p41\",\"p42\",\"p43\",\"p44\",\"p45\",\"p46\",\"p47\",\"p48\",\"p49\",\"p50\",\"p51\",\"p52\",\"p53\",\"p54\",\"p55\",\"p56\",\"p57\",\"p58\",\"p59\",\"p60\",\"p61\",\"p62\"]', 0, 1602266632, 1602266632);
INSERT INTO `osc_location_group` VALUES (6, '[SUPPLY PRODUCT] - Harrier', '{\"include\":\"g3\",\"exclude\":\"\"}', '[\"c66\",\"c108\",\"c119\",\"c122\",\"c127\",\"c128\",\"c129\",\"c137\",\"c166\",\"c233\"]', 0, 1602266666, 1602266666);
INSERT INTO `osc_location_group` VALUES (7, '[SUPPLY PRODUCT] - Prima', '{\"include\":\"c14_c159\",\"exclude\":\"\"}', '[\"c14\",\"p103\",\"p104\",\"p105\",\"p106\",\"p107\",\"p108\",\"p109\",\"p110\",\"c159\"]', 0, 1602266850, 1602266850);
INSERT INTO `osc_location_group` VALUES (8, '[SUPPLY PRODUCT] - Tea Launch', '{\"include\":\"*\",\"exclude\":\"g3\"}', '[\"c1\",\"c2\",\"c3\",\"c4\",\"c5\",\"c6\",\"c7\",\"c8\",\"c9\",\"c10\",\"c11\",\"c12\",\"c13\",\"c14\",\"c15\",\"c16\",\"c17\",\"c18\",\"c19\",\"c20\",\"c21\",\"c22\",\"c23\",\"c24\",\"c25\",\"c26\",\"c27\",\"c28\",\"c29\",\"c30\",\"c31\",\"c32\",\"c33\",\"c34\",\"c35\",\"c36\",\"c37\",\"c38\",\"c39\",\"c40\",\"c41\",\"c42\",\"c43\",\"c44\",\"c45\",\"c46\",\"c47\",\"c48\",\"c49\",\"c50\",\"c51\",\"c52\",\"c53\",\"c54\",\"c55\",\"c56\",\"c57\",\"c58\",\"c59\",\"c60\",\"c61\",\"c62\",\"c63\",\"c64\",\"c65\",\"c67\",\"c68\",\"c69\",\"c70\",\"c71\",\"c72\",\"c73\",\"c74\",\"c75\",\"c76\",\"c77\",\"c78\",\"c79\",\"c80\",\"c81\",\"c82\",\"c83\",\"c84\",\"c85\",\"c86\",\"c87\",\"c88\",\"c89\",\"c90\",\"c91\",\"c92\",\"c93\",\"c94\",\"c95\",\"c96\",\"c97\",\"c98\",\"c99\",\"c100\",\"c101\",\"c102\",\"c103\",\"c104\",\"c105\",\"c106\",\"c107\",\"c109\",\"c110\",\"c111\",\"c112\",\"c113\",\"c114\",\"c115\",\"c116\",\"c117\",\"c118\",\"c120\",\"c121\",\"c123\",\"c124\",\"c125\",\"c126\",\"c130\",\"c131\",\"c132\",\"c133\",\"c134\",\"c135\",\"c136\",\"c138\",\"c139\",\"c140\",\"c141\",\"c142\",\"c143\",\"c144\",\"c145\",\"c146\",\"c147\",\"c148\",\"c149\",\"c150\",\"c151\",\"c152\",\"c153\",\"c154\",\"c155\",\"c156\",\"c157\",\"c158\",\"c159\",\"c160\",\"c161\",\"c162\",\"c163\",\"c164\",\"c165\",\"c167\",\"c168\",\"c169\",\"c170\",\"c171\",\"c172\",\"c173\",\"c174\",\"c175\",\"c176\",\"c177\",\"c178\",\"c179\",\"c180\",\"c181\",\"c182\",\"c183\",\"c184\",\"c185\",\"c186\",\"c187\",\"c188\",\"c189\",\"c190\",\"c191\",\"c192\",\"c193\",\"c194\",\"c195\",\"c196\",\"c197\",\"c198\",\"c199\",\"c200\",\"c201\",\"c202\",\"c203\",\"c204\",\"c205\",\"c206\",\"c207\",\"c208\",\"c209\",\"c210\",\"c211\",\"c212\",\"c213\",\"c214\",\"c215\",\"c216\",\"c217\",\"c218\",\"c219\",\"c220\",\"c221\",\"c222\",\"c223\",\"c224\",\"c225\",\"c226\",\"c227\",\"c228\",\"c229\",\"c230\",\"c231\",\"c232\",\"c234\",\"c235\",\"c236\",\"c237\",\"c238\",\"c239\",\"c240\",\"c241\",\"c242\",\"c243\",\"c244\",\"c245\",\"c246\",\"p1\",\"p2\",\"p3\",\"p4\",\"p5\",\"p6\",\"p7\",\"p8\",\"p9\",\"p10\",\"p11\",\"p12\",\"p13\",\"p14\",\"p15\",\"p16\",\"p17\",\"p18\",\"p19\",\"p20\",\"p21\",\"p22\",\"p23\",\"p24\",\"p25\",\"p26\",\"p27\",\"p28\",\"p29\",\"p30\",\"p31\",\"p32\",\"p33\",\"p34\",\"p35\",\"p36\",\"p37\",\"p38\",\"p39\",\"p40\",\"p41\",\"p42\",\"p43\",\"p44\",\"p45\",\"p46\",\"p47\",\"p48\",\"p49\",\"p50\",\"p51\",\"p52\",\"p53\",\"p54\",\"p55\",\"p56\",\"p57\",\"p58\",\"p59\",\"p60\",\"p61\",\"p62\",\"p63\",\"p64\",\"p65\",\"p66\",\"p67\",\"p68\",\"p69\",\"p70\",\"p71\",\"p72\",\"p73\",\"p74\",\"p75\",\"p76\",\"p77\",\"p78\",\"p79\",\"p80\",\"p81\",\"p82\",\"p83\",\"p84\",\"p85\",\"p86\",\"p87\",\"p88\",\"p89\",\"p90\",\"p91\",\"p92\",\"p93\",\"p94\",\"p95\",\"p96\",\"p97\",\"p98\",\"p99\",\"p100\",\"p101\",\"p102\",\"p103\",\"p104\",\"p105\",\"p106\",\"p107\",\"p108\",\"p109\",\"p110\",\"p111\",\"p112\",\"p113\",\"p114\",\"p115\",\"p116\",\"p117\",\"p118\",\"p119\",\"p120\",\"p121\",\"p122\",\"p123\",\"p124\",\"p125\",\"p126\",\"p127\",\"p128\",\"p129\",\"p130\",\"p131\",\"p132\",\"p133\",\"p134\",\"p135\",\"p136\",\"p137\",\"p138\",\"p139\",\"p140\",\"p141\",\"p142\",\"p143\",\"p144\",\"p145\",\"p146\",\"p147\",\"p148\",\"p149\",\"p150\",\"p151\",\"p152\",\"p153\",\"p154\",\"p155\",\"p156\",\"p157\",\"p158\",\"p159\",\"p160\",\"p161\",\"p162\",\"p163\",\"p164\",\"p165\",\"p166\",\"p167\",\"p168\",\"p169\",\"p170\",\"p171\",\"p172\",\"p173\",\"p174\",\"p175\",\"p176\",\"p177\",\"p178\",\"p179\",\"p180\",\"p181\",\"p182\",\"p183\",\"p184\",\"p185\",\"p186\",\"p187\",\"p188\",\"p189\",\"p190\",\"p191\",\"p192\",\"p193\",\"p194\",\"p195\",\"p196\",\"p197\",\"p198\",\"p199\",\"p200\",\"p201\",\"p202\",\"p203\",\"p204\",\"p205\",\"p206\",\"p207\",\"p208\",\"p209\",\"p210\",\"p211\",\"p212\",\"p213\",\"p214\",\"p215\",\"p216\",\"p217\",\"p218\",\"p219\",\"p220\",\"p221\",\"p222\",\"p223\",\"p224\",\"p225\",\"p226\",\"p227\",\"p228\",\"p229\",\"p230\",\"p231\",\"p232\",\"p233\",\"p234\",\"p235\",\"p236\",\"p237\",\"p238\",\"p239\",\"p240\",\"p241\",\"p242\",\"p243\",\"p244\",\"p245\",\"p246\",\"p247\",\"p248\",\"p249\",\"p250\",\"p251\",\"p252\"]', 0, 1602266924, 1602266924);
INSERT INTO `osc_location_group` VALUES (9, '[SUPPLY PRODUCT] - Custom Cat', '{\"include\":\"*\",\"exclude\":\"g3\"}', '[\"c1\",\"c2\",\"c3\",\"c4\",\"c5\",\"c6\",\"c7\",\"c8\",\"c9\",\"c10\",\"c11\",\"c12\",\"c13\",\"c14\",\"c15\",\"c16\",\"c17\",\"c18\",\"c19\",\"c20\",\"c21\",\"c22\",\"c23\",\"c24\",\"c25\",\"c26\",\"c27\",\"c28\",\"c29\",\"c30\",\"c31\",\"c32\",\"c33\",\"c34\",\"c35\",\"c36\",\"c37\",\"c38\",\"c39\",\"c40\",\"c41\",\"c42\",\"c43\",\"c44\",\"c45\",\"c46\",\"c47\",\"c48\",\"c49\",\"c50\",\"c51\",\"c52\",\"c53\",\"c54\",\"c55\",\"c56\",\"c57\",\"c58\",\"c59\",\"c60\",\"c61\",\"c62\",\"c63\",\"c64\",\"c65\",\"c67\",\"c68\",\"c69\",\"c70\",\"c71\",\"c72\",\"c73\",\"c74\",\"c75\",\"c76\",\"c77\",\"c78\",\"c79\",\"c80\",\"c81\",\"c82\",\"c83\",\"c84\",\"c85\",\"c86\",\"c87\",\"c88\",\"c89\",\"c90\",\"c91\",\"c92\",\"c93\",\"c94\",\"c95\",\"c96\",\"c97\",\"c98\",\"c99\",\"c100\",\"c101\",\"c102\",\"c103\",\"c104\",\"c105\",\"c106\",\"c107\",\"c109\",\"c110\",\"c111\",\"c112\",\"c113\",\"c114\",\"c115\",\"c116\",\"c117\",\"c118\",\"c120\",\"c121\",\"c123\",\"c124\",\"c125\",\"c126\",\"c130\",\"c131\",\"c132\",\"c133\",\"c134\",\"c135\",\"c136\",\"c138\",\"c139\",\"c140\",\"c141\",\"c142\",\"c143\",\"c144\",\"c145\",\"c146\",\"c147\",\"c148\",\"c149\",\"c150\",\"c151\",\"c152\",\"c153\",\"c154\",\"c155\",\"c156\",\"c157\",\"c158\",\"c159\",\"c160\",\"c161\",\"c162\",\"c163\",\"c164\",\"c165\",\"c167\",\"c168\",\"c169\",\"c170\",\"c171\",\"c172\",\"c173\",\"c174\",\"c175\",\"c176\",\"c177\",\"c178\",\"c179\",\"c180\",\"c181\",\"c182\",\"c183\",\"c184\",\"c185\",\"c186\",\"c187\",\"c188\",\"c189\",\"c190\",\"c191\",\"c192\",\"c193\",\"c194\",\"c195\",\"c196\",\"c197\",\"c198\",\"c199\",\"c200\",\"c201\",\"c202\",\"c203\",\"c204\",\"c205\",\"c206\",\"c207\",\"c208\",\"c209\",\"c210\",\"c211\",\"c212\",\"c213\",\"c214\",\"c215\",\"c216\",\"c217\",\"c218\",\"c219\",\"c220\",\"c221\",\"c222\",\"c223\",\"c224\",\"c225\",\"c226\",\"c227\",\"c228\",\"c229\",\"c230\",\"c231\",\"c232\",\"c234\",\"c235\",\"c236\",\"c237\",\"c238\",\"c239\",\"c240\",\"c241\",\"c242\",\"c243\",\"c244\",\"c245\",\"c246\",\"p1\",\"p2\",\"p3\",\"p4\",\"p5\",\"p6\",\"p7\",\"p8\",\"p9\",\"p10\",\"p11\",\"p12\",\"p13\",\"p14\",\"p15\",\"p16\",\"p17\",\"p18\",\"p19\",\"p20\",\"p21\",\"p22\",\"p23\",\"p24\",\"p25\",\"p26\",\"p27\",\"p28\",\"p29\",\"p30\",\"p31\",\"p32\",\"p33\",\"p34\",\"p35\",\"p36\",\"p37\",\"p38\",\"p39\",\"p40\",\"p41\",\"p42\",\"p43\",\"p44\",\"p45\",\"p46\",\"p47\",\"p48\",\"p49\",\"p50\",\"p51\",\"p52\",\"p53\",\"p54\",\"p55\",\"p56\",\"p57\",\"p58\",\"p59\",\"p60\",\"p61\",\"p62\",\"p63\",\"p64\",\"p65\",\"p66\",\"p67\",\"p68\",\"p69\",\"p70\",\"p71\",\"p72\",\"p73\",\"p74\",\"p75\",\"p76\",\"p77\",\"p78\",\"p79\",\"p80\",\"p81\",\"p82\",\"p83\",\"p84\",\"p85\",\"p86\",\"p87\",\"p88\",\"p89\",\"p90\",\"p91\",\"p92\",\"p93\",\"p94\",\"p95\",\"p96\",\"p97\",\"p98\",\"p99\",\"p100\",\"p101\",\"p102\",\"p103\",\"p104\",\"p105\",\"p106\",\"p107\",\"p108\",\"p109\",\"p110\",\"p111\",\"p112\",\"p113\",\"p114\",\"p115\",\"p116\",\"p117\",\"p118\",\"p119\",\"p120\",\"p121\",\"p122\",\"p123\",\"p124\",\"p125\",\"p126\",\"p127\",\"p128\",\"p129\",\"p130\",\"p131\",\"p132\",\"p133\",\"p134\",\"p135\",\"p136\",\"p137\",\"p138\",\"p139\",\"p140\",\"p141\",\"p142\",\"p143\",\"p144\",\"p145\",\"p146\",\"p147\",\"p148\",\"p149\",\"p150\",\"p151\",\"p152\",\"p153\",\"p154\",\"p155\",\"p156\",\"p157\",\"p158\",\"p159\",\"p160\",\"p161\",\"p162\",\"p163\",\"p164\",\"p165\",\"p166\",\"p167\",\"p168\",\"p169\",\"p170\",\"p171\",\"p172\",\"p173\",\"p174\",\"p175\",\"p176\",\"p177\",\"p178\",\"p179\",\"p180\",\"p181\",\"p182\",\"p183\",\"p184\",\"p185\",\"p186\",\"p187\",\"p188\",\"p189\",\"p190\",\"p191\",\"p192\",\"p193\",\"p194\",\"p195\",\"p196\",\"p197\",\"p198\",\"p199\",\"p200\",\"p201\",\"p202\",\"p203\",\"p204\",\"p205\",\"p206\",\"p207\",\"p208\",\"p209\",\"p210\",\"p211\",\"p212\",\"p213\",\"p214\",\"p215\",\"p216\",\"p217\",\"p218\",\"p219\",\"p220\",\"p221\",\"p222\",\"p223\",\"p224\",\"p225\",\"p226\",\"p227\",\"p228\",\"p229\",\"p230\",\"p231\",\"p232\",\"p233\",\"p234\",\"p235\",\"p236\",\"p237\",\"p238\",\"p239\",\"p240\",\"p241\",\"p242\",\"p243\",\"p244\",\"p245\",\"p246\",\"p247\",\"p248\",\"p249\",\"p250\",\"p251\",\"p252\"]', 0, 1602266953, 1602266953);
INSERT INTO `osc_location_group` VALUES (10, '[SUPPLY PRODUCT] - Print Geek', '{\"include\":\"c40\",\"exclude\":\"\"}', '[\"c40\",\"p90\",\"p91\",\"p92\",\"p93\",\"p94\",\"p95\",\"p96\",\"p97\",\"p98\",\"p99\",\"p100\",\"p101\",\"p102\"]', 0, 1602266982, 1602266982);
INSERT INTO `osc_location_group` VALUES (11, '[SHIPPING] - US - Process 4 - Estimate 7', '{\"include\":\"c234\",\"exclude\":\"g12\"}', '[\"c234\",\"p1\",\"p3\",\"p4\",\"p5\",\"p6\",\"p7\",\"p8\",\"p9\",\"p10\",\"p11\",\"p12\",\"p13\",\"p14\",\"p15\",\"p16\",\"p19\",\"p20\",\"p21\",\"p22\",\"p23\",\"p24\",\"p25\",\"p26\",\"p27\",\"p28\",\"p29\",\"p30\",\"p31\",\"p32\",\"p33\",\"p34\",\"p35\",\"p36\",\"p37\",\"p38\",\"p39\",\"p40\",\"p41\",\"p42\",\"p43\",\"p44\",\"p45\",\"p46\",\"p47\",\"p48\",\"p50\",\"p51\",\"p52\",\"p53\",\"p54\",\"p55\",\"p56\",\"p58\",\"p59\",\"p60\",\"p61\",\"p62\"]', 0, 1602268722, 1602268722);
INSERT INTO `osc_location_group` VALUES (12, '[SHIPPING] - US - Process 4 - Estimate 25', '{\"include\":\"p2_p17_p18_p49_p57\",\"exclude\":\"\"}', '[\"p2\",\"p17\",\"p18\",\"p49\",\"p57\"]', 0, 1602268969, 1602268969);
INSERT INTO `osc_location_group` VALUES (13, '[SHIPPING] - EU - Process 4 - Estimate 20', '{\"include\":\"c15_c22_c35_c55_c57_c58_c60_c70_c75_c76_c83_c86_c101_c107_c110_c122_c156_c177_c178_c181_c200_c201_c208_c215\",\"exclude\":\"\"}', '[\"c15\",\"c22\",\"c35\",\"c55\",\"c57\",\"c58\",\"c60\",\"c70\",\"c75\",\"c76\",\"c83\",\"c86\",\"c101\",\"c107\",\"c110\",\"p143\",\"p144\",\"p145\",\"p146\",\"p147\",\"p148\",\"p149\",\"p150\",\"p151\",\"p152\",\"p153\",\"p154\",\"p155\",\"p156\",\"p157\",\"p158\",\"p159\",\"p160\",\"p161\",\"p162\",\"p163\",\"p164\",\"p165\",\"p166\",\"p167\",\"p168\",\"p169\",\"p170\",\"p171\",\"p172\",\"p173\",\"p174\",\"p175\",\"p176\",\"p177\",\"p178\",\"p179\",\"p180\",\"p181\",\"p182\",\"p183\",\"p184\",\"p185\",\"p186\",\"p187\",\"p188\",\"p189\",\"p190\",\"p191\",\"p192\",\"p193\",\"p194\",\"p195\",\"p196\",\"p197\",\"p198\",\"p199\",\"p200\",\"p201\",\"p202\",\"p203\",\"p204\",\"p205\",\"p206\",\"p207\",\"p208\",\"p209\",\"p210\",\"p211\",\"p212\",\"p213\",\"p214\",\"p215\",\"p216\",\"p217\",\"p218\",\"p219\",\"p220\",\"p221\",\"p222\",\"p223\",\"p224\",\"p225\",\"p226\",\"p227\",\"p228\",\"p229\",\"p230\",\"p231\",\"p232\",\"p233\",\"p234\",\"p235\",\"p236\",\"p237\",\"p238\",\"p239\",\"p240\",\"p241\",\"p242\",\"p243\",\"p244\",\"p245\",\"p246\",\"p247\",\"p248\",\"p249\",\"p250\",\"p251\",\"p252\",\"c122\",\"c156\",\"c177\",\"c178\",\"c181\",\"c200\",\"c201\",\"c208\",\"c215\"]', 0, 1602269507, 1602269507);
INSERT INTO `osc_location_group` VALUES (14, '[SHIPPING] - EU - Process 5 - Estimate 8', '{\"include\":\"c66_c108_c119_c122_c127_c128_c129_c137_c166\",\"exclude\":\"\"}', '[\"c66\",\"c108\",\"c119\",\"c122\",\"c127\",\"c128\",\"c129\",\"c137\",\"c166\"]', 0, 1602269588, 1602269588);
INSERT INTO `osc_location_group` VALUES (15, '[SHIPPING] - US (All Provinces) - Special Product - Process 4 - Estimate 7', '{\"include\":\"c234\",\"exclude\":\"g16\"}', '[\"c234\",\"p1\",\"p3\",\"p4\",\"p5\",\"p6\",\"p7\",\"p8\",\"p9\",\"p10\",\"p11\",\"p12\",\"p13\",\"p14\",\"p15\",\"p16\",\"p17\",\"p19\",\"p20\",\"p21\",\"p22\",\"p23\",\"p24\",\"p25\",\"p26\",\"p27\",\"p28\",\"p29\",\"p30\",\"p31\",\"p32\",\"p33\",\"p34\",\"p35\",\"p36\",\"p37\",\"p38\",\"p39\",\"p40\",\"p41\",\"p42\",\"p43\",\"p44\",\"p45\",\"p46\",\"p47\",\"p48\",\"p50\",\"p51\",\"p52\",\"p53\",\"p54\",\"p55\",\"p56\",\"p58\",\"p59\",\"p60\",\"p61\",\"p62\"]', 0, 1602270418, 1602270418);
INSERT INTO `osc_location_group` VALUES (16, '[SHIPPING] - US (Alaska) (Hawaii) (Puerto Rico) (Virgin Islands) - Special Product - Process 4 - Estimate 30', '{\"include\":\"p2_p18_p49_p57\",\"exclude\":\"\"}', '[\"p2\",\"p18\",\"p49\",\"p57\"]', 0, 1602270490, 1602270490);
INSERT INTO `osc_location_group` VALUES (17, '[SHIPPING] - EU - Facemask - Process 5 - Estimate 8', '{\"include\":\"c66_c108_c119_c122_c127_c128_c129_c137_c159_c166\",\"exclude\":\"\"}', '[\"c66\",\"c108\",\"c119\",\"c122\",\"c127\",\"c128\",\"c129\",\"c137\",\"c159\",\"c166\"]', 0, 1602270624, 1602270624);
INSERT INTO `osc_location_group` VALUES (18, '[SHIPPING] - UK (All Provinces) - Process 5 - Estimate 5', '{\"include\":\"c233\",\"exclude\":\"\"}', '[\"c233\"]', 0, 1602274397, 1602274397);
INSERT INTO `osc_location_group` VALUES (19, '[SHIPPING] - AU (All Provinces) - Process 4 - Estimate 6', '{\"include\":\"c14_p103_p104_p105_p106_p107_p108_p109_p110\",\"exclude\":\"\"}', '[\"c14\",\"p103\",\"p104\",\"p105\",\"p106\",\"p107\",\"p108\",\"p109\",\"p110\"]', 0, 1602274518, 1602274518);
INSERT INTO `osc_location_group` VALUES (20, '[SHIPPING] - CA (All Provinces) - Process 4 - Estimate 20', '{\"include\":\"c40_p90_p91_p92_p93_p94_p95_p96_p97_p98_p99_p100_p101_p102\",\"exclude\":\"\"}', '[\"c40\",\"p90\",\"p91\",\"p92\",\"p93\",\"p94\",\"p95\",\"p96\",\"p97\",\"p98\",\"p99\",\"p100\",\"p101\",\"p102\"]', 0, 1602274666, 1602274666);
INSERT INTO `osc_location_group` VALUES (21, '[SHIPPING] - NZ (All Provinces) - Process 4 - Estimate 8', '{\"include\":\"c159\",\"exclude\":\"\"}', '[\"c159\"]', 0, 1602274731, 1602274731);
COMMIT;

-- ----------------------------
-- Table structure for osc_location_province
-- ----------------------------
DROP TABLE IF EXISTS `osc_location_province`;
CREATE TABLE `osc_location_province` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `country_id` int(10) NOT NULL,
  `country_code` varchar(2) NOT NULL,
  `province_code` varchar(255) NOT NULL,
  `province_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `country_province_UNIQUE` (`country_code`,`province_code`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=253 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_location_province
-- ----------------------------
BEGIN;
INSERT INTO `osc_location_province` VALUES (1, 234, 'US', 'AL', 'Alabama');
INSERT INTO `osc_location_province` VALUES (2, 234, 'US', 'AK', 'Alaska');
INSERT INTO `osc_location_province` VALUES (3, 234, 'US', 'AS', 'American Samoa');
INSERT INTO `osc_location_province` VALUES (4, 234, 'US', 'AZ', 'Arizona');
INSERT INTO `osc_location_province` VALUES (5, 234, 'US', 'AR', 'Arkansas');
INSERT INTO `osc_location_province` VALUES (6, 234, 'US', 'AE', 'Armed Forces Middle East');
INSERT INTO `osc_location_province` VALUES (7, 234, 'US', 'AA', 'Armed Forces Americas');
INSERT INTO `osc_location_province` VALUES (8, 234, 'US', 'AP', 'Armed Forces Pacific');
INSERT INTO `osc_location_province` VALUES (9, 234, 'US', 'CA', 'California');
INSERT INTO `osc_location_province` VALUES (10, 234, 'US', 'CO', 'Colorado');
INSERT INTO `osc_location_province` VALUES (11, 234, 'US', 'CT', 'Connecticut');
INSERT INTO `osc_location_province` VALUES (12, 234, 'US', 'DE', 'Delaware');
INSERT INTO `osc_location_province` VALUES (13, 234, 'US', 'DC', 'District of Columbia');
INSERT INTO `osc_location_province` VALUES (14, 234, 'US', 'FM', 'Federated States Of Micronesia');
INSERT INTO `osc_location_province` VALUES (15, 234, 'US', 'FL', 'Florida');
INSERT INTO `osc_location_province` VALUES (16, 234, 'US', 'GA', 'Georgia');
INSERT INTO `osc_location_province` VALUES (17, 234, 'US', 'GU', 'Guam');
INSERT INTO `osc_location_province` VALUES (18, 234, 'US', 'HI', 'Hawaii');
INSERT INTO `osc_location_province` VALUES (19, 234, 'US', 'ID', 'Idaho');
INSERT INTO `osc_location_province` VALUES (20, 234, 'US', 'IL', 'Illinois');
INSERT INTO `osc_location_province` VALUES (21, 234, 'US', 'IN', 'Indiana');
INSERT INTO `osc_location_province` VALUES (22, 234, 'US', 'IA', 'Iowa');
INSERT INTO `osc_location_province` VALUES (23, 234, 'US', 'KS', 'Kansas');
INSERT INTO `osc_location_province` VALUES (24, 234, 'US', 'KY', 'Kentucky');
INSERT INTO `osc_location_province` VALUES (25, 234, 'US', 'LA', 'Louisiana');
INSERT INTO `osc_location_province` VALUES (26, 234, 'US', 'ME', 'Maine');
INSERT INTO `osc_location_province` VALUES (27, 234, 'US', 'MH', 'Marshall Islands');
INSERT INTO `osc_location_province` VALUES (28, 234, 'US', 'MD', 'Maryland');
INSERT INTO `osc_location_province` VALUES (29, 234, 'US', 'MA', 'Massachusetts');
INSERT INTO `osc_location_province` VALUES (30, 234, 'US', 'MI', 'Michigan');
INSERT INTO `osc_location_province` VALUES (31, 234, 'US', 'MN', 'Minnesota');
INSERT INTO `osc_location_province` VALUES (32, 234, 'US', 'MS', 'Mississippi');
INSERT INTO `osc_location_province` VALUES (33, 234, 'US', 'MO', 'Missouri');
INSERT INTO `osc_location_province` VALUES (34, 234, 'US', 'MT', 'Montana');
INSERT INTO `osc_location_province` VALUES (35, 234, 'US', 'NE', 'Nebraska');
INSERT INTO `osc_location_province` VALUES (36, 234, 'US', 'NV', 'Nevada');
INSERT INTO `osc_location_province` VALUES (37, 234, 'US', 'NH', 'New Hampshire');
INSERT INTO `osc_location_province` VALUES (38, 234, 'US', 'NJ', 'New Jersey');
INSERT INTO `osc_location_province` VALUES (39, 234, 'US', 'NM', 'New Mexico');
INSERT INTO `osc_location_province` VALUES (40, 234, 'US', 'NY', 'New York');
INSERT INTO `osc_location_province` VALUES (41, 234, 'US', 'NC', 'North Carolina');
INSERT INTO `osc_location_province` VALUES (42, 234, 'US', 'ND', 'North Dakota');
INSERT INTO `osc_location_province` VALUES (43, 234, 'US', 'MP', 'Northern Mariana Islands');
INSERT INTO `osc_location_province` VALUES (44, 234, 'US', 'OH', 'Ohio');
INSERT INTO `osc_location_province` VALUES (45, 234, 'US', 'OK', 'Oklahoma');
INSERT INTO `osc_location_province` VALUES (46, 234, 'US', 'OR', 'Oregon');
INSERT INTO `osc_location_province` VALUES (47, 234, 'US', 'PW', 'Palau');
INSERT INTO `osc_location_province` VALUES (48, 234, 'US', 'PA', 'Pennsylvania');
INSERT INTO `osc_location_province` VALUES (49, 234, 'US', 'PR', 'Puerto Rico');
INSERT INTO `osc_location_province` VALUES (50, 234, 'US', 'RI', 'Rhode Island');
INSERT INTO `osc_location_province` VALUES (51, 234, 'US', 'SC', 'South Carolina');
INSERT INTO `osc_location_province` VALUES (52, 234, 'US', 'SD', 'South Dakota');
INSERT INTO `osc_location_province` VALUES (53, 234, 'US', 'TN', 'Tennessee');
INSERT INTO `osc_location_province` VALUES (54, 234, 'US', 'TX', 'Texas');
INSERT INTO `osc_location_province` VALUES (55, 234, 'US', 'UT', 'Utah');
INSERT INTO `osc_location_province` VALUES (56, 234, 'US', 'VT', 'Vermont');
INSERT INTO `osc_location_province` VALUES (57, 234, 'US', 'VI', 'Virgin Islands');
INSERT INTO `osc_location_province` VALUES (58, 234, 'US', 'VA', 'Virginia');
INSERT INTO `osc_location_province` VALUES (59, 234, 'US', 'WA', 'Washington');
INSERT INTO `osc_location_province` VALUES (60, 234, 'US', 'WV', 'West Virginia');
INSERT INTO `osc_location_province` VALUES (61, 234, 'US', 'WI', 'Wisconsin');
INSERT INTO `osc_location_province` VALUES (62, 234, 'US', 'WY', 'Wyoming');
INSERT INTO `osc_location_province` VALUES (63, 32, 'BR', 'AC', 'Acre');
INSERT INTO `osc_location_province` VALUES (64, 32, 'BR', 'AL', 'Alagoas');
INSERT INTO `osc_location_province` VALUES (65, 32, 'BR', 'AP', 'Amapá');
INSERT INTO `osc_location_province` VALUES (66, 32, 'BR', 'AM', 'Amazonas');
INSERT INTO `osc_location_province` VALUES (67, 32, 'BR', 'BA', 'Bahia');
INSERT INTO `osc_location_province` VALUES (68, 32, 'BR', 'CE', 'Ceará');
INSERT INTO `osc_location_province` VALUES (69, 32, 'BR', 'ES', 'Espírito Santo');
INSERT INTO `osc_location_province` VALUES (70, 32, 'BR', 'GO', 'Goiás');
INSERT INTO `osc_location_province` VALUES (71, 32, 'BR', 'MA', 'Maranhão');
INSERT INTO `osc_location_province` VALUES (72, 32, 'BR', 'MT', 'Mato Grosso');
INSERT INTO `osc_location_province` VALUES (73, 32, 'BR', 'MS', 'Mato Grosso do Sul');
INSERT INTO `osc_location_province` VALUES (74, 32, 'BR', 'MG', 'Minas Gerais');
INSERT INTO `osc_location_province` VALUES (75, 32, 'BR', 'PA', 'Pará');
INSERT INTO `osc_location_province` VALUES (76, 32, 'BR', 'PB', 'Paraíba');
INSERT INTO `osc_location_province` VALUES (77, 32, 'BR', 'PR', 'Paraná');
INSERT INTO `osc_location_province` VALUES (78, 32, 'BR', 'PE', 'Pernambuco');
INSERT INTO `osc_location_province` VALUES (79, 32, 'BR', 'PI', 'Piauí');
INSERT INTO `osc_location_province` VALUES (80, 32, 'BR', 'RJ', 'Rio de Janeiro');
INSERT INTO `osc_location_province` VALUES (81, 32, 'BR', 'RN', 'Rio Grande do Norte');
INSERT INTO `osc_location_province` VALUES (82, 32, 'BR', 'RS', 'Rio Grande do Sul');
INSERT INTO `osc_location_province` VALUES (83, 32, 'BR', 'RO', 'Rondônia');
INSERT INTO `osc_location_province` VALUES (84, 32, 'BR', 'RR', 'Roraima');
INSERT INTO `osc_location_province` VALUES (85, 32, 'BR', 'SC', 'Santa Catarina');
INSERT INTO `osc_location_province` VALUES (86, 32, 'BR', 'SP', 'São Paulo');
INSERT INTO `osc_location_province` VALUES (87, 32, 'BR', 'SE', 'Sergipe');
INSERT INTO `osc_location_province` VALUES (88, 32, 'BR', 'TO', 'Tocantins');
INSERT INTO `osc_location_province` VALUES (89, 32, 'BR', 'DF', 'Distrito Federal');
INSERT INTO `osc_location_province` VALUES (90, 40, 'CA', 'AB', 'Alberta');
INSERT INTO `osc_location_province` VALUES (91, 40, 'CA', 'BC', 'British Columbia');
INSERT INTO `osc_location_province` VALUES (92, 40, 'CA', 'MB', 'Manitoba');
INSERT INTO `osc_location_province` VALUES (93, 40, 'CA', 'NL', 'Newfoundland and Labrador');
INSERT INTO `osc_location_province` VALUES (94, 40, 'CA', 'NB', 'New Brunswick');
INSERT INTO `osc_location_province` VALUES (95, 40, 'CA', 'NS', 'Nova Scotia');
INSERT INTO `osc_location_province` VALUES (96, 40, 'CA', 'NT', 'Northwest Territories');
INSERT INTO `osc_location_province` VALUES (97, 40, 'CA', 'NU', 'Nunavut');
INSERT INTO `osc_location_province` VALUES (98, 40, 'CA', 'ON', 'Ontario');
INSERT INTO `osc_location_province` VALUES (99, 40, 'CA', 'PE', 'Prince Edward Island');
INSERT INTO `osc_location_province` VALUES (100, 40, 'CA', 'QC', 'Quebec');
INSERT INTO `osc_location_province` VALUES (101, 40, 'CA', 'SK', 'Saskatchewan');
INSERT INTO `osc_location_province` VALUES (102, 40, 'CA', 'YT', 'Yukon Territory');
INSERT INTO `osc_location_province` VALUES (103, 14, 'AU', 'ACT', 'Australian Capital Territory');
INSERT INTO `osc_location_province` VALUES (104, 14, 'AU', 'NSW', 'New South Wales');
INSERT INTO `osc_location_province` VALUES (105, 14, 'AU', 'VIC', 'Victoria');
INSERT INTO `osc_location_province` VALUES (106, 14, 'AU', 'QLD', 'Queensland');
INSERT INTO `osc_location_province` VALUES (107, 14, 'AU', 'SA', 'South Australia');
INSERT INTO `osc_location_province` VALUES (108, 14, 'AU', 'TAS', 'Tasmania');
INSERT INTO `osc_location_province` VALUES (109, 14, 'AU', 'WA', 'Western Australia');
INSERT INTO `osc_location_province` VALUES (110, 14, 'AU', 'NT', 'Northern Territory');
INSERT INTO `osc_location_province` VALUES (111, 143, 'MX', 'AGS', 'Aguascalientes');
INSERT INTO `osc_location_province` VALUES (112, 143, 'MX', 'BC', 'Baja California');
INSERT INTO `osc_location_province` VALUES (113, 143, 'MX', 'BCS', 'Baja California Sur');
INSERT INTO `osc_location_province` VALUES (114, 143, 'MX', 'CAMP', 'Campeche');
INSERT INTO `osc_location_province` VALUES (115, 143, 'MX', 'CHIS', 'Chiapas');
INSERT INTO `osc_location_province` VALUES (116, 143, 'MX', 'CHIH', 'Chihuahua');
INSERT INTO `osc_location_province` VALUES (117, 143, 'MX', 'DF', 'Ciudad de México');
INSERT INTO `osc_location_province` VALUES (118, 143, 'MX', 'COAH', 'Coahuila');
INSERT INTO `osc_location_province` VALUES (119, 143, 'MX', 'COL', 'Colima');
INSERT INTO `osc_location_province` VALUES (120, 143, 'MX', 'DGO', 'Durango');
INSERT INTO `osc_location_province` VALUES (121, 143, 'MX', 'GTO', 'Guanajuato');
INSERT INTO `osc_location_province` VALUES (122, 143, 'MX', 'GRO', 'Guerrero');
INSERT INTO `osc_location_province` VALUES (123, 143, 'MX', 'HGO', 'Hidalgo');
INSERT INTO `osc_location_province` VALUES (124, 143, 'MX', 'JAL', 'Jalisco');
INSERT INTO `osc_location_province` VALUES (125, 143, 'MX', 'MEX', 'México');
INSERT INTO `osc_location_province` VALUES (126, 143, 'MX', 'MICH', 'Michoacán');
INSERT INTO `osc_location_province` VALUES (127, 143, 'MX', 'MOR', 'Morelos');
INSERT INTO `osc_location_province` VALUES (128, 143, 'MX', 'NAY', 'Nayarit');
INSERT INTO `osc_location_province` VALUES (129, 143, 'MX', 'NL', 'Nuevo León');
INSERT INTO `osc_location_province` VALUES (130, 143, 'MX', 'OAX', 'Oaxaca');
INSERT INTO `osc_location_province` VALUES (131, 143, 'MX', 'PUE', 'Puebla');
INSERT INTO `osc_location_province` VALUES (132, 143, 'MX', 'QRO', 'Querétaro');
INSERT INTO `osc_location_province` VALUES (133, 143, 'MX', 'Q ROO', 'Quintana Roo');
INSERT INTO `osc_location_province` VALUES (134, 143, 'MX', 'SLP', 'San Luis Potosí');
INSERT INTO `osc_location_province` VALUES (135, 143, 'MX', 'SIN', 'Sinaloa');
INSERT INTO `osc_location_province` VALUES (136, 143, 'MX', 'SON', 'Sonora');
INSERT INTO `osc_location_province` VALUES (137, 143, 'MX', 'TAB', 'Tabasco');
INSERT INTO `osc_location_province` VALUES (138, 143, 'MX', 'TAMPS', 'Tamaulipas');
INSERT INTO `osc_location_province` VALUES (139, 143, 'MX', 'TLAX', 'Tlaxcala');
INSERT INTO `osc_location_province` VALUES (140, 143, 'MX', 'VER', 'Veracruz');
INSERT INTO `osc_location_province` VALUES (141, 143, 'MX', 'YUC', 'Yucatán');
INSERT INTO `osc_location_province` VALUES (142, 143, 'MX', 'ZAC', 'Zacatecas');
INSERT INTO `osc_location_province` VALUES (143, 110, 'IT', 'AG', 'Agrigento');
INSERT INTO `osc_location_province` VALUES (144, 110, 'IT', 'AL', 'Alessandria');
INSERT INTO `osc_location_province` VALUES (145, 110, 'IT', 'AN', 'Ancona');
INSERT INTO `osc_location_province` VALUES (146, 110, 'IT', 'AO', 'Aosta');
INSERT INTO `osc_location_province` VALUES (147, 110, 'IT', 'AR', 'Arezzo');
INSERT INTO `osc_location_province` VALUES (148, 110, 'IT', 'AP', 'Ascoli Piceno');
INSERT INTO `osc_location_province` VALUES (149, 110, 'IT', 'AT', 'Asti');
INSERT INTO `osc_location_province` VALUES (150, 110, 'IT', 'AV', 'Avellino');
INSERT INTO `osc_location_province` VALUES (151, 110, 'IT', 'BA', 'Bari');
INSERT INTO `osc_location_province` VALUES (152, 110, 'IT', 'BT', 'Barletta-Andria-Trani');
INSERT INTO `osc_location_province` VALUES (153, 110, 'IT', 'BL', 'Belluno');
INSERT INTO `osc_location_province` VALUES (154, 110, 'IT', 'BN', 'Benevento');
INSERT INTO `osc_location_province` VALUES (155, 110, 'IT', 'BG', 'Bergamo');
INSERT INTO `osc_location_province` VALUES (156, 110, 'IT', 'BI', 'Biella');
INSERT INTO `osc_location_province` VALUES (157, 110, 'IT', 'BO', 'Bologna');
INSERT INTO `osc_location_province` VALUES (158, 110, 'IT', 'BZ', 'Bolzano');
INSERT INTO `osc_location_province` VALUES (159, 110, 'IT', 'BS', 'Brescia');
INSERT INTO `osc_location_province` VALUES (160, 110, 'IT', 'BR', 'Brindisi');
INSERT INTO `osc_location_province` VALUES (161, 110, 'IT', 'CA', 'Cagliari');
INSERT INTO `osc_location_province` VALUES (162, 110, 'IT', 'CL', 'Caltanissetta');
INSERT INTO `osc_location_province` VALUES (163, 110, 'IT', 'CB', 'Campobasso');
INSERT INTO `osc_location_province` VALUES (164, 110, 'IT', 'CI', 'Carbonia-Iglesias');
INSERT INTO `osc_location_province` VALUES (165, 110, 'IT', 'CE', 'Caserta');
INSERT INTO `osc_location_province` VALUES (166, 110, 'IT', 'CT', 'Catania');
INSERT INTO `osc_location_province` VALUES (167, 110, 'IT', 'CZ', 'Catanzaro');
INSERT INTO `osc_location_province` VALUES (168, 110, 'IT', 'CH', 'Chieti');
INSERT INTO `osc_location_province` VALUES (169, 110, 'IT', 'CO', 'Como');
INSERT INTO `osc_location_province` VALUES (170, 110, 'IT', 'CS', 'Cosenza');
INSERT INTO `osc_location_province` VALUES (171, 110, 'IT', 'CR', 'Cremona');
INSERT INTO `osc_location_province` VALUES (172, 110, 'IT', 'KR', 'Crotone');
INSERT INTO `osc_location_province` VALUES (173, 110, 'IT', 'CN', 'Cuneo');
INSERT INTO `osc_location_province` VALUES (174, 110, 'IT', 'EN', 'Enna');
INSERT INTO `osc_location_province` VALUES (175, 110, 'IT', 'FM', 'Fermo');
INSERT INTO `osc_location_province` VALUES (176, 110, 'IT', 'FE', 'Ferrara');
INSERT INTO `osc_location_province` VALUES (177, 110, 'IT', 'FI', 'Firenze');
INSERT INTO `osc_location_province` VALUES (178, 110, 'IT', 'FG', 'Foggia');
INSERT INTO `osc_location_province` VALUES (179, 110, 'IT', 'FC', 'ForlÃ¬-Cesena');
INSERT INTO `osc_location_province` VALUES (180, 110, 'IT', 'FR', 'Frosinone');
INSERT INTO `osc_location_province` VALUES (181, 110, 'IT', 'GE', 'Genova');
INSERT INTO `osc_location_province` VALUES (182, 110, 'IT', 'GO', 'Gorizia');
INSERT INTO `osc_location_province` VALUES (183, 110, 'IT', 'GR', 'Grosseto');
INSERT INTO `osc_location_province` VALUES (184, 110, 'IT', 'IM', 'Imperia');
INSERT INTO `osc_location_province` VALUES (185, 110, 'IT', 'IS', 'Isernia');
INSERT INTO `osc_location_province` VALUES (186, 110, 'IT', 'AQ', 'L\'Aquila');
INSERT INTO `osc_location_province` VALUES (187, 110, 'IT', 'SP', 'La Spezia');
INSERT INTO `osc_location_province` VALUES (188, 110, 'IT', 'LT', 'Latina');
INSERT INTO `osc_location_province` VALUES (189, 110, 'IT', 'LE', 'Lecce');
INSERT INTO `osc_location_province` VALUES (190, 110, 'IT', 'LC', 'Lecco');
INSERT INTO `osc_location_province` VALUES (191, 110, 'IT', 'LI', 'Livorno');
INSERT INTO `osc_location_province` VALUES (192, 110, 'IT', 'LO', 'Lodi');
INSERT INTO `osc_location_province` VALUES (193, 110, 'IT', 'LU', 'Lucca');
INSERT INTO `osc_location_province` VALUES (194, 110, 'IT', 'MC', 'Macerata');
INSERT INTO `osc_location_province` VALUES (195, 110, 'IT', 'MN', 'Mantova');
INSERT INTO `osc_location_province` VALUES (196, 110, 'IT', 'MS', 'Massa-Carrara');
INSERT INTO `osc_location_province` VALUES (197, 110, 'IT', 'MT', 'Matera');
INSERT INTO `osc_location_province` VALUES (198, 110, 'IT', 'VS', 'Medio Campidano');
INSERT INTO `osc_location_province` VALUES (199, 110, 'IT', 'ME', 'Messina');
INSERT INTO `osc_location_province` VALUES (200, 110, 'IT', 'MI', 'Milano');
INSERT INTO `osc_location_province` VALUES (201, 110, 'IT', 'MO', 'Modena');
INSERT INTO `osc_location_province` VALUES (202, 110, 'IT', 'MB', 'Monza e Brianza');
INSERT INTO `osc_location_province` VALUES (203, 110, 'IT', 'NA', 'Napoli');
INSERT INTO `osc_location_province` VALUES (204, 110, 'IT', 'NO', 'Novara');
INSERT INTO `osc_location_province` VALUES (205, 110, 'IT', 'NU', 'Nuoro');
INSERT INTO `osc_location_province` VALUES (206, 110, 'IT', 'OG', 'Ogliastra');
INSERT INTO `osc_location_province` VALUES (207, 110, 'IT', 'OT', 'Olbia-Tempio');
INSERT INTO `osc_location_province` VALUES (208, 110, 'IT', 'OR', 'Oristano');
INSERT INTO `osc_location_province` VALUES (209, 110, 'IT', 'PD', 'Padova');
INSERT INTO `osc_location_province` VALUES (210, 110, 'IT', 'PA', 'Palermo');
INSERT INTO `osc_location_province` VALUES (211, 110, 'IT', 'PR', 'Parma');
INSERT INTO `osc_location_province` VALUES (212, 110, 'IT', 'PV', 'Pavia');
INSERT INTO `osc_location_province` VALUES (213, 110, 'IT', 'PG', 'Perugia');
INSERT INTO `osc_location_province` VALUES (214, 110, 'IT', 'PU', 'Pesaro e Urbino');
INSERT INTO `osc_location_province` VALUES (215, 110, 'IT', 'PE', 'Pescara');
INSERT INTO `osc_location_province` VALUES (216, 110, 'IT', 'PC', 'Piacenza');
INSERT INTO `osc_location_province` VALUES (217, 110, 'IT', 'PI', 'Pisa');
INSERT INTO `osc_location_province` VALUES (218, 110, 'IT', 'PT', 'Pistoia');
INSERT INTO `osc_location_province` VALUES (219, 110, 'IT', 'PN', 'Pordenone');
INSERT INTO `osc_location_province` VALUES (220, 110, 'IT', 'PZ', 'Potenza');
INSERT INTO `osc_location_province` VALUES (221, 110, 'IT', 'PO', 'Prato');
INSERT INTO `osc_location_province` VALUES (222, 110, 'IT', 'RG', 'Ragusa');
INSERT INTO `osc_location_province` VALUES (223, 110, 'IT', 'RA', 'Ravenna');
INSERT INTO `osc_location_province` VALUES (224, 110, 'IT', 'RC', 'Reggio Calabria');
INSERT INTO `osc_location_province` VALUES (225, 110, 'IT', 'RE', 'Reggio Emilia');
INSERT INTO `osc_location_province` VALUES (226, 110, 'IT', 'RI', 'Rieti');
INSERT INTO `osc_location_province` VALUES (227, 110, 'IT', 'RN', 'Rimini');
INSERT INTO `osc_location_province` VALUES (228, 110, 'IT', 'RM', 'Roma');
INSERT INTO `osc_location_province` VALUES (229, 110, 'IT', 'RO', 'Rovigo');
INSERT INTO `osc_location_province` VALUES (230, 110, 'IT', 'SA', 'Salerno');
INSERT INTO `osc_location_province` VALUES (231, 110, 'IT', 'SS', 'Sassari');
INSERT INTO `osc_location_province` VALUES (232, 110, 'IT', 'SV', 'Savona');
INSERT INTO `osc_location_province` VALUES (233, 110, 'IT', 'SI', 'Siena');
INSERT INTO `osc_location_province` VALUES (234, 110, 'IT', 'SR', 'Siracusa');
INSERT INTO `osc_location_province` VALUES (235, 110, 'IT', 'SO', 'Sondrio');
INSERT INTO `osc_location_province` VALUES (236, 110, 'IT', 'TA', 'Taranto');
INSERT INTO `osc_location_province` VALUES (237, 110, 'IT', 'TE', 'Teramo');
INSERT INTO `osc_location_province` VALUES (238, 110, 'IT', 'TR', 'Terni');
INSERT INTO `osc_location_province` VALUES (239, 110, 'IT', 'TO', 'Torino');
INSERT INTO `osc_location_province` VALUES (240, 110, 'IT', 'TP', 'Trapani');
INSERT INTO `osc_location_province` VALUES (241, 110, 'IT', 'TN', 'Trento');
INSERT INTO `osc_location_province` VALUES (242, 110, 'IT', 'TV', 'Treviso');
INSERT INTO `osc_location_province` VALUES (243, 110, 'IT', 'TS', 'Trieste');
INSERT INTO `osc_location_province` VALUES (244, 110, 'IT', 'UD', 'Udine');
INSERT INTO `osc_location_province` VALUES (245, 110, 'IT', 'VA', 'Varese');
INSERT INTO `osc_location_province` VALUES (246, 110, 'IT', 'VE', 'Venezia');
INSERT INTO `osc_location_province` VALUES (247, 110, 'IT', 'VB', 'Verbano-Cusio-Ossola');
INSERT INTO `osc_location_province` VALUES (248, 110, 'IT', 'VC', 'Vercelli');
INSERT INTO `osc_location_province` VALUES (249, 110, 'IT', 'VR', 'Verona');
INSERT INTO `osc_location_province` VALUES (250, 110, 'IT', 'VV', 'Vibo Valentia');
INSERT INTO `osc_location_province` VALUES (251, 110, 'IT', 'VI', 'Vicenza');
INSERT INTO `osc_location_province` VALUES (252, 110, 'IT', 'VT', 'Viterbo');
COMMIT;

-- ----------------------------
-- Table structure for osc_mockup
-- ----------------------------
DROP TABLE IF EXISTS `osc_mockup`;
CREATE TABLE `osc_mockup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `ukey` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `status` tinyint(4) DEFAULT NULL,
  `config` longtext NOT NULL,
  `flag_main` tinyint(1) DEFAULT 0,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=89 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_mockup
-- ----------------------------
BEGIN;
INSERT INTO `osc_mockup` VALUES (1, 'canvas 8x10', 'canvas-8x10', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":58,\"y\":-12,\"width\":872,\"height\":1018},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (2, 'canvas 8x12', 'canvas-8x12', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":102,\"y\":-77,\"width\":795,\"height\":1096},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (3, 'canvas 10x8', 'canvas-10x8', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":0,\"y\":46,\"width\":1001,\"height\":858},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (4, 'canvas 11x14', 'canvas-11x14', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":96,\"y\":18,\"width\":776,\"height\":931},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (5, 'canvas 12x8', 'canvas-12x8', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":19,\"y\":151,\"width\":937,\"height\":680},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (6, 'canvas 12x12', 'canvas-12x12', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":21,\"y\":20,\"width\":957,\"height\":957},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (7, 'canvas 12x18', 'canvas-12x18', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":165,\"y\":32,\"width\":644,\"height\":912},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (8, 'canvas 12x24', 'canvas-12x24', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":208,\"y\":-32,\"width\":563,\"height\":1029},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (9, 'canvas 14x11', 'canvas-14x11', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":31,\"y\":115,\"width\":912,\"height\":760},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (10, 'canvas 16x16', 'canvas-16x16', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":84,\"y\":84,\"width\":833,\"height\":833},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (11, 'canvas 16x20', 'canvas-16x20', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":121,\"y\":60,\"width\":725,\"height\":870},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (12, 'canvas 18x12', 'canvas-18x12', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":19,\"y\":158,\"width\":935,\"height\":660},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (13, 'canvas 20x16', 'canvas-20x16', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":14,\"y\":66,\"width\":969,\"height\":808},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (14, 'canvas 20x24', 'canvas-20x24', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":122,\"y\":59,\"width\":724,\"height\":845},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (15, 'canvas 20x30', 'canvas-20x30', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":144,\"y\":-29,\"width\":710,\"height\":1013},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (16, 'canvas 24x12', 'canvas-24x12', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":46,\"y\":254,\"width\":882,\"height\":482},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (17, 'canvas 24x20', 'canvas-24x20', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":33,\"y\":100,\"width\":914,\"height\":784},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (18, 'canvas 24x24', 'canvas-24x24', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":101,\"y\":101,\"width\":799,\"height\":799},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (19, 'canvas 30x20', 'canvas 30x20', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{map:variant_opt__canvas_size}\\/canvas.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"canvas_bbox\",{\"x\":90,\"y\":215,\"width\":799,\"height\":560},\"segment_canvas__width\",\"segment_canvas__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_canvas} -resize {map:canvas_bbox__width}x{map:canvas_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:canvas_bbox__x}{map:canvas_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (20, 'pillow 16x16', 'pillow-16x16', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{map:variant_opt__pillow_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{map:variant_opt__pillow_size}\\/preview\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{map:variant_opt__pillow_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"pillow_bbox\",{\"x\":57,\"y\":60,\"width\":858,\"height\":858},\"segment_pillow__width\",\"segment_pillow__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_pillow} -resize {map:pillow_bbox__width}x{map:pillow_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:pillow_bbox__x}{map:pillow_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (21, 'pillow 18x18', 'pillow-18x18', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{map:variant_opt__pillow_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{map:variant_opt__pillow_size}\\/preview\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{map:variant_opt__pillow_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"pillow_bbox\",{\"x\":57,\"y\":60,\"width\":858,\"height\":858},\"segment_pillow__width\",\"segment_pillow__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_pillow} -resize {map:pillow_bbox__width}x{map:pillow_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:pillow_bbox__x}{map:pillow_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (22, 'desktopPlaque 7x5', 'desktopPlaque-7x5', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{map:variant_opt__desktop_plaque_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{map:variant_opt__desktop_plaque_size}\\/preview\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{map:variant_opt__desktop_plaque_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"desktopPlaque_bbox\",{\"x\":38,\"y\":197,\"width\":872,\"height\":628},\"segment_desktopPlaque__width\",\"segment_desktopPlaque__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_desktopPlaque} -resize {map:desktopPlaque_bbox__width}x{map:desktopPlaque_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:desktopPlaque_bbox__x}{map:desktopPlaque_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (23, 'desktopPlaque 10x8', 'desktopPlaque-10x8', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{map:variant_opt__desktop_plaque_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{map:variant_opt__desktop_plaque_size}\\/preview\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{map:variant_opt__desktop_plaque_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"desktopPlaque_bbox\",{\"x\":51,\"y\":131,\"width\":855,\"height\":689},\"segment_desktopPlaque__width\",\"segment_desktopPlaque__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_desktopPlaque} -resize {map:desktopPlaque_bbox__width}x{map:desktopPlaque_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:desktopPlaque_bbox__x}{map:desktopPlaque_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (24, 'fleeceBlanket 30x40', 'fleeceBlanket-30x40', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"fleeceBlanket_bbox\",{\"x\":148,\"y\":43,\"width\":705,\"height\":915},\"segment_fleeceBlanket__width\",\"segment_fleeceBlanket__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_fleeceBlanket} -resize {map:fleeceBlanket_bbox__width}x{map:fleeceBlanket_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:fleeceBlanket_bbox__x}{map:fleeceBlanket_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (25, 'fleeceBlanket 50x60', 'fleeceBlanket-50x60', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"fleeceBlanket_bbox\",{\"x\":78,\"y\":0,\"width\":832,\"height\":1000},\"segment_fleeceBlanket__width\",\"segment_fleeceBlanket__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_fleeceBlanket} -resize {map:fleeceBlanket_bbox__width}x{map:fleeceBlanket_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:fleeceBlanket_bbox__x}{map:fleeceBlanket_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (26, 'fleeceBlanket 60x80', 'fleeceBlanket-60x80', '', 0, '{\"params\":[{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/frame.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{map:variant_opt__blanket_size}\\/preview\\/background.png\"]},{\"helper\":\"catalog\\/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"fleeceBlanket_bbox\",{\"x\":138,\"y\":9,\"width\":738,\"height\":980},\"segment_fleeceBlanket__width\",\"segment_fleeceBlanket__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_fleeceBlanket} -resize {map:fleeceBlanket_bbox__width}x{map:fleeceBlanket_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}\\/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:fleeceBlanket_bbox__x}{map:fleeceBlanket_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}\\/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (27, 'puless 10x14', 'puless-10x14', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/puzzles/{map:variant_opt__puzzle_size}/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/puzzles/{map:variant_opt__puzzle_size}/preview/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/puzzles/{map:variant_opt__puzzle_size}/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"puzzle_bbox\",{\"x\":114,\"y\":19,\"width\":747,\"height\":951},\"segment_puzzles__width\",\"segment_puzzles__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_puzzles} -resize {map:puzzle_bbox__width}x{map:puzzle_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:puzzle_bbox__x}{map:puzzle_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (28, 'puless 14x10', 'puless-14-10', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/puzzles/{map:variant_opt__puzzle_size}/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/puzzles/{map:variant_opt__puzzle_size}/preview/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/previews/puzzle/{map:variant_opt__puzzle_size}/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"puzzle_bbox\",{\"x\":1,\"y\":95,\"width\":998,\"height\":784},\"segment_puzzles__width\",\"segment_puzzles__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_puzzles} -resize {map:puzzle_bbox__width}x{map:puzzle_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:puzzle_bbox__x}{map:puzzle_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (29, 'mug 11oz front', 'mug-11oz-front', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":335,\"y\":246,\"width\":466,\"height\":603},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (30, 'mug 11oz back', 'mug-11oz-back', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":145,\"y\":246,\"width\":466,\"height\":603},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (31, 'mug 15oz front', 'mug-15oz-front', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":360,\"y\":244,\"width\":418,\"height\":542},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (32, 'mug 15oz back', 'mug-15oz-back', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":255,\"y\":244,\"width\":418,\"height\":542},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (33, 'enamelCampfire front', 'enamelCampfire-front', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/enamelCampfire/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/enamelCampfire/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":280,\"y\":297,\"width\":492,\"height\":476},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (34, 'enamelCampfire back', 'enamelCampfire-back', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/enamelCampfire/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/enamelCampfire/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":220,\"y\":297,\"width\":492,\"height\":476},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (35, 'insulatedCoffee front', 'insulatedCoffee-front', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/insulatedCoffee/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/insulatedCoffee/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":285,\"y\":247,\"width\":462,\"height\":549},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (36, 'insulatedCoffee back', 'insulatedCoffee-back', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/insulatedCoffee/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/insulatedCoffee/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":250,\"y\":247,\"width\":462,\"height\":549},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (37, 'twoTone front', 'twoTone-front', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/twoTone/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/twoTone/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":335,\"y\":240,\"width\":466,\"height\":603},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (38, 'twoTone back', 'twoTone-back', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/twoTone/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/twoTone/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":200,\"y\":246,\"width\":466,\"height\":603},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (39, 'mug 15oz 2 side', 'mug15oz-2side', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":158,\"y\":350,\"width\":310,\"height\":402},\"segment_front__width\",\"segment_front__height\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":534,\"y\":350,\"width\":310,\"height\":402},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.front.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.back.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.front.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {map:file_prefix}.back.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (40, 'insulatedCoffee 2 side', 'insulatedCoffee-2side', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/insulatedCoffee/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/insulatedCoffee/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":112,\"y\":330,\"width\":347,\"height\":412},\"segment_front__width\",\"segment_front__height\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":545,\"y\":330,\"width\":347,\"height\":412},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.front.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.back.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.front.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {map:file_prefix}.back.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (41, 'twoTone 2 side', 'twoTone-2side', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/twoTone/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/twoTone/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":130,\"y\":285,\"width\":262,\"height\":339},\"segment_front__width\",\"segment_front__height\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":452,\"y\":285,\"width\":262,\"height\":339},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.front.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.back.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.front.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {map:file_prefix}.back.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (42, 'classic tee front', 'classic-tee', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/classicTee/{map:variant_opt__gildan_g500_classic_tee_color}/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/classicTee/{map:variant_opt__gildan_g500_classic_tee_color}/front/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/classicTee/{map:variant_opt__gildan_g500_classic_tee_color}/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_multiplyOption\",\"params\":[\"multiply_option\",\"{map:variant_opt__gildan_g500_classic_tee_color}\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":295,\"y\":214,\"width\":400,\"height\":457},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png {map:multiply_option}-geometry {map:front_bbox__x}{map:front_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (43, 'notebook 5x7 front', 'notebook-5x7-front', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/notebook/{map:variant_opt__wiro_notebook_size}/preview/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/notebook/{map:variant_opt__wiro_notebook_size}/preview/front/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/notebook/{map:variant_opt__wiro_notebook_size}/preview/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":202,\"y\":122,\"width\":590,\"height\":808},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (44, 'notebook 5x7 back', 'notebook 5x7 back', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/notebook/{map:variant_opt__wiro_notebook_size}/preview/back/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/notebook/{map:variant_opt__wiro_notebook_size}/preview/back/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/notebook/{map:variant_opt__wiro_notebook_size}/preview/back/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":206,\"y\":122,\"width\":590,\"height\":808},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (45, 'facemask with filter preview', 'facemask-with-filter-preview', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpi/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/dpi/preview/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/facemask/dpi/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"facemask_bbox\",{\"x\":155,\"y\":241,\"width\":686,\"height\":495},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:facemask_bbox__width}x{map:facemask_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:facemask_bbox__x}{map:facemask_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (46, 'facemask without filter preview', 'facemask-without-filter-preview', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/cw/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/cw/preview/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/facemask/cw/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"facemask_bbox\",{\"x\":196,\"y\":292,\"width\":601,\"height\":406},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:facemask_bbox__width}x{map:facemask_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:facemask_bbox__x}{map:facemask_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (47, 'facemask with filter main', 'facemask-with-filter-main', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpi/main/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/dpi/main/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_file\",\"catalog/campaign/type/preview/facemask/dpi/main/map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/facemask/dpi/main/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"facemask_dpi_bbox\",{\"x\":131,\"y\":309,\"width\":750,\"height\":542},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_file}\",\"dest\":\"{map:txt_file_mtime}\",\"md5\":\"{map:txt_file_md5}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:facemask_dpi_bbox__width}x{map:facemask_dpi_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.resized.png -geometry {map:facemask_dpi_bbox__x}{map:facemask_dpi_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_file_mtime})\\\" {map:file_prefix}.meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.meshwarp.png -compose Multiply -composite {map:file_prefix}.composite2.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite2.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite2.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (48, 'facemask without filter main', 'facemask-without-filter-main', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/cw/main/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/cw/main/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_file\",\"catalog/campaign/type/preview/facemask/cw/main/map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/facemask/cw/main/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"facemask_cw_bbox\",{\"x\":143,\"y\":336,\"width\":723,\"height\":489},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_file}\",\"dest\":\"{map:txt_file_mtime}\",\"md5\":\"{map:txt_file_md5}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:facemask_cw_bbox__width}x{map:facemask_cw_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.resized.png -geometry {map:facemask_cw_bbox__x}{map:facemask_cw_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_file_mtime})\\\" {map:file_prefix}.meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.meshwarp.png -compose Multiply -composite {map:file_prefix}.composite2.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite2.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite2.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (49, 'facemask with filter 2.5pm', 'facemask-with-filter-2.5pm', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpi/pm25/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/dpi/pm25/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_file\",\"catalog/campaign/type/preview/facemask/dpi/pm25/map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/facemask/dpi/pm25/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"facemask_dpi_bbox\",{\"x\":69,\"y\":286,\"width\":618,\"height\":446},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_file}\",\"dest\":\"{map:txt_file_mtime}\",\"md5\":\"{map:txt_file_md5}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:facemask_dpi_bbox__width}x{map:facemask_dpi_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.resized.png -geometry {map:facemask_dpi_bbox__x}{map:facemask_dpi_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_file_mtime})\\\" {map:file_prefix}.meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.meshwarp.png -compose Multiply -composite {map:file_prefix}.composite2.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite2.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite2.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (50, 'facemask with filter kid main', 'facemask-with-filter-kid-main', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpiKid/main/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/dpiKid/main/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_file\",\"catalog/campaign/type/preview/facemask/dpiKid/main/map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/facemask/dpiKid/main/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"facemask_dpiKid_bbox\",{\"x\":122,\"y\":311,\"width\":781,\"height\":504},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_file}\",\"dest\":\"{map:txt_file_mtime}\",\"md5\":\"{map:txt_file_md5}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:facemask_dpiKid_bbox__width}x{map:facemask_dpiKid_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.resized.png -geometry {map:facemask_dpiKid_bbox__x}{map:facemask_dpiKid_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_file_mtime})\\\" {map:file_prefix}.meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.meshwarp.png -compose Multiply -composite {map:file_prefix}.composite2.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite2.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite2.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (51, 'facemask with filter kid preview', 'facemask-with-filter-kid-preview', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpiKid/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/dpiKid/preview/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/facemask/dpiKid/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"facemask_bbox\",{\"x\":183,\"y\":296,\"width\":635,\"height\":409},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:facemask_bbox__width}x{map:facemask_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -compose Multiply -geometry {map:facemask_bbox__x}{map:facemask_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (52, 'facemask with filter kid compare', 'facemask-with-filter-kid-compare', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpiKid/compare/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/facemask/dpiKid/compare/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/preview/facemask/dpiKid/compare/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"small_bbox\",{\"x\":274,\"y\":689,\"width\":440,\"height\":283},\"segment_facemask__width\",\"segment_facemask__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize 464x335! {map:file_prefix}.big_resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.big_resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_facemask} -resize {map:small_bbox__width}x{map:small_bbox__height}! {map:file_prefix}.small_resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.small_resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.big_resized.png -compose Multiply -geometry +268+165 -composite {map:file_prefix}.small_resized.png -compose Multiply -geometry {map:small_bbox__x}{map:small_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (53, 'bellaCanvasTee', 'bellaCanvasTee', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/bellaCanvasTee/{map:variant_opt__bella_canvas_3001c_unisex_jersey_short_sleeve_color}/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/bellaCanvasTee/{map:variant_opt__bella_canvas_3001c_unisex_jersey_short_sleeve_color}/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_multiplyOption\",\"params\":[\"multiply_option\",\"{map:variant_opt__bella_canvas_3001c_unisex_jersey_short_sleeve_color}\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":303,\"y\":248,\"width\":395,\"height\":452},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png {map:multiply_option}-geometry {map:front_bbox__x}{map:front_bbox__y} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (54, 'nextLevelTee', 'nextLevelTee', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/nextLevelTee/{map:variant_opt__next_level_nl3600_premium_short_sleeve_color}/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/nextLevelTee/{map:variant_opt__next_level_nl3600_premium_short_sleeve_color}/front/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_multiplyOption\",\"params\":[\"multiply_option\",\"{map:variant_opt__next_level_nl3600_premium_short_sleeve_color}\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":301,\"y\":270,\"width\":395,\"height\":452},\"segment_front__width\",\"segment_front__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png {map:multiply_option}-geometry {map:front_bbox__x}{map:front_bbox__y} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (55, 'ornament aluminium medallion', 'ornament-aluminium-medallion', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/ornament/aluminiumMedallion/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/ornament/aluminiumMedallion/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/ornament/aluminiumMedallion/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"ornament_bbox\",{\"x\":44,\"y\":157,\"width\":910,\"height\":682},\"segment_ornament__width\",\"segment_ornament__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_ornament} -resize {map:ornament_bbox__width}x{map:ornament_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -geometry {map:ornament_bbox__x}{map:ornament_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (56, 'ornament aluminium scalloped', 'ornament-aluminium-scalloped', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/ornament/aluminiumScalloped/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/ornament/aluminiumScalloped/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/ornament/aluminiumScalloped/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"ornament_bbox\",{\"x\":51,\"y\":192,\"width\":893,\"height\":635},\"segment_ornament__width\",\"segment_ornament__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_ornament} -resize {map:ornament_bbox__width}x{map:ornament_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -geometry {map:ornament_bbox__x}{map:ornament_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (57, 'ornament-aluminium-square', 'ornament-aluminium-square', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/ornament/aluminiumSquare/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/ornament/aluminiumSquare/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/ornament/aluminiumSquare/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"ornament_bbox\",{\"x\":140,\"y\":134,\"width\":723,\"height\":723},\"segment_ornament__width\",\"segment_ornament__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_ornament} -resize {map:ornament_bbox__width}x{map:ornament_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -geometry {map:ornament_bbox__x}{map:ornament_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (58, 'mug 11oz 2 side', 'mug-11oz-2side', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_front\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front_map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_back\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back_map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":130,\"y\":244,\"width\":340,\"height\":440},\"segment_front__width\",\"segment_front__height\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":547,\"y\":262,\"width\":340,\"height\":440},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_front}\",\"dest\":\"{map:txt_front_mtime}\",\"md5\":\"{map:txt_front_md5}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_back}\",\"dest\":\"{map:txt_back_mtime}\",\"md5\":\"{map:txt_back_md5}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.front_resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front_resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.back_resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back_resized.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.front_resized.png -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {map:file_prefix}.front_composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front_composite.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.back_resized.png -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {map:file_prefix}.back_composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back_composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.front_composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_front_mtime})\\\" {map:file_prefix}.front_meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front_meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.back_composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_back_mtime})\\\" {map:file_prefix}.back_meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back_meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.front_meshwarp.png -compose Multiply -composite {map:file_prefix}.back_meshwarp.png -compose Multiply -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (59, 'enamelCampfire 2side', 'enamelCampfire-2side', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mug/enamelCampfire/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_front\",\"catalog/campaign/type/preview/mug/enamelCampfire/front_map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateTxtPath\",\"params\":[\"txt_back\",\"catalog/campaign/type/preview/mug/enamelCampfire/back_map.txt\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mug/enamelCampfire/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"front_bbox\",{\"x\":93,\"y\":383,\"width\":336,\"height\":328},\"segment_front__width\",\"segment_front__height\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"back_bbox\",{\"x\":572,\"y\":383,\"width\":336,\"height\":328},\"segment_back__width\",\"segment_back__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_front}\",\"dest\":\"{map:txt_front_mtime}\",\"md5\":\"{map:txt_front_md5}\"},{\"type\":\"downloadFile\",\"source\":\"{map:txt_back}\",\"dest\":\"{map:txt_back_mtime}\",\"md5\":\"{map:txt_back_md5}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_front} -resize {map:front_bbox__width}x{map:front_bbox__height}! {map:file_prefix}.front_resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front_resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_back} -resize {map:back_bbox__width}x{map:back_bbox__height}! {map:file_prefix}.back_resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back_resized.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.front_resized.png -geometry {map:front_bbox__x}{map:front_bbox__y} -composite {map:file_prefix}.front_composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front_composite.png\"},{\"type\":\"exec\",\"command\":\"convert -size 1000x1000 xc:none -background none {map:file_prefix}.back_resized.png -geometry {map:back_bbox__x}{map:back_bbox__y} -composite {map:file_prefix}.back_composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back_composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.front_composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_front_mtime})\\\" {map:file_prefix}.front_meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.front_meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.back_composite.png -virtual-pixel none -distort polynomial \\\"3 $(cat {params:storage_path}/{map:txt_back_mtime})\\\" {map:file_prefix}.back_meshwarp.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.back_meshwarp.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.front_meshwarp.png -compose Multiply -composite {map:file_prefix}.back_meshwarp.png -compose Multiply -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (60, 'heart-ornament', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/ornament/heart/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/ornament/heart/preview/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/ornament/heart/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"ornament_bbox\",{\"x\":70,\"y\":100,\"width\":860,\"height\":805},\"segment_ornament__width\",\"segment_ornament__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_ornament} -resize {map:ornament_bbox__width}x{map:ornament_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -geometry {map:ornament_bbox__x}{map:ornament_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (61, 'circle-ornament preview', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/ornament/circle/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/ornament/circle/preview/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/ornament/circle/preview/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"ornament_bbox\",{\"x\":96,\"y\":97,\"width\":807,\"height\":807},\"segment_ornament__width\",\"segment_ornament__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_ornament} -resize {map:ornament_bbox__width}x{map:ornament_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -geometry {map:ornament_bbox__x}{map:ornament_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (62, 'fullprint 11oz white back', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 268 -l 640 -p 1.5 -n 100 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o -115+45 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (63, 'fullprint 11oz white front', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 268 -l 640 -p 1.5 -n 100 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o +63+45 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (64, 'fullprint 11oz white center', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/center.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/center.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 268 -l 640 -p 1.5 -n 100 -w 90 -e 1.4 -d both -a 360 -c multiply -v background -b none -f none -o +5+45 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (65, 'fullprint 11oz white mockup', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 183 -l 451 -p 1.5 -n 100 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o -192-2 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.pre_mockup.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.pre_mockup.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 183 -l 451 -p 1.5 -n 100 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o +185-2 {map:file_prefix}.resized.png {map:file_prefix}.pre_mockup.png {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (66, 'fullprint 15oz white back', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 259 -l 605 -p 2.5 -n 97 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o -36+5 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (67, 'fullprint 15oz white center', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/center.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/center.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 261 -l 605 -p 2.5 -n 99 -w 90 -e 1.4 -d both -a 360 -c multiply -v background -b none -f none -o -1+5 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (68, 'fullprint 15oz white mockup', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/mockup.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 184 -l 422 -p 2.5 -n 98 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o -191+45 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.pre_mockup.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.pre_mockup.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 184 -l 422 -p 2.5 -n 98 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o +190+45 {map:file_prefix}.resized.png {map:file_prefix}.pre_mockup.png {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (69, 'fullprint 15oz white front', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/{map:variant_opt__mug_size}/{map:variant_opt__mug_color}/front.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 259 -l 605 -p 2.5 -n 97 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o +68+5 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (70, 'fullprint enamelCampfire back', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 291 -l 528 -p 2.5 -n 100 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o -31+35 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (71, 'fullprint enamelCampfire center', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/center.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/center.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 290 -l 520 -p 2.5 -n 97 -w 90 -e 1.4 -d both -a 360 -c multiply -v background -b none -f none -o -1+35 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (72, 'fullprint enamelCampfire front', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/front.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 291 -l 528 -p 2.5 -n 100 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o +25+35 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (73, 'fullprint enamelCampfire mockup', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/enamelCampfire/mockup.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 202 -l 332 -p 1.5 -n 99 -w 80 -e 1.4 -a -270 -R 0.75 -c multiply -v background -b none -f none -o -240+45 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.pre_mockup.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.pre_mockup.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 201 -l 368 -p 0.75 -n 99 -w 80 -e 1.4 -d both -a 270 -R 0.75 -c multiply -v background -b none -f none -o +238+47 {params:segment_path_design} {map:file_prefix}.pre_mockup.png {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (74, 'fullprint insulatedCoffee back', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 272 -l 560 -p 2.5 -n 98 -w 90 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o -17+20 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (75, 'fullprint insulatedCoffee center', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/center.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/center.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 272 -l 560 -p 2.5 -n 98 -w 90 -e 1.4 -d both -a 360 -c multiply -v background -b none -f none -o +4+20 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (76, 'fullprint insulatedCoffee front', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/front.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 272 -l 560 -p 2.5 -n 98 -w 90 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o +15+20 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (77, 'fullprint insulatedCoffee mockup', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/insulatedCoffee/mockup.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 201 -l 420 -p 1.5 -n 98 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o -217+45 {params:segment_path_design} {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.pre_mockup.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.pre_mockup.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 201 -l 420 -p 1.5 -n 98 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o +216+45 {params:segment_path_design} {map:file_prefix}.pre_mockup.png {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (78, 'fullprint towTone back', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/back.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 268 -l 640 -p 1.5 -n 100 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o -63+45 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (79, 'fullprint towTone center', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/center.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/center.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 268 -l 640 -p 1.5 -n 100 -w 90 -e 1.4 -d both -a 360 -c multiply -v background -b none -f none -o +5+45 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (80, 'fullprint towTone front', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/front.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/front.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 268 -l 640 -p 1.5 -n 100 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o +63+45 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (81, 'fullprint towTone mockup', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/mockup.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/mugFullprint/twoTone/{map:variant_opt__mug_color}/mockup.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_design} -resize 2334x991! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 184 -l 421 -p 2.3 -n 100 -w 80 -e 1.4 -d both -a -270 -c multiply -v background -b none -f none -o -198+28 {map:file_prefix}.resized.png {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.pre_mockup.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.pre_mockup.png\"},{\"type\":\"exec\",\"command\":\"{params:bash_root_path}/cylinderize/cylinderize.sh -D {params:bash_root_path}/cylinderize/mug_displace.png -r 184 -l 421 -p 2.3 -n 100 -w 80 -e 1.4 -d both -a 270 -c multiply -v background -b none -f none -o +187+28 {map:file_prefix}.resized.png {map:file_prefix}.pre_mockup.png {params:file_path}\"}]}', 1, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (82, 'circle-ornament perspective', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/ornament/circle/perspective/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/ornament/circle/perspective/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/ornament/circle/perspective/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"ornament_bbox\",{\"x\":96,\"y\":97,\"width\":807,\"height\":807},\"segment_ornament__width\",\"segment_ornament__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_ornament} -resize {map:ornament_bbox__width}x{map:ornament_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -geometry {map:ornament_bbox__x}{map:ornament_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (83, 'heart-ornament perspective', '', '', 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/ornament/heart/perspective/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_frame\",\"catalog/campaign/type/preview/ornament/heart/perspective/frame.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_filePrefix\",\"params\":[\"file_prefix\",\"catalog/campaign/type/preview/ornament/heart/perspective/background.png\"]},{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_calculateInsideBoxGeometry\",\"params\":[\"ornament_bbox\",{\"x\":70,\"y\":100,\"width\":860,\"height\":805},\"segment_ornament__width\",\"segment_ornament__height\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"downloadImage\",\"source\":\"{map:img_frame}\",\"dest\":\"{map:img_frame_mtime}\"},{\"type\":\"exec\",\"command\":\"convert {params:segment_path_ornament} -resize {map:ornament_bbox__width}x{map:ornament_bbox__height}! {map:file_prefix}.resized.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.resized.png\"},{\"type\":\"exec\",\"command\":\"convert {params:storage_path}/{map:img_background_mtime} {map:file_prefix}.resized.png -geometry {map:ornament_bbox__x}{map:ornament_bbox__y} -composite {map:file_prefix}.composite.png\"},{\"type\":\"imageCorrupt\",\"file\":\"{map:file_prefix}.composite.png\"},{\"type\":\"exec\",\"command\":\"convert {map:file_prefix}.composite.png {params:storage_path}/{map:img_frame_mtime} -composite {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (84, 'classic tee back', NULL, NULL, 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/classicTee/{map:variant_opt__gildan_g500_classic_tee_color}/back/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"cp {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (85, 'facemask with filter kid default 3', NULL, NULL, 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpiKid/default/mockup3.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"cp {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (86, 'facemask with filter kid default 4', NULL, NULL, 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/facemask/dpiKid/default/mockup4.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"cp {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (87, 'bella canvas back', NULL, NULL, 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/bellaCanvasTee/{map:variant_opt__bella_canvas_3001c_unisex_jersey_short_sleeve_color}/back/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"cp {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
INSERT INTO `osc_mockup` VALUES (88, 'next level tee back', NULL, NULL, 0, '{\"params\":[{\"helper\":\"catalog/campaign_mockup_command\",\"function\":\"_paramsBuilder_templateImagePath\",\"params\":[\"img_background\",\"catalog/campaign/type/preview/nextLevelTee/{map:variant_opt__next_level_nl3600_premium_short_sleeve_color}/back/back.png\"]}],\"commands\":[{\"type\":\"downloadImage\",\"source\":\"{map:img_background}\",\"dest\":\"{map:img_background_mtime}\"},{\"type\":\"exec\",\"command\":\"cp {params:storage_path}/{map:img_background_mtime} {params:file_path}\"}]}', 0, 1601787157, 1601787157);
COMMIT;

-- ----------------------------
-- Table structure for osc_print_template
-- ----------------------------
DROP TABLE IF EXISTS `osc_print_template`;
CREATE TABLE `osc_print_template` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `short_title` varchar(255) DEFAULT NULL,
  `ukey` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `config` text DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_print_template
-- ----------------------------
BEGIN;
INSERT INTO `osc_print_template` VALUES (1, 'Ceramic Mug 11oz DPI Harrier CW PrintGeek', '', '1/29:30:58', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":335,\"y\":246},\"dimension\":{\"width\":466,\"height\":603}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/{opt.mug_size}\\/{opt.mug_color}\\/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":145,\"y\":246},\"dimension\":{\"width\":466,\"height\":603}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/{opt.mug_size}\\/{opt.mug_color}\\/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":603},\"position\":{\"x\":335,\"y\":246}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/11oz\\/white\\/front.png\"]},\"dimension\":{\"width\":766,\"height\":991},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":603},\"position\":{\"x\":145,\"y\":246}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/11oz\\/white\\/back.png\"]},\"dimension\":{\"width\":766,\"height\":991},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"11oz Ceramic Mug\",\"dimension\":{\"width\":2334,\"height\":991},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":17,\"y\":-0.5},\"dimension\":{\"width\":766,\"height\":991}},\"back\":{\"position\":{\"x\":1551,\"y\":-0.5},\"dimension\":{\"width\":766,\"height\":991}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (2, 'Ceramic Mug 15oz DPI Prima CW ', '', '2/31:32:39', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":360,\"y\":244},\"dimension\":{\"width\":418,\"height\":542}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/{opt.mug_size}\\/{opt.mug_color}\\/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":255,\"y\":244},\"dimension\":{\"width\":418,\"height\":542}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/{opt.mug_size}\\/{opt.mug_color}\\/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":418,\"height\":542},\"position\":{\"x\":360,\"y\":244}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/15oz\\/white\\/front.png\"]},\"dimension\":{\"width\":766,\"height\":991},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":418,\"height\":542},\"position\":{\"x\":255,\"y\":244}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/15oz\\/white\\/back.png\"]},\"dimension\":{\"width\":766,\"height\":991},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"15oz Ceramic Mug\",\"dimension\":{\"width\":2334,\"height\":991},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":17,\"y\":-0.5},\"dimension\":{\"width\":766,\"height\":991}},\"back\":{\"position\":{\"x\":1551,\"y\":-0.5},\"dimension\":{\"width\":766,\"height\":991}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (3, 'Two-Tone Mug 11oz DPI Harier', '', '3/37:38:41', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":335,\"y\":240},\"dimension\":{\"width\":466,\"height\":603}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/twoTone\\/{opt.mug_color}\\/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":200,\"y\":246},\"dimension\":{\"width\":466,\"height\":603}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/twoTone\\/{opt.mug_color}\\/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":603},\"position\":{\"x\":335,\"y\":240}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/twoTone\\/black\\/front.png\"]},\"dimension\":{\"width\":766,\"height\":991},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":603},\"position\":{\"x\":200,\"y\":246}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/twoTone\\/black\\/back.png\"]},\"dimension\":{\"width\":766,\"height\":991},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"11oz Two-Tone Mug\",\"dimension\":{\"width\":2334,\"height\":991},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":17,\"y\":-0.5},\"dimension\":{\"width\":766,\"height\":991}},\"back\":{\"position\":{\"x\":1551,\"y\":-0.5},\"dimension\":{\"width\":766,\"height\":991}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (4, 'Insulated Coffee Mug 12oz DPI', '', '4/35:36:40', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":285,\"y\":247},\"dimension\":{\"width\":462,\"height\":549}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/insulatedCoffee\\/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":250,\"y\":247},\"dimension\":{\"width\":462,\"height\":549}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/insulatedCoffee\\/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":462,\"height\":549},\"position\":{\"x\":285,\"y\":247}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/insulatedCoffee\\/front.png\"]},\"dimension\":{\"width\":921,\"height\":1093},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":462,\"height\":549},\"position\":{\"x\":250,\"y\":247}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/insulatedCoffee\\/back.png\"]},\"dimension\":{\"width\":921,\"height\":1093},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"12oz Insulated Coffee Mug\",\"dimension\":{\"width\":2550,\"height\":1093},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":134.5,\"y\":-0.5},\"dimension\":{\"width\":921,\"height\":1093}},\"back\":{\"position\":{\"x\":1494.5,\"y\":-0.5},\"dimension\":{\"width\":921,\"height\":1093}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (5, 'Enamel Campfire 10oz DPI', '', '5/33:34:59', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":280,\"y\":297},\"dimension\":{\"width\":492,\"height\":476}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/enamelCampfire\\/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":220,\"y\":297},\"dimension\":{\"width\":492,\"height\":476}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/enamelCampfire\\/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":492,\"height\":476},\"position\":{\"x\":280,\"y\":297}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/enamelCampfire\\/front.png\"]},\"dimension\":{\"width\":776,\"height\":750},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":492,\"height\":476},\"position\":{\"x\":220,\"y\":297}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/enamelCampfire\\/back.png\"]},\"dimension\":{\"width\":776,\"height\":750},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"10oz Enamel Campfire\",\"dimension\":{\"width\":2400,\"height\":750},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":168,\"y\":0},\"dimension\":{\"width\":776,\"height\":750}},\"back\":{\"position\":{\"x\":1456,\"y\":0},\"dimension\":{\"width\":776,\"height\":750}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (6, 'Fullprints Ceramic Mug 11oz DPI Harrier CW PrintGeek', '', '6/62:63:64:65', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":288},\"dimension\":{\"width\":1000,\"height\":424}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":424},\"position\":{\"x\":0,\"y\":288}},\"layers\":[]},\"dimension\":{\"width\":2334,\"height\":991},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"11oz Ceramic Mug\",\"dimension\":{\"width\":2334,\"height\":991},\"dpi\":null,\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":2334,\"height\":991}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (7, 'Fullprints Ceramic Mug 15oz DPI Prima CW', '', '7/66:67:68:69', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":288},\"dimension\":{\"width\":1000,\"height\":424}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":424},\"position\":{\"x\":0,\"y\":288}},\"layers\":[]},\"dimension\":{\"width\":2334,\"height\":991},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"15oz Ceramic Mug\",\"dimension\":{\"width\":2334,\"height\":991},\"dpi\":null,\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":2334,\"height\":991}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (8, 'Fullprints Two-Tone Mug 11oz DPI Harier', '', '8/78:79:80:81', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":288},\"dimension\":{\"width\":1000,\"height\":424}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":424},\"position\":{\"x\":0,\"y\":288}},\"layers\":[]},\"dimension\":{\"width\":2334,\"height\":991},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"11oz Two-Tone Mug\",\"dimension\":{\"width\":2334,\"height\":991},\"dpi\":null,\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":2334,\"height\":991}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (9, 'Fullprints Insulated Coffee Mug 12oz dPI', '', '9/74:75:76:77', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":286},\"dimension\":{\"width\":1000,\"height\":428}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":428},\"position\":{\"x\":0,\"y\":286}},\"layers\":[]},\"dimension\":{\"width\":2550,\"height\":1093},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"12oz Insulated Coffee Mug\",\"dimension\":{\"width\":2550,\"height\":1093},\"dpi\":null,\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":2550,\"height\":1093}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (10, 'Fullprints Enamel Campfire 10oz DPI', '', '10/70:71:72:73', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":344},\"dimension\":{\"width\":1000,\"height\":312}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":312},\"position\":{\"x\":0,\"y\":344}},\"layers\":[]},\"dimension\":{\"width\":2400,\"height\":750},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"10oz Enamel Campfire\",\"dimension\":{\"width\":2400,\"height\":750},\"dpi\":null,\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":2400,\"height\":750}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (11, 'Canvas 8\"x10\"  DPI', '', '11/1', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":58,\"y\":-12},\"dimension\":{\"width\":872,\"height\":1018}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":221.5,\"y\":151.5926},\"dimension\":{\"width\":545,\"height\":690.8148}},\"segment_place_config\":{\"dimension\":{\"width\":872,\"height\":1018},\"position\":{\"x\":58,\"y\":-12}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/8x10\\/canvas.png\"]},\"dimension\":{\"width\":3600,\"height\":4200},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"8\\\"x10\\\" Canvas\",\"dimension\":{\"width\":3600,\"height\":4200},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":3600,\"height\":4200}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (12, 'Canvas 8\"x12\"  Prima', '', '12/2', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":102,\"y\":-77},\"dimension\":{\"width\":795,\"height\":1096}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":203.32274999999998,\"y\":24.379999999999995},\"dimension\":{\"width\":592.3545,\"height\":893.24}},\"segment_place_config\":{\"dimension\":{\"width\":795,\"height\":1096},\"position\":{\"x\":102,\"y\":-77}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/8x12\\/canvas.png\"]},\"dimension\":{\"width\":3154,\"height\":4347},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"8\\\"x12\\\" Canvas\",\"dimension\":{\"width\":3154,\"height\":4347},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":3154,\"height\":4347}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (13, 'Canvas 11\"x14\"  DPI ', '', '13/4', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":96,\"y\":18},\"dimension\":{\"width\":776,\"height\":931}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":212.39999999999998,\"y\":134.375},\"dimension\":{\"width\":543.2,\"height\":698.25}},\"segment_place_config\":{\"dimension\":{\"width\":776,\"height\":931},\"position\":{\"x\":96,\"y\":18}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/11x14\\/canvas.png\"]},\"dimension\":{\"width\":4500,\"height\":5400},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"11\\\"x14\\\" Canvas\",\"dimension\":{\"width\":4500,\"height\":5400},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":4500,\"height\":5400}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (14, 'Canvas 12\"x18\" Prima', '', '14/7', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":165,\"y\":32},\"dimension\":{\"width\":644,\"height\":912}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":222.8634,\"y\":89.77520000000004},\"dimension\":{\"width\":528.2732,\"height\":796.4495999999999}},\"segment_place_config\":{\"dimension\":{\"width\":644,\"height\":912},\"position\":{\"x\":165,\"y\":32}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/12x18\\/canvas.png\"]},\"dimension\":{\"width\":4347,\"height\":6154},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"12\\\"x18\\\" Canvas\",\"dimension\":{\"width\":4347,\"height\":6154},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":4347,\"height\":6154}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (15, 'Canvas 12\"x24\" Prima', '', '15/8', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":208,\"y\":-32},\"dimension\":{\"width\":563,\"height\":1029}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":260.0775,\"y\":20.78770000000003},\"dimension\":{\"width\":458.845,\"height\":923.4245999999999}},\"segment_place_config\":{\"dimension\":{\"width\":563,\"height\":1029},\"position\":{\"x\":208,\"y\":-32}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/12x24\\/canvas.png\"]},\"dimension\":{\"width\":4347,\"height\":7950},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"12\\\"x24\\\" Canvas\",\"dimension\":{\"width\":4347,\"height\":7950},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":4347,\"height\":7950}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (16, 'Canvas 16\"x20\"  DPI', '', '16/11', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":121,\"y\":60},\"dimension\":{\"width\":725,\"height\":870}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":202.5625,\"y\":141.5625},\"dimension\":{\"width\":561.875,\"height\":706.875}},\"segment_place_config\":{\"dimension\":{\"width\":725,\"height\":870},\"position\":{\"x\":121,\"y\":60}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/16x20\\/canvas.png\"]},\"dimension\":{\"width\":6000,\"height\":7200},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"16\\\"x20\\\" Canvas\",\"dimension\":{\"width\":6000,\"height\":7200},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":6000,\"height\":7200}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (17, 'Canvas 20\"x24\" DPI', '', '17/14', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":122,\"y\":59},\"dimension\":{\"width\":724,\"height\":845}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":189.875,\"y\":126.89574999999996},\"dimension\":{\"width\":588.25,\"height\":709.2085000000001}},\"segment_place_config\":{\"dimension\":{\"width\":724,\"height\":845},\"position\":{\"x\":122,\"y\":59}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/20x24\\/canvas.png\"]},\"dimension\":{\"width\":7200,\"height\":8400},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"20\\\"x24\\\" Canvas\",\"dimension\":{\"width\":7200,\"height\":8400},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":7200,\"height\":8400}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (18, 'Canvas 20\"x30\" DPI', '', '18/15', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":144,\"y\":-29},\"dimension\":{\"width\":710,\"height\":1013}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":203.78199999999998,\"y\":30.817650000000015},\"dimension\":{\"width\":590.436,\"height\":893.3647}},\"segment_place_config\":{\"dimension\":{\"width\":710,\"height\":1013},\"position\":{\"x\":144,\"y\":-29}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/20x30\\/canvas.png\"]},\"dimension\":{\"width\":7035,\"height\":10035},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"20\\\"x30\\\" Canvas\",\"dimension\":{\"width\":7035,\"height\":10035},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":7035,\"height\":10035}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (19, 'Canvas 10\"x8\" DPI', '', '19/3', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":46},\"dimension\":{\"width\":1001,\"height\":858}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":160.8607,\"y\":206.875},\"dimension\":{\"width\":679.2786,\"height\":536.25}},\"segment_place_config\":{\"dimension\":{\"width\":1001,\"height\":858},\"position\":{\"x\":0,\"y\":46}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/10x8\\/canvas.png\"]},\"dimension\":{\"width\":4200,\"height\":3600},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"10\\\"x8\\\" Canvas\",\"dimension\":{\"width\":4200,\"height\":3600},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":4200,\"height\":3600}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (20, 'Canvas 14\"x11\" DPI', '', '20/9', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":31,\"y\":115},\"dimension\":{\"width\":912,\"height\":760}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":145,\"y\":229},\"dimension\":{\"width\":684,\"height\":532}},\"segment_place_config\":{\"dimension\":{\"width\":912,\"height\":760},\"position\":{\"x\":31,\"y\":115}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/14x11\\/canvas.png\"]},\"dimension\":{\"width\":5400,\"height\":4500},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"14\\\"x11\\\" Canvas\",\"dimension\":{\"width\":5400,\"height\":4500},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":5400,\"height\":4500}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (21, 'Canvas 20\"x16\"  DPI', '', '21/13', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":14,\"y\":66},\"dimension\":{\"width\":969,\"height\":808}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":104.84375,\"y\":156.89999999999998},\"dimension\":{\"width\":787.3125,\"height\":626.2}},\"segment_place_config\":{\"dimension\":{\"width\":969,\"height\":808},\"position\":{\"x\":14,\"y\":66}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/20x16\\/canvas.png\"]},\"dimension\":{\"width\":7200,\"height\":6000},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"20\\\"x16\\\" Canvas\",\"dimension\":{\"width\":7200,\"height\":6000},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":7200,\"height\":6000}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (22, 'Canvas 24\"x20\" DPI', '', '22/17', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":33,\"y\":100},\"dimension\":{\"width\":914,\"height\":784}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":106.43989999999997,\"y\":173.5},\"dimension\":{\"width\":767.1202000000001,\"height\":637}},\"segment_place_config\":{\"dimension\":{\"width\":914,\"height\":784},\"position\":{\"x\":33,\"y\":100}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/24x20\\/canvas.png\"]},\"dimension\":{\"width\":8400,\"height\":7200},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"24\\\"x20\\\" Canvas\",\"dimension\":{\"width\":8400,\"height\":7200},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":8400,\"height\":7200}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (23, 'Canvas 12\"x8\"  Prima', '', '23/5', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":19,\"y\":151},\"dimension\":{\"width\":937,\"height\":680}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":105.67250000000001,\"y\":237.666},\"dimension\":{\"width\":763.655,\"height\":506.668}},\"segment_place_config\":{\"dimension\":{\"width\":937,\"height\":680},\"position\":{\"x\":19,\"y\":151}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/12x8\\/canvas.png\"]},\"dimension\":{\"width\":4347,\"height\":3154},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"12\\\"x8\\\" Canvas\",\"dimension\":{\"width\":4347,\"height\":3154},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":4347,\"height\":3154}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (24, 'Canvas 18\"x12\" Prima', '', '24/12', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":19,\"y\":158},\"dimension\":{\"width\":935,\"height\":660}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":78.23224999999996,\"y\":217.301},\"dimension\":{\"width\":816.5355000000001,\"height\":541.398}},\"segment_place_config\":{\"dimension\":{\"width\":935,\"height\":660},\"position\":{\"x\":19,\"y\":158}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/18x12\\/canvas.png\"]},\"dimension\":{\"width\":6154,\"height\":4347},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"18\\\"x12\\\" Canvas\",\"dimension\":{\"width\":6154,\"height\":4347},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":6154,\"height\":4347}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (25, 'Canvas 24\"x12\" Prima', '', '25/16', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":46,\"y\":254},\"dimension\":{\"width\":882,\"height\":482}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":91.24660000000006,\"y\":298.58500000000004},\"dimension\":{\"width\":791.5067999999999,\"height\":392.83}},\"segment_place_config\":{\"dimension\":{\"width\":882,\"height\":482},\"position\":{\"x\":46,\"y\":254}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/24x12\\/canvas.png\"]},\"dimension\":{\"width\":7950,\"height\":4347},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"24\\\"x12\\\" Canvas\",\"dimension\":{\"width\":7950,\"height\":4347},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":7950,\"height\":4347}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (26, 'Canvas 30\"x20\"  DPI', '', '26/19', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":90,\"y\":215},\"dimension\":{\"width\":799,\"height\":560}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":137.18095,\"y\":262.15200000000004},\"dimension\":{\"width\":704.6381,\"height\":465.69599999999997}},\"segment_place_config\":{\"dimension\":{\"width\":799,\"height\":560},\"position\":{\"x\":90,\"y\":215}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/30x20\\/canvas.png\"]},\"dimension\":{\"width\":10035,\"height\":7035},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"30\\\"x20\\\" Canvas\",\"dimension\":{\"width\":10035,\"height\":7035},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":10035,\"height\":7035}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (27, 'Canvas 12\"x12\"  DPI', '', '27/6', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":21,\"y\":20},\"dimension\":{\"width\":957,\"height\":957}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":155.578125,\"y\":154.578125},\"dimension\":{\"width\":687.84375,\"height\":687.84375}},\"segment_place_config\":{\"dimension\":{\"width\":957,\"height\":957},\"position\":{\"x\":21,\"y\":20}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/12x12\\/canvas.png\"]},\"dimension\":{\"width\":4800,\"height\":4800},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"12\\\"x12\\\" Canvas\",\"dimension\":{\"width\":4800,\"height\":4800},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":4800,\"height\":4800}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (28, 'Canvas 16\"x16\"  Prima', '', '28/10', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":84,\"y\":84},\"dimension\":{\"width\":833,\"height\":833}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":141.68524999999994,\"y\":141.68524999999994},\"dimension\":{\"width\":717.6295000000001,\"height\":717.6295000000001}},\"segment_place_config\":{\"dimension\":{\"width\":833,\"height\":833},\"position\":{\"x\":84,\"y\":84}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/16x16\\/canvas.png\"]},\"dimension\":{\"width\":5552,\"height\":5552},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"16\\\"x16\\\" Canvas\",\"dimension\":{\"width\":5552,\"height\":5552},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":5552,\"height\":5552}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (29, 'Canvas 24\"x24\" Prima', '', '29/18', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":101,\"y\":101},\"dimension\":{\"width\":799,\"height\":799}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/canvas.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/canvas\\/{opt.canvas_size}\\/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":141.34949999999998,\"y\":141.34949999999998},\"dimension\":{\"width\":718.301,\"height\":718.301}},\"segment_place_config\":{\"dimension\":{\"width\":799,\"height\":799},\"position\":{\"x\":101,\"y\":101}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/canvas\\/24x24\\/canvas.png\"]},\"dimension\":{\"width\":7949,\"height\":7949},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"24\\\"x24\\\" Canvas\",\"dimension\":{\"width\":7949,\"height\":7949},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":7949,\"height\":7949}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (30, 'Desktop plaque 7\"x5\" DPI Prima Harrier', '', '30/22', '', '{\"preview_config\":[{\"title\":\"DesktopPlaque\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"desktopPlaque\":{\"position\":{\"x\":38,\"y\":197},\"dimension\":{\"width\":872,\"height\":628}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{opt.desktop_plaque_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{opt.desktop_plaque_size}\\/preview\\/frame.png\"]}],\"segments\":{\"desktopPlaque\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":69.39200000000005,\"y\":228.39999999999998},\"dimension\":{\"width\":809.2159999999999,\"height\":565.2}},\"segment_place_config\":{\"dimension\":{\"width\":872,\"height\":628},\"position\":{\"x\":38,\"y\":197}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/7x5\\/preview\\/background.png\"]},\"dimension\":{\"width\":2139,\"height\":1539},\"title\":\"DesktopPlaque\"}},\"print_file\":{\"default\":{\"title\":\"7\\\"x5\\\" Desktop plaque\",\"dimension\":{\"width\":2139,\"height\":1539},\"dpi\":300,\"config\":{\"desktopPlaque\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":2139,\"height\":1539}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (31, 'Desktop plaque 10\"x8\" DPI Prima Harrier', '', '31/23', '', '{\"preview_config\":[{\"title\":\"DesktopPlaque\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"desktopPlaque\":{\"position\":{\"x\":51,\"y\":131},\"dimension\":{\"width\":855,\"height\":689}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{opt.desktop_plaque_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/{opt.desktop_plaque_size}\\/preview\\/frame.png\"]}],\"segments\":{\"desktopPlaque\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":73.23000000000002,\"y\":153.048},\"dimension\":{\"width\":810.54,\"height\":644.904}},\"segment_place_config\":{\"dimension\":{\"width\":855,\"height\":689},\"position\":{\"x\":51,\"y\":131}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/desktopPlaque\\/10x8\\/preview\\/background.png\"]},\"dimension\":{\"width\":2050,\"height\":1650},\"title\":\"DesktopPlaque\"}},\"print_file\":{\"default\":{\"title\":\"10\\\"x8\\\" Desktop plaque\",\"dimension\":{\"width\":2050,\"height\":1650},\"dpi\":200,\"config\":{\"desktopPlaque\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":2050,\"height\":1650}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (32, 'Fleece blanket 30\"x40\"  DPI CW Prima', '', '32/24', '', '{\"preview_config\":[{\"title\":\"FleeceBlanket\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"fleeceBlanket\":{\"position\":{\"x\":148,\"y\":43},\"dimension\":{\"width\":705,\"height\":915}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{opt.blanket_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{opt.blanket_size}\\/preview\\/frame.png\"]}],\"segments\":{\"fleeceBlanket\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":214.26999999999998,\"y\":109.79500000000002},\"dimension\":{\"width\":572.46,\"height\":781.41}},\"segment_place_config\":{\"dimension\":{\"width\":705,\"height\":915},\"position\":{\"x\":148,\"y\":43}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/30x40\\/preview\\/background.png\"]},\"dimension\":{\"width\":5025,\"height\":6525},\"title\":\"FleeceBlanket\"}},\"print_file\":{\"default\":{\"title\":\"30\\\"x40\\\" Fleece blanket\",\"dimension\":{\"width\":5025,\"height\":6525},\"dpi\":150,\"config\":{\"fleeceBlanket\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":5025,\"height\":6525}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (33, 'Fleece blanket 50\"x60\" DPI CW Harrier', '', '33/25', '', '{\"preview_config\":[{\"title\":\"FleeceBlanket\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"fleeceBlanket\":{\"position\":{\"x\":78,\"y\":0},\"dimension\":{\"width\":832,\"height\":1000}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{opt.blanket_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{opt.blanket_size}\\/preview\\/frame.png\"]}],\"segments\":{\"fleeceBlanket\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":121.26400000000001,\"y\":54},\"dimension\":{\"width\":745.472,\"height\":892}},\"segment_place_config\":{\"dimension\":{\"width\":832,\"height\":1000},\"position\":{\"x\":78,\"y\":0}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/50x60\\/preview\\/background.png\"]},\"dimension\":{\"width\":5400,\"height\":6500},\"title\":\"FleeceBlanket\"}},\"print_file\":{\"default\":{\"title\":\"50\\\"x60\\\" Fleece blanket\",\"dimension\":{\"width\":5400,\"height\":6500},\"dpi\":100,\"config\":{\"fleeceBlanket\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":5400,\"height\":6500}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (34, 'Fleece blanket 60\"x80\" DPI CW', '', '34/26', '', '{\"preview_config\":[{\"title\":\"FleeceBlanket\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"fleeceBlanket\":{\"position\":{\"x\":138,\"y\":9},\"dimension\":{\"width\":738,\"height\":980}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{opt.blanket_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/{opt.blanket_size}\\/preview\\/frame.png\"]}],\"segments\":{\"fleeceBlanket\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":180.80399999999997,\"y\":57.01999999999998},\"dimension\":{\"width\":652.392,\"height\":883.96}},\"segment_place_config\":{\"dimension\":{\"width\":738,\"height\":980},\"position\":{\"x\":138,\"y\":9}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/fleeceBlanket\\/60x80\\/preview\\/background.png\"]},\"dimension\":{\"width\":6374,\"height\":8466},\"title\":\"FleeceBlanket\"}},\"print_file\":{\"default\":{\"title\":\"60\\\"x80\\\" Fleece blanket\",\"dimension\":{\"width\":6374,\"height\":8466},\"dpi\":100,\"config\":{\"fleeceBlanket\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":6374,\"height\":8466}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (35, 'Puzzles 10\"x14\" DPI', '', '35/27', '', '{\"preview_config\":[{\"title\":\"Puzzles\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"puzzles\":{\"position\":{\"x\":114,\"y\":19},\"dimension\":{\"width\":747,\"height\":951}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/puzzles\\/{opt.puzzle_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/puzzles\\/{opt.puzzle_size}\\/preview\\/frame.png\"]}],\"segments\":{\"puzzles\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":165.543,\"y\":80.815},\"dimension\":{\"width\":643.914,\"height\":827.37}},\"segment_place_config\":{\"dimension\":{\"width\":747,\"height\":951},\"position\":{\"x\":114,\"y\":19}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/puzzles\\/10x14\\/preview\\/background.png\"]},\"dimension\":{\"width\":3450,\"height\":4395},\"title\":\"Puzzles\"}},\"print_file\":{\"default\":{\"title\":\"10\\\"x14\\\" Puzzles\",\"dimension\":{\"width\":3450,\"height\":4395},\"dpi\":null,\"config\":{\"puzzles\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":3450,\"height\":4395}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (36, 'Puzzles 14\"x10\"  DPI', '', '36/28', '', '{\"preview_config\":[{\"title\":\"Puzzles\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"puzzles\":{\"position\":{\"x\":1,\"y\":95},\"dimension\":{\"width\":998,\"height\":784}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/puzzles\\/{opt.puzzle_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/puzzles\\/{opt.puzzle_size}\\/preview\\/frame.png\"]}],\"segments\":{\"puzzles\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":65.87,\"y\":149.096},\"dimension\":{\"width\":868.26,\"height\":675.808}},\"segment_place_config\":{\"dimension\":{\"width\":998,\"height\":784},\"position\":{\"x\":1,\"y\":95}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/puzzles\\/14x10\\/preview\\/background.png\"]},\"dimension\":{\"width\":4395,\"height\":3450},\"title\":\"Puzzles\"}},\"print_file\":{\"default\":{\"title\":\"14\\\"x10\\\" Puzzles\",\"dimension\":{\"width\":4395,\"height\":3450},\"dpi\":null,\"config\":{\"puzzles\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":4395,\"height\":3450}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (37, 'Pillow 18\"x18\" DPI CW', '', '37/21', '', '{\"preview_config\":[{\"title\":\"Pillow\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"pillow\":{\"position\":{\"x\":57,\"y\":60},\"dimension\":{\"width\":858,\"height\":858}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{opt.pillow_size}\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/pillow\\/{opt.pillow_size}\\/preview\\/frame.png\"]}],\"segments\":{\"pillow\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":101.61600000000004,\"y\":104.61600000000004},\"dimension\":{\"width\":768.7679999999999,\"height\":768.7679999999999}},\"segment_place_config\":{\"dimension\":{\"width\":858,\"height\":858},\"position\":{\"x\":57,\"y\":60}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/pillow\\/18x18\\/preview\\/background.png\"]},\"dimension\":{\"width\":2925,\"height\":2925},\"title\":\"Pillow\"}},\"print_file\":{\"default\":{\"title\":\"18\\\"x18\\\" Pillow\",\"dimension\":{\"width\":2925,\"height\":2925},\"dpi\":150,\"config\":{\"pillow\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":2925,\"height\":2925}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (38, 'Pillow 16\"x16\"  DPI', '', '38/20', '', '{\"preview_config\":[{\"title\":\"Pillow\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"pillow\":{\"position\":{\"x\":12,\"y\":7},\"dimension\":{\"width\":970,\"height\":970}}},\"layer\":[\"catalog/campaign/type/preview/pillow/{opt.pillow_size}/preview/background.png\",\"main\",\"catalog/campaign/type/preview/pillow/{opt.pillow_size}/preview/frame.png\"]}],\"segments\":{\"pillow\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":110,\"y\":111},\"dimension\":{\"width\":749,\"height\":749}},\"segment_place_config\":{\"dimension\":{\"width\":970,\"height\":970},\"position\":{\"x\":12,\"y\":7}},\"layers\":[\"catalog/campaign/type/preview/pillow/18x18/preview/background.png\"]},\"dimension\":{\"width\":2625,\"height\":2625},\"title\":\"Pillow\"}},\"print_file\":{\"default\":{\"title\":\"18\\\"x18\\\" Pillow\",\"dimension\":{\"width\":2625,\"height\":2625},\"dpi\":150,\"config\":{\"pillow\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":2625,\"height\":2625}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (39, 'Notebook 5\"x7\" DPI', '', '39/43:44', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":202,\"y\":122},\"dimension\":{\"width\":590,\"height\":808}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/notebook\\/{opt.wiro_notebook_size}\\/preview\\/front\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/notebook\\/{opt.wiro_notebook_size}\\/preview\\/front\\/frame.png\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":206,\"y\":122},\"dimension\":{\"width\":590,\"height\":808}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/notebook\\/{opt.wiro_notebook_size}\\/preview\\/back\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/notebook\\/{opt.wiro_notebook_size}\\/preview\\/back\\/frame.png\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":238.875,\"y\":137.35199999999998},\"dimension\":{\"width\":516.25,\"height\":777.296}},\"segment_place_config\":{\"dimension\":{\"width\":590,\"height\":808},\"position\":{\"x\":202,\"y\":122}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/notebook\\/5x7\\/preview\\/front\\/background.png\"]},\"dimension\":{\"width\":1556,\"height\":2138},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":242.875,\"y\":137.35199999999998},\"dimension\":{\"width\":516.25,\"height\":777.296}},\"segment_place_config\":{\"dimension\":{\"width\":590,\"height\":808},\"position\":{\"x\":206,\"y\":122}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/notebook\\/5x7\\/preview\\/back\\/background.png\"]},\"dimension\":{\"width\":1556,\"height\":2138},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"5\\\"x7\\\" Notebook\",\"dimension\":{\"width\":3113,\"height\":2138},\"dpi\":300,\"config\":{\"front\":{\"position\":{\"x\":1557,\"y\":0},\"dimension\":{\"width\":1556,\"height\":2138}},\"back\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":1556,\"height\":2138}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (40, 'Cloth Mask CW', '', '40/46:48', '', '{\"preview_config\":[{\"title\":\"Facemask\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"facemask\":{\"position\":{\"x\":196,\"y\":292},\"dimension\":{\"width\":601,\"height\":406}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/facemask\\/cw\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/facemask\\/cw\\/preview\\/frame.png\"]}],\"segments\":{\"facemask\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":250.99149999999997,\"y\":346.81},\"dimension\":{\"width\":491.01700000000005,\"height\":296.38}},\"segment_place_config\":{\"dimension\":{\"width\":601,\"height\":406},\"position\":{\"x\":196,\"y\":292}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/facemask\\/cw\\/preview\\/template.png\"]},\"dimension\":{\"width\":1275,\"height\":862},\"title\":\"Facemask\"}},\"print_file\":{\"default\":{\"title\":\"Cloth Mask\",\"dimension\":{\"width\":1275,\"height\":862},\"dpi\":150,\"config\":{\"facemask\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":1275,\"height\":862}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (41, 'PM2.5 Cloth Mask DPI Prima Harrier', '', '41/45:47:49', '', '{\"preview_config\":[{\"title\":\"Facemask\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"facemask\":{\"position\":{\"x\":155,\"y\":241},\"dimension\":{\"width\":686,\"height\":495}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/facemask\\/dpi\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/facemask\\/dpi\\/preview\\/frame.png\"]}],\"segments\":{\"facemask\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":212.62399999999997,\"y\":289.015},\"dimension\":{\"width\":570.7520000000001,\"height\":398.97}},\"segment_place_config\":{\"dimension\":{\"width\":686,\"height\":495},\"position\":{\"x\":155,\"y\":241}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/facemask\\/dpi\\/preview\\/template.png\"]},\"dimension\":{\"width\":2325,\"height\":1680},\"title\":\"Facemask\"}},\"print_file\":{\"default\":{\"title\":\"PM2.5 Cloth Mask\",\"dimension\":{\"width\":2325,\"height\":1680},\"dpi\":300,\"config\":{\"facemask\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":2325,\"height\":1680}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (42, 'Classic Tee CW CC Tee launch', '', '42/42:84', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":295,\"y\":214},\"dimension\":{\"width\":400,\"height\":457}}},\"layer\":[\"catalog/campaign/type/preview/classicTee/{opt.gildan_g500_classic_tee_color}/front/background.png\",\"main\",\"catalog/campaign/type/preview/classicTee/{opt.gildan_g500_classic_tee_color}/front/frame.png\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":400,\"height\":457},\"position\":{\"x\":295,\"y\":214}},\"layers\":[\"catalog/campaign/type/preview/classicTee/white/front/background.png\"]},\"dimension\":{\"width\":4200,\"height\":4800},\"title\":\"Front\"}},\"print_file\":{\"default\":{\"title\":\"Classic Tee CW\",\"dimension\":{\"width\":4200,\"height\":4800},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":4200,\"height\":4800}}}}}}', 1, 1601609480, 1601609480);
INSERT INTO `osc_print_template` VALUES (43, 'Canvas 8\"x10\" Harrier', '', '43/1', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":68,\"y\":-3},\"dimension\":{\"width\":842,\"height\":1001}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":199,\"y\":128},\"dimension\":{\"width\":579,\"height\":738}},\"segment_place_config\":{\"dimension\":{\"width\":842,\"height\":1001},\"position\":{\"x\":68,\"y\":-3}},\"layers\":[\"catalog/campaign/type/preview/canvas/8x10/canvas.png\"]},\"dimension\":{\"width\":3142,\"height\":3732},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 8\\\"x10\\\" Harrier\",\"dimension\":{\"width\":3142,\"height\":3732},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":3142,\"height\":3732}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (44, 'Canvas 10\"x8\" Harrier', '', '44/3', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":18,\"y\":69},\"dimension\":{\"width\":966,\"height\":814}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":144,\"y\":195},\"dimension\":{\"width\":712,\"height\":560}},\"segment_place_config\":{\"dimension\":{\"width\":966,\"height\":814},\"position\":{\"x\":18,\"y\":69}},\"layers\":[\"catalog/campaign/type/preview/canvas/10x8/canvas.png\"]},\"dimension\":{\"width\":3732,\"height\":3142},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 10\\\"x8\\\" Harrier\",\"dimension\":{\"width\":3732,\"height\":3142},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":3732,\"height\":3142}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (45, 'Canvas 12\"x12\" Prima', '', '45/6', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":65,\"y\":64},\"dimension\":{\"width\":874,\"height\":874}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":142,\"y\":141},\"dimension\":{\"width\":719,\"height\":719}},\"segment_place_config\":{\"dimension\":{\"width\":874,\"height\":874},\"position\":{\"x\":65,\"y\":64}},\"layers\":[\"catalog/campaign/type/preview/canvas/12x12/canvas.png\"]},\"dimension\":{\"width\":4347,\"height\":4347},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 12\\\"x12\\\" Prima\",\"dimension\":{\"width\":4347,\"height\":4347},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":4347,\"height\":4347}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (46, 'Canvas 16\"x20\" Prima', '', '46/11', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":147,\"y\":85},\"dimension\":{\"width\":671,\"height\":817}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":194,\"y\":132},\"dimension\":{\"width\":576,\"height\":721}},\"segment_place_config\":{\"dimension\":{\"width\":671,\"height\":817},\"position\":{\"x\":147,\"y\":85}},\"layers\":[\"catalog/campaign/type/preview/canvas/16x20/canvas.png\"]},\"dimension\":{\"width\":5552,\"height\":6757},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 16\\\"x20\\\" Prima\",\"dimension\":{\"width\":5552,\"height\":6757},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":5552,\"height\":6757}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (47, 'Canvas 16\"x20\" Harrier', '', '47/11', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":130,\"y\":61},\"dimension\":{\"width\":717,\"height\":871}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":200,\"y\":131},\"dimension\":{\"width\":576,\"height\":730}},\"segment_place_config\":{\"dimension\":{\"width\":717,\"height\":871},\"position\":{\"x\":130,\"y\":61}},\"layers\":[\"catalog/campaign/type/preview/canvas/16x20/canvas.png\"]},\"dimension\":{\"width\":5504,\"height\":6685},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 16\\\"x20\\\" Harrier\",\"dimension\":{\"width\":5504,\"height\":6685},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":5504,\"height\":6685}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (48, 'Canvas 20\"x16\" Prima', '', '48/13', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":40,\"y\":94},\"dimension\":{\"width\":914,\"height\":751}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":93,\"y\":146},\"dimension\":{\"width\":806,\"height\":645}},\"segment_place_config\":{\"dimension\":{\"width\":914,\"height\":751},\"position\":{\"x\":40,\"y\":94}},\"layers\":[\"catalog/campaign/type/preview/canvas/20x16/canvas.png\"]},\"dimension\":{\"width\":6757,\"height\":5552},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 20\\\"x16\\\" Prima\",\"dimension\":{\"width\":6757,\"height\":5552},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":6757,\"height\":5552}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (49, 'Canvas 20\"x16\" Harrier', '', '49/13', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":17,\"y\":73},\"dimension\":{\"width\":962,\"height\":792}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":94,\"y\":150},\"dimension\":{\"width\":806,\"height\":636}},\"segment_place_config\":{\"dimension\":{\"width\":962,\"height\":792},\"position\":{\"x\":17,\"y\":73}},\"layers\":[\"catalog/campaign/type/preview/canvas/20x16/canvas.png\"]},\"dimension\":{\"width\":6685,\"height\":5504},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 20\\\"x16\\\" Harrier\",\"dimension\":{\"width\":6685,\"height\":5504},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":6685,\"height\":5504}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (50, 'Canvas 20\"x24\" Harrier', '', '50/14', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":125,\"y\":61},\"dimension\":{\"width\":714,\"height\":840}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":182,\"y\":118},\"dimension\":{\"width\":598,\"height\":724}},\"segment_place_config\":{\"dimension\":{\"width\":714,\"height\":840},\"position\":{\"x\":125,\"y\":61}},\"layers\":[\"catalog/campaign/type/preview/canvas/20x24/canvas.png\"]},\"dimension\":{\"width\":6685,\"height\":7866},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 20\\\"x24\\\" Harrier\",\"dimension\":{\"width\":6685,\"height\":7866},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":6685,\"height\":7866}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (51, 'Canvas 24\"x20\" Harrier', '', '51/17', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":36,\"y\":105},\"dimension\":{\"width\":904,\"height\":768}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":98,\"y\":167},\"dimension\":{\"width\":779,\"height\":643}},\"segment_place_config\":{\"dimension\":{\"width\":904,\"height\":768},\"position\":{\"x\":36,\"y\":105}},\"layers\":[\"catalog/campaign/type/preview/canvas/24x20/canvas.png\"]},\"dimension\":{\"width\":7866,\"height\":6685},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 24\\\"x20\\\" Harrier\",\"dimension\":{\"width\":7866,\"height\":6685},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":7866,\"height\":6685}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (52, 'Canvas 20\"x30\" Prima', '', '52/15', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":164,\"y\":-2},\"dimension\":{\"width\":663,\"height\":958}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":202,\"y\":36},\"dimension\":{\"width\":585,\"height\":881}},\"segment_place_config\":{\"dimension\":{\"width\":663,\"height\":958},\"position\":{\"x\":164,\"y\":-2}},\"layers\":[\"catalog/campaign/type/preview/canvas/20x30/canvas.png\"]},\"dimension\":{\"width\":6757,\"height\":9757},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 20\\\"x30\\\" Prima\",\"dimension\":{\"width\":6757,\"height\":9757},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":6757,\"height\":9757}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (53, 'Canvas 30\"x20\" Prima', '', '53/19', '', '{\"preview_config\":[{\"title\":\"Canvas\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"canvas\":{\"position\":{\"x\":108,\"y\":229},\"dimension\":{\"width\":765,\"height\":529}}},\"layer\":[\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/canvas.png\",\"main\",\"catalog/campaign/type/preview/canvas/{opt.canvas_size}/frame.png\"]}],\"segments\":{\"canvas\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":138,\"y\":260},\"dimension\":{\"width\":703,\"height\":466}},\"segment_place_config\":{\"dimension\":{\"width\":765,\"height\":529},\"position\":{\"x\":108,\"y\":229}},\"layers\":[\"catalog/campaign/type/preview/canvas/30x20/canvas.png\"]},\"dimension\":{\"width\":9757,\"height\":6757},\"title\":\"Canvas\"}},\"print_file\":{\"default\":{\"title\":\"Canvas 30\\\"x20\\\" Prima\",\"dimension\":{\"width\":9757,\"height\":6757},\"dpi\":null,\"config\":{\"canvas\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":9757,\"height\":6757}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (54, 'Pillow 16\"x16\" CW', '', '54/20', '', '{\"preview_config\":[{\"title\":\"Pillow\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"pillow\":{\"position\":{\"x\":74,\"y\":75},\"dimension\":{\"width\":823,\"height\":823}}},\"layer\":[\"catalog/campaign/type/preview/pillow/{opt.pillow_size}/preview/background.png\",\"main\",\"catalog/campaign/type/preview/pillow/{opt.pillow_size}/preview/frame.png\"]}],\"segments\":{\"pillow\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":110,\"y\":111},\"dimension\":{\"width\":749,\"height\":749}},\"segment_place_config\":{\"dimension\":{\"width\":823,\"height\":823},\"position\":{\"x\":74,\"y\":75}},\"layers\":[\"catalog/campaign/type/preview/pillow/16x16/preview/background.png\"]},\"dimension\":{\"width\":1701,\"height\":1701},\"title\":\"Pillow\"}},\"print_file\":{\"default\":{\"title\":\"Pillow 16\\\"x16\\\" CW\",\"dimension\":{\"width\":1701,\"height\":1701},\"dpi\":150,\"config\":{\"pillow\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":1701,\"height\":1701}}}}}}', 1, 1601609579, 1601609579);
INSERT INTO `osc_print_template` VALUES (55, 'Mug 11oz white Prima', '', '55/29:30:58', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":335,\"y\":246},\"dimension\":{\"width\":466,\"height\":602}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/{opt.mug_size}\\/{opt.mug_color}\\/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":145,\"y\":246},\"dimension\":{\"width\":466,\"height\":602}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/{opt.mug_size}\\/{opt.mug_color}\\/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":602},\"position\":{\"x\":335,\"y\":246}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/11oz\\/white\\/front.png\"]},\"dimension\":{\"width\":796,\"height\":1028},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":602},\"position\":{\"x\":145,\"y\":246}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/11oz\\/white\\/back.png\"]},\"dimension\":{\"width\":796,\"height\":1028},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"Mug 11oz white Prima\",\"dimension\":{\"width\":2410,\"height\":1029},\"dpi\":300,\"config\":{\"front\":{\"position\":{\"x\":19,\"y\":0},\"dimension\":{\"width\":796,\"height\":1028}},\"back\":{\"position\":{\"x\":1595,\"y\":0},\"dimension\":{\"width\":796,\"height\":1028}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (56, 'Mug 11oz Tow Tone white Prima', '', '56/37:38:41', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":335,\"y\":246},\"dimension\":{\"width\":466,\"height\":602}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/towTone\\/{opt.mug_color}\\/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":145,\"y\":246},\"dimension\":{\"width\":466,\"height\":602}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/towTone\\/{opt.mug_color}\\/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":602},\"position\":{\"x\":335,\"y\":246}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/towTone\\/black\\/front.png\"]},\"dimension\":{\"width\":796,\"height\":1028},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":466,\"height\":602},\"position\":{\"x\":145,\"y\":246}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/mug\\/towTone\\/black\\/back.png\"]},\"dimension\":{\"width\":796,\"height\":1028},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"Mug 11oz Tow Tone white Prima\",\"dimension\":{\"width\":2410,\"height\":1029},\"dpi\":300,\"config\":{\"front\":{\"position\":{\"x\":19,\"y\":0},\"dimension\":{\"width\":796,\"height\":1028}},\"back\":{\"position\":{\"x\":1595,\"y\":0},\"dimension\":{\"width\":796,\"height\":1028}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (57, 'Mug 15oz white Harier', '', '57/31:32:39', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":360,\"y\":221},\"dimension\":{\"width\":418,\"height\":574}}},\"layer\":[\"catalog/campaign/type/preview/mug/{opt.mug_size}/{opt.mug_color}/front.png\",\"main\"]},{\"title\":\"Back\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"back\":{\"position\":{\"x\":255,\"y\":221},\"dimension\":{\"width\":418,\"height\":574}}},\"layer\":[\"catalog/campaign/type/preview/mug/{opt.mug_size}/{opt.mug_color}/back.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":418,\"height\":574},\"position\":{\"x\":360,\"y\":221}},\"layers\":[\"catalog/campaign/type/preview/mug/15oz/white/front.png\"]},\"dimension\":{\"width\":842,\"height\":1157},\"title\":\"Front\"},\"back\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":418,\"height\":574},\"position\":{\"x\":255,\"y\":221}},\"layers\":[\"catalog/campaign/type/preview/mug/15oz/white/back.png\"]},\"dimension\":{\"width\":842,\"height\":1157},\"title\":\"Back\"}},\"print_file\":{\"default\":{\"title\":\"Mug 15oz white Harier\",\"dimension\":{\"width\":2563,\"height\":1157},\"dpi\":300,\"config\":{\"front\":{\"position\":{\"x\":19,\"y\":0},\"dimension\":{\"width\":842,\"height\":1157}},\"back\":{\"position\":{\"x\":1702,\"y\":0},\"dimension\":{\"width\":842,\"height\":1157}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (58, 'Bella Canvas Tee CC Tee Launch', '', '58/53:87', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":303,\"y\":248},\"dimension\":{\"width\":395,\"height\":452}}},\"layer\":[\"catalog/campaign/type/preview/bellaCanvasTee/{opt.bella_canvas_3001c_unisex_jersey_short_sleeve_color}/front/background.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":395,\"height\":452},\"position\":{\"x\":303,\"y\":248}},\"layers\":[\"catalog/campaign/type/preview/bellaCanvasTee/white/front/background.png\"]},\"dimension\":{\"width\":4200,\"height\":4800},\"title\":\"Front\"}},\"print_file\":{\"default\":{\"title\":\"Bella Canvas Tee CC Tee Launch\",\"dimension\":{\"width\":4200,\"height\":4800},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":4200,\"height\":4800}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (59, 'Next Level Tee CC Tee Launch', '', '59/54:88', '', '{\"preview_config\":[{\"title\":\"Front\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"front\":{\"position\":{\"x\":301,\"y\":270},\"dimension\":{\"width\":395,\"height\":452}}},\"layer\":[\"catalog/campaign/type/preview/nextLevelTee/{opt.next_level_nl3600_premium_short_sleeve_color}/front/background.png\",\"main\"]}],\"segments\":{\"front\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":395,\"height\":452},\"position\":{\"x\":301,\"y\":270}},\"layers\":[\"catalog/campaign/type/preview/nextLevelTee/white/front/background.png\"]},\"dimension\":{\"width\":4200,\"height\":4800},\"title\":\"Front\"}},\"print_file\":{\"default\":{\"title\":\"Next Level Tee CC Tee Launch\",\"dimension\":{\"width\":4200,\"height\":4800},\"dpi\":null,\"config\":{\"front\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":4200,\"height\":4800}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (60, 'Aluminum Medallion Ornament DPI Harrier Prima', '', '60/55', '', '{\"preview_config\":[{\"title\":\"Ornament\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"ornament\":{\"position\":{\"x\":44,\"y\":157},\"dimension\":{\"width\":910,\"height\":682}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumMedallion\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumMedallion\\/frame.png\"]}],\"segments\":{\"ornament\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":109.66274509803918,\"y\":226.1987447698745},\"dimension\":{\"width\":778.6745098039216,\"height\":543.602510460251}},\"segment_place_config\":{\"dimension\":{\"width\":910,\"height\":682},\"position\":{\"x\":44,\"y\":157}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumMedallion\\/background.png\"]},\"dimension\":{\"width\":1275,\"height\":956},\"title\":\"Ornament\"}},\"print_file\":{\"default\":{\"title\":\"Aluminum Medallion Ornament DPI Harrier Prima\",\"dimension\":{\"width\":1275,\"height\":956},\"dpi\":null,\"config\":{\"ornament\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":1275,\"height\":956}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (61, 'Aluminum Scalloped Ornament DPI Harrier Prima', '', '61/56', '', '{\"preview_config\":[{\"title\":\"Ornament\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"ornament\":{\"position\":{\"x\":51,\"y\":192},\"dimension\":{\"width\":893,\"height\":635}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumScalloped\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumScalloped\\/frame.png\"]}],\"segments\":{\"ornament\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":117.33910700538877,\"y\":257.97402597402595},\"dimension\":{\"width\":760.3217859892225,\"height\":503.05194805194805}},\"segment_place_config\":{\"dimension\":{\"width\":893,\"height\":635},\"position\":{\"x\":51,\"y\":192}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumScalloped\\/background.png\"]},\"dimension\":{\"width\":1299,\"height\":924},\"title\":\"Ornament\"}},\"print_file\":{\"default\":{\"title\":\"Aluminum Scalloped Ornament DPI Harrier Prima\",\"dimension\":{\"width\":1299,\"height\":924},\"dpi\":null,\"config\":{\"ornament\":{\"position\":{\"x\":-0.5,\"y\":0},\"dimension\":{\"width\":1299,\"height\":924}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (62, 'Aluminum Square Ornament DPI Harrier Prima', '', '62/57', '', '{\"preview_config\":[{\"title\":\"Ornament\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"ornament\":{\"position\":{\"x\":140,\"y\":134},\"dimension\":{\"width\":723,\"height\":723}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumSquare\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumSquare\\/frame.png\"]}],\"segments\":{\"ornament\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":209.47747747747746,\"y\":203.47747747747746},\"dimension\":{\"width\":584.0450450450451,\"height\":584.0450450450451}},\"segment_place_config\":{\"dimension\":{\"width\":723,\"height\":723},\"position\":{\"x\":140,\"y\":134}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/ornament\\/aluminiumSquare\\/background.png\"]},\"dimension\":{\"width\":999,\"height\":999},\"title\":\"Ornament\"}},\"print_file\":{\"default\":{\"title\":\"Aluminum Square Ornament DPI Harrier Prima\",\"dimension\":{\"width\":999,\"height\":999},\"dpi\":null,\"config\":{\"ornament\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":999,\"height\":999}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (63, 'Kid PM2.5 Cloth Mask DPI', '', '63/50:51:52:85:86', '', '{\"preview_config\":[{\"title\":\"Facemask\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"facemask\":{\"position\":{\"x\":183,\"y\":296},\"dimension\":{\"width\":635,\"height\":409}}},\"layer\":[\"catalog\\/campaign\\/type\\/preview\\/facemask\\/dpiKid\\/preview\\/background.png\",\"main\",\"catalog\\/campaign\\/type\\/preview\\/facemask\\/dpiKid\\/preview\\/frame.png\"]}],\"segments\":{\"facemask\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":251.54108635097492,\"y\":351.6522678185745},\"dimension\":{\"width\":497.91782729805016,\"height\":297.69546436285094}},\"segment_place_config\":{\"dimension\":{\"width\":635,\"height\":409},\"position\":{\"x\":183,\"y\":296}},\"layers\":[\"catalog\\/campaign\\/type\\/preview\\/facemask\\/dpiKid\\/preview\\/template.png\"]},\"dimension\":{\"width\":2154,\"height\":1389},\"title\":\"Facemask\"}},\"print_file\":{\"default\":{\"title\":\"Kid PM2.5 Cloth Mask DPI\",\"dimension\":{\"width\":2154,\"height\":1389},\"dpi\":300,\"config\":{\"facemask\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":2154,\"height\":1389}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (64, 'FullPrints Mug 11oz white Prima', '', '64/62:63:64:65', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":286},\"dimension\":{\"width\":1000,\"height\":427}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":427},\"position\":{\"x\":0,\"y\":286}},\"layers\":[]},\"dimension\":{\"width\":2410,\"height\":1029},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"FullPrints Mug 11oz white Prima\",\"dimension\":{\"width\":2410,\"height\":1029},\"dpi\":300,\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":2410,\"height\":1029}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (65, 'FullPrints Mug 11oz 2 tone Prima', '', '65/78:79:80:81', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":286},\"dimension\":{\"width\":1000,\"height\":427}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":427},\"position\":{\"x\":0,\"y\":286}},\"layers\":[]},\"dimension\":{\"width\":2410,\"height\":1029},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"FullPrints Mug 11oz 2 tone Prima\",\"dimension\":{\"width\":2410,\"height\":1029},\"dpi\":300,\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":-0.5},\"dimension\":{\"width\":2410,\"height\":1029}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (66, 'FullPrints Mug 15oz white Harrier', '', '66/66:67:68:69', '', '{\"preview_config\":[{\"title\":\"Design\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"design\":{\"position\":{\"x\":0,\"y\":274},\"dimension\":{\"width\":1000,\"height\":451}}},\"layer\":[\"main\"]}],\"segments\":{\"design\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":null,\"segment_place_config\":{\"dimension\":{\"width\":1000,\"height\":451},\"position\":{\"x\":0,\"y\":274}},\"layers\":[]},\"dimension\":{\"width\":2563,\"height\":1157},\"title\":\"Design\"}},\"print_file\":{\"default\":{\"title\":\"FullPrints Mug 15oz white Harrier\",\"dimension\":{\"width\":2563,\"height\":1157},\"dpi\":300,\"config\":{\"design\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":2563,\"height\":1157}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (67, 'Circle Ornament CC Tee Launch', '', '67/61:82', '', '{\"preview_config\":[{\"title\":\"Ornament\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"ornament\":{\"position\":{\"x\":96,\"y\":97},\"dimension\":{\"width\":807,\"height\":807}}},\"layer\":[\"catalog/campaign/type/preview/ornament/circle/preview/background.png\",\"main\",\"catalog/campaign/type/preview/ornament/circle/preview/frame.png\"]}],\"segments\":{\"ornament\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":183.54036326942486,\"y\":184.54036326942486},\"dimension\":{\"width\":631.9192734611503,\"height\":631.9192734611503}},\"segment_place_config\":{\"dimension\":{\"width\":807,\"height\":807},\"position\":{\"x\":96,\"y\":97}},\"layers\":[\"catalog/campaign/type/preview/ornament/circle/preview/background.png\"]},\"dimension\":{\"width\":991,\"height\":991},\"title\":\"Ornament\"}},\"print_file\":{\"default\":{\"title\":\"Circle Ornament CC Tee Launch\",\"dimension\":{\"width\":991,\"height\":991},\"dpi\":null,\"config\":{\"ornament\":{\"position\":{\"x\":-0.5,\"y\":-0.5},\"dimension\":{\"width\":991,\"height\":991}}}}}}', 1, 1601972607, 1601972607);
INSERT INTO `osc_print_template` VALUES (68, 'Heart Ornament CC Tee Launch', '', '68/60:83', '', '{\"preview_config\":[{\"title\":\"Ornament\",\"dimension\":{\"width\":1000,\"height\":1000},\"config\":{\"ornament\":{\"position\":{\"x\":70,\"y\":100},\"dimension\":{\"width\":860,\"height\":805}}},\"layer\":[\"catalog/campaign/type/preview/ornament/heart/preview/background.png\",\"main\",\"catalog/campaign/type/preview/ornament/heart/preview/frame.png\"]}],\"segments\":{\"ornament\":{\"builder_config\":{\"dimension\":{\"width\":1000,\"height\":1000},\"view_box\":null,\"safe_box\":{\"position\":{\"x\":160.06756756756755,\"y\":189.62886597938143},\"dimension\":{\"width\":679.8648648648649,\"height\":625.7422680412371}},\"segment_place_config\":{\"dimension\":{\"width\":860,\"height\":805},\"position\":{\"x\":70,\"y\":100}},\"layers\":[\"catalog/campaign/type/preview/ornament/heart/preview/background.png\"]},\"dimension\":{\"width\":1036,\"height\":970},\"title\":\"Ornament\"}},\"print_file\":{\"default\":{\"title\":\"Heart Ornament CC Tee Launch\",\"dimension\":{\"width\":1036,\"height\":970},\"dpi\":null,\"config\":{\"ornament\":{\"position\":{\"x\":0,\"y\":0},\"dimension\":{\"width\":1036,\"height\":970}}}}}}', 1, 1601972607, 1601972607);
COMMIT;

-- ----------------------------
-- Table structure for osc_print_template_map
-- ----------------------------
DROP TABLE IF EXISTS `osc_print_template_map`;
CREATE TABLE `osc_print_template_map` (
  `id` int(11) DEFAULT NULL,
  `print_template_id` int(11) DEFAULT NULL,
  `print_template_map_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- ----------------------------
-- Records of osc_print_template_map
-- ----------------------------
BEGIN;
INSERT INTO `osc_print_template_map` VALUES (1, 1, 55);
INSERT INTO `osc_print_template_map` VALUES (2, 2, 57);
INSERT INTO `osc_print_template_map` VALUES (3, 3, 56);
INSERT INTO `osc_print_template_map` VALUES (4, 6, 64);
INSERT INTO `osc_print_template_map` VALUES (5, 7, 66);
INSERT INTO `osc_print_template_map` VALUES (6, 8, 65);
INSERT INTO `osc_print_template_map` VALUES (7, 11, 43);
INSERT INTO `osc_print_template_map` VALUES (8, 16, 46);
INSERT INTO `osc_print_template_map` VALUES (9, 16, 47);
INSERT INTO `osc_print_template_map` VALUES (10, 46, 47);
INSERT INTO `osc_print_template_map` VALUES (11, 18, 52);
INSERT INTO `osc_print_template_map` VALUES (12, 19, 44);
INSERT INTO `osc_print_template_map` VALUES (13, 21, 48);
INSERT INTO `osc_print_template_map` VALUES (14, 21, 49);
INSERT INTO `osc_print_template_map` VALUES (15, 48, 49);
INSERT INTO `osc_print_template_map` VALUES (16, 22, 51);
INSERT INTO `osc_print_template_map` VALUES (17, 17, 50);
INSERT INTO `osc_print_template_map` VALUES (18, 26, 53);
INSERT INTO `osc_print_template_map` VALUES (19, 27, 45);
INSERT INTO `osc_print_template_map` VALUES (20, 38, 54);
COMMIT;

-- ----------------------------
-- Table structure for osc_print_template_mockup_rel
-- ----------------------------
DROP TABLE IF EXISTS `osc_print_template_mockup_rel`;
CREATE TABLE `osc_print_template_mockup_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(45) NOT NULL,
  `print_template_id` int(11) DEFAULT NULL,
  `mockup_id` int(11) DEFAULT NULL,
  `position` int(11) unsigned NOT NULL DEFAULT 1,
  `flag_main` tinyint(1) DEFAULT 0,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  `is_default_mockup` tinyint(1) DEFAULT 0 COMMENT 'Danh dau day la mockup mac dinh cua print template',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `ukey_UNIQUE` (`ukey`) USING BTREE,
  UNIQUE KEY `print_template_mockup_index` (`print_template_id`,`mockup_id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=122 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_print_template_mockup_rel
-- ----------------------------
BEGIN;
INSERT INTO `osc_print_template_mockup_rel` VALUES (1, '29_1', 1, 29, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (2, '30_1', 1, 30, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (3, '58_1', 1, 58, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (4, '31_2', 2, 31, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (5, '32_2', 2, 32, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (6, '39_2', 2, 39, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (7, '37_3', 3, 37, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (8, '38_3', 3, 38, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (9, '41_3', 3, 41, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (10, '35_4', 4, 35, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (11, '36_4', 4, 36, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (12, '40_4', 4, 40, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (13, '33_5', 5, 33, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (14, '34_5', 5, 34, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (15, '59_5', 5, 59, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (16, '62_6', 6, 62, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (17, '63_6', 6, 63, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (18, '64_6', 6, 64, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (19, '65_6', 6, 65, 4, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (20, '66_7', 7, 66, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (21, '67_7', 7, 67, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (22, '68_7', 7, 68, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (23, '69_7', 7, 69, 4, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (24, '78_8', 8, 78, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (25, '79_8', 8, 79, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (26, '80_8', 8, 80, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (27, '81_8', 8, 81, 4, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (28, '74_9', 9, 74, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (29, '75_9', 9, 75, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (30, '76_9', 9, 76, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (31, '77_9', 9, 77, 4, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (32, '70_10', 10, 70, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (33, '71_10', 10, 71, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (34, '72_10', 10, 72, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (35, '73_10', 10, 73, 4, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (36, '1_11', 11, 1, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (37, '2_12', 12, 2, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (38, '4_13', 13, 4, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (39, '7_14', 14, 7, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (40, '8_15', 15, 8, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (41, '11_16', 16, 11, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (42, '14_17', 17, 14, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (43, '15_18', 18, 15, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (44, '3_19', 19, 3, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (45, '9_20', 20, 9, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (46, '13_21', 21, 13, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (47, '17_22', 22, 17, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (48, '5_23', 23, 5, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (49, '12_24', 24, 12, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (50, '16_25', 25, 16, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (51, '19_26', 26, 19, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (52, '6_27', 27, 6, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (53, '10_28', 28, 10, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (54, '18_29', 29, 18, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (55, '22_30', 30, 22, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (56, '23_31', 31, 23, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (57, '24_32', 32, 24, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (58, '25_33', 33, 25, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (59, '26_34', 34, 26, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (60, '27_35', 35, 27, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (61, '28_36', 36, 28, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (62, '21_37', 37, 21, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (63, '20_38', 38, 20, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (64, '43_39', 39, 43, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (65, '44_39', 39, 44, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (66, '46_40', 40, 46, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (67, '48_40', 40, 48, 2, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (68, '45_41', 41, 45, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (69, '47_41', 41, 47, 2, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (70, '49_41', 41, 49, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (71, '42_42', 42, 42, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (72, '84_42', 42, 84, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (73, '1_43', 43, 1, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (74, '3_44', 44, 3, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (75, '6_45', 45, 6, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (76, '11_46', 46, 11, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (77, '11_47', 47, 11, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (78, '13_48', 48, 13, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (79, '13_49', 49, 13, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (80, '14_50', 50, 14, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (81, '17_51', 51, 17, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (82, '15_52', 52, 15, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (83, '19_53', 53, 19, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (84, '20_54', 54, 20, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (85, '29_55', 55, 29, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (86, '30_55', 55, 30, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (87, '58_55', 55, 58, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (88, '37_56', 56, 37, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (89, '38_56', 56, 38, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (90, '41_56', 56, 41, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (91, '31_57', 57, 31, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (92, '32_57', 57, 32, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (93, '39_57', 57, 39, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (94, '53_58', 58, 53, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (95, '87_58', 58, 87, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (96, '54_59', 59, 54, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (97, '88_59', 59, 88, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (98, '55_60', 60, 55, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (99, '56_61', 61, 56, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (100, '57_62', 62, 57, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (101, '50_63', 63, 50, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (102, '51_63', 63, 51, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (103, '52_63', 63, 52, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (104, '85_63', 63, 85, 4, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (105, '86_63', 63, 86, 5, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (106, '62_64', 64, 62, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (107, '63_64', 64, 63, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (108, '64_64', 64, 64, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (109, '65_64', 64, 65, 4, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (110, '78_65', 65, 78, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (111, '79_65', 65, 79, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (112, '80_65', 65, 80, 3, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (113, '81_65', 65, 81, 4, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (114, '66_66', 66, 66, 1, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (115, '67_66', 66, 67, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (116, '68_66', 66, 68, 3, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (117, '69_66', 66, 69, 4, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (118, '61_67', 67, 61, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (119, '82_67', 67, 82, 2, 0, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (120, '60_68', 68, 60, 1, 1, 1603003950, 1603003950, 1);
INSERT INTO `osc_print_template_mockup_rel` VALUES (121, '83_68', 68, 83, 2, 0, 1603003950, 1603003950, 1);
COMMIT;

-- ----------------------------
-- Table structure for osc_product_type
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type`;
CREATE TABLE `osc_product_type` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) NOT NULL,
  `tab_name` varchar(255) NOT NULL,
  `group_name` varchar(255) NOT NULL DEFAULT '0',
  `title` varchar(255) DEFAULT NULL,
  `short_title` varchar(255) DEFAULT NULL,
  `identifier` varchar(500) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `product_type_option_ids` varchar(255) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_key` (`ukey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_product_type
-- ----------------------------
BEGIN;
INSERT INTO `osc_product_type` VALUES (1, 'wrapped_canvas', 'Canvas', 'Canvas', 'Wrapped Canvas', '', '{\"personalized\":\"Personalized Wrapped Canvas\",\"photo\":\"Photo Wrapped Canvas\",\"image\":\"Wrapped Canvas\"}', 'catalog/campaign/type/icon/canvas-portrait.png', '', '1', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type` VALUES (2, 'matte_poster', 'Poster', 'Poster', 'Matte Poster', '', '{\"personalized\":\"Personalized Poster\",\"photo\":\"Photo Poster\",\"image\":\"Poster\"}', '', '', '2', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type` VALUES (3, 'glossy_poster', 'Poster', 'Poster', 'Glossy Poster', '', '{\"personalized\":\"Personalized Poster\",\"photo\":\"Photo Poster\",\"image\":\"Poster\"}', '', '', '2', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type` VALUES (4, 'gildan_g500_classic_tee', 'Apparel', 'Shirts', 'Gildan G500 Classic Tee', '', '{\"personalized\":\"Personalized Shirt\",\"photo\":\"Photo Shirt\",\"image\":\"Shirt\"}', 'catalog/campaign/type/icon/classicTee/front.png', '', '3,4', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type` VALUES (5, 'bella_canvas_3001c_unisex_jersey_short_sleeve', 'Apparel', 'Shirts', 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve', '', '{\"personalized\":\"Personalized Shirt\",\"photo\":\"Photo Shirt\",\"image\":\"Shirt\"}', 'catalog/campaign/type/icon/bellaCanvasTee/front.png', '', '5,4', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type` VALUES (6, 'next_level_nl3600_premium_short_sleeve', 'Apparel', 'Shirts', 'Next Level NL3600 Premium Short Sleeve', '', '{\"personalized\":\"Personalized Shirt\",\"photo\":\"Photo Shirt\",\"image\":\"Shirt\"}', 'catalog/campaign/type/icon/nextLevelTee/front.png', '', '6,4', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type` VALUES (7, 'ceramic_mug', 'Drinkware', 'Mug', 'Ceramic Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Personalized Photo Mug\",\"image\":\"Mug\"}', 'catalog/campaign/type/icon/mug/11oz.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (8, 'two_tone_mug', 'Drinkware', 'Mug', 'Two Tone Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Photo Mug\",\"image\":\"Mug\"}', 'catalog/campaign/type/icon/mug/twoTone.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (9, 'enamel_campfire_mug', 'Drinkware', 'Mug', 'Enamel Campfire Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Photo Mug\",\"image\":\"Mug\"}', 'catalog/campaign/type/icon/mug/enamelCampfire.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (10, 'insulated_coffee_mug', 'Drinkware', 'Mug', 'Insulated Coffee Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Photo Mug\",\"image\":\"Mug\"}', 'catalog/campaign/type/icon/mug/insulatedCoffee.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (11, 'fleece_blanket', 'Bedroom', 'Blanket', 'Fleece Blanket', '', '{\"personalized\":\"Personalized Blanket\",\"photo\":\"Photo Blanket\",\"image\":\"Fleece Blanket\"}', 'catalog/campaign/type/icon/fleeceBlanket/30x40.png', '', '9,10', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (12, 'sherpa_flannel_blanket', 'Bedroom', 'Blanket', 'Sherpa Flannel Blanket', '', '{\"personalized\":\"Personalized Blanket\",\"photo\":\"Photo Blanket\",\"image\":\"Blanket\"}', 'catalog/campaign/type/icon/fleeceBlanket/50x60.png', '', '11,10', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (13, 'pillow', 'Bedroom', 'Pillow', 'Pillow', '', '{\"personalized\":\"Personalized Pillow\",\"photo\":\"Photo Pillow\",\"image\":\"Pillow\"}', 'catalog/campaign/type/icon/pillow/16x16.png', '', '12,13', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (14, 'tea_towel', 'Bathroom', 'Towel', 'Tea Towel', '', '{\"personalized\":\"Personalized Towel\",\"photo\":\"Photo Towel\",\"image\":\"Towel\"}', '', '', '14,15', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (15, 'beach_towel', 'Bathroom', 'Towel', 'Beach Towel', '', '{\"personalized\":\"Personalized Towel\",\"photo\":\"Photo Towel\",\"image\":\"Towel\"}', '', '', '16,15', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (16, 'kid_towel', 'Bathroom', 'Towel', 'Kid Towel', '', '{\"personalized\":\"Personalized Towel\",\"photo\":\"Photo Towel\",\"image\":\"Towel\"}', '', '', '17,15', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (17, 'ornament_square', 'Accessories', 'Ornament', 'Ornament Square', '', '{\"personalized\":\"Personalized Ornament\",\"photo\":\"Photo Ornament\",\"image\":\"Ornament\"}', 'catalog/campaign/type/icon/ornament/aluminiumSquare/background.png', '', '18,19', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (18, 'ornament_medallion', 'Accessories', 'Ornament', 'Ornament Medallion', '', '{\"personalized\":\"Personalized Ornament\",\"photo\":\"Photo Ornament\",\"image\":\"Ornament\"}', 'catalog/campaign/type/icon/ornament/aluminiumMedallion/background.png', '', '18,19', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (19, 'ornament_scalloped', 'Accessories', 'Ornament', 'Ornament Scalloped', '', '{\"personalized\":\"Personalized Ornament\",\"photo\":\"Photo Ornament\",\"image\":\"Ornament\"}', 'catalog/campaign/type/icon/ornament/aluminiumScalloped/background.png', '', '18,19', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (20, 'ornament_circle', 'Accessories', 'Ornament', 'Ornament Circle', '', '{\"personalized\":\"Personalized Ornament\",\"photo\":\"Photo Ornament\",\"image\":\"Ornament\"}', 'catalog/campaign/type/icon/ornament/circle/background.png', '', '18,19', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (21, 'ornament_heart', 'Accessories', 'Ornament', 'Ornament Heart', '', '{\"personalized\":\"Personalized Ornament\",\"photo\":\"Photo Ornament\",\"image\":\"Ornament\"}', 'catalog/campaign/type/icon/ornament/heart/background.png', '', '18,19', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (22, 'ornament_oval', 'Accessories', 'Ornament', 'Ornament Oval', '', '{\"personalized\":\"Personalized Ornament\",\"photo\":\"Photo Ornament\",\"image\":\"Ornament\"}', '', '', '18,19', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (23, 'ornament_star', 'Accessories', 'Ornament', 'Ornament Star', '', '{\"personalized\":\"Personalized Ornament\",\"photo\":\"Photo Ornament\",\"image\":\"Ornament\"}', '', '', '18,19', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (24, 'mouse_pad', 'Accessories', 'Mouse Pad', 'Mouse Pad', '', '{\"personalized\":\"Personalized Mouse Pad\",\"photo\":\"Photo Mouse Pad\",\"image\":\"Mouse Pad\"}', '', '', '20', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (25, 'coaster_set_4', 'Accessories', 'Coaster', 'Coaster set 4', '', '{\"personalized\":\"Personalized Coaster\",\"photo\":\"Photo Coaster\",\"image\":\"Coaster\"}', '', '', '21', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (26, 'place_mat', 'Accessories', 'Place Mat', 'Place Mat', '', '{\"personalized\":\"Personalized Place Mat\",\"photo\":\"Photo Place Mat\",\"image\":\"Place Mat\"}', '', '', '22', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (27, 'puzzle', 'Accessories', 'Puzzle', 'Puzzle', '', '{\"personalized\":\"Personalized Puzzle\",\"photo\":\"Photo Puzzle\",\"image\":\"Puzzle\"}', 'catalog/campaign/type/icon/puzzles/10x14.png', '', '23,24', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (28, 'desktop_plaque', 'Accessories', 'Desktop Plaque', 'Desktop Plaque', '', '{\"personalized\":\"Personalized Desktop\",\"photo\":\"Photo Desktop\",\"image\":\"Desktop\"}', 'catalog/campaign/type/icon/desktopPlaque/7x5.png', '', '25', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (29, 'wiro_notebook', 'Accessories', 'Notebook', 'Wiro Notebook', '', '{\"personalized\":\"Personalized Notebook\",\"photo\":\"Photo Notebook\",\"image\":\"Notebook\"}', 'catalog/campaign/type/icon/notebook/5x7.png', '', '26,27', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (30, 'facemask_with_filter', 'Accessories', 'Facemask', 'Facemask with filter', '', '{\"personalized\":\"Personalized Facemask\",\"photo\":\"Photo Facemask\",\"image\":\"Facemask\"}', 'catalog/campaign/type/icon/facemask.png', '', '28,29', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (31, 'facemask_without_filter', 'Accessories', 'Facemask', 'Facemask without filter', '', '{\"personalized\":\"Personalized Facemask\",\"photo\":\"Photo Facemask\",\"image\":\"Facemask\"}', 'catalog/campaign/type/icon/facemask-white.png', '', '30,31', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (32, 'phonecase', 'Accessories', 'Phonecase', 'Phonecase', '', '{\"personalized\":\"Personalized Phone Case\",\"photo\":\"Photo Phone Case\",\"image\":\"Phone Case\"}', '', '', '32,33', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (33, 'stock', 'Accessories', 'Stock', 'Stock', '', '{\"personalized\":\"Personalized Stock\",\"photo\":\"Photo Stock\",\"image\":\"Stock\"}', '', '', '34', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (34, 'fullPrints_ceramic_mug', 'Drinkware', 'Mug', 'FullPrints Ceramic Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Photo Mug\",\"image\":\"Phone Mug\"}', 'catalog/campaign/type/icon/mug/11oz.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (35, 'fullPrints_two_tone_mug', 'Drinkware', 'Mug', 'FullPrints Two Tone Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Photo Mug\",\"image\":\"Phone Mug\"}', 'catalog/campaign/type/icon/mug/twoTone.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (36, 'fullPrints_enamel_campfire_mug', 'Drinkware', 'Mug', 'FullPrints Enamel Campfire Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Photo Mug\",\"image\":\"Phone Mug\"}', 'catalog/campaign/type/icon/mug/enamelCampfire.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (37, 'fullPrints_insulated_coffee_mug', 'Drinkware', 'Mug', 'FullPrints Insulated Coffee Mug', '', '{\"personalized\":\"Personalized Mug\",\"photo\":\"Photo Mug\",\"image\":\"Phone Mug\"}', 'catalog/campaign/type/icon/mug/insulatedCoffee.jpg', '', '7,8', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (38, 'yard_sign', 'Accessories', 'Yard Sign', 'Yard Sign', '', '{\"personalized\":\"Personalized Yard Sign\",\"photo\":\"Photo Yard Sign\",\"image\":\"Yard Sign\"}', '', '', '35,36', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type` VALUES (39, 'garden_flag', 'Accessories', 'Garden Flag', 'Garden Flag', '', '{\"personalized\":\"Personalized Garden Flag\",\"photo\":\"Photo Garden Flag\",\"image\":\"Garden Flag\"}', '', '', '37,38', 1, 1603019609, 1603019609);
COMMIT;

-- ----------------------------
-- Table structure for osc_product_type_option
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_option`;
CREATE TABLE `osc_product_type_option` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL COMMENT '1: checkbox, 2: selector...',
  `ukey` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_product_type_option
-- ----------------------------
BEGIN;
INSERT INTO `osc_product_type_option` VALUES (1, 'Size', 'select', 'canvas_size', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option` VALUES (2, 'Size', 'select', 'poster_size', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option` VALUES (3, 'Color', 'color', 'gildan_g500_classic_tee_color', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option` VALUES (4, 'Size', 'select', 'shirt_size', '', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option` VALUES (5, 'Color', 'color', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color', '', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option` VALUES (6, 'Color', 'color', 'next_level_nl3600_premium_short_sleeve_color', '', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option` VALUES (7, 'Color', 'color', 'mug_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (8, 'Size', 'button', 'mug_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (9, 'Color', 'color', 'fleece_blanket_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (10, 'Size', 'button', 'blanket_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (11, 'Color', 'color', 'sherpa_flannel_blanket_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (12, 'Color', 'color', 'pillow_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (13, 'Size', 'button', 'pillow_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (14, 'Color', 'color', 'tea_towel_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (15, 'Size', 'button', 'towel_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (16, 'Color', 'color', 'beach_towel_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (17, 'Color', 'color', 'kid_towel_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (18, 'Material', 'button', 'ornament_material', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (19, 'Size', 'button', 'ornament_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (20, 'Size', 'button', 'mouse_pad_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (21, 'Size', 'button', 'coaster_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (22, 'Size', 'button', 'place_mat_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (23, 'Color', 'color', 'puzzle_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (24, 'Size', 'button', 'puzzle_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (25, 'Size', 'button', 'desktop_plaque_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (26, 'Color', 'color', 'wiro_notebook_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (27, 'Size', 'button', 'wiro_notebook_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (28, 'Color', 'color', 'facemask_with_filter_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (29, 'Size', 'button', 'facemask_with_filter_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (30, 'Color', 'color', 'facemask_without_filter_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (31, 'Size', 'button', 'facemask_without_filter_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (32, 'Color', 'color', 'phonecase_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (33, 'Size', 'button', 'phonecase_shape', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (34, 'Color', 'color', 'stock_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (35, 'Color', 'color', 'yard_sign_color', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (36, 'Size', 'button', 'yard_sign_size', '', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option` VALUES (37, 'Color', 'color', 'garden_flag_color', '', 1, 1603019609, 1603019609);
INSERT INTO `osc_product_type_option` VALUES (38, 'Size', 'button', 'garden_flag_size', '', 1, 1603019609, 1603019609);
COMMIT;

-- ----------------------------
-- Table structure for osc_product_type_option_value
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_option_value`;
CREATE TABLE `osc_product_type_option_value` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type_option_id` int(11) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `ukey` varchar(255) DEFAULT NULL,
  `meta_data` varchar(1000) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_key` (`ukey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=151 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_product_type_option_value
-- ----------------------------
BEGIN;
INSERT INTO `osc_product_type_option_value` VALUES (1, 1, '8\"x10\"', 'canvas_size/8x10', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (2, 1, '8\"x12\"', 'canvas_size/8x12', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (3, 1, '11\"x14\"', 'canvas_size/11x14', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (4, 1, '12\"x18\"', 'canvas_size/12x18', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (5, 1, '12\"x24\"', 'canvas_size/12x24', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (6, 1, '16\"x20\"', 'canvas_size/16x20', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (7, 1, '20\"x24\"', 'canvas_size/20x24', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (8, 1, '20\"x30\"', 'canvas_size/20x30', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (9, 1, '10\"x8\"', 'canvas_size/10x8', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (10, 1, '12\"x8\"', 'canvas_size/12x8', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (11, 1, '14\"x11\"', 'canvas_size/14x11', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (12, 1, '18\"x12\"', 'canvas_size/18x12', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (13, 1, '20\"x16\"', 'canvas_size/20x16', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (14, 1, '24\"x20\"', 'canvas_size/24x20', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (15, 1, '24\"x12\"', 'canvas_size/24x12', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (16, 1, '30\"x20\"', 'canvas_size/30x20', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (17, 1, '12\"x12\"', 'canvas_size/12x12', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (18, 1, '16\"x16\"', 'canvas_size/16x16', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (19, 1, '24\"x24\"', 'canvas_size/24x24', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (20, 2, '10\"x15\"', 'poster_size/10x15', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (21, 2, '12\"x18\"', 'poster_size/12x18', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (22, 2, '16\"x20\"', 'poster_size/16x20', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (23, 2, '20\"x30\"', 'poster_size/20x30', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (24, 2, '24\"x36\"', 'poster_size/24x36', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (25, 2, '30\"x40\"', 'poster_size/30x40', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (26, 2, '20\"x24\"', 'poster_size/20x24', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (27, 2, '12\"x12\"', 'poster_size/12x12', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (28, 2, '11\"x14\"', 'poster_size/11x14', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (29, 2, '12\"x16\"', 'poster_size/12x16', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (30, 2, '15\"x10\"', 'poster_size/15x10', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (31, 2, '14\"x11\"', 'poster_size/14x11', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (32, 2, '16\"x12\"', 'poster_size/16x12', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (33, 2, '20\"x16\"', 'poster_size/20x16', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (34, 2, '24\"x20\"', 'poster_size/24x20', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (35, 2, '30\"x20\"', 'poster_size/30x20', '[]', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (36, 3, 'Black', 'gildan_g500_classic_tee_color/black', '{\"hex\":\"#000\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (37, 3, 'White', 'gildan_g500_classic_tee_color/white', '{\"hex\":\"#EEE\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (38, 3, 'Sport Grey', 'gildan_g500_classic_tee_color/sport_grey', '{\"hex\":\"#bcbcc6\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (39, 3, 'Navy', 'gildan_g500_classic_tee_color/navy', '{\"hex\":\"#2f3b4e\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (40, 3, 'Light Blue', 'gildan_g500_classic_tee_color/light_blue', '{\"hex\":\"#9ab5d2\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (41, 3, 'Cardinal', 'gildan_g500_classic_tee_color/cardinal', '{\"hex\":\"#9c263a\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (42, 3, 'Dark Chocolate', 'gildan_g500_classic_tee_color/dark_chocolate', '{\"hex\":\"#4c3025\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (43, 3, 'Gold', 'gildan_g500_classic_tee_color/gold', '{\"hex\":\"#fea821\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (44, 3, 'Irish Green', 'gildan_g500_classic_tee_color/irish_green', '{\"hex\":\"#259b5e\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (45, 3, 'Light Pink', 'gildan_g500_classic_tee_color/light_pink', '{\"hex\":\"#e2b5c9\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (46, 3, 'Orange', 'gildan_g500_classic_tee_color/orange', '{\"hex\":\"#ff3300\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (47, 3, 'Purple', 'gildan_g500_classic_tee_color/purple', '{\"hex\":\"#5c4881\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (48, 3, 'Red', 'gildan_g500_classic_tee_color/red', '{\"hex\":\"#cd0102\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (49, 3, 'Royal', 'gildan_g500_classic_tee_color/royal', '{\"hex\":\"#1d4e9a\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (50, 3, 'Kiwi', 'gildan_g500_classic_tee_color/kiwi', '{\"hex\":\"#8cac69\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (51, 3, 'Military Green', 'gildan_g500_classic_tee_color/military_green', '{\"hex\":\"#4b4d34\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (52, 3, 'Ash', 'gildan_g500_classic_tee_color/ash', '{\"hex\":\"#e6e7ec\"}', 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_option_value` VALUES (53, 4, 'XS', 'shirt_size/xs', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (54, 4, 'S', 'shirt_size/s', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (55, 4, 'M', 'shirt_size/m', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (56, 4, 'L', 'shirt_size/l', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (57, 4, 'XL', 'shirt_size/xl', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (58, 4, '2XL', 'shirt_size/2xl', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (59, 4, '3XL', 'shirt_size/3xl', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (60, 4, '4XL', 'shirt_size/4xl', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (61, 4, '5XL', 'shirt_size/5xl', '[]', 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_option_value` VALUES (62, 5, 'Black', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/black', '{\"hex\":\"#000000\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (63, 5, 'Dark Grey Heather', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/dark_grey_heather', '{\"hex\":\"#302e2f\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (64, 5, 'Light Blue', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/light_blue', '{\"hex\":\"#9cb4cc\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (65, 5, 'Navy', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/navy', '{\"hex\":\"#292838\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (66, 5, 'White', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/white', '{\"hex\":\"#EEE\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (67, 5, 'Canvas Red', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/canvas_red', '{\"hex\":\"#9f1d2a\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (68, 5, 'Cardinal', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/cardinal', '{\"hex\":\"#6c2333\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (69, 5, 'Gold', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/gold', '{\"hex\":\"#ffa624\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (70, 5, 'Orange', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/orange', '{\"hex\":\"#fa8b31\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (71, 5, 'Soft Pink', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/soft_pink', '{\"hex\":\"#f5e4ec\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (72, 5, 'Asphalt', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/asphalt', '{\"hex\":\"#43474a\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (73, 5, 'Heather Royal', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/heather_royal', '{\"hex\":\"#364c81\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (74, 5, 'Kelly', 'bella_canvas_3001c_unisex_jersey_short_sleeve_color/kelly', '{\"hex\":\"#036650\"}', 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_option_value` VALUES (75, 6, 'Black', 'next_level_nl3600_premium_short_sleeve_color/black', '{\"hex\":\"#000\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (76, 6, 'White', 'next_level_nl3600_premium_short_sleeve_color/white', '{\"hex\":\"#EEE\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (77, 6, 'Heather Grey', 'next_level_nl3600_premium_short_sleeve_color/heather_grey', '{\"hex\":\"#b3b2ba\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (78, 6, 'Midnight Navy', 'next_level_nl3600_premium_short_sleeve_color/midnight_navy', '{\"hex\":\"#212632\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (79, 6, 'Military Green', 'next_level_nl3600_premium_short_sleeve_color/military_green', '{\"hex\":\"#534e3f\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (80, 6, 'Cardinal', 'next_level_nl3600_premium_short_sleeve_color/cardinal', '{\"hex\":\"#651728\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (81, 6, 'Kelly Green', 'next_level_nl3600_premium_short_sleeve_color/kelly_green', '{\"hex\":\"#0b7140\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (82, 6, 'Light Blue', 'next_level_nl3600_premium_short_sleeve_color/light_blue', '{\"hex\":\"#aac0cd\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (83, 6, 'Red', 'next_level_nl3600_premium_short_sleeve_color/red', '{\"hex\":\"#bb0321\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (84, 6, 'royal', 'next_level_nl3600_premium_short_sleeve_color/royal', '{\"hex\":\"#1c3c6f\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (85, 6, 'Tahiti Blue', 'next_level_nl3600_premium_short_sleeve_color/tahiti_blue', '{\"hex\":\"#2a8d95\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (86, 6, 'Banana Cream', 'next_level_nl3600_premium_short_sleeve_color/banana_cream', '{\"hex\":\"#efdeab\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (87, 6, 'Maroon', 'next_level_nl3600_premium_short_sleeve_color/maroon', '{\"hex\":\"#4e1323\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (88, 6, 'Purple Rush', 'next_level_nl3600_premium_short_sleeve_color/purple_rush', '{\"hex\":\"#47346c\"}', 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_option_value` VALUES (89, 7, 'White', 'mug_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (90, 7, 'Black', 'mug_color/black', '{\"hex\":\"#000\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (91, 7, 'Blue', 'mug_color/blue', '{\"hex\":\"#4198CD\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (92, 7, 'Red', 'mug_color/red', '{\"hex\":\"#D30000\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (93, 7, 'Navy', 'mug_color/navy', '{\"hex\":\"#000080\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (94, 7, 'Pink', 'mug_color/pink', '{\"hex\":\"#E2AAAD\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (95, 8, '10 oz', 'mug_size/10oz', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (96, 8, '11 oz', 'mug_size/11oz', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (97, 8, '12 oz', 'mug_size/12oz', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (98, 8, '15 oz', 'mug_size/15oz', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (99, 9, 'White', 'fleece_blanket_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (100, 10, '30x40', 'blanket_size/30x40', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (101, 10, '50x60', 'blanket_size/50x60', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (102, 10, '60x80', 'blanket_size/60x80', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (103, 11, 'White', 'sherpa_flannel_blanket_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (104, 12, 'White', 'pillow_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (105, 13, '16x16', 'pillow_size/16x16', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (106, 13, '18x18', 'pillow_size/18x18', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (107, 14, 'White', 'tea_towel_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (108, 15, '16x25', 'towel_size/16x25', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (109, 16, 'White', 'beach_towel_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (110, 15, '35x60', 'towel_size/35x60', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (111, 17, 'White', 'kid_towel_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (112, 15, '22x42', 'towel_size/22x42', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (113, 18, 'Aluminium', 'ornament_material/aluminium', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (114, 18, 'MDF/Plastic', 'ornament_material/mdf_plastic', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (115, 18, 'Ceramic', 'ornament_material/ceramic', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (116, 19, '3.2x3.2', 'ornament_size/3.2x3.2', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (117, 19, '2.75x4', 'ornament_size/2.75x4', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (118, 19, '4x2.75', 'ornament_size/4x2.75', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (119, 19, '3 inches tall', 'ornament_size/3_inches_tall', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (120, 19, '3.25 inches tall', 'ornament_size/3.25_inches_tall', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (121, 20, '8x9', 'mouse_pad_size/8x9', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (122, 21, '4x4', 'coaster_size/4x4', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (123, 22, '8x9.5', 'place_mat_size/8x9.5', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (124, 23, 'White', 'puzzle_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (125, 24, '10x14', 'puzzle_size/10x14', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (126, 24, '14x10', 'puzzle_size/14x10', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (127, 24, '16x20', 'puzzle_size/16x20', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (128, 24, '20x16', 'puzzle_size/20x16', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (129, 25, '5x7', 'desktop_plaque_size/5x7', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (130, 25, '8x10', 'desktop_plaque_size/8x10', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (131, 25, '7x5', 'desktop_plaque_size/7x5', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (132, 25, '10x8', 'desktop_plaque_size/10x8', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (133, 26, 'White', 'wiro_notebook_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (134, 27, '5x7', 'wiro_notebook_size/5x7', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (135, 27, '5.8x8.27', 'wiro_notebook_size/5.8x8.27', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (136, 27, '8.27x11.69', 'wiro_notebook_size/8.27x11.69', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (137, 28, 'White', 'facemask_with_filter_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (138, 29, '7.25x5.1', 'facemask_with_filter_size/7.25x5.1', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (139, 29, '6.39x3.81', 'facemask_with_filter_size/6.39x3.81', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (140, 30, 'White', 'facemask_without_filter_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (141, 31, '7.28x4.53', 'facemask_without_filter_size/7.28x4.53', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (142, 32, 'White', 'phonecase_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (143, 33, 'iPhone 11', 'phonecase_shape/iphone_11', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (144, 33, 'iPhone 11 Pro', 'phonecase_shape/iphone_11pro', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (145, 34, 'White', 'stock_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (146, 35, 'White', 'yard_sign_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (147, 36, '22x15', 'yard_sign_size/22x15', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (148, 36, '24x18', 'yard_sign_size/24x18', '[]', 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_option_value` VALUES (149, 37, 'White', 'garden_flag_color/white', '{\"hex\":\"#FFF\"}', 1, 1603019609, 1603019609);
INSERT INTO `osc_product_type_option_value` VALUES (150, 38, '12.5x8', 'garden_flag_size/12.5x8', '[]', 1, 1603019609, 1603019609);
COMMIT;

-- ----------------------------
-- Table structure for osc_product_type_variant
-- ----------------------------
DROP TABLE IF EXISTS `osc_product_type_variant`;
CREATE TABLE `osc_product_type_variant` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type_id` int(11) DEFAULT NULL,
  `title` varchar(500) DEFAULT NULL,
  `ukey` varchar(500) DEFAULT NULL,
  `price` int(11) DEFAULT 0,
  `compare_at_price` int(11) DEFAULT 0,
  `status` tinyint(4) DEFAULT 1,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `unique_variant` (`product_type_id`,`ukey`) USING BTREE,
  KEY `product_type_variant_id` (`product_type_id`,`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=444 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_product_type_variant
-- ----------------------------
BEGIN;
INSERT INTO `osc_product_type_variant` VALUES (1, 1, 'Wrapped Canvas 8\"x10\"', '1/1:1', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (2, 1, 'Wrapped Canvas 8\"x12\"', '1/1:2', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (3, 1, 'Wrapped Canvas 11\"x14\"', '1/1:3', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (4, 1, 'Wrapped Canvas 12\"x18\"', '1/1:4', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (5, 1, 'Wrapped Canvas 12\"x24\"', '1/1:5', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (6, 1, 'Wrapped Canvas 16\"x20\"', '1/1:6', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (7, 1, 'Wrapped Canvas 20\"x24\"', '1/1:7', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (8, 1, 'Wrapped Canvas 20\"x30\"', '1/1:8', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (9, 1, 'Wrapped Canvas 10\"x8\"', '1/1:9', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (10, 1, 'Wrapped Canvas 12\"x8\"', '1/1:10', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (11, 1, 'Wrapped Canvas 14\"x11\"', '1/1:11', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (12, 1, 'Wrapped Canvas 18\"x12\"', '1/1:12', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (13, 1, 'Wrapped Canvas 20\"x16\"', '1/1:13', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (14, 1, 'Wrapped Canvas 24\"x20\"', '1/1:14', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (15, 1, 'Wrapped Canvas 24\"x12\"', '1/1:15', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (16, 1, 'Wrapped Canvas 30\"x20\"', '1/1:16', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (17, 1, 'Wrapped Canvas 12\"x12\"', '1/1:17', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (18, 1, 'Wrapped Canvas 16\"x16\"', '1/1:18', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (19, 1, 'Wrapped Canvas 24\"x24\"', '1/1:19', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (20, 2, 'Matte Poster 10\"x15\"', '2/2:20', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (21, 2, 'Matte Poster 12\"x18\"', '2/2:21', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (22, 2, 'Matte Poster 16\"x20\"', '2/2:22', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (23, 2, 'Matte Poster 20\"x30\"', '2/2:23', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (24, 2, 'Matte Poster 24\"x36\"', '2/2:24', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (25, 2, 'Matte Poster 30\"x40\"', '2/2:25', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (26, 2, 'Matte Poster 20\"x24\"', '2/2:26', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (27, 2, 'Matte Poster 12\"x12\"', '2/2:27', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (28, 2, 'Matte Poster 11\"x14\"', '2/2:28', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (29, 3, 'Glossy Poster 10\"x15\"', '3/2:20', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (30, 3, 'Glossy Poster 11\"x14\"', '3/2:28', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (31, 3, 'Glossy Poster 12\"x16\"', '3/2:29', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (32, 3, 'Glossy Poster 16\"x20\"', '3/2:22', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (33, 3, 'Glossy Poster 20\"x24\"', '3/2:26', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (34, 3, 'Glossy Poster 20\"x30\"', '3/2:23', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (35, 3, 'Glossy Poster 15\"x10\"', '3/2:30', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (36, 3, 'Glossy Poster 14\"x11\"', '3/2:31', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (37, 3, 'Glossy Poster 16\"x12\"', '3/2:32', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (38, 3, 'Glossy Poster 20\"x16\"', '3/2:33', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (39, 3, 'Glossy Poster 24\"x20\"', '3/2:34', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (40, 3, 'Glossy Poster 30\"x20\"', '3/2:35', 0, 0, 1, 1603019603, 1603019603);
INSERT INTO `osc_product_type_variant` VALUES (41, 4, 'Gildan G500 Classic Tee Black XS', '4/3:36_4:53', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (42, 4, 'Gildan G500 Classic Tee Black S', '4/3:36_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (43, 4, 'Gildan G500 Classic Tee Black M', '4/3:36_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (44, 4, 'Gildan G500 Classic Tee Black L', '4/3:36_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (45, 4, 'Gildan G500 Classic Tee Black XL', '4/3:36_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (46, 4, 'Gildan G500 Classic Tee Black 2XL', '4/3:36_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (47, 4, 'Gildan G500 Classic Tee Black 3XL', '4/3:36_4:59', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (48, 4, 'Gildan G500 Classic Tee Black 4XL', '4/3:36_4:60', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (49, 4, 'Gildan G500 Classic Tee Black 5XL', '4/3:36_4:61', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (50, 4, 'Gildan G500 Classic Tee White XS', '4/3:37_4:53', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (51, 4, 'Gildan G500 Classic Tee White S', '4/3:37_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (52, 4, 'Gildan G500 Classic Tee White M', '4/3:37_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (53, 4, 'Gildan G500 Classic Tee White L', '4/3:37_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (54, 4, 'Gildan G500 Classic Tee White XL', '4/3:37_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (55, 4, 'Gildan G500 Classic Tee White 2XL', '4/3:37_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (56, 4, 'Gildan G500 Classic Tee White 3XL', '4/3:37_4:59', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (57, 4, 'Gildan G500 Classic Tee White 4XL', '4/3:37_4:60', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (58, 4, 'Gildan G500 Classic Tee White 5XL', '4/3:37_4:61', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (59, 4, 'Gildan G500 Classic Tee Sport Grey S', '4/3:38_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (60, 4, 'Gildan G500 Classic Tee Sport Grey M', '4/3:38_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (61, 4, 'Gildan G500 Classic Tee Sport Grey L', '4/3:38_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (62, 4, 'Gildan G500 Classic Tee Sport Grey XL', '4/3:38_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (63, 4, 'Gildan G500 Classic Tee Sport Grey 2XL', '4/3:38_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (64, 4, 'Gildan G500 Classic Tee Sport Grey 3XL', '4/3:38_4:59', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (65, 4, 'Gildan G500 Classic Tee Sport Grey 4XL', '4/3:38_4:60', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (66, 4, 'Gildan G500 Classic Tee Sport Grey 5XL', '4/3:38_4:61', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (67, 4, 'Gildan G500 Classic Tee Navy S', '4/3:39_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (68, 4, 'Gildan G500 Classic Tee Navy M', '4/3:39_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (69, 4, 'Gildan G500 Classic Tee Navy L', '4/3:39_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (70, 4, 'Gildan G500 Classic Tee Navy XL', '4/3:39_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (71, 4, 'Gildan G500 Classic Tee Navy 2XL', '4/3:39_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (72, 4, 'Gildan G500 Classic Tee Navy 3XL', '4/3:39_4:59', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (73, 4, 'Gildan G500 Classic Tee Navy 4XL', '4/3:39_4:60', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (74, 4, 'Gildan G500 Classic Tee Navy 5XL', '4/3:39_4:61', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (75, 4, 'Gildan G500 Classic Tee Light Blue S', '4/3:40_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (76, 4, 'Gildan G500 Classic Tee Light Blue M', '4/3:40_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (77, 4, 'Gildan G500 Classic Tee Light Blue L', '4/3:40_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (78, 4, 'Gildan G500 Classic Tee Light Blue XL', '4/3:40_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (79, 4, 'Gildan G500 Classic Tee Light Blue 2XL', '4/3:40_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (80, 4, 'Gildan G500 Classic Tee Light Blue 3XL', '4/3:40_4:59', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (81, 4, 'Gildan G500 Classic Tee Light Blue 4XL', '4/3:40_4:60', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (82, 4, 'Gildan G500 Classic Tee Light Blue 5XL', '4/3:40_4:61', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (83, 4, 'Gildan G500 Classic Tee Cardinal S', '4/3:41_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (84, 4, 'Gildan G500 Classic Tee Cardinal M', '4/3:41_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (85, 4, 'Gildan G500 Classic Tee Cardinal L', '4/3:41_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (86, 4, 'Gildan G500 Classic Tee Cardinal XL', '4/3:41_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (87, 4, 'Gildan G500 Classic Tee Cardinal 2XL', '4/3:41_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (88, 4, 'Gildan G500 Classic Tee Cardinal 3XL', '4/3:41_4:59', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (89, 4, 'Gildan G500 Classic Tee Cardinal 4XL', '4/3:41_4:60', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (90, 4, 'Gildan G500 Classic Tee Cardinal 5XL', '4/3:41_4:61', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (91, 4, 'Gildan G500 Classic Tee Dark Chocolate S', '4/3:42_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (92, 4, 'Gildan G500 Classic Tee Dark Chocolate M', '4/3:42_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (93, 4, 'Gildan G500 Classic Tee Dark Chocolate L', '4/3:42_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (94, 4, 'Gildan G500 Classic Tee Dark Chocolate XL', '4/3:42_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (95, 4, 'Gildan G500 Classic Tee Dark Chocolate 2XL', '4/3:42_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (96, 4, 'Gildan G500 Classic Tee Dark Chocolate 3XL', '4/3:42_4:59', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (97, 4, 'Gildan G500 Classic Tee Dark Chocolate 4XL', '4/3:42_4:60', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (98, 4, 'Gildan G500 Classic Tee Dark Chocolate 5XL', '4/3:42_4:61', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (99, 4, 'Gildan G500 Classic Tee Gold S', '4/3:43_4:54', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (100, 4, 'Gildan G500 Classic Tee Gold M', '4/3:43_4:55', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (101, 4, 'Gildan G500 Classic Tee Gold L', '4/3:43_4:56', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (102, 4, 'Gildan G500 Classic Tee Gold XL', '4/3:43_4:57', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (103, 4, 'Gildan G500 Classic Tee Gold 2XL', '4/3:43_4:58', 0, 0, 1, 1603019604, 1603019604);
INSERT INTO `osc_product_type_variant` VALUES (104, 4, 'Gildan G500 Classic Tee Gold 3XL', '4/3:43_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (105, 4, 'Gildan G500 Classic Tee Gold 4XL', '4/3:43_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (106, 4, 'Gildan G500 Classic Tee Gold 5XL', '4/3:43_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (107, 4, 'Gildan G500 Classic Tee Irish Green S', '4/3:44_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (108, 4, 'Gildan G500 Classic Tee Irish Green M', '4/3:44_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (109, 4, 'Gildan G500 Classic Tee Irish Green L', '4/3:44_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (110, 4, 'Gildan G500 Classic Tee Irish Green XL', '4/3:44_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (111, 4, 'Gildan G500 Classic Tee Irish Green 2XL', '4/3:44_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (112, 4, 'Gildan G500 Classic Tee Irish Green 3XL', '4/3:44_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (113, 4, 'Gildan G500 Classic Tee Irish Green 4XL', '4/3:44_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (114, 4, 'Gildan G500 Classic Tee Irish Green 5XL', '4/3:44_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (115, 4, 'Gildan G500 Classic Tee Light Pink S', '4/3:45_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (116, 4, 'Gildan G500 Classic Tee Light Pink M', '4/3:45_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (117, 4, 'Gildan G500 Classic Tee Light Pink L', '4/3:45_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (118, 4, 'Gildan G500 Classic Tee Light Pink XL', '4/3:45_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (119, 4, 'Gildan G500 Classic Tee Light Pink 2XL', '4/3:45_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (120, 4, 'Gildan G500 Classic Tee Light Pink 3XL', '4/3:45_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (121, 4, 'Gildan G500 Classic Tee Light Pink 4XL', '4/3:45_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (122, 4, 'Gildan G500 Classic Tee Light Pink 5XL', '4/3:45_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (123, 4, 'Gildan G500 Classic Tee Orange S', '4/3:46_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (124, 4, 'Gildan G500 Classic Tee Orange M', '4/3:46_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (125, 4, 'Gildan G500 Classic Tee Orange L', '4/3:46_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (126, 4, 'Gildan G500 Classic Tee Orange XL', '4/3:46_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (127, 4, 'Gildan G500 Classic Tee Orange 2XL', '4/3:46_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (128, 4, 'Gildan G500 Classic Tee Orange 3XL', '4/3:46_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (129, 4, 'Gildan G500 Classic Tee Orange 4XL', '4/3:46_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (130, 4, 'Gildan G500 Classic Tee Orange 5XL', '4/3:46_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (131, 4, 'Gildan G500 Classic Tee Purple S', '4/3:47_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (132, 4, 'Gildan G500 Classic Tee Purple M', '4/3:47_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (133, 4, 'Gildan G500 Classic Tee Purple L', '4/3:47_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (134, 4, 'Gildan G500 Classic Tee Purple XL', '4/3:47_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (135, 4, 'Gildan G500 Classic Tee Purple 2XL', '4/3:47_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (136, 4, 'Gildan G500 Classic Tee Purple 3XL', '4/3:47_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (137, 4, 'Gildan G500 Classic Tee Purple 4XL', '4/3:47_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (138, 4, 'Gildan G500 Classic Tee Purple 5XL', '4/3:47_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (139, 4, 'Gildan G500 Classic Tee Red S', '4/3:48_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (140, 4, 'Gildan G500 Classic Tee Red M', '4/3:48_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (141, 4, 'Gildan G500 Classic Tee Red L', '4/3:48_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (142, 4, 'Gildan G500 Classic Tee Red XL', '4/3:48_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (143, 4, 'Gildan G500 Classic Tee Red 2XL', '4/3:48_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (144, 4, 'Gildan G500 Classic Tee Red 3XL', '4/3:48_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (145, 4, 'Gildan G500 Classic Tee Red 4XL', '4/3:48_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (146, 4, 'Gildan G500 Classic Tee Red 5XL', '4/3:48_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (147, 4, 'Gildan G500 Classic Tee Royal S', '4/3:49_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (148, 4, 'Gildan G500 Classic Tee Royal M', '4/3:49_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (149, 4, 'Gildan G500 Classic Tee Royal L', '4/3:49_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (150, 4, 'Gildan G500 Classic Tee Royal XL', '4/3:49_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (151, 4, 'Gildan G500 Classic Tee Royal 2XL', '4/3:49_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (152, 4, 'Gildan G500 Classic Tee Royal 3XL', '4/3:49_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (153, 4, 'Gildan G500 Classic Tee Royal 4XL', '4/3:49_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (154, 4, 'Gildan G500 Classic Tee Royal 5XL', '4/3:49_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (155, 4, 'Gildan G500 Classic Tee Kiwi S', '4/3:50_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (156, 4, 'Gildan G500 Classic Tee Kiwi M', '4/3:50_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (157, 4, 'Gildan G500 Classic Tee Kiwi L', '4/3:50_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (158, 4, 'Gildan G500 Classic Tee Kiwi XL', '4/3:50_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (159, 4, 'Gildan G500 Classic Tee Kiwi 2XL', '4/3:50_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (160, 4, 'Gildan G500 Classic Tee Kiwi 3XL', '4/3:50_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (161, 4, 'Gildan G500 Classic Tee Kiwi 4XL', '4/3:50_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (162, 4, 'Gildan G500 Classic Tee Kiwi 5XL', '4/3:50_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (163, 4, 'Gildan G500 Classic Tee Military Green S', '4/3:51_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (164, 4, 'Gildan G500 Classic Tee Military Green M', '4/3:51_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (165, 4, 'Gildan G500 Classic Tee Military Green L', '4/3:51_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (166, 4, 'Gildan G500 Classic Tee Military Green XL', '4/3:51_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (167, 4, 'Gildan G500 Classic Tee Military Green 2XL', '4/3:51_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (168, 4, 'Gildan G500 Classic Tee Military Green 3XL', '4/3:51_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (169, 4, 'Gildan G500 Classic Tee Military Green 4XL', '4/3:51_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (170, 4, 'Gildan G500 Classic Tee Military Green 5XL', '4/3:51_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (171, 4, 'Gildan G500 Classic Tee Ash S', '4/3:52_4:54', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (172, 4, 'Gildan G500 Classic Tee Ash M', '4/3:52_4:55', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (173, 4, 'Gildan G500 Classic Tee Ash L', '4/3:52_4:56', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (174, 4, 'Gildan G500 Classic Tee Ash XL', '4/3:52_4:57', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (175, 4, 'Gildan G500 Classic Tee Ash 2XL', '4/3:52_4:58', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (176, 4, 'Gildan G500 Classic Tee Ash 3XL', '4/3:52_4:59', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (177, 4, 'Gildan G500 Classic Tee Ash 4XL', '4/3:52_4:60', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (178, 4, 'Gildan G500 Classic Tee Ash 5XL', '4/3:52_4:61', 0, 0, 1, 1603019605, 1603019605);
INSERT INTO `osc_product_type_variant` VALUES (179, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black XS', '5/4:53_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (180, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black S', '5/4:54_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (181, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black M', '5/4:55_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (182, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black L', '5/4:56_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (183, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black XL', '5/4:57_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (184, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black 2XL', '5/4:58_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (185, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black 3XL', '5/4:59_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (186, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Black 4XL', '5/4:60_5:62', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (187, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather XS', '5/4:53_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (188, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather S', '5/4:54_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (189, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather M', '5/4:55_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (190, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather L', '5/4:56_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (191, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather XL', '5/4:57_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (192, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather 2XL', '5/4:58_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (193, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather 3XL', '5/4:59_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (194, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Dark Grey Heather 4XL', '5/4:60_5:63', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (195, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue XS', '5/4:53_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (196, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue S', '5/4:54_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (197, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue M', '5/4:55_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (198, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue L', '5/4:56_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (199, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue XL', '5/4:57_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (200, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue 2XL', '5/4:58_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (201, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue 3XL', '5/4:59_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (202, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Light Blue 4XL', '5/4:60_5:64', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (203, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy XS', '5/4:53_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (204, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy S', '5/4:54_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (205, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy M', '5/4:55_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (206, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy L', '5/4:56_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (207, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy XL', '5/4:57_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (208, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy 2XL', '5/4:58_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (209, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy 3XL', '5/4:59_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (210, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Navy 4XL', '5/4:60_5:65', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (211, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White XS', '5/4:53_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (212, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White S', '5/4:54_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (213, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White M', '5/4:55_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (214, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White L', '5/4:56_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (215, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White XL', '5/4:57_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (216, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White 2XL', '5/4:58_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (217, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White 3XL', '5/4:59_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (218, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve White 4XL', '5/4:60_5:66', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (219, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red XS', '5/4:53_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (220, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red S', '5/4:54_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (221, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red M', '5/4:55_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (222, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red L', '5/4:56_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (223, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red XL', '5/4:57_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (224, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red 2XL', '5/4:58_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (225, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red 3XL', '5/4:59_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (226, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Canvas Red 4XL', '5/4:60_5:67', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (227, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal XS', '5/4:53_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (228, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal S', '5/4:54_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (229, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal M', '5/4:55_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (230, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal L', '5/4:56_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (231, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal XL', '5/4:57_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (232, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal 2XL', '5/4:58_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (233, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal 3XL', '5/4:59_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (234, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Cardinal 4XL', '5/4:60_5:68', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (235, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold XS', '5/4:53_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (236, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold S', '5/4:54_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (237, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold M', '5/4:55_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (238, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold L', '5/4:56_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (239, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold XL', '5/4:57_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (240, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold 2XL', '5/4:58_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (241, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold 3XL', '5/4:59_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (242, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Gold 4XL', '5/4:60_5:69', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (243, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange XS', '5/4:53_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (244, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange S', '5/4:54_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (245, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange M', '5/4:55_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (246, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange L', '5/4:56_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (247, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange XL', '5/4:57_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (248, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange 2XL', '5/4:58_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (249, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange 3XL', '5/4:59_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (250, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Orange 4XL', '5/4:60_5:70', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (251, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink XS', '5/4:53_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (252, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink S', '5/4:54_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (253, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink M', '5/4:55_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (254, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink L', '5/4:56_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (255, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink XL', '5/4:57_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (256, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink 2XL', '5/4:58_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (257, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink 3XL', '5/4:59_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (258, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Soft Pink 4XL', '5/4:60_5:71', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (259, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt XS', '5/4:53_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (260, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt S', '5/4:54_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (261, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt M', '5/4:55_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (262, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt L', '5/4:56_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (263, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt XL', '5/4:57_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (264, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt 2XL', '5/4:58_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (265, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt 3XL', '5/4:59_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (266, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Asphalt 4XL', '5/4:60_5:72', 0, 0, 1, 1603019606, 1603019606);
INSERT INTO `osc_product_type_variant` VALUES (267, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal XS', '5/4:53_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (268, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal S', '5/4:54_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (269, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal M', '5/4:55_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (270, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal L', '5/4:56_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (271, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal XL', '5/4:57_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (272, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal 2XL', '5/4:58_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (273, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal 3XL', '5/4:59_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (274, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Heather Royal 4XL', '5/4:60_5:73', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (275, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly XS', '5/4:53_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (276, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly S', '5/4:54_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (277, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly M', '5/4:55_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (278, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly L', '5/4:56_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (279, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly XL', '5/4:57_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (280, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly 2XL', '5/4:58_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (281, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly 3XL', '5/4:59_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (282, 5, 'Bella + Canvas 3001C Unisex Jersey Short-Sleeve Kelly 4XL', '5/4:60_5:74', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (283, 6, 'Next Level NL3600 Premium Short Sleeve Black XS', '6/4:53_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (284, 6, 'Next Level NL3600 Premium Short Sleeve Black S', '6/4:54_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (285, 6, 'Next Level NL3600 Premium Short Sleeve Black M', '6/4:55_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (286, 6, 'Next Level NL3600 Premium Short Sleeve Black L', '6/4:56_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (287, 6, 'Next Level NL3600 Premium Short Sleeve Black XL', '6/4:57_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (288, 6, 'Next Level NL3600 Premium Short Sleeve Black 2XL', '6/4:58_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (289, 6, 'Next Level NL3600 Premium Short Sleeve Black 3XL', '6/4:59_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (290, 6, 'Next Level NL3600 Premium Short Sleeve Black 4XL', '6/4:60_6:75', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (291, 6, 'Next Level NL3600 Premium Short Sleeve White XS', '6/4:53_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (292, 6, 'Next Level NL3600 Premium Short Sleeve White S', '6/4:54_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (293, 6, 'Next Level NL3600 Premium Short Sleeve White M', '6/4:55_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (294, 6, 'Next Level NL3600 Premium Short Sleeve White L', '6/4:56_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (295, 6, 'Next Level NL3600 Premium Short Sleeve White XL', '6/4:57_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (296, 6, 'Next Level NL3600 Premium Short Sleeve White 2XL', '6/4:58_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (297, 6, 'Next Level NL3600 Premium Short Sleeve White 3XL', '6/4:59_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (298, 6, 'Next Level NL3600 Premium Short Sleeve White 4XL', '6/4:60_6:76', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (299, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey XS', '6/4:53_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (300, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey S', '6/4:54_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (301, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey M', '6/4:55_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (302, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey L', '6/4:56_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (303, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey XL', '6/4:57_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (304, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey 2XL', '6/4:58_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (305, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey 3XL', '6/4:59_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (306, 6, 'Next Level NL3600 Premium Short Sleeve Heather Grey 4XL', '6/4:60_6:77', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (307, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy XS', '6/4:53_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (308, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy S', '6/4:54_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (309, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy M', '6/4:55_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (310, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy L', '6/4:56_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (311, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy XL', '6/4:57_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (312, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy 2XL', '6/4:58_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (313, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy 3XL', '6/4:59_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (314, 6, 'Next Level NL3600 Premium Short Sleeve Midnight Navy 4XL', '6/4:60_6:78', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (315, 6, 'Next Level NL3600 Premium Short Sleeve Military Green XS', '6/4:53_6:79', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (316, 6, 'Next Level NL3600 Premium Short Sleeve Military Green S', '6/4:54_6:79', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (317, 6, 'Next Level NL3600 Premium Short Sleeve Military Green M', '6/4:55_6:79', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (318, 6, 'Next Level NL3600 Premium Short Sleeve Military Green L', '6/4:56_6:79', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (319, 6, 'Next Level NL3600 Premium Short Sleeve Military Green XL', '6/4:57_6:79', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (320, 6, 'Next Level NL3600 Premium Short Sleeve Military Green 2XL', '6/4:58_6:79', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (321, 6, 'Next Level NL3600 Premium Short Sleeve Military Green 3XL', '6/4:59_6:79', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (322, 6, 'Next Level NL3600 Premium Short Sleeve Cardinal XS', '6/4:53_6:80', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (323, 6, 'Next Level NL3600 Premium Short Sleeve Cardinal S', '6/4:54_6:80', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (324, 6, 'Next Level NL3600 Premium Short Sleeve Cardinal M', '6/4:55_6:80', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (325, 6, 'Next Level NL3600 Premium Short Sleeve Cardinal L', '6/4:56_6:80', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (326, 6, 'Next Level NL3600 Premium Short Sleeve Cardinal XL', '6/4:57_6:80', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (327, 6, 'Next Level NL3600 Premium Short Sleeve Cardinal 2XL', '6/4:58_6:80', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (328, 6, 'Next Level NL3600 Premium Short Sleeve Cardinal 3XL', '6/4:59_6:80', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (329, 6, 'Next Level NL3600 Premium Short Sleeve Kelly Green XS', '6/4:53_6:81', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (330, 6, 'Next Level NL3600 Premium Short Sleeve Kelly Green S', '6/4:54_6:81', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (331, 6, 'Next Level NL3600 Premium Short Sleeve Kelly Green M', '6/4:55_6:81', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (332, 6, 'Next Level NL3600 Premium Short Sleeve Kelly Green L', '6/4:56_6:81', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (333, 6, 'Next Level NL3600 Premium Short Sleeve Kelly Green XL', '6/4:57_6:81', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (334, 6, 'Next Level NL3600 Premium Short Sleeve Kelly Green 2XL', '6/4:58_6:81', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (335, 6, 'Next Level NL3600 Premium Short Sleeve Kelly Green 3XL', '6/4:59_6:81', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (336, 6, 'Next Level NL3600 Premium Short Sleeve Light Blue XS', '6/4:53_6:82', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (337, 6, 'Next Level NL3600 Premium Short Sleeve Light Blue S', '6/4:54_6:82', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (338, 6, 'Next Level NL3600 Premium Short Sleeve Light Blue M', '6/4:55_6:82', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (339, 6, 'Next Level NL3600 Premium Short Sleeve Light Blue L', '6/4:56_6:82', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (340, 6, 'Next Level NL3600 Premium Short Sleeve Light Blue XL', '6/4:57_6:82', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (341, 6, 'Next Level NL3600 Premium Short Sleeve Light Blue 2XL', '6/4:58_6:82', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (342, 6, 'Next Level NL3600 Premium Short Sleeve Light Blue 3XL', '6/4:59_6:82', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (343, 6, 'Next Level NL3600 Premium Short Sleeve Red XS', '6/4:53_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (344, 6, 'Next Level NL3600 Premium Short Sleeve Red S', '6/4:54_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (345, 6, 'Next Level NL3600 Premium Short Sleeve Red M', '6/4:55_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (346, 6, 'Next Level NL3600 Premium Short Sleeve Red L', '6/4:56_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (347, 6, 'Next Level NL3600 Premium Short Sleeve Red XL', '6/4:57_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (348, 6, 'Next Level NL3600 Premium Short Sleeve Red 2XL', '6/4:58_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (349, 6, 'Next Level NL3600 Premium Short Sleeve Red 3XL', '6/4:59_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (350, 6, 'Next Level NL3600 Premium Short Sleeve Red 4XL', '6/4:60_6:83', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (351, 6, 'Next Level NL3600 Premium Short Sleeve royal XS', '6/4:53_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (352, 6, 'Next Level NL3600 Premium Short Sleeve royal S', '6/4:54_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (353, 6, 'Next Level NL3600 Premium Short Sleeve royal M', '6/4:55_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (354, 6, 'Next Level NL3600 Premium Short Sleeve royal L', '6/4:56_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (355, 6, 'Next Level NL3600 Premium Short Sleeve royal XL', '6/4:57_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (356, 6, 'Next Level NL3600 Premium Short Sleeve royal 2XL', '6/4:58_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (357, 6, 'Next Level NL3600 Premium Short Sleeve royal 3XL', '6/4:59_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (358, 6, 'Next Level NL3600 Premium Short Sleeve royal 4XL', '6/4:60_6:84', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (359, 6, 'Next Level NL3600 Premium Short Sleeve Tahiti Blue XS', '6/4:53_6:85', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (360, 6, 'Next Level NL3600 Premium Short Sleeve Tahiti Blue S', '6/4:54_6:85', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (361, 6, 'Next Level NL3600 Premium Short Sleeve Tahiti Blue M', '6/4:55_6:85', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (362, 6, 'Next Level NL3600 Premium Short Sleeve Tahiti Blue L', '6/4:56_6:85', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (363, 6, 'Next Level NL3600 Premium Short Sleeve Tahiti Blue XL', '6/4:57_6:85', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (364, 6, 'Next Level NL3600 Premium Short Sleeve Tahiti Blue 2XL', '6/4:58_6:85', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (365, 6, 'Next Level NL3600 Premium Short Sleeve Tahiti Blue 3XL', '6/4:59_6:85', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (366, 6, 'Next Level NL3600 Premium Short Sleeve Banana Cream XS', '6/4:53_6:86', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (367, 6, 'Next Level NL3600 Premium Short Sleeve Banana Cream S', '6/4:54_6:86', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (368, 6, 'Next Level NL3600 Premium Short Sleeve Banana Cream M', '6/4:55_6:86', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (369, 6, 'Next Level NL3600 Premium Short Sleeve Banana Cream L', '6/4:56_6:86', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (370, 6, 'Next Level NL3600 Premium Short Sleeve Banana Cream XL', '6/4:57_6:86', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (371, 6, 'Next Level NL3600 Premium Short Sleeve Banana Cream 2XL', '6/4:58_6:86', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (372, 6, 'Next Level NL3600 Premium Short Sleeve Banana Cream 3XL', '6/4:59_6:86', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (373, 6, 'Next Level NL3600 Premium Short Sleeve Maroon XS', '6/4:53_6:87', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (374, 6, 'Next Level NL3600 Premium Short Sleeve Maroon S', '6/4:54_6:87', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (375, 6, 'Next Level NL3600 Premium Short Sleeve Maroon M', '6/4:55_6:87', 0, 0, 1, 1603019607, 1603019607);
INSERT INTO `osc_product_type_variant` VALUES (376, 6, 'Next Level NL3600 Premium Short Sleeve Maroon L', '6/4:56_6:87', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (377, 6, 'Next Level NL3600 Premium Short Sleeve Maroon XL', '6/4:57_6:87', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (378, 6, 'Next Level NL3600 Premium Short Sleeve Purple Rush XS', '6/4:53_6:88', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (379, 6, 'Next Level NL3600 Premium Short Sleeve Purple Rush S', '6/4:54_6:88', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (380, 6, 'Next Level NL3600 Premium Short Sleeve Purple Rush M', '6/4:55_6:88', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (381, 6, 'Next Level NL3600 Premium Short Sleeve Purple Rush L', '6/4:56_6:88', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (382, 6, 'Next Level NL3600 Premium Short Sleeve Purple Rush XL', '6/4:57_6:88', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (383, 6, 'Next Level NL3600 Premium Short Sleeve Purple Rush 2XL', '6/4:58_6:88', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (384, 6, 'Next Level NL3600 Premium Short Sleeve Purple Rush 3XL', '6/4:59_6:88', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (385, 7, 'Ceramic Mug White 11 oz', '7/7:89_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (386, 7, 'Ceramic Mug White 15 oz', '7/7:89_8:98', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (387, 8, 'Two Tone Mug Black 11 oz', '8/7:90_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (388, 8, 'Two Tone Mug Blue 11 oz', '8/7:91_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (389, 8, 'Two Tone Mug Red 11 oz', '8/7:92_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (390, 8, 'Two Tone Mug Navy 11 oz', '8/7:93_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (391, 8, 'Two Tone Mug Pink 11 oz', '8/7:94_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (392, 9, 'Enamel Campfire Mug White 10 oz', '9/7:89_8:95', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (393, 10, 'Insulated Coffee Mug White 12 oz', '10/7:89_8:97', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (394, 11, 'Fleece Blanket White 30x40', '11/9:99_10:100', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (395, 11, 'Fleece Blanket White 50x60', '11/9:99_10:101', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (396, 11, 'Fleece Blanket White 60x80', '11/9:99_10:102', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (397, 12, 'Sherpa Flannel Blanket White 50x60', '12/10:101_11:103', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (398, 13, 'Pillow White 16x16', '13/12:104_13:105', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (399, 13, 'Pillow White 18x18', '13/12:104_13:106', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (400, 14, 'Tea Towel White 16x25', '14/14:107_15:108', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (401, 15, 'Beach Towel White 35x60', '15/15:110_16:109', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (402, 16, 'Kid Towel White 22x42', '16/15:112_17:111', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (403, 17, 'Ornament Square Aluminium 3.2x3.2', '17/18:113_19:116', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (404, 18, 'Ornament Medallion Aluminium 2.75x4', '18/18:113_19:117', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (405, 19, 'Ornament Scalloped Aluminium 4x2.75', '19/18:113_19:118', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (406, 20, 'Ornament Circle MDF/Plastic 3 inches tall', '20/18:114_19:119', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (407, 20, 'Ornament Circle Ceramic 3 inches tall', '20/18:115_19:119', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (408, 21, 'Ornament Heart MDF/Plastic 3 inches tall', '21/18:114_19:119', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (409, 21, 'Ornament Heart Ceramic 3 inches tall', '21/18:115_19:119', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (410, 22, 'Ornament Oval MDF/Plastic 3.25 inches tall', '22/18:114_19:120', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (411, 23, 'Ornament Star MDF/Plastic 3.25 inches tall', '23/18:114_19:120', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (412, 24, 'Mouse Pad 8x9', '24/20:121', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (413, 25, 'Coaster set 4 4x4', '25/21:122', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (414, 26, 'Place Mat 8x9.5', '26/22:123', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (415, 27, 'Puzzle White 10x14', '27/23:124_24:125', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (416, 27, 'Puzzle White 14x10', '27/23:124_24:126', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (417, 27, 'Puzzle White 16x20', '27/23:124_24:127', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (418, 27, 'Puzzle White 20x16', '27/23:124_24:128', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (419, 28, 'Desktop Plaque 5x7', '28/25:129', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (420, 28, 'Desktop Plaque 8x10', '28/25:130', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (421, 28, 'Desktop Plaque 7x5', '28/25:131', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (422, 28, 'Desktop Plaque 10x8', '28/25:132', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (423, 29, 'Wiro Notebook White 5x7', '29/26:133_27:134', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (424, 29, 'Wiro Notebook White 5.8x8.27', '29/26:133_27:135', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (425, 29, 'Wiro Notebook White 8.27x11.69', '29/26:133_27:136', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (426, 30, 'Facemask with filter White 7.25x5.1', '30/28:137_29:138', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (427, 30, 'Facemask with filter White 6.39x3.81', '30/28:137_29:139', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (428, 31, 'Facemask without filter White 7.28x4.53', '31/30:140_31:141', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (429, 32, 'Phonecase White iPhone 11', '32/32:142_33:143', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (430, 32, 'Phonecase White iPhone 11 Pro', '32/32:142_33:144', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (431, 33, 'Stock White', '33/34:145', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (432, 34, 'FullPrints Ceramic Mug White 11 oz', '34/7:89_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (433, 34, 'FullPrints Ceramic Mug White 15 oz', '34/7:89_8:98', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (434, 35, 'FullPrints Two Tone Mug Black 11 oz', '35/7:90_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (435, 35, 'FullPrints Two Tone Mug Blue 11 oz', '35/7:91_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (436, 35, 'FullPrints Two Tone Mug Red 11 oz', '35/7:92_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (437, 35, 'FullPrints Two Tone Mug Navy 11 oz', '35/7:93_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (438, 35, 'FullPrints Two Tone Mug Pink 11 oz', '35/7:94_8:96', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (439, 36, 'FullPrints Enamel Campfire Mug White 10 oz', '36/7:89_8:95', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (440, 37, 'FullPrints Insulated Coffee Mug White 12 oz', '37/7:89_8:97', 0, 0, 1, 1603019608, 1603019608);
INSERT INTO `osc_product_type_variant` VALUES (441, 38, 'Yard Sign White 22x15', '38/35:146_36:147', 0, 0, 1, 1603019609, 1603019609);
INSERT INTO `osc_product_type_variant` VALUES (442, 38, 'Yard Sign White 24x18', '38/35:146_36:148', 0, 0, 1, 1603019609, 1603019609);
INSERT INTO `osc_product_type_variant` VALUES (443, 39, 'Garden Flag White 12.5x8', '39/37:149_38:150', 0, 0, 1, 1603019609, 1603019609);
COMMIT;

-- ----------------------------
-- Table structure for osc_supplier
-- ----------------------------
DROP TABLE IF EXISTS `osc_supplier`;
CREATE TABLE `osc_supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` varchar(1000) DEFAULT NULL,
  `status` tinyint(4) DEFAULT 1,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `ukey_UNIQUE` (`ukey`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_supplier
-- ----------------------------
BEGIN;
INSERT INTO `osc_supplier` VALUES (1, 'dpi', 'DPI', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_supplier` VALUES (2, 'custom_cat', 'custom_cat', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_supplier` VALUES (3, 'harrier', 'Harrier', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_supplier` VALUES (4, 'prima', 'Prima', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_supplier` VALUES (5, 'cw', 'CW', '', 1, 1603019603, 1603019603);
INSERT INTO `osc_supplier` VALUES (6, 'tee_launch', 'Tee Launch', '', 1, 1603019603, 1603019603);
COMMIT;

-- ----------------------------
-- Table structure for osc_supplier_location_rel
-- ----------------------------
DROP TABLE IF EXISTS `osc_supplier_location_rel`;
CREATE TABLE `osc_supplier_location_rel` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `supplier_id` int(10) unsigned NOT NULL DEFAULT 0,
  `product_type_variant_id` int(10) unsigned NOT NULL DEFAULT 0,
  `location_data` varchar(255) NOT NULL DEFAULT '''',
  `added_timestamp` int(11) NOT NULL DEFAULT 0,
  `modified_timestamp` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`record_id`) USING BTREE,
  UNIQUE KEY `item_INDEX` (`supplier_id`,`product_type_variant_id`,`location_data`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=876 DEFAULT CHARSET=utf8;

-- ----------------------------
-- Records of osc_supplier_location_rel
-- ----------------------------
BEGIN;
INSERT INTO `osc_supplier_location_rel` VALUES (1, 1, 1, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (2, 3, 1, 'g6', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (3, 4, 2, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (4, 1, 3, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (5, 4, 4, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (6, 4, 5, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (7, 1, 6, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (8, 3, 6, 'g6', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (9, 4, 6, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (10, 1, 7, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (11, 3, 7, 'g6', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (12, 1, 8, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (13, 3, 8, 'g6', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (14, 4, 8, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (15, 1, 9, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (16, 3, 9, 'g6', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (17, 4, 10, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (18, 1, 11, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (19, 4, 12, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (20, 1, 13, 'g4', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (21, 3, 13, 'g6', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (22, 4, 13, 'g7', 1603115209, 1603115209);
INSERT INTO `osc_supplier_location_rel` VALUES (23, 1, 14, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (24, 3, 14, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (25, 4, 15, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (26, 1, 16, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (27, 3, 16, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (28, 4, 16, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (29, 4, 17, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (30, 1, 17, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (31, 4, 18, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (32, 4, 19, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (33, 3, 20, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (34, 3, 21, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (35, 1, 22, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (36, 3, 22, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (37, 1, 23, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (38, 3, 23, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (39, 1, 24, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (40, 3, 24, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (41, 3, 25, 'g6', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (42, 1, 26, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (43, 1, 27, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (44, 1, 28, 'g4', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (45, 4, 29, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (46, 4, 30, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (47, 4, 31, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (48, 4, 32, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (49, 4, 33, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (50, 4, 34, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (51, 4, 35, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (52, 4, 36, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (53, 4, 37, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (54, 4, 38, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (55, 4, 39, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (56, 4, 40, 'g7', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (57, 5, 41, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (58, 2, 42, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (59, 5, 42, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (60, 6, 42, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (61, 2, 43, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (62, 5, 43, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (63, 6, 43, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (64, 2, 44, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (65, 5, 44, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (66, 6, 44, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (67, 2, 45, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (68, 5, 45, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (69, 6, 45, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (70, 2, 46, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (71, 5, 46, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (72, 6, 46, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (73, 2, 47, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (74, 5, 47, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (75, 6, 47, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (76, 2, 48, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (77, 6, 48, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (78, 2, 49, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (79, 6, 49, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (80, 5, 50, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (81, 2, 51, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (82, 5, 51, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (83, 6, 51, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (84, 2, 52, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (85, 5, 52, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (86, 6, 52, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (87, 2, 53, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (88, 5, 53, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (89, 6, 53, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (90, 2, 54, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (91, 5, 54, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (92, 6, 54, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (93, 2, 55, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (94, 5, 55, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (95, 6, 55, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (96, 2, 56, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (97, 5, 56, 'g5', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (98, 6, 56, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (99, 2, 57, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (100, 6, 57, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (101, 2, 58, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (102, 6, 58, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (103, 2, 59, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (104, 6, 59, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (105, 2, 60, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (106, 6, 60, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (107, 2, 61, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (108, 6, 61, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (109, 2, 62, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (110, 6, 62, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (111, 2, 63, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (112, 6, 63, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (113, 2, 64, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (114, 6, 64, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (115, 2, 65, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (116, 6, 65, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (117, 2, 66, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (118, 6, 66, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (119, 2, 67, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (120, 6, 67, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (121, 2, 68, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (122, 6, 68, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (123, 2, 69, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (124, 6, 69, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (125, 2, 70, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (126, 6, 70, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (127, 2, 71, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (128, 6, 71, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (129, 2, 72, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (130, 6, 72, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (131, 2, 73, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (132, 6, 73, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (133, 2, 74, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (134, 6, 74, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (135, 2, 75, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (136, 6, 75, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (137, 2, 76, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (138, 6, 76, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (139, 2, 77, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (140, 6, 77, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (141, 2, 78, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (142, 6, 78, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (143, 2, 79, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (144, 6, 79, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (145, 2, 80, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (146, 6, 80, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (147, 2, 81, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (148, 6, 81, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (149, 2, 82, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (150, 6, 82, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (151, 2, 83, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (152, 6, 83, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (153, 2, 84, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (154, 6, 84, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (155, 2, 85, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (156, 6, 85, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (157, 2, 86, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (158, 6, 86, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (159, 2, 87, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (160, 6, 87, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (161, 2, 88, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (162, 6, 88, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (163, 2, 89, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (164, 6, 89, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (165, 2, 90, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (166, 6, 90, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (167, 2, 91, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (168, 6, 91, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (169, 2, 92, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (170, 6, 92, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (171, 2, 93, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (172, 6, 93, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (173, 2, 94, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (174, 6, 94, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (175, 2, 95, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (176, 6, 95, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (177, 2, 96, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (178, 6, 96, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (179, 2, 97, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (180, 6, 97, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (181, 2, 98, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (182, 6, 98, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (183, 2, 99, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (184, 6, 99, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (185, 2, 100, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (186, 6, 100, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (187, 2, 101, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (188, 6, 101, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (189, 2, 102, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (190, 6, 102, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (191, 2, 103, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (192, 6, 103, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (193, 2, 104, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (194, 6, 104, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (195, 2, 105, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (196, 6, 105, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (197, 2, 106, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (198, 6, 106, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (199, 2, 107, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (200, 6, 107, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (201, 2, 108, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (202, 6, 108, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (203, 2, 109, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (204, 6, 109, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (205, 2, 110, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (206, 6, 110, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (207, 2, 111, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (208, 6, 111, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (209, 2, 112, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (210, 6, 112, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (211, 2, 113, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (212, 6, 113, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (213, 2, 114, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (214, 6, 114, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (215, 2, 115, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (216, 6, 115, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (217, 2, 116, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (218, 6, 116, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (219, 2, 117, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (220, 6, 117, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (221, 2, 118, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (222, 6, 118, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (223, 2, 119, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (224, 6, 119, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (225, 2, 120, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (226, 6, 120, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (227, 2, 121, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (228, 6, 121, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (229, 2, 122, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (230, 6, 122, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (231, 2, 123, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (232, 6, 123, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (233, 2, 124, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (234, 6, 124, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (235, 2, 125, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (236, 6, 125, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (237, 2, 126, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (238, 6, 126, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (239, 2, 127, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (240, 6, 127, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (241, 2, 128, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (242, 6, 128, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (243, 2, 129, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (244, 6, 129, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (245, 2, 130, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (246, 6, 130, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (247, 2, 131, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (248, 6, 131, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (249, 2, 132, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (250, 6, 132, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (251, 2, 133, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (252, 6, 133, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (253, 2, 134, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (254, 6, 134, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (255, 2, 135, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (256, 6, 135, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (257, 2, 136, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (258, 6, 136, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (259, 2, 137, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (260, 6, 137, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (261, 2, 138, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (262, 6, 138, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (263, 2, 139, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (264, 6, 139, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (265, 2, 140, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (266, 6, 140, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (267, 2, 141, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (268, 6, 141, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (269, 2, 142, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (270, 6, 142, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (271, 2, 143, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (272, 6, 143, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (273, 2, 144, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (274, 6, 144, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (275, 2, 145, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (276, 6, 145, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (277, 2, 146, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (278, 6, 146, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (279, 2, 147, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (280, 6, 147, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (281, 2, 148, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (282, 6, 148, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (283, 2, 149, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (284, 6, 149, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (285, 2, 150, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (286, 6, 150, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (287, 2, 151, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (288, 6, 151, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (289, 2, 152, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (290, 6, 152, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (291, 2, 153, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (292, 6, 153, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (293, 2, 154, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (294, 6, 154, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (295, 2, 155, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (296, 6, 155, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (297, 2, 156, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (298, 6, 156, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (299, 2, 157, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (300, 6, 157, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (301, 2, 158, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (302, 6, 158, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (303, 2, 159, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (304, 6, 159, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (305, 2, 160, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (306, 6, 160, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (307, 2, 161, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (308, 6, 161, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (309, 2, 162, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (310, 6, 162, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (311, 2, 163, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (312, 6, 163, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (313, 2, 164, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (314, 6, 164, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (315, 2, 165, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (316, 6, 165, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (317, 2, 166, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (318, 6, 166, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (319, 2, 167, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (320, 6, 167, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (321, 2, 168, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (322, 6, 168, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (323, 2, 169, 'g9', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (324, 6, 169, 'g8', 1603115210, 1603115210);
INSERT INTO `osc_supplier_location_rel` VALUES (325, 2, 170, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (326, 6, 170, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (327, 2, 171, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (328, 6, 171, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (329, 2, 172, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (330, 6, 172, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (331, 2, 173, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (332, 6, 173, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (333, 2, 174, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (334, 6, 174, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (335, 2, 175, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (336, 6, 175, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (337, 2, 176, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (338, 6, 176, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (339, 2, 177, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (340, 6, 177, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (341, 2, 178, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (342, 6, 178, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (343, 2, 179, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (344, 6, 179, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (345, 2, 180, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (346, 6, 180, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (347, 2, 181, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (348, 6, 181, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (349, 2, 182, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (350, 6, 182, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (351, 2, 183, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (352, 6, 183, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (353, 2, 184, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (354, 6, 184, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (355, 2, 185, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (356, 6, 185, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (357, 2, 186, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (358, 6, 186, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (359, 2, 187, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (360, 6, 187, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (361, 2, 188, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (362, 6, 188, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (363, 2, 189, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (364, 6, 189, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (365, 2, 190, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (366, 6, 190, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (367, 2, 191, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (368, 6, 191, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (369, 2, 192, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (370, 6, 192, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (371, 2, 193, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (372, 6, 193, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (373, 2, 194, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (374, 6, 194, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (375, 2, 195, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (376, 6, 195, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (377, 2, 196, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (378, 6, 196, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (379, 2, 197, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (380, 6, 197, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (381, 2, 198, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (382, 6, 198, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (383, 2, 199, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (384, 6, 199, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (385, 2, 200, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (386, 6, 200, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (387, 2, 201, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (388, 6, 201, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (389, 2, 202, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (390, 6, 202, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (391, 2, 203, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (392, 6, 203, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (393, 2, 204, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (394, 6, 204, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (395, 2, 205, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (396, 6, 205, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (397, 2, 206, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (398, 6, 206, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (399, 2, 207, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (400, 6, 207, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (401, 2, 208, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (402, 6, 208, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (403, 2, 209, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (404, 6, 209, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (405, 2, 210, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (406, 6, 210, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (407, 2, 211, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (408, 6, 211, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (409, 2, 212, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (410, 6, 212, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (411, 2, 213, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (412, 6, 213, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (413, 2, 214, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (414, 6, 214, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (415, 2, 215, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (416, 6, 215, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (417, 2, 216, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (418, 6, 216, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (419, 2, 217, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (420, 6, 217, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (421, 2, 218, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (422, 6, 218, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (423, 2, 219, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (424, 6, 219, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (425, 2, 220, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (426, 6, 220, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (427, 2, 221, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (428, 6, 221, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (429, 2, 222, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (430, 6, 222, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (431, 2, 223, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (432, 6, 223, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (433, 2, 224, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (434, 6, 224, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (435, 2, 225, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (436, 6, 225, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (437, 2, 226, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (438, 6, 226, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (439, 2, 227, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (440, 6, 227, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (441, 2, 228, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (442, 6, 228, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (443, 2, 229, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (444, 6, 229, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (445, 2, 230, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (446, 6, 230, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (447, 2, 231, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (448, 6, 231, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (449, 2, 232, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (450, 6, 232, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (451, 2, 233, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (452, 6, 233, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (453, 2, 234, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (454, 6, 234, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (455, 2, 235, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (456, 6, 235, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (457, 2, 236, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (458, 6, 236, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (459, 2, 237, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (460, 6, 237, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (461, 2, 238, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (462, 6, 238, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (463, 2, 239, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (464, 6, 239, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (465, 2, 240, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (466, 6, 240, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (467, 2, 241, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (468, 6, 241, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (469, 2, 242, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (470, 6, 242, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (471, 2, 243, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (472, 6, 243, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (473, 2, 244, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (474, 6, 244, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (475, 2, 245, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (476, 6, 245, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (477, 2, 246, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (478, 6, 246, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (479, 2, 247, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (480, 6, 247, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (481, 2, 248, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (482, 6, 248, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (483, 2, 249, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (484, 6, 249, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (485, 2, 250, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (486, 6, 250, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (487, 2, 251, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (488, 6, 251, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (489, 2, 252, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (490, 6, 252, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (491, 2, 253, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (492, 6, 253, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (493, 2, 254, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (494, 6, 254, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (495, 2, 255, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (496, 6, 255, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (497, 2, 256, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (498, 6, 256, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (499, 2, 257, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (500, 6, 257, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (501, 2, 258, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (502, 6, 258, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (503, 2, 259, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (504, 6, 259, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (505, 2, 260, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (506, 6, 260, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (507, 2, 261, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (508, 6, 261, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (509, 2, 262, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (510, 6, 262, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (511, 2, 263, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (512, 6, 263, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (513, 2, 264, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (514, 6, 264, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (515, 2, 265, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (516, 6, 265, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (517, 2, 266, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (518, 6, 266, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (519, 2, 267, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (520, 6, 267, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (521, 2, 268, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (522, 6, 268, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (523, 2, 269, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (524, 6, 269, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (525, 2, 270, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (526, 6, 270, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (527, 2, 271, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (528, 6, 271, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (529, 2, 272, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (530, 6, 272, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (531, 2, 273, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (532, 6, 273, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (533, 2, 274, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (534, 6, 274, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (535, 2, 275, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (536, 6, 275, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (537, 2, 276, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (538, 6, 276, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (539, 2, 277, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (540, 6, 277, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (541, 2, 278, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (542, 6, 278, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (543, 2, 279, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (544, 6, 279, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (545, 2, 280, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (546, 6, 280, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (547, 2, 281, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (548, 6, 281, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (549, 2, 282, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (550, 6, 282, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (551, 2, 283, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (552, 6, 283, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (553, 2, 284, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (554, 6, 284, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (555, 2, 285, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (556, 6, 285, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (557, 2, 286, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (558, 6, 286, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (559, 2, 287, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (560, 6, 287, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (561, 2, 288, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (562, 6, 288, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (563, 2, 289, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (564, 6, 289, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (565, 2, 290, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (566, 6, 290, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (567, 2, 291, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (568, 6, 291, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (569, 2, 292, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (570, 6, 292, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (571, 2, 293, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (572, 6, 293, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (573, 2, 294, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (574, 6, 294, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (575, 2, 295, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (576, 6, 295, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (577, 2, 296, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (578, 6, 296, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (579, 2, 297, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (580, 6, 297, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (581, 2, 298, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (582, 6, 298, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (583, 2, 299, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (584, 6, 299, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (585, 2, 300, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (586, 6, 300, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (587, 2, 301, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (588, 6, 301, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (589, 2, 302, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (590, 6, 302, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (591, 2, 303, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (592, 6, 303, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (593, 2, 304, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (594, 6, 304, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (595, 2, 305, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (596, 6, 305, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (597, 2, 306, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (598, 6, 306, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (599, 2, 307, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (600, 6, 307, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (601, 2, 308, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (602, 6, 308, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (603, 2, 309, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (604, 6, 309, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (605, 2, 310, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (606, 6, 310, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (607, 2, 311, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (608, 6, 311, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (609, 2, 312, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (610, 6, 312, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (611, 2, 313, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (612, 6, 313, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (613, 2, 314, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (614, 6, 314, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (615, 2, 315, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (616, 6, 315, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (617, 2, 316, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (618, 6, 316, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (619, 2, 317, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (620, 6, 317, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (621, 2, 318, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (622, 6, 318, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (623, 2, 319, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (624, 6, 319, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (625, 2, 320, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (626, 6, 320, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (627, 2, 321, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (628, 6, 321, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (629, 2, 322, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (630, 6, 322, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (631, 2, 323, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (632, 6, 323, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (633, 2, 324, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (634, 6, 324, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (635, 2, 325, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (636, 6, 325, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (637, 2, 326, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (638, 6, 326, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (639, 2, 327, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (640, 6, 327, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (641, 2, 328, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (642, 6, 328, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (643, 2, 329, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (644, 6, 329, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (645, 2, 330, 'g9', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (646, 6, 330, 'g8', 1603115211, 1603115211);
INSERT INTO `osc_supplier_location_rel` VALUES (647, 2, 331, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (648, 6, 331, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (649, 2, 332, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (650, 6, 332, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (651, 2, 333, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (652, 6, 333, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (653, 2, 334, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (654, 6, 334, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (655, 2, 335, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (656, 6, 335, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (657, 2, 336, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (658, 6, 336, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (659, 2, 337, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (660, 6, 337, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (661, 2, 338, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (662, 6, 338, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (663, 2, 339, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (664, 6, 339, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (665, 2, 340, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (666, 6, 340, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (667, 2, 341, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (668, 6, 341, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (669, 2, 342, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (670, 6, 342, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (671, 2, 343, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (672, 6, 343, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (673, 2, 344, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (674, 6, 344, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (675, 2, 345, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (676, 6, 345, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (677, 2, 346, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (678, 6, 346, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (679, 2, 347, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (680, 6, 347, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (681, 2, 348, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (682, 6, 348, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (683, 2, 349, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (684, 6, 349, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (685, 2, 350, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (686, 6, 350, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (687, 2, 351, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (688, 6, 351, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (689, 2, 352, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (690, 6, 352, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (691, 2, 353, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (692, 6, 353, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (693, 2, 354, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (694, 6, 354, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (695, 2, 355, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (696, 6, 355, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (697, 2, 356, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (698, 6, 356, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (699, 2, 357, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (700, 6, 357, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (701, 2, 358, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (702, 6, 358, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (703, 2, 359, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (704, 6, 359, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (705, 2, 360, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (706, 6, 360, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (707, 2, 361, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (708, 6, 361, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (709, 2, 362, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (710, 6, 362, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (711, 2, 363, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (712, 6, 363, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (713, 2, 364, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (714, 6, 364, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (715, 2, 365, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (716, 6, 365, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (717, 2, 366, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (718, 6, 366, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (719, 2, 367, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (720, 6, 367, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (721, 2, 368, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (722, 6, 368, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (723, 2, 369, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (724, 6, 369, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (725, 2, 370, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (726, 6, 370, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (727, 2, 371, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (728, 6, 371, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (729, 2, 372, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (730, 6, 372, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (731, 2, 373, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (732, 6, 373, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (733, 2, 374, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (734, 6, 374, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (735, 2, 375, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (736, 6, 375, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (737, 2, 376, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (738, 6, 376, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (739, 2, 377, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (740, 6, 377, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (741, 2, 378, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (742, 6, 378, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (743, 2, 379, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (744, 6, 379, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (745, 2, 380, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (746, 6, 380, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (747, 2, 381, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (748, 6, 381, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (749, 2, 382, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (750, 6, 382, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (751, 2, 383, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (752, 6, 383, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (753, 2, 384, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (754, 6, 384, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (755, 1, 385, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (756, 5, 385, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (757, 3, 385, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (758, 4, 385, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (759, 7, 385, 'g10', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (760, 1, 386, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (761, 5, 386, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (762, 3, 386, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (763, 4, 386, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (764, 1, 387, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (765, 3, 387, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (766, 4, 387, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (767, 1, 388, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (768, 3, 388, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (769, 4, 388, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (770, 1, 389, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (771, 3, 389, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (772, 4, 389, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (773, 1, 390, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (774, 1, 391, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (775, 3, 391, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (776, 4, 391, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (777, 1, 392, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (778, 1, 393, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (779, 1, 394, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (780, 5, 394, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (781, 4, 394, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (782, 1, 395, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (783, 5, 395, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (784, 3, 395, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (785, 1, 396, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (786, 5, 396, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (787, 3, 397, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (788, 1, 398, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (789, 5, 398, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (790, 1, 399, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (791, 5, 399, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (792, 3, 399, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (793, 1, 400, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (794, 4, 400, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (795, 3, 400, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (796, 1, 401, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (797, 3, 401, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (798, 4, 402, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (799, 1, 403, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (800, 3, 403, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (801, 4, 403, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (802, 1, 404, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (803, 3, 404, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (804, 4, 404, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (805, 1, 405, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (806, 4, 405, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (807, 2, 406, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (808, 6, 407, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (809, 2, 408, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (810, 6, 409, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (811, 2, 410, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (812, 2, 411, 'g9', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (813, 1, 412, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (814, 3, 412, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (815, 4, 412, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (816, 1, 413, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (817, 3, 413, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (818, 4, 413, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (819, 1, 414, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (820, 3, 414, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (821, 4, 414, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (822, 1, 415, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (823, 1, 416, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (824, 1, 417, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (825, 1, 418, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (826, 1, 419, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (827, 3, 419, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (828, 4, 419, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (829, 1, 420, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (830, 3, 420, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (831, 4, 420, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (832, 1, 421, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (833, 3, 421, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (834, 4, 421, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (835, 1, 422, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (836, 3, 422, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (837, 4, 422, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (838, 1, 423, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (839, 3, 424, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (840, 3, 425, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (841, 1, 426, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (842, 3, 426, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (843, 4, 426, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (844, 1, 427, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (845, 5, 428, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (846, 5, 429, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (847, 5, 430, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (848, 6, 431, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (849, 1, 432, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (850, 5, 432, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (851, 3, 432, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (852, 4, 432, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (853, 7, 432, 'g10', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (854, 1, 433, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (855, 5, 433, 'g5', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (856, 3, 433, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (857, 4, 433, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (858, 1, 434, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (859, 3, 434, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (860, 4, 434, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (861, 1, 435, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (862, 3, 435, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (863, 4, 435, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (864, 1, 436, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (865, 3, 436, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (866, 4, 436, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (867, 1, 437, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (868, 1, 438, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (869, 3, 438, 'g6', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (870, 4, 438, 'g7', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (871, 1, 439, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (872, 1, 440, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (873, 1, 441, 'g4', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (874, 6, 442, 'g8', 1603115212, 1603115212);
INSERT INTO `osc_supplier_location_rel` VALUES (875, 6, 443, 'g8', 1603115212, 1603115212);
COMMIT;

-- ----------------------------
-- Table structure for osc_supplier_variant_rel
-- ----------------------------
DROP TABLE IF EXISTS `osc_supplier_variant_rel`;
CREATE TABLE `osc_supplier_variant_rel` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_type_variant_id` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `supplier_variant_sku` varchar(255) DEFAULT NULL,
  `print_template_id` int(11) DEFAULT NULL,
  `meta_data` text DEFAULT NULL,
  `added_timestamp` int(11) DEFAULT NULL,
  `modified_timestamp` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=875 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

-- ----------------------------
-- Records of osc_supplier_variant_rel
-- ----------------------------
BEGIN;
INSERT INTO `osc_supplier_variant_rel` VALUES (1, 1, 1, '1865', 11, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (2, 1, 3, '810SLIMCANVAS', 43, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (3, 2, 4, 'SF34407', 12, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (4, 3, 1, '1867', 13, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (5, 4, 4, 'SF34275', 14, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (6, 5, 4, 'SF34280', 15, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (7, 6, 1, '1868', 16, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (8, 6, 3, '1620SLIMCANVAS', 47, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (9, 6, 4, 'SF34276', 46, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (10, 7, 1, '1869', 17, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (11, 7, 3, '2024SLIMCANVAS', 50, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (12, 8, 1, '1870', 18, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (13, 8, 3, '2030SLIMCANVAS', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (14, 8, 4, 'SF34277', 52, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (15, 9, 1, '1865', 19, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (16, 9, 3, '810SLIMCANVAS', 44, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (17, 10, 4, 'SF34407', 23, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (18, 11, 1, '1867', 20, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (19, 12, 4, 'SF34275', 24, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (20, 13, 1, '1868', 21, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (21, 13, 3, '1620SLIMCANVAS', 49, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (22, 13, 4, 'SF34276', 48, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (23, 14, 1, '1869', 22, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (24, 14, 3, '2024SLIMCANVAS', 51, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (25, 15, 4, 'SF34280', 25, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (26, 16, 1, '1870', 26, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (27, 16, 3, '2030SLIMCANVAS', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (28, 16, 4, 'SF34277', 53, '{\"rotate\":90}', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (29, 17, 1, '1866', 27, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (30, 17, 4, 'SF34271', 45, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (31, 18, 4, 'SF34272', 28, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (32, 19, 4, 'SF34283', 29, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (33, 20, 3, 'poster1015', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (34, 21, 3, 'poster1218', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (35, 22, 1, '231', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (36, 22, 3, 'poster1620', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (37, 23, 1, '233', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (38, 23, 3, 'poster2030', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (39, 24, 1, '244', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (40, 24, 3, 'poster2436', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (41, 25, 3, 'poster3040', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (42, 26, 1, '235', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (43, 27, 1, '197', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (44, 28, 1, '140', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (45, 29, 4, 'CommerceProduct_68260', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (46, 30, 4, 'CommerceProduct_68261', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (47, 31, 4, 'CommerceProduct_68262', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (48, 32, 4, 'SF1402G', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (49, 33, 4, 'SF1406G', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (50, 34, 4, 'SF1403G', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (51, 35, 4, 'CommerceProduct_68260', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (52, 36, 4, 'CommerceProduct_68261', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (53, 37, 4, 'CommerceProduct_68262', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (54, 38, 4, 'SF1402G', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (55, 39, 4, 'SF1406G', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (56, 40, 4, 'SF1403G', 0, '[]', 1603019603, 1603019603);
INSERT INTO `osc_supplier_variant_rel` VALUES (57, 41, 5, 'N0302017-BK-XS', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (58, 42, 2, '48144', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (59, 42, 5, 'N0302017-BK-S', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (60, 42, 6, '48144', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (61, 43, 2, '48145', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (62, 43, 5, 'N0302017-BK-M', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (63, 43, 6, '48145', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (64, 44, 2, '48146', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (65, 44, 5, 'N0302017-BK-l', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (66, 44, 6, '48146', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (67, 45, 2, '48147', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (68, 45, 5, 'N0302017-BK-XL', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (69, 45, 6, '48147', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (70, 46, 2, '48148', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (71, 46, 5, 'N0302017-BK-XXL', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (72, 46, 6, '48148', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (73, 47, 2, '48149', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (74, 47, 5, 'N0302017-BK-3XL', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (75, 47, 6, '48149', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (76, 48, 2, '48150', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (77, 48, 6, '48150', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (78, 49, 2, '48151', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (79, 49, 6, '48151', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (80, 50, 5, 'N0302017-WH-XS', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (81, 51, 2, '48300', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (82, 51, 5, 'N0302017-WH-S', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (83, 51, 6, '48300', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (84, 52, 2, '48301', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (85, 52, 5, 'N0302017-WH-M', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (86, 52, 6, '48301', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (87, 53, 2, '48302', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (88, 53, 5, 'N0302017-WH-L', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (89, 53, 6, '48302', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (90, 54, 2, '48303', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (91, 54, 5, 'N0302017-WH-XL', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (92, 54, 6, '48303', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (93, 55, 2, '48304', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (94, 55, 5, 'N0302017-WH-XXL', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (95, 55, 6, '48304', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (96, 56, 2, '48305', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (97, 56, 5, 'N0302017-WH-3XL', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (98, 56, 6, '48305', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (99, 57, 2, '48306', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (100, 57, 6, '48306', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (101, 58, 2, '48307', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (102, 58, 6, '48307', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (103, 59, 2, '48200', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (104, 59, 6, '48200', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (105, 60, 2, '48201', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (106, 60, 6, '48201', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (107, 61, 2, '48202', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (108, 61, 6, '48202', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (109, 62, 2, '48203', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (110, 62, 6, '48203', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (111, 63, 2, '48204', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (112, 63, 6, '48204', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (113, 64, 2, '48205', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (114, 64, 6, '48205', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (115, 65, 2, '48206', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (116, 65, 6, '48206', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (117, 66, 2, '48207', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (118, 66, 6, '48207', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (119, 67, 2, '48248', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (120, 67, 6, '48248', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (121, 68, 2, '48249', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (122, 68, 6, '48249', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (123, 69, 2, '48250', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (124, 69, 6, '48250', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (125, 70, 2, '48251', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (126, 70, 6, '48251', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (127, 71, 2, '48252', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (128, 71, 6, '48252', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (129, 72, 2, '48253', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (130, 72, 6, '48253', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (131, 73, 2, '48254', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (132, 73, 6, '48254', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (133, 74, 2, '48255', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (134, 74, 6, '48255', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (135, 75, 2, '48168', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (136, 75, 6, '48168', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (137, 76, 2, '48169', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (138, 76, 6, '48169', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (139, 77, 2, '48170', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (140, 77, 6, '48170', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (141, 78, 2, '48171', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (142, 78, 6, '48171', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (143, 79, 2, '48172', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (144, 79, 6, '48172', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (145, 80, 2, '48173', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (146, 80, 6, '48173', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (147, 81, 2, '48174', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (148, 81, 6, '48174', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (149, 82, 2, '48175', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (150, 82, 6, '48175', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (151, 83, 2, '48308', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (152, 83, 6, '48308', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (153, 84, 2, '48309', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (154, 84, 6, '48309', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (155, 85, 2, '48310', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (156, 85, 6, '48310', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (157, 86, 2, '48311', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (158, 86, 6, '48311', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (159, 87, 2, '48312', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (160, 87, 6, '48312', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (161, 88, 2, '48313', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (162, 88, 6, '48313', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (163, 89, 2, '48314', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (164, 89, 6, '48314', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (165, 90, 2, '48315', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (166, 90, 6, '48315', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (167, 91, 2, '48152', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (168, 91, 6, '48152', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (169, 92, 2, '48153', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (170, 92, 6, '48153', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (171, 93, 2, '48154', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (172, 93, 6, '48154', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (173, 94, 2, '48155', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (174, 94, 6, '48155', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (175, 95, 2, '48156', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (176, 95, 6, '48156', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (177, 96, 2, '48157', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (178, 96, 6, '48157', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (179, 97, 2, '48158', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (180, 97, 6, '48158', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (181, 98, 2, '48159', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (182, 98, 6, '48159', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (183, 99, 2, '48176', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (184, 99, 6, '48176', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (185, 100, 2, '48177', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (186, 100, 6, '48177', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (187, 101, 2, '48178', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (188, 101, 6, '48178', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (189, 102, 2, '48179', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (190, 102, 6, '48179', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (191, 103, 2, '48180', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (192, 103, 6, '48180', 42, '[]', 1603019604, 1603019604);
INSERT INTO `osc_supplier_variant_rel` VALUES (193, 104, 2, '48181', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (194, 104, 6, '48181', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (195, 105, 2, '48182', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (196, 105, 6, '48182', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (197, 106, 2, '48183', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (198, 106, 6, '48183', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (199, 107, 2, '48216', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (200, 107, 6, '48216', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (201, 108, 2, '48217', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (202, 108, 6, '48217', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (203, 109, 2, '48218', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (204, 109, 6, '48218', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (205, 110, 2, '48219', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (206, 110, 6, '48219', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (207, 111, 2, '48220', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (208, 111, 6, '48220', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (209, 112, 2, '48221', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (210, 112, 6, '48221', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (211, 113, 2, '48222', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (212, 113, 6, '48222', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (213, 114, 2, '48223', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (214, 114, 6, '48223', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (215, 115, 2, '48224', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (216, 115, 6, '48224', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (217, 116, 2, '48225', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (218, 116, 6, '48225', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (219, 117, 2, '48226', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (220, 117, 6, '48226', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (221, 118, 2, '48227', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (222, 118, 6, '48227', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (223, 119, 2, '48228', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (224, 119, 6, '48228', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (225, 120, 2, '48229', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (226, 120, 6, '48229', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (227, 121, 2, '48230', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (228, 121, 6, '48230', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (229, 122, 2, '48231', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (230, 122, 6, '48231', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (231, 123, 2, '48262', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (232, 123, 6, '48262', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (233, 124, 2, '48263', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (234, 124, 6, '48263', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (235, 125, 2, '48264', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (236, 125, 6, '48264', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (237, 126, 2, '48265', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (238, 126, 6, '48265', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (239, 127, 2, '48266', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (240, 127, 6, '48266', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (241, 128, 2, '48267', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (242, 128, 6, '48267', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (243, 129, 2, '48268', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (244, 129, 6, '48268', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (245, 130, 2, '48269', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (246, 130, 6, '48269', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (247, 131, 2, '48270', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (248, 131, 6, '48270', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (249, 132, 2, '48271', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (250, 132, 6, '48271', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (251, 133, 2, '48272', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (252, 133, 6, '48272', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (253, 134, 2, '48273', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (254, 134, 6, '48273', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (255, 135, 2, '48274', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (256, 135, 6, '48274', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (257, 136, 2, '48275', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (258, 136, 6, '48275', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (259, 137, 2, '48276', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (260, 137, 6, '48276', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (261, 138, 2, '48277', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (262, 138, 6, '48277', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (263, 139, 2, '48278', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (264, 139, 6, '48278', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (265, 140, 2, '48279', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (266, 140, 6, '48279', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (267, 141, 2, '48280', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (268, 141, 6, '48280', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (269, 142, 2, '48281', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (270, 142, 6, '48281', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (271, 143, 2, '48282', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (272, 143, 6, '48282', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (273, 144, 2, '48283', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (274, 144, 6, '48283', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (275, 145, 2, '48284', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (276, 145, 6, '48284', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (277, 146, 2, '48285', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (278, 146, 6, '48285', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (279, 147, 2, '48286', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (280, 147, 6, '48286', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (281, 148, 2, '48287', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (282, 148, 6, '48287', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (283, 149, 2, '48288', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (284, 149, 6, '48288', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (285, 150, 2, '48289', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (286, 150, 6, '48289', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (287, 151, 2, '48290', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (288, 151, 6, '48290', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (289, 152, 2, '48291', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (290, 152, 6, '48291', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (291, 153, 2, '48292', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (292, 153, 6, '48292', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (293, 154, 2, '48293', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (294, 154, 6, '48293', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (295, 155, 2, '48393', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (296, 155, 6, '48393', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (297, 156, 2, '48394', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (298, 156, 6, '48394', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (299, 157, 2, '48395', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (300, 157, 6, '48395', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (301, 158, 2, '48396', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (302, 158, 6, '48396', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (303, 159, 2, '48397', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (304, 159, 6, '48397', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (305, 160, 2, '48398', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (306, 160, 6, '48398', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (307, 161, 2, '48399', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (308, 161, 6, '48399', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (309, 162, 2, '48400', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (310, 162, 6, '48400', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (311, 163, 2, '55488', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (312, 163, 6, '55488', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (313, 164, 2, '55489', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (314, 164, 6, '55489', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (315, 165, 2, '55490', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (316, 165, 6, '55490', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (317, 166, 2, '55491', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (318, 166, 6, '55491', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (319, 167, 2, '55492', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (320, 167, 6, '55492', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (321, 168, 2, '55493', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (322, 168, 6, '55493', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (323, 169, 2, '55494', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (324, 169, 6, '55494', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (325, 170, 2, '55495', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (326, 170, 6, '55495', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (327, 171, 2, '48184', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (328, 171, 6, '48184', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (329, 172, 2, '48185', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (330, 172, 6, '48185', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (331, 173, 2, '48186', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (332, 173, 6, '48186', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (333, 174, 2, '48187', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (334, 174, 6, '48187', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (335, 175, 2, '48188', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (336, 175, 6, '48188', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (337, 176, 2, '48189', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (338, 176, 6, '48189', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (339, 177, 2, '48190', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (340, 177, 6, '48190', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (341, 178, 2, '48191', 42, '[]', 1603019605, 1603019605);
INSERT INTO `osc_supplier_variant_rel` VALUES (342, 178, 6, '48191', 42, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (343, 179, 2, '45474', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (344, 179, 6, '45474', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (345, 180, 2, '45475', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (346, 180, 6, '45475', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (347, 181, 2, '45476', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (348, 181, 6, '45476', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (349, 182, 2, '45478', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (350, 182, 6, '45478', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (351, 183, 2, '45479', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (352, 183, 6, '45479', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (353, 184, 2, '45480', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (354, 184, 6, '45480', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (355, 185, 2, '45481', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (356, 185, 6, '45481', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (357, 186, 2, '45482', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (358, 186, 6, '45482', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (359, 187, 2, '45515', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (360, 187, 6, '45515', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (361, 188, 2, '45516', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (362, 188, 6, '45516', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (363, 189, 2, '45517', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (364, 189, 6, '45517', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (365, 190, 2, '45518', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (366, 190, 6, '45518', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (367, 191, 2, '45519', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (368, 191, 6, '45519', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (369, 192, 2, '45520', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (370, 192, 6, '45520', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (371, 193, 2, '45521', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (372, 193, 6, '45521', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (373, 194, 2, '45522', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (374, 194, 6, '45522', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (375, 195, 2, '45523', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (376, 195, 6, '45523', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (377, 196, 2, '45524', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (378, 196, 6, '45524', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (379, 197, 2, '45525', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (380, 197, 6, '45525', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (381, 198, 2, '45526', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (382, 198, 6, '45526', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (383, 199, 2, '45527', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (384, 199, 6, '45527', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (385, 200, 2, '45528', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (386, 200, 6, '45528', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (387, 201, 2, '45529', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (388, 201, 6, '45529', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (389, 202, 2, '45530', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (390, 202, 6, '45530', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (391, 203, 2, '45539', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (392, 203, 6, '45539', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (393, 204, 2, '45540', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (394, 204, 6, '45540', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (395, 205, 2, '45541', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (396, 205, 6, '45541', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (397, 206, 2, '45542', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (398, 206, 6, '45542', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (399, 207, 2, '45543', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (400, 207, 6, '45543', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (401, 208, 2, '45544', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (402, 208, 6, '45544', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (403, 209, 2, '45545', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (404, 209, 6, '45545', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (405, 210, 2, '45546', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (406, 210, 6, '45546', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (407, 211, 2, '45603', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (408, 211, 6, '45603', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (409, 212, 2, '45604', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (410, 212, 6, '45604', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (411, 213, 2, '45605', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (412, 213, 6, '45605', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (413, 214, 2, '45606', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (414, 214, 6, '45606', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (415, 215, 2, '45607', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (416, 215, 6, '45607', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (417, 216, 2, '45608', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (418, 216, 6, '45608', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (419, 217, 2, '45609', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (420, 217, 6, '45609', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (421, 218, 2, '45610', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (422, 218, 6, '45610', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (423, 219, 2, '45571', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (424, 219, 6, '45571', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (425, 220, 2, '45572', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (426, 220, 6, '45572', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (427, 221, 2, '45573', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (428, 221, 6, '45573', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (429, 222, 2, '45574', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (430, 222, 6, '45574', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (431, 223, 2, '45575', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (432, 223, 6, '45575', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (433, 224, 2, '45576', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (434, 224, 6, '45576', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (435, 225, 2, '45577', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (436, 225, 6, '45577', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (437, 226, 2, '45578', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (438, 226, 6, '45578', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (439, 227, 2, '45579', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (440, 227, 6, '45579', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (441, 228, 2, '45580', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (442, 228, 6, '45580', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (443, 229, 2, '45581', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (444, 229, 6, '45581', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (445, 230, 2, '45582', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (446, 230, 6, '45582', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (447, 231, 2, '45583', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (448, 231, 6, '45583', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (449, 232, 2, '45584', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (450, 232, 6, '45584', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (451, 233, 2, '45585', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (452, 233, 6, '45585', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (453, 234, 2, '45586', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (454, 234, 6, '45586', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (455, 235, 2, '45491', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (456, 235, 6, '45491', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (457, 236, 2, '45492', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (458, 236, 6, '45492', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (459, 237, 2, '45493', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (460, 237, 6, '45493', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (461, 238, 2, '45494', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (462, 238, 6, '45494', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (463, 239, 2, '45495', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (464, 239, 6, '45495', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (465, 240, 2, '45496', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (466, 240, 6, '45496', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (467, 241, 2, '45497', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (468, 241, 6, '45497', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (469, 242, 2, '45498', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (470, 242, 6, '45498', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (471, 243, 2, '45547', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (472, 243, 6, '45547', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (473, 244, 2, '45548', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (474, 244, 6, '45548', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (475, 245, 2, '45549', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (476, 245, 6, '45549', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (477, 246, 2, '45550', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (478, 246, 6, '45550', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (479, 247, 2, '45551', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (480, 247, 6, '45551', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (481, 248, 2, '45552', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (482, 248, 6, '45552', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (483, 249, 2, '45553', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (484, 249, 6, '45553', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (485, 250, 2, '45554', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (486, 250, 6, '45554', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (487, 251, 2, '45563', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (488, 251, 6, '45563', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (489, 252, 2, '45564', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (490, 252, 6, '45564', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (491, 253, 2, '45565', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (492, 253, 6, '45565', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (493, 254, 2, '45566', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (494, 254, 6, '45566', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (495, 255, 2, '45567', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (496, 255, 6, '45567', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (497, 256, 2, '45568', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (498, 256, 6, '45568', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (499, 257, 2, '45569', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (500, 257, 6, '45569', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (501, 258, 2, '45570', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (502, 258, 6, '45570', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (503, 259, 2, '54621', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (504, 259, 6, '54621', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (505, 260, 2, '54622', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (506, 260, 6, '54622', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (507, 261, 2, '54624', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (508, 261, 6, '54624', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (509, 262, 2, '54625', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (510, 262, 6, '54625', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (511, 263, 2, '54626', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (512, 263, 6, '54626', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (513, 264, 2, '54627', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (514, 264, 6, '54627', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (515, 265, 2, '54628', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (516, 265, 6, '54628', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (517, 266, 2, '54629', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (518, 266, 6, '54629', 58, '[]', 1603019606, 1603019606);
INSERT INTO `osc_supplier_variant_rel` VALUES (519, 267, 2, '54612', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (520, 267, 6, '54612', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (521, 268, 2, '54614', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (522, 268, 6, '54614', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (523, 269, 2, '54615', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (524, 269, 6, '54615', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (525, 270, 2, '54616', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (526, 270, 6, '54616', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (527, 271, 2, '54617', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (528, 271, 6, '54617', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (529, 272, 2, '54618', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (530, 272, 6, '54618', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (531, 273, 2, '54619', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (532, 273, 6, '54619', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (533, 274, 2, '54620', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (534, 274, 6, '54620', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (535, 275, 2, '54638', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (536, 275, 6, '54638', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (537, 276, 2, '54639', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (538, 276, 6, '54639', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (539, 277, 2, '54640', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (540, 277, 6, '54640', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (541, 278, 2, '54641', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (542, 278, 6, '54641', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (543, 279, 2, '54642', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (544, 279, 6, '54642', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (545, 280, 2, '54643', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (546, 280, 6, '54643', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (547, 281, 2, '54644', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (548, 281, 6, '54644', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (549, 282, 2, '54645', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (550, 282, 6, '54645', 58, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (551, 283, 2, '39170', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (552, 283, 6, '39170', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (553, 284, 2, '39409', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (554, 284, 6, '39409', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (555, 285, 2, '39410', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (556, 285, 6, '39410', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (557, 286, 2, '39411', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (558, 286, 6, '39411', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (559, 287, 2, '39412', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (560, 287, 6, '39412', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (561, 288, 2, '39413', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (562, 288, 6, '39413', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (563, 289, 2, '39414', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (564, 289, 6, '39414', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (565, 290, 2, '46978', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (566, 290, 6, '46978', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (567, 291, 2, '39514', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (568, 291, 6, '39514', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (569, 292, 2, '39515', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (570, 292, 6, '39515', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (571, 293, 2, '39516', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (572, 293, 6, '39516', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (573, 294, 2, '39517', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (574, 294, 6, '39517', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (575, 295, 2, '39518', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (576, 295, 6, '39518', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (577, 296, 2, '39519', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (578, 296, 6, '39519', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (579, 297, 2, '39520', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (580, 297, 6, '39520', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (581, 298, 2, '46985', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (582, 298, 6, '46985', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (583, 299, 2, '39429', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (584, 299, 6, '39429', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (585, 300, 2, '39430', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (586, 300, 6, '39430', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (587, 301, 2, '39431', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (588, 301, 6, '39431', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (589, 302, 2, '39432', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (590, 302, 6, '39432', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (591, 303, 2, '39433', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (592, 303, 6, '39433', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (593, 304, 2, '39434', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (594, 304, 6, '39434', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (595, 305, 2, '39435', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (596, 305, 6, '39435', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (597, 306, 2, '46979', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (598, 306, 6, '46979', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (599, 307, 2, '39472', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (600, 307, 6, '39472', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (601, 308, 2, '39473', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (602, 308, 6, '39473', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (603, 309, 2, '39474', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (604, 309, 6, '39474', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (605, 310, 2, '39475', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (606, 310, 6, '39475', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (607, 311, 2, '39476', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (608, 311, 6, '39476', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (609, 312, 2, '39477', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (610, 312, 6, '39477', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (611, 313, 2, '39478', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (612, 313, 6, '39478', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (613, 314, 2, '46982', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (614, 314, 6, '46982', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (615, 315, 2, '39451', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (616, 315, 6, '39451', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (617, 316, 2, '39452', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (618, 316, 6, '39452', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (619, 317, 2, '39453', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (620, 317, 6, '39453', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (621, 318, 2, '39454', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (622, 318, 6, '39454', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (623, 319, 2, '39455', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (624, 319, 6, '39455', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (625, 320, 2, '39456', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (626, 320, 6, '39456', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (627, 321, 2, '39457', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (628, 321, 6, '39457', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (629, 322, 2, '39415', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (630, 322, 6, '39415', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (631, 323, 2, '39416', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (632, 323, 6, '39416', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (633, 324, 2, '39417', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (634, 324, 6, '39417', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (635, 325, 2, '39418', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (636, 325, 6, '39418', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (637, 326, 2, '39419', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (638, 326, 6, '39419', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (639, 327, 2, '39420', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (640, 327, 6, '39420', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (641, 328, 2, '39421', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (642, 328, 6, '39421', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (643, 329, 2, '39458', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (644, 329, 6, '39458', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (645, 330, 2, '39459', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (646, 330, 6, '39459', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (647, 331, 2, '39460', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (648, 331, 6, '39460', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (649, 332, 2, '39461', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (650, 332, 6, '39461', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (651, 333, 2, '39462', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (652, 333, 6, '39462', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (653, 334, 2, '39463', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (654, 334, 6, '39463', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (655, 335, 2, '39464', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (656, 335, 6, '39464', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (657, 336, 2, '39422', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (658, 336, 6, '39422', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (659, 337, 2, '39423', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (660, 337, 6, '39423', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (661, 338, 2, '39424', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (662, 338, 6, '39424', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (663, 339, 2, '39425', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (664, 339, 6, '39425', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (665, 340, 2, '39426', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (666, 340, 6, '39426', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (667, 341, 2, '39427', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (668, 341, 6, '39427', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (669, 342, 2, '39428', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (670, 342, 6, '39428', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (671, 343, 2, '39486', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (672, 343, 6, '39486', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (673, 344, 2, '39487', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (674, 344, 6, '39487', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (675, 345, 2, '39488', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (676, 345, 6, '39488', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (677, 346, 2, '39489', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (678, 346, 6, '39489', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (679, 347, 2, '39490', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (680, 347, 6, '39490', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (681, 348, 2, '39491', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (682, 348, 6, '39491', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (683, 349, 2, '39492', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (684, 349, 6, '39492', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (685, 350, 2, '46983', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (686, 350, 6, '46983', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (687, 351, 2, '39493', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (688, 351, 6, '39493', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (689, 352, 2, '39494', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (690, 352, 6, '39494', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (691, 353, 2, '39495', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (692, 353, 6, '39495', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (693, 354, 2, '39496', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (694, 354, 6, '39496', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (695, 355, 2, '39497', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (696, 355, 6, '39497', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (697, 356, 2, '39498', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (698, 356, 6, '39498', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (699, 357, 2, '39499', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (700, 357, 6, '39499', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (701, 358, 2, '46984', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (702, 358, 6, '46984', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (703, 359, 2, '39500', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (704, 359, 6, '39500', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (705, 360, 2, '39501', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (706, 360, 6, '39501', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (707, 361, 2, '39502', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (708, 361, 6, '39502', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (709, 362, 2, '39503', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (710, 362, 6, '39503', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (711, 363, 2, '39504', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (712, 363, 6, '39504', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (713, 364, 2, '39505', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (714, 364, 6, '39505', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (715, 365, 2, '39506', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (716, 365, 6, '39506', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (717, 366, 2, '51860', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (718, 366, 6, '51860', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (719, 367, 2, '51861', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (720, 367, 6, '51861', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (721, 368, 2, '51862', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (722, 368, 6, '51862', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (723, 369, 2, '51863', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (724, 369, 6, '51863', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (725, 370, 2, '51864', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (726, 370, 6, '51864', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (727, 371, 2, '51865', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (728, 371, 6, '51865', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (729, 372, 2, '51866', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (730, 372, 6, '51866', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (731, 373, 2, '57292', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (732, 373, 6, '57292', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (733, 374, 2, '57293', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (734, 374, 6, '57293', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (735, 375, 2, '57294', 59, '[]', 1603019607, 1603019607);
INSERT INTO `osc_supplier_variant_rel` VALUES (736, 375, 6, '57294', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (737, 376, 2, '57295', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (738, 376, 6, '57295', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (739, 377, 2, '57296', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (740, 377, 6, '57296', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (741, 378, 2, '39479', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (742, 378, 6, '39479', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (743, 379, 2, '39480', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (744, 379, 6, '39480', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (745, 380, 2, '39481', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (746, 380, 6, '39481', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (747, 381, 2, '39482', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (748, 381, 6, '39482', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (749, 382, 2, '39483', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (750, 382, 6, '39483', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (751, 383, 2, '39484', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (752, 383, 6, '39484', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (753, 384, 2, '39485', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (754, 384, 6, '39485', 59, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (755, 385, 1, '1122', 1, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (756, 385, 3, '11OZWHITE', 1, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (757, 385, 4, 'SF20704', 55, '{\"format\":\"pdf\"}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (758, 385, 5, 'N0601015', 1, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (759, 385, 7, 'mug', 1, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (760, 386, 1, '5050', 2, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (761, 386, 3, '15OZMUG', 57, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (762, 386, 4, 'PR000001', 2, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (763, 386, 5, 'N0601017', 2, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (764, 387, 1, '5055', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (765, 387, 3, '11OZBLACK', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (766, 387, 4, 'CommerceProduct_96206', 56, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (767, 388, 1, '5085', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (768, 388, 3, '11OZBLUE', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (769, 388, 4, 'CommerceProduct_96206', 56, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (770, 389, 1, '5086', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (771, 389, 3, '11OZRED', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (772, 389, 4, 'CommerceProduct_96206', 56, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (773, 390, 1, '5052', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (774, 391, 1, '5080', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (775, 391, 3, '11OZPINK', 3, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (776, 391, 4, 'CommerceProduct_96206', 56, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (777, 392, 1, '1161', 5, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (778, 393, 1, '1160', 4, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (779, 394, 1, '1511', 32, '{\"format\":\"jpg\"}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (780, 394, 4, 'CommerceProduct_265394', 32, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (781, 394, 5, 'N0601004-S', 32, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (782, 395, 1, '1332', 33, '{\"format\":\"jpg\"}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (783, 395, 3, 'MINKBLNKT', 33, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (784, 395, 5, 'N0601004-M', 33, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (785, 396, 1, '1333', 34, '{\"format\":\"jpg\"}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (786, 396, 5, 'N0601004-L', 34, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (787, 397, 3, 'SHERPABLNKT', 33, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (788, 398, 1, '1594', 38, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (789, 398, 5, 'C0601006-S-F', 54, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (790, 399, 1, '1587', 37, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (791, 399, 3, '1818CUSH', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (792, 399, 5, 'C0601006-M-F', 37, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (793, 400, 1, '1441', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (794, 400, 4, 'CommerceProduct_265395', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (795, 400, 3, '1625TEATOWEL', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (796, 401, 1, '1416', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (797, 401, 3, '916BEACHTOWEL', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (798, 402, 4, 'CommerceProduct_265396', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (799, 403, 4, 'CommerceProduct_152717', 62, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (800, 403, 3, 'ALUMINIUM SQUARE ORNAMENT', 62, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (801, 403, 1, '1164', 62, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (802, 404, 4, 'CommerceProduct_152719', 60, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (803, 404, 3, 'ALUMINIUM MEDALLION ORNAMENT', 60, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (804, 404, 1, '1166', 60, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (805, 405, 4, 'CommerceProduct_152718', 61, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (806, 405, 1, '1165', 61, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (807, 406, 2, '54723', 67, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (808, 407, 6, 'RORN', 67, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (809, 408, 2, '54726', 68, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (810, 409, 6, 'HRTORN', 68, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (811, 410, 2, '54724', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (812, 411, 2, '54725', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (813, 412, 1, '5069', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (814, 412, 3, 'DS MOUSEMAT', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (815, 412, 4, 'SF12200', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (816, 413, 1, '1396', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (817, 413, 3, 'DSCOASTER4MULTI', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (818, 413, 4, 'CommerceProduct_87608', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (819, 414, 3, 'DS PLACEMAT', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (820, 414, 4, 'CommerceProduct_130274', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (821, 415, 1, '12370', 35, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (822, 416, 1, '12370', 36, '{\"rotate\":90}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (823, 417, 1, '12331', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (824, 418, 1, '12331', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (825, 419, 1, '1690', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (826, 419, 3, '57DESKPAN', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (827, 419, 4, 'CommerceProduct_121683', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (828, 420, 1, '1691', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (829, 420, 3, '810DESKPAN', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (830, 420, 4, 'CommerceProduct_121684', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (831, 421, 1, '1690', 30, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (832, 421, 3, '57DESKPAN', 30, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (833, 421, 4, 'CommerceProduct_121683', 30, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (834, 422, 1, '1691', 31, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (835, 422, 3, '810DESKPAN', 31, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (836, 422, 4, 'CommerceProduct_121684', 31, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (837, 423, 1, '11500', 39, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (838, 424, 3, '148210WIL', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (839, 425, 3, '21297WIL', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (840, 426, 1, '1598', 41, '{\"format\":\"jpg\"}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (841, 426, 3, 'facemask', 41, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (842, 426, 4, 'CommerceProduct_240119', 41, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (843, 427, 1, '1599', 63, '{\"format\":\"jpg\"}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (844, 428, 5, 'N0701008', 40, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (845, 429, 5, 'N0801002-11-WH', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (846, 430, 5, 'N0801002-11P-WH', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (847, 431, 6, 'STKING', 0, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (848, 432, 1, '1122', 6, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (849, 432, 3, '11OZWHITE', 6, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (850, 432, 4, 'SF20704', 64, '{\"format\":\"pdf\"}', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (851, 432, 5, 'N0601015', 6, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (852, 432, 7, 'mug', 6, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (853, 433, 1, '5050', 7, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (854, 433, 3, '15OZMUG', 66, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (855, 433, 4, 'PR000001', 7, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (856, 433, 5, 'N0601017', 7, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (857, 434, 1, '5055', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (858, 434, 3, '11OZBLACK', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (859, 434, 4, 'CommerceProduct_96206', 65, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (860, 435, 1, '5085', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (861, 435, 3, '11OZBLUE', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (862, 435, 4, 'CommerceProduct_96206', 65, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (863, 436, 1, '5086', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (864, 436, 3, '11OZRED', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (865, 436, 4, 'CommerceProduct_96206', 65, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (866, 437, 1, '5052', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (867, 438, 1, '5080', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (868, 438, 3, '11OZPINK', 8, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (869, 438, 4, 'CommerceProduct_96206', 65, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (870, 439, 1, '1161', 10, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (871, 440, 1, '1160', 9, '[]', 1603019608, 1603019608);
INSERT INTO `osc_supplier_variant_rel` VALUES (872, 441, 1, '7083', 0, '[]', 1603019609, 1603019609);
INSERT INTO `osc_supplier_variant_rel` VALUES (873, 442, 6, 'SIGN1824', 0, '[]', 1603019609, 1603019609);
INSERT INTO `osc_supplier_variant_rel` VALUES (874, 443, 6, 'GFLAG', 0, '[]', 1603019609, 1603019609);
COMMIT;

-- ----------------------------
-- Table structure for osc_image_convert_path
-- ----------------------------
DROP TABLE IF EXISTS `osc_image_convert_path`;
CREATE TABLE `osc_image_convert_path`  (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_id` int NULL DEFAULT NULL,
  `path_old` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `path_new` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL,
  `error_flag` tinyint NULL DEFAULT 0,
  `error_message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL,
  `added_timestamp` int NOT NULL,
  `modified_timestamp` int NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8mb4 COLLATE = utf8mb4_general_ci ROW_FORMAT = Dynamic;

UPDATE osc_core_setting SET setting_value = '[{"location_data":"*","product_types":["*"],"shipping_configs":{"1":{"price":"7.99","processing":4,"estimate":25},"2":{"price":"11.99","processing":4,"estimate":25},"3":{"price":"15.99","processing":4,"estimate":25},"4":{"price":"19.99","processing":4,"estimate":25},"5":{"price":"23.99","processing":4,"estimate":25},"6":{"price":"27.99","processing":4,"estimate":25},"7":{"price":"31.99","processing":4,"estimate":25},"8":{"price":"35.99","processing":4,"estimate":25},"9":{"price":"39.99","processing":4,"estimate":25},"10":{"price":"43.99","processing":4,"estimate":25},"11":{"price":"47.99","processing":4,"estimate":25},"12":{"price":"51.99","processing":4,"estimate":25},"13":{"price":"55.99","processing":4,"estimate":25},"14":{"price":"59.99","processing":4,"estimate":25},"15":{"price":"63.99","processing":4,"estimate":25},"16":{"price":"67.99","processing":4,"estimate":25},"17":{"price":"71.99","processing":4,"estimate":25},"18":{"price":"75.99","processing":4,"estimate":25},"19":{"price":"79.99","processing":4,"estimate":25},"20":{"price":"83.99","processing":4,"estimate":25}}},{"location_data":"g11","product_types":["*"],"shipping_configs":{"1":{"price":"6.99","processing":4,"estimate":7},"2":{"price":"10.99","processing":4,"estimate":7},"3":{"price":"14.99","processing":4,"estimate":7},"4":{"price":"18.99","processing":4,"estimate":7},"5":{"price":"22.99","processing":4,"estimate":7},"6":{"price":"26.99","processing":4,"estimate":7},"7":{"price":"30.99","processing":4,"estimate":7},"8":{"price":"34.99","processing":4,"estimate":7},"9":{"price":"38.99","processing":4,"estimate":7},"10":{"price":"42.99","processing":4,"estimate":7},"11":{"price":"46.99","processing":4,"estimate":7},"12":{"price":"50.99","processing":4,"estimate":7},"13":{"price":"54.99","processing":4,"estimate":7},"14":{"price":"58.99","processing":4,"estimate":7},"15":{"price":"62.99","processing":4,"estimate":7},"16":{"price":"66.99","processing":4,"estimate":7},"17":{"price":"70.00","processing":4,"estimate":7},"18":{"price":"74.99","processing":4,"estimate":7},"19":{"price":"78.99","processing":4,"estimate":7},"20":{"price":"82.99","processing":4,"estimate":7}}},{"location_data":"g12","product_types":["*"],"shipping_configs":{"1":{"price":"6.99","processing":4,"estimate":25},"2":{"price":"10.99","processing":4,"estimate":25},"3":{"price":"14.99","processing":4,"estimate":25},"4":{"price":"18.99","processing":4,"estimate":25},"5":{"price":"22.99","processing":4,"estimate":25},"6":{"price":"26.99","processing":4,"estimate":25},"7":{"price":"30.99","processing":4,"estimate":25},"8":{"price":"34.99","processing":4,"estimate":25},"9":{"price":"38.99","processing":4,"estimate":25},"10":{"price":"42.99","processing":4,"estimate":25},"11":{"price":"46.99","processing":4,"estimate":25},"12":{"price":"50.99","processing":4,"estimate":25},"13":{"price":"54.99","processing":4,"estimate":25},"14":{"price":"58.99","processing":4,"estimate":25},"15":{"price":"62.99","processing":4,"estimate":25},"16":{"price":"66.99","processing":4,"estimate":25},"17":{"price":"70.99","processing":4,"estimate":25},"18":{"price":"74.99","processing":4,"estimate":25},"19":{"price":"78.99","processing":4,"estimate":25},"20":{"price":"82.99","processing":4,"estimate":25}}},{"location_data":"g18","product_types":["*"],"shipping_configs":{"1":{"price":"7.99","processing":5,"estimate":5},"2":{"price":"11.99","processing":5,"estimate":5},"3":{"price":"15.99","processing":5,"estimate":5},"4":{"price":"19.99","processing":5,"estimate":5},"5":{"price":"23.99","processing":5,"estimate":5},"6":{"price":"27.99","processing":5,"estimate":5},"7":{"price":"31.99","processing":5,"estimate":5},"8":{"price":"35.99","processing":5,"estimate":5},"9":{"price":"39.99","processing":5,"estimate":5},"10":{"price":"43.99","processing":5,"estimate":5},"11":{"price":"47.99","processing":5,"estimate":5},"12":{"price":"51.99","processing":5,"estimate":5},"13":{"price":"55.99","processing":5,"estimate":5},"14":{"price":"59.99","processing":5,"estimate":5},"15":{"price":"63.99","processing":5,"estimate":5},"16":{"price":"67.99","processing":5,"estimate":5},"17":{"price":"71.99","processing":5,"estimate":5},"18":{"price":"75.99","processing":5,"estimate":5},"19":{"price":"79.99","processing":5,"estimate":5},"20":{"price":"83.99","processing":5,"estimate":5}}},{"location_data":"g13","product_types":["*"],"shipping_configs":{"1":{"price":"7.99","processing":4,"estimate":20},"2":{"price":"11.99","processing":4,"estimate":20},"3":{"price":"15.99","processing":4,"estimate":20},"4":{"price":"19.99","processing":4,"estimate":20},"5":{"price":"23.99","processing":4,"estimate":20},"6":{"price":"27.99","processing":4,"estimate":20},"7":{"price":"31.99","processing":4,"estimate":20},"8":{"price":"35.99","processing":4,"estimate":20},"9":{"price":"39.99","processing":4,"estimate":20},"10":{"price":"43.99","processing":4,"estimate":20},"11":{"price":"47.99","processing":4,"estimate":20},"12":{"price":"51.99","processing":4,"estimate":20},"13":{"price":"55.99","processing":4,"estimate":20},"14":{"price":"59.99","processing":4,"estimate":20},"15":{"price":"63.99","processing":4,"estimate":20},"16":{"price":"67.99","processing":4,"estimate":20},"17":{"price":"71.99","processing":4,"estimate":20},"18":{"price":"75.99","processing":4,"estimate":20},"19":{"price":"79.99","processing":4,"estimate":20},"20":{"price":"83.99","processing":4,"estimate":20}}},{"location_data":"g19","product_types":["*"],"shipping_configs":{"1":{"price":"7.99","processing":4,"estimate":6},"2":{"price":"11.99","processing":4,"estimate":6},"3":{"price":"15.99","processing":4,"estimate":6},"4":{"price":"19.99","processing":4,"estimate":6},"5":{"price":"23.99","processing":4,"estimate":6},"6":{"price":"27.99","processing":4,"estimate":6},"7":{"price":"31.99","processing":4,"estimate":6},"8":{"price":"35.99","processing":4,"estimate":6},"9":{"price":"39.99","processing":4,"estimate":6},"10":{"price":"43.99","processing":4,"estimate":6},"11":{"price":"47.99","processing":4,"estimate":6},"12":{"price":"51.99","processing":4,"estimate":6},"13":{"price":"55.99","processing":4,"estimate":6},"14":{"price":"59.99","processing":4,"estimate":6},"15":{"price":"63.99","processing":4,"estimate":6},"16":{"price":"67.99","processing":4,"estimate":6},"17":{"price":"71.99","processing":4,"estimate":6},"18":{"price":"75.99","processing":4,"estimate":6},"19":{"price":"79.99","processing":4,"estimate":6},"20":{"price":"83.99","processing":4,"estimate":6}}},{"location_data":"g14","product_types":["*"],"shipping_configs":{"1":{"price":"7.99","processing":5,"estimate":8},"2":{"price":"11.99","processing":5,"estimate":8},"3":{"price":"15.99","processing":5,"estimate":8},"4":{"price":"19.99","processing":5,"estimate":8},"5":{"price":"23.99","processing":5,"estimate":8},"6":{"price":"27.99","processing":5,"estimate":8},"7":{"price":"31.99","processing":5,"estimate":8},"8":{"price":"35.99","processing":5,"estimate":8},"9":{"price":"39.99","processing":5,"estimate":8},"10":{"price":"43.99","processing":5,"estimate":8},"11":{"price":"47.99","processing":5,"estimate":8},"12":{"price":"51.99","processing":5,"estimate":8},"13":{"price":"55.99","processing":5,"estimate":8},"14":{"price":"59.99","processing":5,"estimate":8},"15":{"price":"63.99","processing":5,"estimate":8},"16":{"price":"67.99","processing":5,"estimate":8},"17":{"price":"71.99","processing":5,"estimate":8},"18":{"price":"75.99","processing":5,"estimate":8},"19":{"price":"79.99","processing":5,"estimate":8},"20":{"price":"83.99","processing":5,"estimate":8}}},{"location_data":"g20","product_types":["*"],"shipping_configs":{"1":{"price":"7.99","processing":4,"estimate":20},"2":{"price":"11.99","processing":4,"estimate":20},"3":{"price":"15.99","processing":4,"estimate":20},"4":{"price":"19.99","processing":4,"estimate":20},"5":{"price":"23.99","processing":4,"estimate":20},"6":{"price":"27.99","processing":4,"estimate":20},"7":{"price":"31.99","processing":4,"estimate":20},"8":{"price":"35.99","processing":4,"estimate":20},"9":{"price":"39.99","processing":4,"estimate":20},"10":{"price":"43.99","processing":4,"estimate":20},"11":{"price":"47.99","processing":4,"estimate":20},"12":{"price":"51.99","processing":4,"estimate":20},"13":{"price":"55.99","processing":4,"estimate":20},"14":{"price":"59.99","processing":4,"estimate":20},"15":{"price":"63.99","processing":4,"estimate":20},"16":{"price":"67.99","processing":4,"estimate":20},"17":{"price":"71.99","processing":4,"estimate":20},"18":{"price":"75.99","processing":4,"estimate":20},"19":{"price":"79.99","processing":4,"estimate":20},"20":{"price":"83.99","processing":4,"estimate":20}}},{"location_data":"g21","product_types":["*"],"shipping_configs":{"1":{"price":"7.99","processing":4,"estimate":8},"2":{"price":"11.99","processing":4,"estimate":8},"3":{"price":"15.99","processing":4,"estimate":8},"4":{"price":"19.99","processing":4,"estimate":8},"5":{"price":"23.99","processing":4,"estimate":8},"6":{"price":"27.99","processing":4,"estimate":8},"7":{"price":"31.99","processing":4,"estimate":8},"8":{"price":"35.99","processing":4,"estimate":8},"9":{"price":"39.99","processing":4,"estimate":8},"10":{"price":"43.99","processing":4,"estimate":8},"11":{"price":"47.99","processing":4,"estimate":8},"12":{"price":"51.99","processing":4,"estimate":8},"13":{"price":"55.99","processing":4,"estimate":8},"14":{"price":"59.99","processing":4,"estimate":8},"15":{"price":"63.99","processing":4,"estimate":8},"16":{"price":"67.99","processing":4,"estimate":8},"17":{"price":"71.99","processing":4,"estimate":8},"18":{"price":"75.99","processing":4,"estimate":8},"19":{"price":"79.99","processing":4,"estimate":8},"20":{"price":"83.99","processing":4,"estimate":8}}},{"location_data":"*","product_types":[17,18,19,20,21,22,23,30,31],"shipping_configs":{"1":{"price":"4.99","processing":4,"estimate":25},"2":{"price":"8.99","processing":4,"estimate":25},"3":{"price":"12.99","processing":4,"estimate":25},"4":{"price":"16.99","processing":4,"estimate":25},"5":{"price":"20.99","processing":4,"estimate":25},"6":{"price":"24.99","processing":4,"estimate":25},"7":{"price":"28.99","processing":4,"estimate":25},"8":{"price":"32.99","processing":4,"estimate":25},"9":{"price":"36.99","processing":4,"estimate":25},"10":{"price":"40.99","processing":4,"estimate":25},"11":{"price":"44.99","processing":4,"estimate":25},"12":{"price":"48.99","processing":4,"estimate":25},"13":{"price":"52.99","processing":4,"estimate":25},"14":{"price":"56.99","processing":4,"estimate":25},"15":{"price":"60.99","processing":4,"estimate":25},"16":{"price":"64.99","processing":4,"estimate":25},"17":{"price":"68.99","processing":4,"estimate":25},"18":{"price":"72.99","processing":4,"estimate":25},"19":{"price":"76.99","processing":4,"estimate":25},"20":{"price":"80.99","processing":4,"estimate":25}}},{"location_data":"g15","product_types":[17,18,19,20,21,22,23,30,31],"shipping_configs":{"1":{"price":"4.99","processing":4,"estimate":7},"2":{"price":"8.99","processing":4,"estimate":7},"3":{"price":"12.99","processing":4,"estimate":7},"4":{"price":"16.99","processing":4,"estimate":7},"5":{"price":"20.99","processing":4,"estimate":7},"6":{"price":"24.99","processing":4,"estimate":7},"7":{"price":"28.99","processing":4,"estimate":7},"8":{"price":"32.99","processing":4,"estimate":7},"9":{"price":"36.99","processing":4,"estimate":7},"10":{"price":"40.99","processing":4,"estimate":7},"11":{"price":"44.99","processing":4,"estimate":7},"12":{"price":"48.99","processing":4,"estimate":7},"13":{"price":"52.99","processing":4,"estimate":7},"14":{"price":"56.99","processing":4,"estimate":7},"15":{"price":"60.99","processing":4,"estimate":7},"16":{"price":"64.99","processing":4,"estimate":7},"17":{"price":"68.99","processing":4,"estimate":7},"18":{"price":"72.99","processing":4,"estimate":7},"19":{"price":"76.99","processing":4,"estimate":7},"20":{"price":"80.99","processing":4,"estimate":7}}},{"location_data":"g16","product_types":[17,18,19,20,21,22,23,30,31],"shipping_configs":{"1":{"price":"4.99","processing":4,"estimate":30},"2":{"price":"8.99","processing":4,"estimate":30},"3":{"price":"12.99","processing":4,"estimate":30},"4":{"price":"16.99","processing":4,"estimate":30},"5":{"price":"20.99","processing":4,"estimate":30},"6":{"price":"24.99","processing":4,"estimate":30},"7":{"price":"28.99","processing":4,"estimate":30},"8":{"price":"32.99","processing":4,"estimate":30},"9":{"price":"36.99","processing":4,"estimate":30},"10":{"price":"40.99","processing":4,"estimate":30},"11":{"price":"44.99","processing":4,"estimate":30},"12":{"price":"48.99","processing":4,"estimate":30},"13":{"price":"52.99","processing":4,"estimate":30},"14":{"price":"56.99","processing":4,"estimate":30},"15":{"price":"60.99","processing":4,"estimate":30},"16":{"price":"64.99","processing":4,"estimate":30},"17":{"price":"68.99","processing":4,"estimate":30},"18":{"price":"72.99","processing":4,"estimate":30},"19":{"price":"76.99","processing":4,"estimate":30},"20":{"price":"80.99","processing":4,"estimate":30}}},{"location_data":"g18","product_types":[17,18,19,20,21,22,23,30,31],"shipping_configs":{"1":{"price":"4.99","estimate":5,"processing":5},"2":{"price":"8.99","estimate":5,"processing":5},"3":{"price":"12.99","estimate":5,"processing":5},"4":{"price":"16.99","estimate":5,"processing":5},"5":{"price":"20.99","estimate":5,"processing":5},"6":{"price":"24.99","estimate":5,"processing":5},"7":{"price":"28.99","estimate":5,"processing":5},"8":{"price":"32.99","estimate":5,"processing":5},"9":{"price":"36.99","estimate":5,"processing":5},"10":{"price":"40.99","estimate":5,"processing":5},"11":{"price":"44.99","estimate":5,"processing":5},"12":{"price":"48.99","estimate":5,"processing":5},"13":{"price":"52.99","estimate":5,"processing":5},"14":{"price":"56.99","estimate":5,"processing":5},"15":{"price":"60.99","estimate":5,"processing":5},"16":{"price":"64.99","estimate":5,"processing":5},"17":{"price":"68.99","estimate":5,"processing":5},"18":{"price":"72.99","estimate":5,"processing":5},"19":{"price":"76.99","estimate":5,"processing":5},"20":{"price":"80.99","estimate":5,"processing":5}}},{"location_data":"g20","product_types":[17,18,19,20,21,22,23,30,31],"shipping_configs":{"1":{"price":"4.99","estimate":20,"processing":4},"2":{"price":"8.99","estimate":20,"processing":4},"3":{"price":"12.99","estimate":20,"processing":4},"4":{"price":"16.99","estimate":20,"processing":4},"5":{"price":"20.99","estimate":20,"processing":4},"6":{"price":"24.99","estimate":20,"processing":4},"7":{"price":"28.99","estimate":20,"processing":4},"8":{"price":"32.99","estimate":20,"processing":4},"9":{"price":"36.99","estimate":20,"processing":4},"10":{"price":"40.99","estimate":20,"processing":4},"11":{"price":"44.99","estimate":20,"processing":4},"12":{"price":"48.99","estimate":20,"processing":4},"13":{"price":"52.99","estimate":20,"processing":4},"14":{"price":"56.99","estimate":20,"processing":4},"15":{"price":"60.99","estimate":20,"processing":4},"16":{"price":"64.99","estimate":20,"processing":4},"17":{"price":"68.99","estimate":20,"processing":4},"18":{"price":"72.99","estimate":20,"processing":4},"19":{"price":"76.99","estimate":20,"processing":4},"20":{"price":"80.99","estimate":20,"processing":4}}},{"location_data":"g19","product_types":[17,18,19,20,21,22,23,30,31],"shipping_configs":{"1":{"price":"4.99","estimate":6,"processing":4},"2":{"price":"8.99","estimate":6,"processing":4},"3":{"price":"12.99","estimate":6,"processing":4},"4":{"price":"16.99","estimate":6,"processing":4},"5":{"price":"20.99","estimate":6,"processing":4},"6":{"price":"24.99","estimate":6,"processing":4},"7":{"price":"28.99","estimate":6,"processing":4},"8":{"price":"32.99","estimate":6,"processing":4},"9":{"price":"36.99","estimate":6,"processing":4},"10":{"price":"40.99","estimate":6,"processing":4},"11":{"price":"44.99","estimate":6,"processing":4},"12":{"price":"48.99","estimate":6,"processing":4},"13":{"price":"52.99","estimate":6,"processing":4},"14":{"price":"56.99","estimate":6,"processing":4},"15":{"price":"60.99","estimate":6,"processing":4},"16":{"price":"64.99","estimate":6,"processing":4},"17":{"price":"68.99","estimate":6,"processing":4},"18":{"price":"72.99","estimate":6,"processing":4},"19":{"price":"76.99","estimate":6,"processing":4},"20":{"price":"80.99","estimate":6,"processing":4}}},{"location_data":"g17","product_types":[17,18,19,20,21,22,23,30,31],"shipping_configs":{"1":{"price":"4.99","estimate":8,"processing":5},"2":{"price":"8.99","estimate":8,"processing":5},"3":{"price":"12.99","estimate":8,"processing":5},"4":{"price":"16.99","estimate":8,"processing":5},"5":{"price":"20.99","estimate":8,"processing":5},"6":{"price":"24.99","estimate":8,"processing":5},"7":{"price":"28.99","estimate":8,"processing":5},"8":{"price":"32.99","estimate":8,"processing":5},"9":{"price":"36.99","estimate":8,"processing":5},"10":{"price":"40.99","estimate":8,"processing":5},"11":{"price":"44.99","estimate":8,"processing":5},"12":{"price":"48.99","estimate":8,"processing":5},"13":{"price":"52.99","estimate":8,"processing":5},"14":{"price":"56.99","estimate":8,"processing":5},"15":{"price":"60.99","estimate":8,"processing":5},"16":{"price":"64.99","estimate":8,"processing":5},"17":{"price":"68.99","estimate":8,"processing":5},"18":{"price":"72.99","estimate":8,"processing":5},"19":{"price":"76.99","estimate":8,"processing":5},"20":{"price":"80.99","estimate":8,"processing":5}}}]' where setting_key = 'shipping/shipping_by_quantity/table'