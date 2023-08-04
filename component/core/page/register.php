<?php

OSC_Controller::registerBind('page', 'page');

OSC_Observer::registerObserver('navigation/collect_item_type', array('Observer_Page_Backend', 'navCollectItemType'));
OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Page_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Page_Backend', 'collectPermKey'));
