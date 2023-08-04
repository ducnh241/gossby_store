<?php

OSC_Controller::registerBind('navigation', 'navigation');

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Navigation_Backend', 'collectMenu']);
OSC_Observer::registerObserver('collect_setting_type', ['Observer_Navigation_Backend', 'collectSettingType']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Navigation_Backend', 'collectPermKey'));
