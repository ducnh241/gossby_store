<?php

OSC_Observer::registerObserver('catalog/collect_payment_method', array('Observer_Stripe_Common', 'collectMethods'));
