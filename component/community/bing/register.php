<?php

OSC_Controller::registerBind('bing', 'bing');
OSC_Observer::registerObserver('frontend/tracking', ['Observer_Bing_Common', 'initialize']);