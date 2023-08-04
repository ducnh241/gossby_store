<?php
OSC_Controller::registerBind('klaviyo', 'klaviyo');

OSC_Observer::registerObserver('frontend/tracking', ['Observer_Klaviyo_Common', 'initialize']);

OSC_Observer::registerObserver('collect_setting_item', ['Observer_Klaviyo_Backend', 'collectSettingItem']);

OSC_Cron::registerScheduler('klaviyo/abandoned', null, '*/5 * * * *', ['estimate_time' => 60*60]);

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Klaviyo_Backend', 'collectMenu']);
OSC_Observer::registerObserver('collect_setting_section', ['Observer_Klaviyo_Backend', 'collectSettingSection']);