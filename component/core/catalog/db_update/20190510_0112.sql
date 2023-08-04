ALTER TABLE `osc_catalog_cart` ADD COLUMN `abandoned_email_sents` INT NOT NULL DEFAULT 0 AFTER `billing_zip`;
DROP TABLE `osc_catalog_checkout_abandoned_email`, `osc_catalog_checkout_abandoned_email_clicked`, `osc_catalog_order_thankyou_email`, `osc_catalog_order_thankyou_email_clicked`;
ALTER TABLE `osc_catalog_cart` ADD COLUMN `abandoned_sms_sent` TINYINT(1) NOT NULL DEFAULT 0 AFTER `abandoned_email_sents`;
