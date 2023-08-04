ALTER TABLE `osc_catalog_cart_item` 
ADD CONSTRAINT `cart_id_23423asdf`
  FOREIGN KEY (`cart_id`)
  REFERENCES `osc_catalog_cart` (`cart_id`)
  ON DELETE CASCADE
  ON UPDATE CASCADE;