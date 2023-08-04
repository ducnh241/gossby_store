<?php

OSC_Controller::registerBind('srefReport', 'srefReport');

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_SrefReport_Backend', 'collectMenu']);
OSC_Observer::registerObserver('collect_setting_section', ['Observer_SrefReport_Backend', 'collectSettingSection']);
OSC_Observer::registerObserver('collect_setting_item', ['Observer_SrefReport_Backend', 'collectSettingItem']);
OSC_Observer::registerObserver('collect_setting_type', ['Observer_SrefReport_Backend', 'collectSettingType']);
OSC_Observer::registerObserver('user/permmask/collect_keys', array('Observer_SrefReport_Backend', 'collectPermKey'));
