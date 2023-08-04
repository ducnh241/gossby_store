<?php

OSC_Controller::registerBind('snapchat', 'snapchat');
OSC_Observer::registerObserver('frontend/tracking', ['Observer_Snapchat_Common', 'initialize']);
