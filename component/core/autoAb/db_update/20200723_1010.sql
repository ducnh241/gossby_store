CREATE TABLE osc_auto_ab_product_price_group (
  record_id int(11) NOT NULL AUTO_INCREMENT,
  group_country varchar(1000) NOT NULL,
  fees int(3) UNSIGNED NOT NULL DEFAULT 0,
  added_timestamp int(10) NOT NULL DEFAULT 0,
  modified_timestamp int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (record_id),
  UNIQUE INDEX group_country_UNIQUE (group_country ASC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE osc_auto_ab_product_price_condition (
  record_id int(11) NOT NULL AUTO_INCREMENT,
  group_id int(11) UNSIGNED NOT NULL,
  product_type varchar(50) NOT NULL DEFAULT '',
  condition_start varchar(50) NOT NULL DEFAULT '',
  condition_end varchar(50) NOT NULL DEFAULT '',
  price_range varchar(2000) NOT NULL DEFAULT '',
  base_cost int(10) UNSIGNED NOT NULL DEFAULT 0,
  added_timestamp int(10) NOT NULL DEFAULT 0,
  modified_timestamp int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE osc_auto_ab_product_price_tracking (
  record_id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) UNSIGNED NOT NULL,
  condition_id int(11) UNSIGNED NOT NULL DEFAULT 0,
  order_item_id int(11) UNSIGNED NOT NULL,
  product_type varchar(50) NOT NULL DEFAULT '',
  price int(11) UNSIGNED NOT NULL DEFAULT 0,
  quantity int(10) UNSIGNED NOT NULL DEFAULT 0,
  added_timestamp int(10) NOT NULL DEFAULT 0,
  modified_timestamp int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE osc_auto_ab_product_price_log (
  record_id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) UNSIGNED NOT NULL,
  product_type varchar(50) NOT NULL DEFAULT '',
  note varchar(1000) NOT NULL DEFAULT '',
  added_timestamp int(10) NOT NULL DEFAULT 0,
  modified_timestamp int(10) NOT NULL DEFAULT 0,
  PRIMARY KEY (record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
