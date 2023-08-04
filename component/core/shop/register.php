<?php

OSC_Controller::registerBind('shop', 'shop');

OSC_Observer::registerObserver('backend/collect_menu', array('Observer_shop_Backend', 'collectMenu'));

OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Shop_Backend', 'collectPermKey'));