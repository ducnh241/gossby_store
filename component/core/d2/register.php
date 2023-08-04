<?php

OSC_Controller::registerBind('d2', 'd2');

OSC_Cron::registerScheduler('d2/scanAirtableDesign', null, '*/10 * * * *', ['estimate_time' => 60*60]);
OSC_Cron::registerScheduler('d2/syncFlowReply', null, '*/2 * * * *', ['estimate_time' => 60*60]);

OSC_Observer::registerObserver('navigation/collect_item_type', array('Observer_D2_Backend', 'navCollectItemType'));
OSC_Observer::registerObserver('backend/collect_menu', ['Observer_D2_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_D2_Backend', 'collectPermKey'));