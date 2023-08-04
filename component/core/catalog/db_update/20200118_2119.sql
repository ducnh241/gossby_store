CREATE TABLE osc_catalog_order_process_v2 (
  record_id INT(11) NOT NULL AUTO_INCREMENT,
  ukey VARCHAR(45) NOT NULL,
  member_id INT(10) NOT NULL,
  order_id INT(11) NOT NULL,
  line_items TEXT NOT NULL,
  quantity INT(10) NOT NULL,
  service VARCHAR(45) NULL DEFAULT NULL,
  queue_flag TINYINT(1) NOT NULL DEFAULT 0,
  error_message TEXT NULL DEFAULT NULL,
  added_timestamp INT(10) NOT NULL,
  modified_timestamp INT(10) NOT NULL,
  PRIMARY KEY (record_id),
  UNIQUE INDEX ukey_UNIQUE (ukey ASC)) ENGINE=InnoDB DEFAULT CHARSET=utf8;


ALTER TABLE osc_catalog_order_fulfillment
ADD COLUMN process_ukey VARCHAR(45) NULL AFTER service;