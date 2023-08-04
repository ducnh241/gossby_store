<?php

class Observer_CrossSell_Common {
    public static function checkoutSummaryItem($line_item) {
        if (!$line_item->isCrossSellMode()) {
            return null;
        }
        return 'crossSell/catalog/checkout/summary/item';
    }

    public static function orderVerifyLineItemToCreate($params) {
        /* @var $line_item Model_Catalog_Order_Item */

        foreach ($params['line_items'] as $cart_item_id => $line_item) {
            $cart_item = OSC::model('catalog/cart_item')->load($cart_item_id);

            if (!$cart_item->isCrossSellMode()) {
                continue;
            }
            $crossSellData = $cart_item->getCrossSellData();
            $custom_data = $cart_item->data['custom_data'];
            $custom_data_idx = $cart_item->getCrossSellDataIdx();
            $crossSellData['print_template']['segment_source']['front']['source']['timestamp'] = time();
            $custom_data[$custom_data_idx]['data'] = $crossSellData;

            $line_item_meta = OSC::model('catalog/order_item_meta')->setData([
                'custom_data' => $custom_data
            ])->save();

            $product_type = $cart_item->getProductType()->getUkey() ?? '';

            $design_url = new stdClass();

            $design_url->{'0'} = ['default' => $crossSellData['print_template']['segment_source']['front']['source']['design_url']];

            $line_item->setData([
                'order_item_meta_id' => $line_item_meta->getId(),
                'product_type' => $product_type,
                'image_url' => $crossSellData['print_template']['segment_source']['front']['source']['mockup_url'],
                'design_url' => $design_url
            ]);
        }
    }
}
