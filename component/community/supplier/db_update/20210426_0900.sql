CREATE TABLE osc_supplier_location_rel_tmp (
    id int(11) NOT NULL AUTO_INCREMENT,
    supplier_id int(11) UNSIGNED NOT NULL DEFAULT 0,
    location_data varchar(255) NOT NULL DEFAULT '',
    variant_data VARCHAR(2000) NOT NULL DEFAULT '',
    added_timestamp int(10) NOT NULL DEFAULT 0,
    modified_timestamp int(10) NOT NULL DEFAULT 0,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
