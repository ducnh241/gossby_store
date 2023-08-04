<?php

OSC_Controller::registerBind('user', 'user');

OSC_Observer::registerObserver('backend/collect_menu', array('Observer_User_Backend', 'collectMenu'));

OSC_Observer::registerObserver('catalog/afterPlaceOrder', ['Observer_User_Common', 'afterPlaceOrder']);

