ALTER TABLE `osc_catalog_cart_item` 
DROP FOREIGN KEY `variant_id_2132`,
DROP FOREIGN KEY `cart_id_23423asdf`;
ALTER TABLE `osc_catalog_cart_item` 
DROP INDEX `variant_id_idx` ,
DROP INDEX `variant_id` ;

ALTER TABLE `osc_catalog_cart_item` 
ADD UNIQUE INDEX `cart_variant_id` (`cart_id` ASC, `variant_id` ASC),
ADD INDEX `variant_id` (`variant_id` ASC);

ALTER TABLE `osc_catalog_order_thankyou_email` 
DROP FOREIGN KEY `order_id_1234`,
DROP FOREIGN KEY `discount_code_id_1234`;
ALTER TABLE `osc_catalog_order_thankyou_email` 
DROP INDEX `discount_code_id_UNIQUE` ,
DROP INDEX `order_id_UNIQUE` ;

ALTER TABLE `osc_catalog_order_thankyou_email` 
ADD UNIQUE INDEX `order_id` (`order_id` ASC),
ADD UNIQUE INDEX `discount_code_id` (`discount_code_id` ASC);

ALTER TABLE `osc_catalog_order_thankyou_email_clicked` 
DROP FOREIGN KEY `email_id_4324asdads`;
ALTER TABLE `osc_catalog_order_thankyou_email_clicked` 
DROP INDEX `email_id` ;

ALTER TABLE `osc_catalog_order_thankyou_email_clicked` 
ADD INDEX `email_id` (`email_id` ASC);

ALTER TABLE `osc_catalog_checkout_abandoned_email` 
DROP FOREIGN KEY `discount_code_id_q324412`,
DROP FOREIGN KEY `cart_id`;
ALTER TABLE `osc_catalog_checkout_abandoned_email` 
DROP INDEX `discount_code_id_UNIQUE` ,
DROP INDEX `cart_id_UNIQUE` ;

ALTER TABLE `osc_catalog_checkout_abandoned_email` 
ADD UNIQUE INDEX `cart_id` (`cart_id` ASC),
ADD INDEX `discount_code_id` (`discount_code_id` ASC);

ALTER TABLE `osc_catalog_checkout_abandoned_email` 
ADD COLUMN `ukey` VARCHAR(27) NOT NULL AFTER `email_id`,
ADD COLUMN `clicks` INT NOT NULL DEFAULT 0 AFTER `opened_flag`,
ADD UNIQUE INDEX `ukey_UNIQUE` (`ukey` ASC);

CREATE TABLE `osc_catalog_checkout_abandoned_email_clicked` (
  `record_id` int(11) NOT NULL AUTO_INCREMENT,
  `email_id` int(11) NOT NULL,
  `referer_url` varchar(255) DEFAULT NULL,
  `clicked_url` varchar(255) NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`record_id`),
  KEY `email_id` (`email_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
