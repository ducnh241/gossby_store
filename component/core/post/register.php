<?php

OSC_Controller::registerBind('post', 'post');

OSC_Observer::registerObserver('navigation/collect_item_type', array('Observer_Post_Backend', 'navCollectItemType'));
OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Post_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Post_Backend', 'collectPermKey'));
OSC_Observer::registerObserver('collect_setting_section', ['Observer_Post_Backend', 'collectSettingSection']);
OSC_Observer::registerObserver('collect_setting_item', ['Observer_Post_Backend', 'collectSettingItem']);

OSC_Cron::registerScheduler('post/rss', null, '@daily', ['estimate_time' => 60*60]);
