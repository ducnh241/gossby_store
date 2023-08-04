<?php

OSC_Controller::registerBind('smsMarketing', 'smsMarketing');
OSC_Observer::registerObserver('collect_setting_item', ['Observer_SmsMarketing_Backend', 'collectSettingItem']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_SmsMarketing_Backend', 'collectPermKey'));
OSC_Cron::registerScheduler('smsMarketing/abandoned', null, '*/15 * * * *', ['estimate_time' => 60 * 60]);