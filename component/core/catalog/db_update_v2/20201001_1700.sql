ALTER TABLE osc_product_type ADD COLUMN short_title VARCHAR (255) NULL AFTER title;
ALTER TABLE osc_product_type ADD COLUMN identifier VARCHAR (500) NULL AFTER short_title;