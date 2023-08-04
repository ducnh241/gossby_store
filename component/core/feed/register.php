<?php

OSC_Controller::registerBind('feed', 'feed');
OSC_Cron::registerScheduler('feed/review', null, '0 23 * * *', ['estimate_time' => 60*30]);
OSC_Cron::registerScheduler('feed/klaviyo_newArrival', null, '0 16 * * 6', ['estimate_time' => 60*2]);

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Feed_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', ['Observer_Feed_Backend', 'collectPermKey']);
