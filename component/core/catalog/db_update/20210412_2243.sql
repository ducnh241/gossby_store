ALTER TABLE osc_catalog_product ADD COLUMN seo_tags TEXT DEFAULT NULL AFTER meta_tags;
ALTER TABLE osc_catalog_collection ADD COLUMN custom_title VARCHAR(255) DEFAULT NULL AFTER title;

CREATE TABLE `osc_catalog_collection_bulk_queue` (
  `queue_id` int NOT NULL AUTO_INCREMENT,
  `ukey` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `member_id` int NOT NULL,
  `action` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `queue_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL,
  `queue_flag` tinyint(1) NOT NULL DEFAULT 0,
  `error` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL,
  `added_timestamp` int NOT NULL DEFAULT 0,
  `modified_timestamp` int NOT NULL DEFAULT 0,
  PRIMARY KEY (`queue_id`) USING BTREE,
  UNIQUE INDEX `ukey_UNIQUE`(`ukey`) USING BTREE
);