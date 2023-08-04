<?php

class OSC_Editor_TextColor extends OSC_Editor_Abstract {

    /**
     * 
     * @param OSC_Editor $editor
     * @param array $config
     * @return \OSC_Editor_TextColor
     */
    public function initialize($editor, $config) {
        parent::initialize($editor, $config);

        $editor->enableStyle('color', 'span');

        return $this;
    }

}
