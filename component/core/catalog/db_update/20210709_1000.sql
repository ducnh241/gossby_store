/*Delete from setting table*/
DELETE FROM `osc_core_setting` where setting_key in ('catalog/campaign/mug_11oz/price',
'catalog/campaign/mug_11oz/description',
'catalog/campaign/mug_15oz/price',
'catalog/campaign/mug_15oz/description',
'catalog/campaign/mug_twoTone/price',
'catalog/campaign/mug_twoTone/description',
'catalog/campaign/mug_insulatedCoffee/price',
'catalog/campaign/mug_insulatedCoffee/description',
'catalog/campaign/mug_enamelCampfire/price',
'catalog/campaign/mug_enamelCampfire/description',
'catalog/campaign/canvas_8x10/price',
'catalog/campaign/canvas_8x10/description',
'catalog/campaign/canvas_10x8/price',
'catalog/campaign/canvas_10x8/description',
'catalog/campaign/canvas_11x14/price',
'catalog/campaign/canvas_11x14/description',
'catalog/campaign/canvas_14x11/price',
'catalog/campaign/canvas_14x11/description',
'catalog/campaign/canvas_16x20/price',
'catalog/campaign/canvas_16x20/description',
'catalog/campaign/canvas_20x16/price',
'catalog/campaign/canvas_20x16/description',
'catalog/campaign/canvas_20x24/price',
'catalog/campaign/canvas_20x24/description',
'catalog/campaign/canvas_24x20/price',
'catalog/campaign/canvas_24x20/description',
'catalog/campaign/canvas_12x12/price',
'catalog/campaign/canvas_12x12/description',
'catalog/campaign/canvas_20x30/price',
'catalog/campaign/canvas_20x30/description',
'catalog/campaign/canvas_12x18/price',
'catalog/campaign/canvas_12x18/description',
'catalog/campaign/canvas_8x12/price',
'catalog/campaign/canvas_8x12/description',
'catalog/campaign/canvas_12x24/price',
'catalog/campaign/canvas_12x24/description',
'catalog/campaign/canvas_16x16/price',
'catalog/campaign/canvas_16x16/description',
'catalog/campaign/canvas_24x24/price',
'catalog/campaign/canvas_24x24/description',
'catalog/campaign/canvas_12x8/price',
'catalog/campaign/canvas_12x8/description',
'catalog/campaign/canvas_18x12/price',
'catalog/campaign/canvas_18x12/description',
'catalog/campaign/canvas_24x12/price',
'catalog/campaign/canvas_24x12/description',
'catalog/campaign/canvas_30x20/price',
'catalog/campaign/canvas_30x20/description',
'catalog/campaign/desktopPlaque_7x5/price',
'catalog/campaign/desktopPlaque_7x5/description',
'catalog/campaign/desktopPlaque_10x8/price',
'catalog/campaign/desktopPlaque_10x8/description',
'catalog/campaign/fleeceBlanket_30x40/price',
'catalog/campaign/fleeceBlanket_30x40/description',
'catalog/campaign/fleeceBlanket_50x60/price',
'catalog/campaign/fleeceBlanket_50x60/premium_price',
'catalog/campaign/fleeceBlanket_50x60/premium_compare_price',
'catalog/campaign/fleeceBlanket_50x60/description',
'catalog/campaign/fleeceBlanket_50x60/list_country_by_premium',
'catalog/campaign/fleeceBlanket_60x80/price',
'catalog/campaign/fleeceBlanket_60x80/description',
'catalog/campaign/notebook_5x7/price',
'catalog/campaign/notebook_5x7/description',
'catalog/campaign/pillow_18x18/price',
'catalog/campaign/pillow_18x18/description',
'catalog/campaign/pillow_16x16/price',
'catalog/campaign/pillow_16x16/description',
'catalog/campaign/puzzles_10x14/price',
'catalog/campaign/puzzles_10x14/description',
'catalog/campaign/puzzles_14x10/price',
'catalog/campaign/puzzles_14x10/description',
'catalog/campaign/t_shirt/price',
'catalog/campaign/t_shirt/description',
'catalog/campaign/classic_tee/price',
'catalog/campaign/classic_tee/description',
'catalog/campaign/bella_canvas_tee/price',
'catalog/campaign/bella_canvas_tee/description',
'catalog/campaign/next_level_tee/price',
'catalog/campaign/next_level_tee/description',
'catalog/campaign/aluminium_square_ornament/price',
'catalog/campaign/aluminium_square_ornament/description',
'catalog/campaign/aluminium_medallion_ornament/price',
'catalog/campaign/aluminium_medallion_ornament/description',
'catalog/campaign/aluminium_scalloped_ornament/price',
'catalog/campaign/aluminium_scalloped_ornament/description',
'catalog/campaign/circle_ornament/price',
'catalog/campaign/circle_ornament/description',
'catalog/campaign/heart_ornament/price',
'catalog/campaign/heart_ornament/description',
'catalog/campaign/facemask_cw/price',
'catalog/campaign/facemask_cw/pack3_price',
'catalog/campaign/facemask_cw/pack3_compare_price',
'catalog/campaign/facemask_cw/pack5_price',
'catalog/campaign/facemask_cw/pack5_compare_price',
'catalog/campaign/facemask_cw/pack10_price',
'catalog/campaign/facemask_cw/pack10_compare_price',
'catalog/campaign/facemask_cw/description',
'catalog/campaign/facemask_dpi/price',
'catalog/campaign/facemask_dpi/pack3_price',
'catalog/campaign/facemask_dpi/pack3_compare_price',
'catalog/campaign/facemask_dpi/pack5_price',
'catalog/campaign/facemask_dpi/pack5_compare_price',
'catalog/campaign/facemask_dpi/pack10_price',
'catalog/campaign/facemask_dpi/pack10_compare_price',
'catalog/campaign/facemask_dpi/description',
'catalog/campaign/facemask_dpi_kid/price',
'catalog/campaign/facemask_dpi_kid/pack3_price',
'catalog/campaign/facemask_dpi_kid/pack3_compare_price',
'catalog/campaign/facemask_dpi_kid/pack5_price',
'catalog/campaign/facemask_dpi_kid/pack5_compare_price',
'catalog/campaign/facemask_dpi_kid/pack10_price',
'catalog/campaign/facemask_dpi_kid/pack10_compare_price',
'catalog/campaign/facemask_dpi_kid/description');

