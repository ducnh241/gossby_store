CREATE TABLE osc_price_exchange_rate (
  id int(11) NOT NULL AUTO_INCREMENT,
  exchange_rate varchar(20) NOT NULL,
  value float NOT NULL,
  symbol varchar(6) NOT NULL,
  added_timestamp int(10) NOT NULL,
  update_timestamp int(10) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY exchange_rate_UNIQUE (exchange_rate)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8




ALTER TABLE osc_price_exchange_rate
CHANGE COLUMN exchange_rate currency_code VARCHAR(20) NOT NULL ,
CHANGE COLUMN value exchange_rate DECIMAL(20,10) UNSIGNED NOT NULL ;

ALTER TABLE osc_price_exchange_rate DROP INDEX exchange_rate_UNIQUE;
ALTER TABLE osc_price_exchange_rate ADD UNIQUE INDEX currency_code_UNIQUE (currency_code ASC);