<?php

class OSC_Editor_AutosizeTextbox extends OSC_Editor_Abstract {

    public $max_item = 0;
    public $control_align_enable = true;
    public $control_align_level_enable = 'initial';
    public $control_zoom_enable = true;
    public $control_zoom_levels = 'initial';
    public $control_zoom_align_levels = 'initial';
    public $min_fontsize = 16;
    public $max_fontsize = 32;

    /**
     * 
     * @param OSC_Editor $editor
     * @param array $config
     * @return \OSC_Editor_AutosizeTextbox
     */
    public function initialize($editor, $config) {
        parent::initialize($editor, $config);

        $this->max_item = intval($this->max_item);
        
        $this->min_fontsize = intval($this->min_fontsize);
        $this->max_fontsize = intval($this->max_fontsize);

        if ($this->min_fontsize < 10) {
            $this->min_fontsize = 10;
        }

        if ($this->max_fontsize < 10) {
            $this->max_fontsize = 10;
        }

        if ($this->max_fontsize == $this->min_fontsize) {
            $this->max_fontsize += 10;
        } else if ($this->min_fontsize > $this->max_fontsize) {
            $buff = $this->max_fontsize;
            $this->max_fontsize = $this->min_fontsize;
            $this->min_fontsize = $buff;
        }

        $editor->addObserver('packEditorElements', array($this, 'packEditorElements'));

        return $this;
    }

    /**
     * 
     * @param DOMElement $parent_node
     */
    public function packEditorElements($parent_node) {
        $xpath = new DOMXPath($parent_node->ownerDocument);

        $counter = 0;

        /* @var $element DOMElement */
        foreach ($xpath->query('.//*[contains(@class,"osc-editor-autosize-textbox")]', $parent_node) as $element) {
            if ($element->nodeType !== XML_ELEMENT_NODE || !$this->_editor->nodeIsAllowedBlock($element) || $element->childNodes->length < 1) {
                continue;
            }

            /* @var $content_node DOMElement */
            $content_node = null;

            /* @var $child DOMElement */
            foreach ($element->childNodes as $child) {
                if (strpos($child->getAttribute('class'), 'content') !== false) {
                    $content_node = $child;
                    break;
                }
            }

            if (!$content_node) {
                continue;
            }

            try {
                $content_html = OSC::core('editor', 'editor_autosize_textbox_cleaner')->config($this->_editor->getConfig())->clean($this->_editor->nodeGetInnerHtml($content_node));
            } catch (Exception $ex) {
                continue;
            }

            if (!$content_html) {
                continue;
            }

            $this->_editor->nodeSetInnerHtml($content_node, $content_html);
            
            $this->_editor->nodeAddSafeAttribute($element, array('min-fs' => $this->min_fontsize, 'max-fs' => $this->max_fontsize, 'data-insert-cb' => 'editorAutosizeTextboxRenderCallback'));
            $this->_editor->nodeAddSafeClass($element, array('osc-editor-autosize-textbox'));
            $this->_editor->nodeAddSafeClass($content_node, array('content'));
            $this->_editor->nodeAddSafeContent($content_node, $content_html);

            $this->_editor->nodeMarkAsSafe($content_node);

            $controls = array();

            if ($this->control_zoom_enable) {
                $controls[] = array('key' => 'zoom', 'config' => array('levels' => $this->control_zoom_levels, 'align_levels' => $this->control_zoom_align_levels));
            }

            if ($this->control_align_enable) {
                $controls[] = array('key' => 'align', 'config' => array('level_enable' => $this->control_align_level_enable));
            }

            if (count($controls) > 0) {
                $this->_editor->processEditorElementControl($element, $controls);
            }

            $this->_editor->packEditorElement($element, true);

            $counter ++;

            if ($this->max_item > 0 && $counter == $this->max_item) {
                break;
            }
        }
    }

}