/*twilio options*/
DELETE FROM `osc_core_setting` where setting_key in
('marketing/twilio/sid'
'marketing/twilio/token'
'marketing/twilio/service_id'
'marketing/twilio/sender_number'
'marketing/twilio/abandoned_cart_sms'
'marketing/twilio/enable_abandoned_cart'
'marketing/twilio/order_confirmation_sms'
'marketing/twilio/enable_order_confirmation'
'marketing/twilio/thank_you_sms'
'marketing/twilio/send_sms_thank_you_after_x_day'
'marketing/twilio/enable_thank_you');


/* Drop table twilio */
DROP TABLE `osc_twilio_sms_queue`;

/* product price level A, B, C */
DELETE FROM `osc_core_setting` where setting_key IN
('catalog/send_email_customer/levela',
'catalog/send_email_customer/levelb',
'catalog/send_email_customer/levelc');

/*Product price plus, A/B test */
DELETE FROM `osc_core_setting` where setting_key IN ('catalog/product/plus_price',
'catalog/product/plus_price_product_type',
'catalog/product/plus_price/ab_test_key',
'catalog/product/plus_price/enable',
'catalog/product/plus_price/ab_test');

/* Tag reason refund */
DELETE FROM `osc_core_setting` where setting_key IN ('tag/reason_refund/problem','tag/reason_refund/supplier');
