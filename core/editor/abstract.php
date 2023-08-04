<?php

class OSC_Editor_Abstract extends OSC_Object {

    /**
     *
     * @var OSC_Editor 
     */
    protected $_editor = null;

    /**
     * 
     * @param OSC_Editor $editor
     * @param array $config
     * @return \OSC_Editor_Abstract
     */
    public function initialize($editor, $config) {
        $this->_editor = $editor;

        if (is_array($config) && count($config) > 0) {
            foreach ($config as $k => $v) {
                if (substr($k, 0, 1) === '_') {
                    continue;
                }

                if (!property_exists($this, $k)) {
                    continue;
                }

                $this->$k = $v;
            }
        }

        return $this;
    }

}
