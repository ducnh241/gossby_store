<?php

OSC_Controller::registerBind('personalizedDesign', 'personalizedDesign');

OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_PersonalizedDesign_Backend', 'collectPermKey'));
OSC_Observer::registerObserver('backend/collect_menu', ['Observer_PersonalizedDesign_Backend', 'collectMenu']);

OSC_Observer::registerObserver('catalog/product/postFrmRender', ['Observer_PersonalizedDesign_Backend', 'productPostFrmRender']);
OSC_Observer::registerObserver('catalog/product/postFrmSaveData', ['Observer_PersonalizedDesign_Backend', 'productPostFrmSaveData']);

OSC_Observer::registerObserver('catalog/cart_lineItem_customize', ['Observer_PersonalizedDesign_Frontend', 'validate']);

OSC_Observer::registerObserver('model__personalized_design__after_save', ['Observer_PersonalizedDesign_Common', 'resetCache']);


OSC_Template::registerComponent(
    ['key' => 'personalized_design', 'default' => true], ['type' => 'js', 'data' => '[frontend/template]personalizedDesign/common.js'], ['type' => 'css', 'data' => ['[frontend/template]personalizedDesign/common.scss']]
);

OSC_Observer::registerObserver('catalog/afterPlaceOrder', ['Observer_PersonalizedDesign_Analytic', 'afterPlaceOrder']);
OSC_Observer::registerObserver('catalog/preCustomerEditOrder', ['Observer_PersonalizedDesign_Frontend', 'preCustomerEditOrder']);
OSC_Observer::registerObserver('catalog/render/checkout_summary/item', ['Observer_PersonalizedDesign_Renderer', 'checkoutSummaryItem']);
OSC_Observer::registerObserver('catalog/render/cart_summary/item', ['Observer_PersonalizedDesign_Renderer', 'cartSummaryItem']);
OSC_Observer::registerObserver('catalog/render/email_template/order_summary/item', ['Observer_PersonalizedDesign_Renderer', 'emailOrderSummaryItem']);

OSC_Observer::registerObserver('catalog/order_collect_design', ['Observer_PersonalizedDesign_Common', 'orderCollectDesign']);


OSC_Cron::registerScheduler('personalizedDesign/analyticProcessQueue', null, '* * * * *', ['estimate_time' => 60*60]);
OSC_Cron::registerScheduler('personalizedDesign/sync', ['process_key' => 1], '* * * * *', ['estimate_time' => 60*60]);
OSC_Cron::registerScheduler('personalizedDesign/sync', ['process_key' => 2], '* * * * *', ['estimate_time' => 60*60]);
OSC_Cron::registerScheduler('personalizedDesign/sync', ['process_key' => 3], '* * * * *', ['estimate_time' => 60*60]);
OSC_Cron::registerScheduler('personalizedDesign/sync', ['sync_type' => ["font","image","imagelib"]], '* * * * *', ['estimate_time' => 60*60]);

//OSC_Observer::registerObserver('catalog/orderCreate', ['Observer_PersonalizedDesign_Frontend', 'checkOverflowPersonalized']);

//OSC_Cron::registerScheduler('personalizedDesign/checkPersonalizedOverflow', null, '* * * * *', ['estimate_time' => 60*10]);
OSC_Cron::registerScheduler('personalizedDesign/deleteRerenderLog', null, '0 0 * * 0', ['estimate_time' => 60*10]);
