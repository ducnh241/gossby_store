<?php
OSC_Controller::registerBind('filter', 'filter');

OSC_Observer::registerObserver('collect_setting_section', ['Observer_Filter_Backend', 'collectSettingSection']);
OSC_Observer::registerObserver('collect_setting_item', ['Observer_Filter_Backend', 'collectSettingItem']);
OSC_Observer::registerObserver('collect_setting_type', ['Observer_Filter_Backend', 'collectSettingType']);

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Filter_Backend', 'collectMenu']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_Filter_Backend', 'collectPermKey'));

OSC_Cron::registerScheduler('filter/settingFilterCollection', ['process_key' => 'settingFilterCollection'], '0 10 * * *', ['estimate_time' => 60 * 60 * 2]);
OSC_Cron::registerScheduler('filter/exportKeyword', ['process_key' => 'exportKeyword'], '0 2 */3 * *', ['estimate_time' => 60 * 60]);
