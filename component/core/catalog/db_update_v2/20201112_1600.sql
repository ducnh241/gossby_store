ALTER TABLE osc_product_type_option
ADD COLUMN auto_select TINYINT(1) NULL DEFAULT 0 COMMENT '0: Normal\n1: auto selected (T-shirt)\n2: main select (Apply for color)' AFTER type;