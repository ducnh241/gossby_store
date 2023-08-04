<?php

OSC_Controller::registerBind('autoAb', 'autoAb');

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_AutoAb_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', ['Observer_AutoAb_Backend', 'collectPermKey']);

OSC_Observer::registerObserver('catalog/orderCreate', ['Observer_AutoAb_Tracking', 'orderCreate']);