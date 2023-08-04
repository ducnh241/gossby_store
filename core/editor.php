<?php

class OSC_Editor extends OSC_Object {

    protected $_config_image_enable = true;
    protected $_config_table_enable = true;
    protected $_config_list_enable = true;
    protected $_table_map = array();
    protected $_list_map = array();
    protected $_node_allowed_attrs = array(
        'a' => array('id', 'href', 'target'),
        'img' => array('src', 'width', 'height', 'alt'),
        'p' => array('mrk-figure'),
		'td' => array('colspan', 'rowspan'),
        '*' => array('style', 'osc-editor-safe-style', 'osc-editor-safe-class', 'osc-editor-safe-attr', 'osc-editor-safe-content')
    );
    protected $_node_allowed_style = array(
        '*' => array('text-align', 'font-size', 'font-weight', 'font-style', 'text-decoration')
    );
    protected $_block_element_names = array(
        'p', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'ul', 'li', 'ol',
        'address', 'article', 'aside', 'audio', 'canvas', 'div', 'dd', 'dl', 'dt', 'fieldset',
        'figcaption', 'figure', 'footer', 'form', 'header', 'hgroup', 'main', 'nav',
        'noscript', 'output', 'section', 'table', 'tbody', 'thead', 'tfoot', 'video'
    );
    protected $_block_element_names_able_in_list = array('p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'figure', 'ul', 'li', 'ol', 'table');
    protected $_remove_element_names = array('caption', 'col', 'colgroup', 'noembed', 'frame', 'iframe', 'frameset', 'input', 'button', 'select', 'script', 'style', 'noscript', 'keygen', 'datalist', 'output', 'progress');
    protected $_allowed_block_element_names = array('p', 'div', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'blockquote', 'pre', 'figure', 'ul', 'li', 'ol', 'table', 'tbody', 'tfoot', 'thead');
    protected $_allowed_element_names = array('span', 'em', 'strong', 'b', 'i', 'p', 'u', 'del', 'ins', 'br', 'sub', 'sup', 'a', 'strike', 'tr', 'td', 'th', 'img');
    protected $_loaded_plugins = array();
    protected $_document = null;

    public function __construct() {
        parent::__construct();

        $this->addObserver('editorElementControl_zoom', array($this, 'editorElementControl_zoom'), null, 'zoom');
        $this->addObserver('editorElementControl_align', array($this, 'editorElementControl_align'), null, 'align');
        $this->addObserver('editorElementControl_caption', array($this, 'editorElementControl_caption'), null, 'caption');

        $this->addObserver('packEditorElements', array($this, 'editorElementHr'));
    }

    public function enableStyle($style_names, $node_name = '*') {
        $node_name = strtolower($node_name);

        if (!$node_name) {
            $node_name = '*';
        }

        if (!is_array($style_names)) {
            $style_names = array($style_names);
        }

        if (!isset($this->_node_allowed_style[$node_name])) {
            $this->_node_allowed_style[$node_name] = array();
        }

        $this->_node_allowed_style[$node_name] = array_merge($this->_node_allowed_style[$node_name], $style_names);

        return $this;
    }

    public function enableAttribute($attr_names, $node_name = '*') {
        $node_name = strtolower($node_name);

        if (!$node_name) {
            $node_name = '*';
        }

        if (!is_array($attr_names)) {
            $attr_names = array($attr_names);
        }

        if (!isset($this->_node_allowed_attrs[$node_name])) {
            $this->_node_allowed_attrs[$node_name] = array();
        }

        $this->_node_allowed_attrs[$node_name] = array_merge($this->_node_allowed_attrs[$node_name], $attr_names);

        return $this;
    }

    public function getDocument() {
        return $this->_document;
    }

    /**
     * 
     * @return \OSC_Editor
     * @throws Exception
     */
    public function addPlugins() {
        foreach (func_get_args() as $plugin) {
            if (!is_array($plugin)) {
                $plugin = array('name' => $plugin, 'config' => array());
            } else if (!isset($plugin['name'])) {
                throw new Exception("Editor plugin is incorrect: " . print_r($plugin, 1));
            } else if (!isset($plugin['config'])) {
                $plugin['config'] = array();
            }

            if (in_array(strtolower($plugin['name']), $this->_loaded_plugins, true)) {
                continue;
            }

            if (!OSC::classExists('OSC_Editor_' . $plugin['name'])) {
                throw new Exception("Editor plugin [{$plugin['name']}] is not exists");
            }

            OSC::core('editor_' . $plugin['name'], $this->getInstanceKey())->initialize($this, $plugin['config']);

            $this->_loaded_plugins[] = strtolower($plugin['name']);
        }

        return $this;
    }

    public function config($config) {
        if (is_array($config) && count($config) > 0) {
            foreach ($config as $k => $v) {
                $config_key = '_config_' . $k;

                if (!property_exists($this, $config_key)) {
                    continue;
                }

                $this->$config_key = $v;
            }
        }

        return $this;
    }

    public function getConfig() {
        $configs = array();

        foreach (get_object_vars($this) as $k => $v) {
            if (substr($k, 0, 8) === '_config_') {
                $configs[substr($k, 8)] = $v;
            }
        }

        return $configs;
    }

    protected $_editor_element_markers = array();

    /**
     * 
     * @param DOMElement $parent_node
     * @return OSC_Editor
     */
    protected function _packEditorElements($parent_node) {
        $this->dispatchEvent('packEditorElements', $parent_node);
        return $this;
    }

    /**
     * 
     * @return OSC_Editor
     */
    protected function _unpackEditorElements() {
        $xpath = new DOMXPath($this->_document);

        /* @var $marker DOMElement */
        foreach ($xpath->query('//*[@editor-element-marker]') as $marker) {
            $uniqid = $marker->getAttribute('editor-element-marker');

            if (isset($this->_editor_element_markers[$uniqid])) {
                $marker->parentNode->insertBefore($this->_editor_element_markers[$uniqid], $marker);
                unset($this->_editor_element_markers[$uniqid]);
            }

            $marker->parentNode->removeChild($marker);
        }

        return $this;
    }

    public function packEditorElement($element, $pack_as_block = true) {
        $this->nodeMarkAsSafe($element);

        $this->cleanUnSafeNode($element);

        $uniqid = uniqid('', true);

        $this->_editor_element_markers[$uniqid] = $element;

        $marker = $element->ownerDocument->createElement($pack_as_block ? 'p' : 'span', 'X');

        $this->nodeMarkAsEditorElement($marker, true);

        $marker->setAttribute('editor-element-marker', $uniqid);

        $element->parentNode->insertBefore($marker, $element);
        $element->parentNode->removeChild($element);
    }

    /**
     * 
     * @param DOMElement $parent_node
     */
    public function editorElementHr($parent_node) {
        $xpath = new DOMXPath($parent_node->ownerDocument);

        /* @var $element DOMElement */
        foreach ($xpath->query('.//*[contains(@class,"osc-editor-hr")]', $parent_node) as $element) {
            if ($element->nodeType !== XML_ELEMENT_NODE || !$this->nodeIsAllowedBlock($element) || $element->childNodes->length < 1) {
                continue;
            }

            /* @var $hr_tag DOMElement */
            $hr_tag = null;

            /* @var $child DOMElement */
            foreach ($element->childNodes as $child) {
                if (strtolower($child->nodeName) === 'hr') {
                    $hr_tag = $child;
                    break;
                }
            }

            if (!$hr_tag) {
                continue;
            }

            $this->nodeAddSafeClass($element, array('osc-editor-hr'));

            $this->nodeMarkAsSafe($hr_tag);

            $this->packEditorElement($element, true);
        }
    }

    protected $_session_id = null;

    /**
     * 
     * @param string $html
     * @return string
     */
    public function clean($html) {
        $this->_session_id = OSC::makeUniqid();

        try {
            $this->_document = OSC::makeDomFromContent($html);
        } catch (Exception $ex) {
            if ($ex->getCode() == 404) {
                return '';
            }

            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        $body = $this->_document->getElementsByTagName('body')->item(0);

        $this->_packEditorElements($body);

        $this->_cleanUselessNode($body);

        $this->_cleanRootNode($body);

        $this->_trimEmptyBlock($body);

        $this->_unpackEditorElements();

        $this->cleanAttributes($body);

        return $this->nodeGetInnerHtml($body);
    }

    /**
     * 
     * @param DOMElement $block_container
     * @return \OSC_Editor
     */
    protected function _trimEmptyBlock($block_container) {
        foreach (array('firstChild', 'lastChild') as $child_key) {
            while ($block_container->$child_key) {
                if ($this->nodeIsEditorElement($block_container->$child_key)) {
                    break;
                }

                if ($block_container->$child_key->childNodes->length > 1) {
                    break;
                }

                if ($block_container->$child_key->childNodes->length == 1 && (($block_container->$child_key->firstChild->nodeType == XML_ELEMENT_NODE && strtolower($block_container->$child_key->firstChild->nodeName) != 'br') || ($block_container->$child_key->firstChild->nodeType == XML_TEXT_NODE && trim(preg_replace("/\xEF\xBB\xBF/", '', $block_container->$child_key->firstChild->nodeValue)) != ''))) {
                    break;
                }

                $block_container->removeChild($block_container->$child_key);
            }
        }

        /* @var $child DOMElement */

        foreach ($block_container->childNodes as $child) {
            $node_name = strtolower($child->nodeName);

            if (in_array($node_name, array('ul', 'ol'), true)) {
                /* @var $li DOMElement */

                foreach ($child->childNodes as $li) {
                    $this->_trimEmptyBlock($li);
                }

                continue;
            }

            if ($node_name == 'table') {
                /* @var $tbody DOMElement */

                foreach ($child->childNodes as $tbody) {
                    /* @var $row DOMElement */
                    foreach ($tbody->childNodes as $row) {
                        /* @var $cell DOMElement */
                        foreach ($row->childNodes as $cell) {
                            $this->_trimEmptyBlock($cell);
                        }
                    }
                }

                continue;
            }
        }

        return $this;
    }

    public function processEditorElementControl($element, $controls) {
        foreach ($controls as $control) {
            if (!is_array($control)) {
                $control = array('key' => $control, 'config' => array());
            } else if (!isset($control['key'])) {
                continue;
            } else if (!isset($control['config'])) {
                $control['config'] = array();
            } else {
                $control['key'] = trim(strval($control['key']));
            }

            $this->dispatchEvent('editorElementControl_' . $control['key'], array('editor' => $this, 'element' => $element, 'config' => $control['config']));
        }
    }

    public function editorElementControlParseConfig($config, $custom_config) {
        if (!is_array($custom_config)) {
            $custom_config = array();
        }

        foreach ($custom_config as $key => $value) {
            if ($value === 'initial' || !isset($config[$key])) {
                continue;
            }

            $config[$key] = $value;
        }

        return $config;
    }

    public function editorElementControl_zoom($params) {
        $config = $this->editorElementControlParseConfig(array('node' => $params['element'], 'max_width' => 0, 'constrain_proportions' => true, 'levels' => array(600, 800, 900, 1080), 'align_levels' => array(320, 450)), $params['config']);

        if (!($config['node'] instanceof DOMElement)) {
            return;
        }

        $config['max_width'] = intval($config['max_width']);

        foreach (array('levels', 'align_levels') as $level_key) {
            if (!is_array($config[$level_key])) {
                $config[$level_key] = array();
            } else {
                $levels = array();

                foreach ($config[$level_key] as $k => $v) {
                    $v = intval($v);

                    if ($v > 0 && !in_array($v, $levels) && ($config['max_width'] <= 0 || $v <= $config['max_width'])) {
                        $levels[] = $v;
                    }
                }

                usort($levels, function($a, $b) {
                    return $a == $b ? 0 : (($a < $b) ? -1 : 1);
                });

                $config[$level_key] = $levels;
            }
        }

        if (count($config['levels']) < 1 && count($config['align_levels']) < 1) {
            return;
        }

        if (count($config['levels']) < 1) {
            $config['levels'] = $config['align_levels'];
        } else if (count($config['align_levels']) < 1) {
            $config['align_levels'] = $config['levels'];
        }

        $align_state = $this->editorElementControlAlign_GetState($params['element']);

        $levels = $align_state['align'] ? $config['align_levels'] : $config['levels'];

        $width = intval($this->nodeGetStyle($config['node'], 'width'));
        $height = intval($this->nodeGetStyle($config['node'], 'height'));

        $max_width = 0;
        $min_width = 0;

        $corrent_width = null;

        foreach ($levels as $k => $v) {
            if ($width == $v) {
                $corrent_width = $v;
                break;
            }

            if ($v > $width) {
                $max_width = $v;
                break;
            } else {
                $min_width = $v;
            }
        }

        if ($corrent_width === null) {
            if ($max_width < 1) {
                $corrent_width = $min_width;
            } else if ($min_width < 1) {
                $corrent_width = $max_width;
            } else {
                $corrent_width = (($max_width - $min_width) / 2) > ($width - $min_width) ? $min_width : $max_width;
            }
        }

        $this->nodeSetStyle($config['node'], array('width' => $corrent_width . 'px'), true);

        if ($config['constrain_proportions'] && $height > 0) {
            $correct_height = $height * $corrent_width / $width;
            $this->nodeSetStyle($config['node'], array('height' => $correct_height . 'px'), true);
        }
    }

    public function nodeParseStyle($node) {
        $styles = array();

        $style_data = $node->getAttribute('style');

        if ($style_data) {
            $style_data = explode(';', $style_data);

            foreach ($style_data as $style_item) {
                $style_item = explode(':', $style_item, 2);

                if (count($style_item) != 2) {
                    continue;
                }

                $style_item[0] = trim($style_item[0]);
                $style_item[1] = trim($style_item[1]);

                if (preg_match('/[^a-zA-Z0-9\-]/', $style_item[0]) || !$style_item[1]) {
                    continue;
                }

                $styles[$style_item[0]] = $style_item[1];
            }
        }

        return $styles;
    }

    public function nodeGetStyle($node, $style_key) {
        $style_arr = $this->nodeParseStyle($node);
        return isset($style_arr[$style_key]) ? $style_arr[$style_key] : null;
    }

    public function styleArrayToStyle($style_arr) {
        $style_data = array();

        foreach ($style_arr as $k => $v) {
            $style_data[] = $k . ': ' . $v;
        }

        return implode('; ', $style_data);
    }

    public function nodeSetStyle($node, $styles, $add_safe_flag = true) {
        $style_arr = $this->nodeParseStyle($node);

        foreach ($styles as $k => $v) {
            if ($v === null || $v === false || $v === '') {
                if (isset($style_arr[$k])) {
                    unset($style_arr[$k]);
                }

                continue;
            }

            $style_arr[$k] = $v;
        }

        $style_string = $this->styleArrayToStyle($style_arr);

        if ($style_string) {
            $node->setAttribute('style', $style_string);
        } else {
            $node->removeAttribute('style');
            $style_string = null;
        }

        $this->nodeAddSafeAttribute($node, array('style' => $style_string));

        if ($add_safe_flag) {
            $this->nodeAddSafeStyle($node, $styles);
        }

        return $this;
    }

    public function editorElementControlAlign_GetState($element) {
        $state = array(
            'align' => false,
            'level' => 0,
            'overflow_mode' => false,
            'full_mode' => false
        );

        $element_css_class = $element->getAttribute('class');

        if (strpos($element_css_class, 'align-left') !== false) {
            $state['align'] = 'left';
        } else if (strpos($element_css_class, 'align-right') !== false) {
            $state['align'] = 'right';
        }

        if ($state['align']) {
            $state['level'] = $element->getAttribute('align-level') == 2 ? 2 : 1;
        } else if (strpos($element_css_class, 'overflow-mode') !== false) {
            $state['overflow_mode'] = true;
        } else if (strpos($element_css_class, 'full-mode') !== false) {
            $state['full_mode'] = true;
        }

        return $state;
    }

    public function editorElementControl_align($params) {
        $config = $this->editorElementControlParseConfig(array('level_enable' => true, 'overflow_mode' => false, 'full_mode' => false), $params['config']);

        $align_state = $this->editorElementControlAlign_GetState($params['element']);

        if ($align_state['align']) {
            if (!$config['level_enable'] && $align_state['level'] == 2) {
                $align_state['level'] = 1;
                $params['element']->setAttribute('align-level', 1);
            }
        }

        $this->nodeAddSafeAttribute($params['element'], array('align-level' => $align_state['level']));

        if ($align_state['align']) {
            $this->nodeAddSafeClass($params['element'], 'align-' . $align_state['align']);
        } else if ($config['overflow_mode'] && $align_state['overflow_mode']) {
            $this->nodeAddSafeClass($params['element'], 'overflow-mode');
        } else if ($config['full_mode'] && $align_state['full_mode']) {
            $this->nodeAddSafeClass($params['element'], 'full-mode');
        }
    }

    public function editorElementControl_caption($params) {
        /* @var $caption DOMElement */
        $caption = null;

        /* @var $child DOMElement */
        foreach ($params['element']->childNodes as $child) {
            if ($child->nodeType != XML_ELEMENT_NODE) {
                continue;
            }

            if (strpos($child->getAttribute('class'), 'caption') !== false) {
                $caption = $child;
                break;
            }
        }

        if ($caption) {
            $this->nodeMarkAsSafe($caption);
            $this->nodeAddSafeClass($caption, array('caption'));
        }
    }

    /**
     * 
     * @param DOMElement $body
     * @return OSC_Editor
     */
    protected function _cleanUnallowedNode($body) {
        $xpath = new DOMXPath($body->ownerDocument);

        /* @var $node DOMElement */
        foreach ($xpath->query('.//*[self::' . implode(' or self::', $this->_remove_element_names) . ']', $body) as $node) {
            $node->parentNode->removeChild($node);
        }

        /* @var $node DOMElement */
        foreach ($xpath->query('.//*[not(self::' . implode(') and not(self::', array_merge($this->_allowed_element_names, $this->_allowed_block_element_names)) . ')]', $body) as $node) {
            if ($node->nodeType == XML_TEXT_NODE) {
                continue;
            }

            if (in_array(strtolower($node->nodeName), $this->_block_element_names)) {
                $new_node = $node->ownerDocument->createElement('div');
                $node->parentNode->insertBefore($new_node, $node);

                while ($node->firstChild) {
                    $new_node->appendChild($node->firstChild);
                }
            } else {
                while ($node->firstChild) {
                    $node->parentNode->insertBefore($node->firstChild, $node);
                }
            }

            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $body
     * @return OSC_Editor
     */
    protected function _cleanUselessNode($body) {
        $this->_cleanUselessNodeRecursive($body);

        $this->_cleanUnallowedNode($body);

        $xpath = new DOMXPath($body->ownerDocument);

        if (!$this->_config_image_enable) {
            $images = $xpath->query("//img");

            /* @var $image DOMElement */
            foreach ($images as $image) {
                if ($this->nodeIsEditorElement($image)) {
                    continue;
                }

                $image->parentNode->removeChild($image);
            }
        }

        do {
            $nodes = $xpath->query("//*[not(self::img or self::iframe or self::th or self::td or self::br or (self::a and @id))][not(text()) and not(./*)]");

            /* @var $node DOMElement */
            foreach ($nodes as $node) {
                $node->parentNode->removeChild($node);
            }
        } while ($nodes->length > 0);

        /* @var $node DOMElement */
        foreach ($xpath->query("//span[not(@*)]") as $node) {
            while ($node->firstChild) {
                $node->parentNode->insertBefore($node->firstChild, $node);
            }

            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    /**
     * 
     * @param DOMNode $node
     */
    protected function _cleanUselessNodeRecursive($node) {
        for ($i = 0; $i < $node->childNodes->length; $i ++) {
            $child = $node->childNodes->item($i);

            if ($child->nodeType == XML_COMMENT_NODE || ($child->nodeType == XML_TEXT_NODE && !preg_match('/\S/', preg_replace("/\xEF\xBB\xBF/", '', $child->nodeValue)))) {
                $node->removeChild($child);
                $i --;
            } else if ($child->nodeType == XML_ELEMENT_NODE) {
                $this->_cleanUselessNodeRecursive($child);
            }
        }
    }

    /**
     * 
     * @param DOMElement $node
     */
    public function nodeIsAllowedBlock($node) {
        return in_array(strtolower($node->nodeName), $this->_allowed_block_element_names, true);
    }

    /**
     * 
     * @param DOMNode $node
     */
    protected function _cleanRootNode($node) {
        if ($node->childNodes->length < 1) {
            return;
        }

        $this->_processNonBlockNode($node);

        $this->_markTableNode($node, true);
        $this->_markListNode($node, true);

        $this->_cleanBlockRecursive($node);

        $this->_replaceTableMarker($node);
        $this->_replaceListMarker($node);
    }

    /**
     * 
     * @param DOMElement $parent_node
     * @return OSC_Editor
     */
    protected function _markTableNode($parent_node) {
        while ($table = $parent_node->getElementsByTagName('table')->item(0)) {
            $uniqid = uniqid('', true);

            $this->_table_map[$uniqid] = $table;

            $marker = $table->ownerDocument->createElement('p', 'X');

            $marker->setAttribute('osc-editor-table', $uniqid);
            $table->parentNode->insertBefore($marker, $table);
            $table->parentNode->removeChild($table);
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $parent_node
     * @return OSC_Editor
     */
    protected function _replaceTableMarker($parent_node) {
        $xpath = new DOMXPath($parent_node->ownerDocument);

        /* @var $list_marker DOMElement */
        foreach ($xpath->query('.//*[@osc-editor-table]', $parent_node) as $table_marker) {
            $uniqid = $table_marker->getAttribute('osc-editor-table');

            if (isset($this->_table_map[$uniqid])) {
                $table = $this->_table_map[$uniqid];

                $table_marker->parentNode->insertBefore($table, $table_marker);

                $this->_cleanTable($table);

                unset($this->_table_map[$uniqid]);
            }

            $table_marker->parentNode->removeChild($table_marker);
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $figure
     * @return boolean
     */
    protected function _cleanFigure($figure) {
        $figure_type = $figure->getAttribute('figure-type');

        if (!$figure_type) {
            return false;
        }

        $xpath = new DOMXPath($figure->ownerDocument);

        $figure_content = $xpath->query("./div[@osc-editor-figure-content][./div[1][text() or ./*]]", $figure)->item(0);

        if (!$figure_content) {
            return false;
        }

        /* @var $node DOMElement */
        foreach ($xpath->query(".//*[@mrk-figure-elm]", $figure) as $node) {
            $node->removeAttribute('mrk-figure-elm');
        }

        switch ($figure_type) {
            case 'video':
                if (!$this->_cleanFigureVideo($figure, $figure_content)) {
                    return false;
                }
                break;
            case 'image':
                if (!$this->_cleanFigureImage($figure, $figure_content)) {
                    return false;
                }
                break;
            default:
                return false;
        }

        $figure_content->setAttribute('mrk-figure-elm', 1);
        $xpath->query("./div[1]", $figure_content)->item(0)->setAttribute('mrk-figure-elm', 1);

        /* @var $node DOMElement */
        while ($node = $xpath->query(".//*[not(@mrk-figure-elm)]", $figure)->item(0)) {
            $node->parentNode->removeChild($node);
        }

        /* @var $node DOMElement */
        foreach ($xpath->query(".//*[@mrk-figure-elm]", $figure) as $node) {
            $node->removeAttribute('mrk-figure-elm');
        }

        $this->nodeCleanAttributes($figure, array('figure-type'));
        $this->nodeCleanAttributes($figure_content, array('osc-editor-figure-content'));
        $this->nodeCleanAttributes($xpath->query("./div[1]", $figure_content)->item(0), array('style'));

        return true;
    }

    /**
     * 
     * @param DOMElement $figure
     * @param DOMElement $figure_content
     * @return boolean
     */
    protected function _cleanFigureVideo($figure, $figure_content) {
        $xpath = new DOMXPath($figure->ownerDocument);

        $content_node = $xpath->query("./div[1]", $figure_content)->item(0);

        $video_data = $this->_figureVideoGetData($content_node);

        if (!$video_data) {
            return false;
        }

        while ($content_node->firstChild) {
            $content_node->removeChild($content_node->firstChild);
        }

        if ($video_data['type'] == 'iframe') {
            $iframe = $content_node->ownerDocument->createElement('iframe');
            $iframe->setAttribute('mrk-figure-elm', 1);
            $iframe->setAttribute('src', $video_data['src']);

            $content_node->appendChild($iframe);
        } else {
            
        }

        return true;
    }

    /**
     * 
     * @param DOMElement $content_node
     * @return mixed
     */
    protected function _figureVideoGetData($content_node) {
        $youtube_regex = array(
            'link' => array('regex' => "/^(https?:)?\/\/(www\.)?youtu\.be\/([^\/?&#]+)([\/?&#].*)?/i", 'matched' => 3),
            'embed' => array('regex' => "/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com\/.*[?&]v=([^\/?&#]+)([\/?&#].*)?/i", 'matched' => 4),
            'iframe' => array('regex' => "/^(https?:)?\/\/(www\.)?(youtube-nocookie|youtube)\.com(\/.+)?\/(v|embed)\/([^\/?&#]+)([\/?&#].*)?/i", 'matched' => 6)
        );

        $youtube_code = null;

        $xpath = new DOMXPath($content_node->ownerDocument);

        $iframe = $xpath->query(".//iframe", $content_node)->item(0);

        $code = null;

        if ($iframe) {
            $url = $iframe->getAttribute('src');

            if (!$url || !preg_match($youtube_regex['iframe']['regex'], $url, $matches)) {
                return null;
            }

            $youtube_code = $matches[$youtube_regex['iframe']['matched']];

            $code = array(
                'type' => 'iframe',
                'src' => $url
            );
        } else {
            $obj_node = $xpath->query(".//object", $content_node)->item(0);
            $param_nodes = array();

            if ($obj_node) {
                $embed_node = $xpath->query(".//embed", $obj_node);
                $param_nodes = $xpath->query(".//param", $obj_node);
            } else {
                $embed_node = $xpath->query(".//embed", $content_node);
            }

            $params_map = array('wmode', 'movie', 'allowfullscreen', 'allowscriptaccess', 'quality', 'flashvars');
            $params = array();

            foreach ($params_map as $param_key) {
                $param_value = null;

                foreach ($param_nodes as $param_node) {
                    if (strtolower($param_node->getAttribute('name')) == $param_key) {
                        $param_value = $param_node->getAttribute('value');
                        break;
                    }
                }

                if (!$param_value && $embed_node) {
                    $param_value = $embed_node->getAttribute($param_key == 'movie' ? 'src' : $param_key);
                }

                if ($param_value) {
                    $params[$param_key] = $param_value;
                }
            }

            if (!isset($params['movie'])) {
                return null;
            }

            if (preg_match($youtube_regex['embed']['regex'], $params['movie'], $matches)) {
                $youtube_code = $matches[$youtube_regex['embed']['matched']];
            }

            $code = array('type' => 'object', 'params' => $params);
        }

        if ($youtube_code) {
            $code['thumbnail'] = 'http://i2.ytimg.com/vi/' + $youtube_code + '/hqdefault.jpg';
        }

        return $code;
    }

    /**
     * 
     * @param DOMElement $figure
     * @param DOMElement $figure_content
     * @return boolean
     */
    protected function _cleanFigureImage($figure, $figure_content) {
        return true;
    }

    /**
     * 
     * @param DOMElement $parent_node
     * @return OSC_Editor
     */
    protected function _markListNode($parent_node) {
        $xpath = new DOMXPath($parent_node->ownerDocument);

        while ($list = $xpath->query('.//*[self::ul | self::ol]', $parent_node)->item(0)) {
            $uniqid = uniqid('', true);

            $this->_list_map[$uniqid] = $list;

            $marker = $list->ownerDocument->createElement('p', 'X');

            $marker->setAttribute('osc-editor-list', $uniqid);
            $list->parentNode->insertBefore($marker, $list);
            $list->parentNode->removeChild($list);
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $parent_node
     * @return OSC_Editor
     */
    protected function _replaceListMarker($parent_node) {
        $xpath = new DOMXPath($parent_node->ownerDocument);

        /* @var $list_marker DOMElement */
        foreach ($xpath->query('.//*[@osc-editor-list]', $parent_node) as $list_marker) {
            $uniqid = $list_marker->getAttribute('osc-editor-list');

            if (isset($this->_list_map[$uniqid])) {
                $list = $this->_list_map[$uniqid];

                $list_marker->parentNode->insertBefore($list, $list_marker);

                $this->_cleanList($list);

                unset($this->_list_map[$uniqid]);
            }

            $list_marker->parentNode->removeChild($list_marker);
        }

        return $this;
    }

    /**
     * 
     * @param DOMNode $parent_node
     * @param string $block_node_name
     * @return OSC_Editor
     */
    protected function _processNonBlockNode($parent_node, $block_node_name = 'p') {
        if ($parent_node->childNodes->length < 1) {
            return $this;
        }

        $block_node_name = strtolower($block_node_name);

        $node = $parent_node->firstChild;

        do {
            if (in_array(strtolower($node->nodeName), $this->_block_element_names)) {
                continue;
            }

            if ($node->previousSibling && strtolower($node->previousSibling->nodeName) == $block_node_name && ($block_node_name != 'li' || $node->previousSibling->childNodes->length < 1 || !in_array(strtolower($node->previousSibling->lastChild->nodeName), array('ol', 'ul')))) {
                $block = $node->previousSibling;
            } else {
                $block = $node->ownerDocument->createElement($block_node_name);
                $parent_node->insertBefore($block, $node);
            }

            $block->appendChild($node);

            $node = $block;
        } while ($node = $node->nextSibling);

        return $this;
    }

    public function nodeGetOuterHtml($node) {
        return $node->ownerDocument->saveHTML($node);
    }

    public function nodeGetInnerHtml($node) {
        return preg_replace('/^\<[^\>]+\>(.+)\<\/[^\>]+\>$/is', '\\1', $node->ownerDocument->saveHTML($node));
    }

    /**
     * 
     * @param DOMElement $node
     * @param string $html
     * @return \OSC_Editor
     */
    public function nodeSetInnerHtml($node, $html) {
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        foreach ($document->childNodes as $item) {
            if ($item->nodeType == XML_PI_NODE) {
                $document->removeChild($item);
            }
        }

        $document->encoding = 'UTF-8';

        $body = $document->getElementsByTagName('body')->item(0);

        $node_owner_document = $node->ownerDocument;

        while ($node->firstChild) {
            $node->removeChild($node->firstChild);
        }

        foreach ($body->childNodes as $child) {
            $counter ++;

            $node->appendChild($node_owner_document->importNode($child, true));
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $list
     * @return OSC_Editor
     */
    protected function _cleanList($list) {
        /* @var $child DOMElement */
        foreach (iterator_to_array($list->childNodes) as $child) {
            $child_node_name = strtolower($child->nodeName);

            if ($child_node_name == 'li') {
                continue;
            }

            if (in_array($child_node_name, array('ol', 'ul'))) {
                if (!$child->previousSibling) {
                    $new_li = $child->ownerDocument->createElement('li');
                    $list->insertBefore($new_li, $child);
                    $new_li->appendChild($child);
                } else {
                    if ($child->previousSibling->lastChild && in_array(strtolower($child->previousSibling->lastChild->nodeName), array('ul', 'ol'))) {
                        $sub_list = $child->previousSibling->lastChild;

                        while ($child->firstChild) {
                            $sub_list->appendChild($child->firstChild);
                        }

                        $list->removeChild($child);
                    } else {
                        $child->previousSibling->appendChild($child);
                    }
                }

                continue;
            }

            if (in_array($child_node_name, $this->_block_element_names)) {
                $new_li = $child->ownerDocument->createElement('li');
                $list->insertBefore($new_li, $child);

                while ($child->firstChild) {
                    $new_li->appendChild($child->firstChild);
                }

                $list->removeChild($child);

                continue;
            }

            if (!$child->previousSibling) {
                $list->insertBefore($child->ownerDocument->createElement('li'), $child);
            }

            $child->previousSibling->appendChild($child);
        }

        foreach (iterator_to_array($list->childNodes) as $child) {
            $this->_cleanRootNode($child);

            $flag_node = $child->firstChild;

            while ($flag_node) {
                $sub_child = $flag_node;
                $flag_node = $flag_node->nextSibling;

                if (!in_array(strtolower($sub_child->nodeName), array('ol', 'ul')) || !$sub_child->nextSibling) {
                    continue;
                }

                $new_li = $sub_child->ownerDocument->createElement('li');

                $list->insertBefore($new_li, $child);

                $break_flag = false;

                while ($child->firstChild && !$break_flag) {
                    if ($child->firstChild->isSameNode($sub_child)) {
                        $break_flag = true;
                    }

                    $new_li->appendChild($child->firstChild);
                }
            }
        }

        $xpath = new DOMXPath($list->ownerDocument);

        if (in_array(strtolower($list->firstChild->firstChild->nodeName), array('ul', 'ol'))) {
            $bad_list = $list->firstChild->firstChild;

            while ($bad_list->lastChild) {
                $list->insertBefore($bad_list->lastChild, $list->firstChild);
            }

            $list->removeChild($bad_list->parentNode);
        }

        while ($bad_sub_list = $xpath->query("./li/*[last()][self::ul or self::ol]/../following-sibling::li[1][not(text())]/*[1][self::ul or self::ol]", $list)->item(0)) {
            $sub_list = $bad_sub_list->parentNode->previousSibling->lastChild;

            while ($bad_sub_list->firstChild) {
                $sub_list->appendChild($bad_sub_list->firstChild);
            }

            $list->removeChild($bad_sub_list->parentNode);
        }

        while ($bad_li = $xpath->query("./li[not(./*[last()][self::ol or self::ul])]/following-sibling::li[1][not(text())][./*[1][self::ul or self::ol]]", $list)->item(0)) {
            $bad_li->previousSibling->appendChild($bad_li->firstChild);
            $list->removeChild($bad_li);
        }

        $bad_block_name = array_diff($this->_block_element_names, $this->_block_element_names_able_in_list);

        /* @var $node DOMElement */
        foreach ($xpath->query("./li/*[self::" . implode(' or self::', $bad_block_name) . "]", $list) as $node) {
            if ($node->nextSibling) {
                $next_node_name = strtolower($node->nextSibling->nodeName);

                if ($next_node_name != 'br' && !in_array($next_node_name, $this->_block_element_names_able_in_list)) {
                    $br = $node->ownerDocument->createElement('br');
                    $node->parentNode->insertBefore($br, $node->nextSibling);
                }
            }

            if ($node->previousSibling) {
                $prev_node_name = strtolower($node->previousSibling->nodeName);

                if ($prev_node_name != 'br' && !in_array($prev_node_name, $bad_block_name)) {
                    $br = $node->ownerDocument->createElement('br');
                    $node->parentNode->insertBefore($br, $node);
                }
            }

            $flag_node = null;

            if ($node->nextSibling) {
                $flag_node = $node->nextSibling;
            }

            while ($node->firstChild) {
                if ($flag_node) {
                    $flag_node->parentNode->insertBefore($node->firstChild, $flag_node);
                } else {
                    $node->parentNode->appendChild($node->firstChild);
                }
            }

            $node->parentNode->removeChild($node);
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $table
     * @return OSC_Editor
     */
    protected function _cleanTable($table) {
        $xpath = new DOMXPath($table->ownerDocument);

        $tbody = $table->ownerDocument->createElement('tbody');

        /* @var $tr DOMElement */
        foreach ($xpath->query("./tr|./*/tr", $table) as $tr) {
            /* @var $node DOMElement */

            foreach (iterator_to_array($tr->childNodes) as $node) {
                $node_name = strtolower($node->nodeName);

                if ($node_name == 'th') {
                    $new_node = $node->ownerDocument->createElement('td');

                    $this->_nodeCopyAttributes($node, $new_node);

                    while ($node->firstChild) {
                        $new_node->appendChild($node->firstChild);
                    }

                    $tr->insertBefore($new_node, $node);

                    $tr->removeChild($node);
                } else if ($node_name != 'td') {
                    $tr->removeChild($node);
                }
            }

            $tbody->appendChild($tr);
        }

        while ($table->firstChild) {
            $table->removeChild($table->firstChild);
        }

        $table->appendChild($tbody);

        foreach ($xpath->query("./tbody/tr/td", $table) as $td) {
            $this->_cleanRootNode($td);
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $root_node
     */
    protected function _cleanBlockRecursive($root_node) {
        $xpath = new DOMXPath($root_node->ownerDocument);

        $bad_block = $xpath->query('(./*//*[self::' . implode(' or self::', $this->_block_element_names) . '])[1]', $root_node)->item(0);

        if (!$bad_block) {
            return;
        }

        $parent_block = $xpath->query('./ancestor::*[self::' . implode(' or self::', $this->_block_element_names) . '][1]', $bad_block)->item(0);

        $new_block = $this->_extractNodeContent($parent_block, $bad_block, true);

        if ($new_block) {
            $parent_block->parentNode->insertBefore($new_block, $parent_block);
        }

        $parent_block->parentNode->insertBefore($bad_block, $parent_block);

        /* @var $block DOMElement */

        foreach (array($parent_block, $new_block, $bad_block) as $block) {
            if ($block && $block->childNodes->length < 1) {
                $block->parentNode->removeChild($block);
            }
        }

        $this->_cleanBlockRecursive($root_node);
    }

    /**
     * 
     * @param DOMElement $src_node
     * @param DOMElement $flag_node
     * @param boolean $extract_left
     * @return OSC_Editor
     */
    protected function _extractNodeContent($src_node, $flag_node, $extract_left = false) {
        $marker_id = uniqid('', true);

        $flag_node->setAttribute('mrk-extract-content-flag', $marker_id);

        $xpath = new DOMXPath($src_node->ownerDocument);

        if ($xpath->query(".//*[@mrk-extract-content-flag='{$marker_id}']", $src_node)->length < 1) {
            $flag_node->removeAttribute('mrk-extract-content-flag');
            return null;
        }

        $flag_node->removeAttribute('mrk-extract-content-flag');

        $new_node = $src_node->cloneNode();

        if (!$src_node->isSameNode($flag_node->parentNode)) {
            $node = $flag_node;

            do {
                $node = $node->parentNode;

                $clone_node = $node->cloneNode();

                while ($flag_node->previousSibling) {
                    if ($clone_node->childNodes->length > 0) {
                        $clone_node->insertBefore($flag_node->previousSibling, $clone_node->childNodes->item(0));
                    } else {
                        $clone_node->appendChild($flag_node->previousSibling);
                    }
                }

                while ($new_node->firstChild) {
                    $clone_node->appendChild($new_node->firstChild);
                }

                $new_node->appendChild($clone_node);

                $flag_node = $node;
            } while (!$src_node->isSameNode($node->parentNode));
        }

        while ($flag_node->previousSibling) {
            if ($new_node->childNodes->length > 0) {
                $new_node->insertBefore($flag_node->previousSibling, $new_node->childNodes->item(0));
            } else {
                $new_node->appendChild($flag_node->previousSibling);
            }
        }

        return $new_node;
    }

    /**
     * 
     * @param DOMNode $node
     * @param string $class_name
     * @return boolean
     */
    protected function _nodeHasClass($node, $class_name) {
        $class_name = trim(preg_replace('/[^a-zA-Z0-9\-\_]+/', ' ', $class_name));

        if ($class_name == '') {
            return true;
        }

        $class_name_array = explode(' ', $class_name);

        $node_class = trim(preg_replace('/[^a-zA-Z0-9\-\_]+/', ' ', $node->getAttribute('class')));
        $node_class_array = explode(' ', $node_class);

        $matched_counter = 0;

        foreach ($class_name_array as $class_name) {
            if (in_array($class_name, $node_class_array)) {
                $matched_counter ++;
            }
        }

        return $matched_counter == count($class_name_array);
    }

    public function buildTree($parent, $prefix = '+') {
        if ($parent->nodeType == XML_TEXT_NODE) {
            echo $prefix . '-> #Text: ' . $parent->nodeValue . "\n";
            return;
        }

        foreach ($parent->childNodes as $node) {
            if ($node->nodeType == XML_TEXT_NODE) {
                echo $prefix . '-> #Text: ' . $node->nodeValue . "\n";
                continue;
            }

            echo $prefix . '-> [' . $node->nodeName . ']';

            if ($node->childNodes->length == 1 && $node->firstChild->nodeType == XML_TEXT_NODE) {
                echo " #Text: " . $node->firstChild->nodeValue . "\n";
                continue;
            } else {
                echo "\n";
            }

            $this->buildTree($node, $prefix . '--');
        }
    }

    /**
     * 
     * @param DOMElement $node
     * @param boolean $flag
     * @return \OSC_Editor
     */
    public function nodeMarkAsEditorElement($node, $flag = true) {
        if ($flag) {
            $node->setAttribute('osc-editor-element', $this->_session_id);
        } else if ($node->hasAttribute('osc-editor-element')) {
            $node->removeAttribute('osc-editor-element');
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $node
     * @return boolean
     */
    public function nodeIsEditorElement($node) {
        return $node->hasAttribute('osc-editor-element') && $node->getAttribute('osc-editor-element') == $this->_session_id;
    }

    /**
     * 
     * @param DOMElement $parent_node
     * @return OSC_Editor
     */
    public function cleanAttributes($parent_node) {
        $allowed_attrs_data = array();
        $allowed_style_data = array();

        $nodes = iterator_to_array($parent_node->getElementsByTagName('*'));

        foreach ($nodes as $node) {
            if ($this->nodeIsEditorElement($node)) {
                continue;
            }

            $node_name = strtolower($node->nodeName);

            if (!isset($allowed_attrs_data[$node_name])) {
                $allowed_attrs = array();

                if (isset($this->_node_allowed_attrs[$node_name])) {
                    $allowed_attrs = array_merge($allowed_attrs, $this->_node_allowed_attrs[$node_name]);
                }

                if (isset($this->_node_allowed_attrs['*'])) {
                    $allowed_attrs = array_merge($allowed_attrs, $this->_node_allowed_attrs['*']);
                }

                $allowed_attrs_data[$node_name] = $allowed_attrs;
            }

            if (!isset($allowed_style_data[$node_name])) {
                $allowed_style = array();

                if (isset($this->_node_allowed_style[$node_name])) {
                    $allowed_style = array_merge($allowed_style, $this->_node_allowed_style[$node_name]);
                }

                if (isset($this->_node_allowed_style['*'])) {
                    $allowed_style = array_merge($allowed_style, $this->_node_allowed_style['*']);
                }

                $allowed_style_data[$node_name] = $allowed_style;
            }

            $this->nodeCleanAttributes($node, $allowed_attrs_data[$node_name]);
            $this->nodeCleanStyle($node, $allowed_style_data[$node_name]);
        }

        $node_name = strtolower($parent_node->nodeName);

        $this->nodeCleanAttributes($parent_node, isset($allowed_attrs_data[$node_name]) ? $allowed_attrs_data[$node_name] : null);
        $this->nodeCleanStyle($parent_node, isset($allowed_style_data[$node_name]) ? $allowed_style_data[$node_name] : null);

        return $this;
    }

    /**
     * 
     * @param DOMElement $node
     * @param array $allowed_attrs
     * @return OSC_Editor
     */
    public function nodeCleanAttributes($node, $allowed_attrs = array()) {
        $attrs = array();

        for ($i = 0; $i < $node->attributes->length; $i ++) {
            $attrs[] = strtolower($node->attributes->item($i)->name);
        }

        if (!is_array($allowed_attrs)) {
            $allowed_attrs = array();
        }

        foreach ($attrs as $attr) {
            if (!in_array($attr, $allowed_attrs, true)) {
                $node->removeAttribute($attr);
            }
        }

        if ($node->hasAttribute('osc-editor-safe-attr')) {
            $keys = explode(':', $node->getAttribute('osc-editor-safe-attr'));

            $node->removeAttribute('osc-editor-safe-attr');

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_attributes[$keys[1]])) {
                $safe_attributes = $this->_node_safe_attributes[$keys[1]];
                unset($this->_node_safe_attributes[$keys[1]]);

                foreach ($safe_attributes as $k => $v) {
                    $node->setAttribute($k, $v);
                }
            }
        }

        if ($node->hasAttribute('osc-editor-safe-class')) {
            $keys = explode(':', $node->getAttribute('osc-editor-safe-class'));

            $node->removeAttribute('osc-editor-safe-class');

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_classes[$keys[1]])) {
                $classes = trim(implode(' ', array_unique(array_merge(explode(',', $node->getAttribute('class')), $this->_node_safe_classes[$keys[1]]))));

                unset($this->_node_safe_classes[$keys[1]]);

                if ($classes) {
                    $node->setAttribute('class', $classes);
                } else {
                    $node->removeAttribute('class');
                }
            }
        }

        if ($node->hasAttribute('osc-editor-safe-content')) {
            $keys = explode(':', $node->getAttribute('osc-editor-safe-content'));

            $node->removeAttribute('osc-editor-safe-content');

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_content[$keys[1]])) {
                $content = $this->_node_safe_content[$keys[1]];

                unset($this->_node_safe_content[$keys[1]]);

                if ($node->nodeType === XML_ELEMENT_NODE) {
                    if (is_string($content)) {
                        $this->nodeSetInnerHtml($node, $content);
                    } else {
                        if (!is_array($content)) {
                            $content = array($content);
                        }

                        foreach ($content as $content_node) {
                            if ($content_node instanceof DOMElement) {
                                $node->appendChild($content_node);
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    protected $_node_safe_attributes = array();

    /**
     * 
     * @param DOMElement $node
     * @param array $attributes
     * @return \OSC_Editor
     */
    public function nodeAddSafeAttribute($node, $attributes) {
        $safe_key = null;

        if ($node->hasAttribute('osc-editor-safe-attr')) {
            $keys = $node->getAttribute('osc-editor-safe-attr');
            $keys = explode(':', $keys);

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_attributes[$keys[1]])) {
                $safe_key = $keys[1];
            }
        }

        if (!$safe_key) {
            $safe_key = OSC::makeUniqid();
            $node->setAttribute('osc-editor-safe-attr', $this->_session_id . ':' . $safe_key);
            $this->_node_safe_attributes[$safe_key] = array();
        }

        foreach ($attributes as $attr_key => $attr_value) {
            if ($attr_value === false || $attr_value === null) {
                if (isset($this->_node_safe_attributes[$safe_key][$attr_key])) {
                    unset($this->_node_safe_attributes[$safe_key][$attr_key]);
                }

                continue;
            }

            $this->_node_safe_attributes[$safe_key][$attr_key] = $attr_value;
        }

        return $this;
    }

    protected $_node_safe_content = array();

    /**
     * 
     * @param DOMElement $node
     * @param mixed $content
     * @return \OSC_Editor
     */
    public function nodeAddSafeContent($node, $content) {
        $safe_key = null;

        if ($node->hasAttribute('osc-editor-safe-content')) {
            $keys = $node->getAttribute('osc-editor-safe-content');
            $keys = explode(':', $keys);

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_content[$keys[1]])) {
                $safe_key = $keys[1];
            }
        }

        if (!$safe_key) {
            $safe_key = OSC::makeUniqid();
            $node->setAttribute('osc-editor-safe-content', $this->_session_id . ':' . $safe_key);
        }

        $this->_node_safe_content[$safe_key] = $content;

        return $this;
    }

    protected $_node_safe_classes = array();

    /**
     * 
     * @param DOMElement $node
     * @param array $class_names
     * @return \OSC_Editor
     */
    public function nodeAddSafeClass($node, $class_names) {
        $safe_key = null;

        if ($node->hasAttribute('osc-editor-safe-class')) {
            $keys = $node->getAttribute('osc-editor-safe-class');
            $keys = explode(':', $keys);

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_classes[$keys[1]])) {
                $safe_key = $keys[1];
            }
        }

        if (!$safe_key) {
            $safe_key = OSC::makeUniqid();
            $node->setAttribute('osc-editor-safe-class', $this->_session_id . ':' . $safe_key);
            $this->_node_safe_classes[$safe_key] = array();
        }

        if (!is_array($class_names)) {
            $class_names = array($class_names);
        }

        foreach ($class_names as $k => $class_name) {
            if (is_bool($class_name)) {
                $idx = array_search($k, $this->_node_safe_classes[$safe_key], true);

                if ($idx !== false) {
                    unset($this->_node_safe_classes[$safe_key][$idx]);
                }

                continue;
            }

            $this->_node_safe_classes[$safe_key][] = $class_name;
        }

        return $this;
    }

    protected $_node_safe_styles = array();

    /**
     * 
     * @param DOMElement $node
     * @param array $styles
     * @return \OSC_Editor
     */
    public function nodeAddSafeStyle($node, $styles) {
        $safe_key = null;

        if ($node->hasAttribute('osc-editor-safe-style')) {
            $keys = $node->getAttribute('osc-editor-safe-style');
            $keys = explode(':', $keys);

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_styles[$keys[1]])) {
                $safe_key = $keys[1];
            }
        }

        if (!$safe_key) {
            $safe_key = OSC::makeUniqid();
            $node->setAttribute('osc-editor-safe-style', $this->_session_id . ':' . $safe_key);
            $this->_node_safe_styles[$safe_key] = array();
        }

        foreach ($styles as $style_key => $style_value) {
            if ($style_value === false || $style_value === null || $style_value === '') {
                if (isset($this->_node_safe_styles[$safe_key][$style_key])) {
                    unset($this->_node_safe_styles[$safe_key][$style_key]);
                }

                continue;
            }

            $this->_node_safe_styles[$safe_key][$style_key] = $style_value;
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $node
     * @return \OSC_Editor
     */
    public function nodeMarkAsSafe($node, $deep_flag = false) {
        $node->setAttribute('osc-editor-safe', $this->_session_id);

        if ($deep_flag) {
            foreach ($node->childNodes as $child) {
                $this->nodeMarkAsSafe($child, true);
            }
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $node
     * @return boolean
     */
    public function nodeIsSafe($node) {
        return $node->getAttribute('osc-editor-safe') == $this->_session_id;
    }

    /**
     * 
     * @param DOMElement $node
     * @return boolean
     */
    public function cleanUnSafeNode($node) {
        if ($node->nodeType !== XML_ELEMENT_NODE) {
            return false;
        }

        if (!$this->nodeIsSafe($node)) {
            $node->parentNode->removeChild($node);
            return true;
        }

        for ($i = 0; $i < $node->childNodes->length; $i ++) {
            $child = $node->childNodes->item($i);

            if ($this->cleanUnSafeNode($child)) {
                $i --;
            }
        }

        return false;
    }

    /**
     * 
     * @param DOMElement $node
     * @return OSC_Editor
     */
    public function nodeCleanStyle($node, $allowed_style = array()) {
        if (!is_array($allowed_style)) {
            $allowed_style = array();
        }

        $style_arr = $this->nodeParseStyle($node);

        foreach ($style_arr as $k => $v) {
            if (!in_array($k, $allowed_style, true)) {
                unset($style_arr[$k]);
            }
        }

        if ($node->hasAttribute('osc-editor-safe-style')) {
            $keys = explode(':', $node->getAttribute('osc-editor-safe-style'));

            $node->removeAttribute('osc-editor-safe-style');

            if (count($keys) == 2 && $keys[0] == $this->_session_id && isset($this->_node_safe_styles[$keys[1]])) {
                $safe_style = $this->_node_safe_styles[$keys[1]];
                unset($this->_node_safe_styles[$keys[1]]);

                foreach ($safe_style as $k => $v) {
                    $style_arr[$k] = $v;
                }
            }
        }

        $style_string = $this->styleArrayToStyle($style_arr);

        if ($style_string) {
            $node->setAttribute('style', $style_string);
        } else {
            $node->removeAttribute('style');
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $src_node
     * @param DOMElement $dest_node
     * @return OSC_Editor
     */
    protected function _nodeCopyAttributes($src_node, $dest_node) {
        for ($i = 0; $i < $src_node->attributes->length; $i ++) {
            $dest_node->setAttribute($src_node->attributes->item($i)->name, $src_node->getAttribute($src_node->attributes->item($i)->name));
        }

        return $this;
    }

    /**
     * 
     * @param DOMElement $parent
     * @param DOMElement $child
     * @return OSC_Editor
     */
    protected function _nodeAppendChild($parent, $child) {
        $parent->appendChild($child);
        return $this;

        if ($child->nodeType == XML_TEXT_NODE && $parent->lastChild && $parent->lastChild->nodeType == XML_TEXT_NODE) {
            $parent->lastChild->nodeValue .= $child->nodeValue;

            if ($child->parentNode) {
                $child->parentNode->removeChild($child);
            }
        } else {
            $parent->appendChild($child);
        }

        return $this;
    }

}
