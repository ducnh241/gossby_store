<?php

OSC_Controller::registerBind('masterSync', 'masterSync');

OSC_Cron::registerScheduler('masterSync/sync', null, '* * * * *', ['estimate_time' => 60*10]);

OSC_Observer::registerObserver('setting_updated_by_user', ['Observer_MasterSync_Common', 'settingUpdated']);

OSC_Observer::registerObserver('masterSync:masterSync/store_setting', ['Observer_MasterSync_Common', 'collectSettingSyncData']);
