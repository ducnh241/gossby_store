<?php

OSC_Controller::registerBind('article', 'article');

OSC_Observer::registerObserver('backend/collect_menu', ['Observer_Article_Backend', 'collectMenu']);
