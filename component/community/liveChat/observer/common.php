<?php
class Observer_LiveChat_Common {
    public static function initialize() {
        OSC::helper('frontend/template')->addComponent('live_chat');
    }
}
