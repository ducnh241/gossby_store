<?php

OSC_Observer::registerObserver('frontend/tracking', ['Observer_Pinterest_Common', 'initialize']);

OSC_Observer::registerObserver('collect_setting_item', ['Observer_Pinterest_Backend', 'collectSettingItem']);
