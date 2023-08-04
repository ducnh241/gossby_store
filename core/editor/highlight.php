<?php

class OSC_Editor_Highlight extends OSC_Editor_Abstract {

    /**
     * 
     * @param OSC_Editor $editor
     * @param array $config
     * @return \OSC_Editor_Highlight
     */
    public function initialize($editor, $config) {
        parent::initialize($editor, $config);

        $editor->enableStyle('background-color', 'span');

        return $this;
    }

}
