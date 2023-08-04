DROP TABLE IF EXISTS osc_catalog_product_stock_option;
CREATE TABLE osc_catalog_product_stock_option (
  record_id int(11) NOT NULL AUTO_INCREMENT,
  ukey varchar(255) NOT NULL DEFAULT '',
  instock int(11) unsigned NOT NULL DEFAULT 0,
  solds int(11) UNSIGNED NOT NULL DEFAULT 0,
  solds_date int(8) NOT NULL DEFAULT 0,
  added_timestamp int(10) NOT NULL DEFAULT 0,
  modified_timestamp int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (record_id),
  UNIQUE INDEX ukey_UNIQUE (ukey ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
