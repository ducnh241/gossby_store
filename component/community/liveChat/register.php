<?php
OSC_Controller::registerBind('liveChat', 'liveChat');
OSC_Observer::registerObserver('frontend_initialize', ['Observer_LiveChat_Common', 'initialize']);
OSC_Template::registerComponent('live_chat', ['type' => 'template', 'data' => '[frontend_template]livechat/common']);
