<?php

OSC_Observer::registerObserver('catalog/collect_payment_method', array('Observer_PaypalPro_Common', 'collectMethods'));
