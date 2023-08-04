<?php

class Helper_Backend_Backend_Search_Result extends Abstract_Backend_Helper_Search_Result {

    public function render($doc) {
        $tpl = OSC::controller()->getTemplate();

        switch($doc['item_group']) {
            case 'product':
                $product = OSC::model('catalog/product')
                    ->getCollection()
                    ->addCondition('product_id', $doc['item_id'])
                    ->load()
                    ->first();
                if ($product) {
                    return $tpl->build('backend/search/result/product', ['product' => $product]);
                } else {
                    return;
                }
                break;
            case 'order':
                $order = OSC::model('catalog/order')
                    ->getCollection()
                    ->addCondition('order_id', $doc['item_id'])
                    ->load()
                    ->first();
                if ($order) {
                    return $tpl->build('backend/search/result/order', ['order' => $order]);
                } else {
                    return;
                }
                break;
            case 'personalizedDesign':
                $design = OSC::model('personalizedDesign/design')
                    ->getCollection()
                    ->addCondition('design_id', $doc['item_id'])
                    ->load()
                    ->first();
                if ($design) {
                    return $tpl->build('backend/search/result/personalDesign', ['design' => $design]);
                } else {
                    return;
                }
                break;
        }
    }

}
