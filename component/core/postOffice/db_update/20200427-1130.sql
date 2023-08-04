ALTER TABLE osc_post_office_subscriber ADD COLUMN flag_action tinyint(1) NOT NULL DEFAULT 0 AFTER newsletter;

ALTER TABLE osc_post_office_subscriber ADD COLUMN confirm tinyint(1) NOT NULL DEFAULT 0 AFTER flag_action;

CREATE TABLE osc_subscribers_export_draft (
  record_id bigint(20) NOT NULL AUTO_INCREMENT,
  export_key varchar(32) NOT NULL,
  subscriber_id int(10) NOT NULL,
  export_data mediumtext NOT NULL,
  added_timestamp int(10) NOT NULL,
  PRIMARY KEY (record_id)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8

ALTER TABLE osc_post_office_subscriber ADD COLUMN content text default  null AFTER flag_action;

ALTER TABLE osc_post_office_subscriber ADD COLUMN full_name varchar(255) NULL DEFAULT null AFTER email;