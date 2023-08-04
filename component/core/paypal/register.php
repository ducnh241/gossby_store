<?php

OSC_Controller::registerBind('paypal', 'paypal');

OSC_Observer::registerObserver('catalog/collect_checkout_btn', array('Observer_Paypal_Common', 'renderCartCheckoutBtn'));
OSC_Observer::registerObserver('catalog/collect_payment_method', array('Observer_Paypal_Common', 'collectMethods'));
