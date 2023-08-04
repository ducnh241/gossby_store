DROP TABLE IF EXISTS osc_supplier_location_rel;

CREATE TABLE osc_supplier_location_rel (
  record_id int(11) NOT NULL AUTO_INCREMENT,
  supplier_id int(11) UNSIGNED NOT NULL DEFAULT 0,
  product_type_variant_id int(11) UNSIGNED NOT NULL DEFAULT 0,
  location_data varchar(255) NOT NULL DEFAULT '',
  added_timestamp int(10) NOT NULL DEFAULT 0,
  modified_timestamp int(10) NOT NULL DEFAULT 0,
  UNIQUE INDEX `item_INDEX` (`supplier_id` ASC, `product_type_variant_id` ASC, `location_data` ASC),
  PRIMARY KEY (record_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
