<?php

class OSC_Editor_EmbedBlock extends OSC_Editor_Abstract {

    public $max_item = 0;
    public $control_align_enable = true;
    public $control_align_level_enable = 'initial';
    public $control_zoom_enable = true;
    public $control_zoom_levels = 'initial';
    public $control_zoom_align_levels = 'initial';
    public $control_caption_enable = true;

    /**
     * 
     * @param OSC_Editor $editor
     * @param array $config
     * @return \OSC_Editor_EmbedBlock
     */
    public function initialize($editor, $config) {
        parent::initialize($editor, $config);

        $this->max_item = intval($this->max_item);

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
        foreach ($xpath->query('.//*[contains(@class,"osc-editor-embed-block")]', $parent_node) as $element) {
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
                $embed_data = OSC::helper('core/embed')->parse($this->_editor->nodeGetInnerHtml($content_node));
            } catch (Exception $ex) {
                continue;
            }

            $this->_editor->nodeSetInnerHtml($content_node, $embed_data['code']);

            $this->_editor->nodeAddSafeClass($element, array('osc-editor-embed-block'));
            $this->_editor->nodeAddSafeClass($content_node, array('content'));
            $this->_editor->nodeAddSafeContent($content_node, $embed_data['code']);

            $this->_editor->nodeMarkAsSafe($content_node);

            $controls = array();

            if ($this->control_zoom_enable) {
                $controls[] = array('key' => 'zoom', 'config' => array('node' => $content_node, 'levels' => $this->control_zoom_levels, 'align_levels' => $this->control_zoom_align_levels));
            }

            if ($this->control_align_enable) {
                $controls[] = array('key' => 'align', 'config' => array('level_enable' => $this->control_align_level_enable));
            }

            if ($this->control_caption_enable) {
                $controls[] = 'caption';
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
