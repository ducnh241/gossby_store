<?php

class Observer_PersonalizedDesign_Renderer {

    public static function checkoutSummaryItem($line_item) {
        return OSC::helper('personalizedDesign/common')->fetchCustomDataIndex($line_item->data['custom_data']) === null ? null : 'personalizedDesign/catalog/checkout/summary/item';
    }

    public static function cartSummaryItem($line_item) {
        return OSC::helper('personalizedDesign/common')->fetchCustomDataIndex($line_item->data['custom_data']) === null ? null : 'personalizedDesign/catalog/cart/summary/item';
    }

    public static function emailOrderSummaryItem($line_item) {
        return OSC::helper('personalizedDesign/common')->fetchCustomDataIndex($line_item->data['custom_data']) === null ? null : 'personalizedDesign/catalog/email_template/html/order/summary/lineItem';
    }

}
