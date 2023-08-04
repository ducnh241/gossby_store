<?php
OSC_Controller::registerBind('marketing', 'marketing');

OSC_Observer::registerObserver('collect_setting_section', ['Observer_Marketing_Backend', 'collectSettingSection']);
OSC_Observer::registerObserver('collect_setting_item', ['Observer_Marketing_Backend', 'collectSettingItem']);
OSC_Observer::registerObserver('collect_setting_type', ['Observer_Marketing_Backend', 'collectSettingType']);