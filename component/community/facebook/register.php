<?php

OSC_Controller::registerBind('facebook', 'facebook');
OSC_Observer::registerObserver('frontend/tracking', ['Observer_Facebook_Common', 'initialize']);
OSC_Observer::registerObserver('frontend/tracking', ['Observer_Facebook_Common', 'setFacebookClickId']);

//OSC_Cron::registerScheduler('facebook/executeSetEvent', null, '*/1 * * * *', ['estimate_time' => 60*30]);

OSC_Observer::registerObserver('navigation/collect_item_type', ['Observer_Facebook_Backend', 'navCollectItemType']);
OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Facebook_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', ['Observer_Facebook_Backend', 'collectPermKey']);
