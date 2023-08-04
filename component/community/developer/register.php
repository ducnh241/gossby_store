<?php
OSC_Controller::registerBind('developer', 'developer');

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Developer_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Developer_Backend', 'collectPermKey'));


//OSC_Cron::registerScheduler('developer/exportDataByCEO', null, '* * * * *', ['estimate_time' => 60 * 60]);


