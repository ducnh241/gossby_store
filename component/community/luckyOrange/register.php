<?php

OSC_Observer::registerObserver('frontend_initialize', ['Observer_LuckyOrange_Common', 'initialize']);
OSC_Observer::registerObserver('reactjs_collect_common_layout', ['Observer_LuckyOrange_Common', 'collectReactJSCommonLayout']);

OSC_Observer::registerObserver('collect_setting_item', ['Observer_LuckyOrange_Backend', 'collectSettingItem']);
