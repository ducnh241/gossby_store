<?php

OSC_Controller::registerBind('designManager', 'designManager');

OSC_Observer::registerObserver('catalog/order_collect_design', ['Observer_DesignManager_Backend', 'orderCollectDesign']);
