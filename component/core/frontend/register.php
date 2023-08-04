<?php

OSC_Controller::registerBind('frontend', 'frontend');
OSC_Controller::registerDefaultRequestString('backend');

OSC_Template::registerComponent(['key' => 'frontend', 'depends' => 'catalog_frontend'], array('type' => 'js', 'data' => '[core]core/search.js'));

OSC_Observer::registerObserver('collect_setting_section', ['Observer_Frontend_Backend', 'collectSettingSection']);
OSC_Observer::registerObserver('collect_setting_item', ['Observer_Frontend_Backend', 'collectSettingItem']);
OSC_Observer::registerObserver('collect_setting_type', ['Observer_Frontend_Backend', 'collectSettingType']);
OSC_Observer::registerObserver('check_ip', ['Observer_Frontend_Backend', 'checkAddressIp']);

