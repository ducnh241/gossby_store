<?php

OSC_Controller::registerBind('addon', 'addon');

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Addon_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', ['Observer_Addon_Backend', 'collectPermKey']);

OSC_Observer::registerObserver('model__addon_service__after_save', ['Observer_Addon_Cache', 'resetCache']);
OSC_Observer::registerObserver('tracking_view_ab_test_function', ['Observer_Addon_Tracking', 'trackingView']);
OSC_Observer::registerObserver('catalog/orderCreate', ['Observer_Addon_Tracking', 'trackingOrder']);

