/* Update order item*/
UPDATE osc_catalog_order_item SET other_quantity = 0 WHERE other_quantity = 1
AND product_type NOT IN ('facemask-dpi', 'facemask-cw', 'facemask_without_filter', 'facemask_with_filter');

UPDATE osc_catalog_order_item SET other_quantity = 1 WHERE other_quantity = 0
AND product_type = 'ornament_medallion'
AND added_timestamp > 1606473915
AND added_timestamp < 1606732505;
