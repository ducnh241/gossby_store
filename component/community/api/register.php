<?php

OSC_Controller::registerBind('api', 'api');

OSC_Observer::registerObserver('parse_shorten_url', array('Observer_Api_Common', 'parseShortenUrl'));
