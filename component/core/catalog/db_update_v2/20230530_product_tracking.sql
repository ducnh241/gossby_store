DROP TABLE IF EXISTS `osc_report_product_tracking`;
CREATE TABLE osc_report_product_tracking
(
    record_id          INT NOT NULL AUTO_INCREMENT,
    report_key         VARCHAR(20) NOT NULL DEFAULT '',
    product_id         INT NOT NULL DEFAULT 0,
    report_value       INT(11) NOT NULL DEFAULT 0,
    date               INT(8) NOT NULL DEFAULT 0,
    added_timestamp    INT NOT NULL DEFAULT 0,
    modified_timestamp INT NOT NULL DEFAULT 0,
    PRIMARY KEY (record_id),
    UNIQUE KEY `product_tracking_unique` (`report_key`,`product_id`,`date`)
);