<?php

OSC_Observer::registerObserver('frontend/tracking', ['Observer_Google_Common', 'initialize']);

OSC_Observer::registerObserver('collect_setting_item', ['Observer_Google_Backend', 'collectSettingItem']);
