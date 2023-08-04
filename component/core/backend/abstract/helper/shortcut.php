<?php

abstract class Abstract_Backend_Helper_Shortcut extends OSC_Object {

    abstract public function getInfo($shortcut_key, $shortcut_data);

    abstract public function preShortcutData($data);
    
}
