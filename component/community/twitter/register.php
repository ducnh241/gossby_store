<?php

OSC_Observer::registerObserver('frontend/tracking', ['Observer_Twitter_Common', 'initialize']);

OSC_Observer::registerObserver('collect_setting_item', ['Observer_Twitter_Backend', 'collectSettingItem']);
