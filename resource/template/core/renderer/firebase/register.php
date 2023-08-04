<?php

/* @var $this OSC_Template */

$this->push('<link rel="manifest" href="' . OSC::$base_url . '/manifest.json">', 'head_tags');
$this->push(array('https://www.gstatic.com/firebasejs/5.7.1/firebase-app.js', 'https://www.gstatic.com/firebasejs/5.7.1/firebase-messaging.js', '[core]firebase/initialize.js'), 'js');