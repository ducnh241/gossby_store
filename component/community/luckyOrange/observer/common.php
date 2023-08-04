<?php

class Observer_LuckyOrange_Common {

    public static function initialize() {
        return;
        if (!OSC::helper('core/setting')->get('tracking/luckyorange')) {
            return;
        }

        $tracking_code = OSC::helper('core/setting')->get('tracking/luckyorange/code');

        if ($tracking_code) {
            OSC::helper('frontend/template')->push($tracking_code, 'js_init');
        }
    }

    public static function collectReactJSCommonLayout($params) {
        if (!OSC::helper('core/setting')->get('tracking/luckyorange')) {
            return;
        }

        $tracking_code = OSC::helper('core/setting')->get('tracking/luckyorange/code');

        if ($tracking_code) {
            $params['data']['luckyorange'] = $tracking_code;
        }
    }

}
