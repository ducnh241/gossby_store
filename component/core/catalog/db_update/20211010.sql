CREATE TABLE `osc_catalog_order_ad_info` (
	order_id INT NOT NULL DEFAULT 0,
	sref_id INT NOT NULL DEFAULT 0,
	campaign_id varchar(100) NOT NULL DEFAULT '',
	adset_id varchar(100) NOT NULL DEFAULT '',
	ad_id varchar(100) NOT NULL DEFAULT '',
	ad_name varchar(255) NULL,
	adset_name varchar(255) NULL,
	utm_source varchar(255) NULL,
	utm_medium varchar(255) NULL,
	utm_campaign varchar(255) NULL,
	utm_content varchar(255) NULL,
  PRIMARY KEY (`order_id`)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;
