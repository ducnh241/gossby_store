<?php

class OSC_Editor_BlockImage extends OSC_Editor_Abstract {

    public $max_item = 0;
    public $url_processor = null;
    public $control_align_enable = true;
    public $control_align_level_enable = 'initial';
    public $control_align_overflow_mode = 'initial';
    public $control_align_full_mode = 'initial';
    public $control_zoom_enable = true;
    public $control_zoom_levels = 'initial';
    public $control_zoom_align_levels = 'initial';
    public $control_caption_enable = true;

    /**
     * 
     * @param OSC_Editor $editor
     * @param array $config
     * @return \OSC_Editor_BlockImage
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
        foreach ($xpath->query('.//*[contains(@class,"osc-editor-block-image")]', $parent_node) as $element) {
            if ($element->nodeType !== XML_ELEMENT_NODE || !$this->_editor->nodeIsAllowedBlock($element) || $element->childNodes->length < 1) {
                continue;
            }

            /* @var $img_container DOMElement */
            $img_container = null;

            /* @var $child DOMElement */
            foreach ($element->childNodes as $child) {
                if($child->nodeType !== XML_ELEMENT_NODE) {
                    continue;
                }
                
                if (strpos($child->getAttribute('class'), 'img-container') !== false) {
                    $img_container = $child;
                    break;
                }
            }

            if (!$img_container) {
                continue;
            }

            /* @var $img DOMElement */
            $img = null;

            /* @var $child DOMElement */
            foreach ($img_container->childNodes as $child) {
                if (strtolower($child->nodeName) === 'a') {
                    if ($child->firstChild->nodeName === 'img') {
                        $img = $child->firstChild;
                        break;
                    }
                } else {
                    if (strtolower($child->nodeName) === 'img') {
                        $img = $child;
                        break;
                    }
                }
            }

            if (!$img) {
                continue;
            }

            if ($this->url_processor) {
                try {
                    $url = call_user_func($this->url_processor, $img->getAttribute('src'));

                    if (!$url) {
                        continue;
                    }

                    $img->setAttribute('src', $url);
                } catch (Exception $ex) {
                    continue;
                }
            }

            $this->_editor->nodeAddSafeClass($element, array('osc-editor-block-image'));
            $this->_editor->nodeAddSafeClass($img_container, array('img-container'));

            if ($img->parentNode->nodeName == 'a') {
                $this->_editor->nodeAddSafeClass($img->parentNode, array('hyperlink-img-container'));
                $this->_editor->nodeMarkAsSafe($img->parentNode);
            }

            $this->_editor->nodeMarkAsSafe($img_container);
            $this->_editor->nodeMarkAsSafe($img);

            $controls = array();

            if ($this->control_zoom_enable) {
                $controls[] = array('key' => 'zoom', 'config' => array('levels' => $this->control_zoom_levels, 'align_levels' => $this->control_zoom_align_levels));
            }

            if ($this->control_align_enable) {
                $controls[] = array('key' => 'align', 'config' => array('level_enable' => $this->control_align_level_enable, 'overflow_mode' => $this->control_align_overflow_mode, 'full_mode' => $this->control_align_full_mode));
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
