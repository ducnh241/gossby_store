<?php

class Helper_Core_Embed {

    protected $_width = 0;
    protected $_height = 0;
    protected static $_processors = array();

    public function __construct() {
        self::_initialize();
    }

    protected static function _initialize() {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;

        self::$_processors = array();

        OSC::core('observer')->dispatchEvent('core/embed/collect_processor', array('processors' => &self::$_processors));

        $core_embed_processor_dir = dirname(__FILE__) . '/embed';

        $dh = opendir($core_embed_processor_dir);

        while ($f = readdir($dh)) {
            if ($f == '.' || $f == '..') {
                continue;
            }

            $processor_name = preg_replace('/^(([^\.]+)\.).+$/i', '\\2', $f);

            if ($processor_name == 'abstract') {
                continue;
            }

            $processor = OSC::helper('core/embed_' . $processor_name);

            self::$_processors[$processor->getName()] = $processor;
        }
    }

    public function parse($embed, $width = 0, $height = 0, $allow_all_iframe = false) {
        $width = intval($width);
        $height = intval($height);

        if ($width < 1 || $height < 1) {
            $width = 0;
            $height = 0;
        }

        $this->_width = $width;
        $this->_height = $height;

        foreach (self::$_processors as $processor) {
            $embed_data = $processor->setWidth($width)->setHeight($height)->parse($embed);

            if ($embed_data) {
                break;
            }
        }

        if (!$embed_data) {
            $embed_data = $this->_parse($embed, $allow_all_iframe);
        }

        if (!$embed_data) {
            throw new OSC_Exception_Condition('Embed không chính xác hoặc không được cho phép');
        }

        if (is_array($embed_data['code'])) {
            if (!isset($embed_data['code']['type']) || !$embed_data['code']['type']) {
                throw new Exception('Hệ thống không xác định được embed type');
            }

            if ($embed_data['code']['type'] == 'iframe') {
                $width = 0;
                $height = 0;

                if (isset($embed_data['code']['width']) && $embed_data['code']['width'] > 0 && isset($embed_data['code']['height']) && $embed_data['code']['height'] > 0) {
                    $width = $embed_data['code']['width'];
                    $height = $embed_data['code']['height'];
                }

                $embed_data['code'] = "<iframe src=\"{$embed_data['code']['url']}\"" . ($width > 0 ? " width=\"{$width}\" height=\"{$height}\"" : '') . ' allowfullscreen></iframe>';
            } else if ($embed_data['code']['type'] == 'flash') {
                $width = 0;
                $height = 0;

                if (isset($embed_data['code']['width']) && $embed_data['code']['width'] > 0 && isset($embed_data['code']['height']) && $embed_data['code']['height'] > 0) {
                    $width = $embed_data['code']['width'];
                    $height = $embed_data['code']['height'];
                }

                $flash_code = '<object' . ($width > 0 ? " width=\"{$width}\" height=\"{$height}\"" : '') . '>';
                $embed_tag = '<embed type="application/x-shockwave-flash"' . ($width > 0 ? " width=\"{$width}\" height=\"{$height}\"" : '');

                foreach ($embed_data['code']['params'] as $key => $val) {
                    $flash_code .= '<param name="' . $key . '" value="' . $val . '"></param>';
                    $embed_tag .= ' ' . ($key == 'movie' ? 'src' : $key) . '="' . $val . '"';
                }

                $embed_tag .= '></embed>';
                $flash_code .= $embed_tag . '</object>';

                $embed_data['code'] = $flash_code;
            } else {
                throw new Exception('Hệ thống không hỗ trợ embed type [' . $embed_data['code']['type'] . ']');
            }
        }

        $embed_data['code'] = trim($embed_data['code']);

        if (!$embed_data['code']) {
            throw new Exception('Hệ thống không tạo được embed code');
        }

        foreach (array('thumbnail_url', 'site_url', 'site_name', 'video_title') as $key) {
            if (!isset($embed_data[$key])) {
                $embed_data[$key] = '';
            }
        }

        return $embed_data;
    }

    protected function _parse($embed_code, $allow_all_iframe) {
        $document = new DOMDocument();
        libxml_use_internal_errors(true);
        $document->loadHTML('<?xml encoding="UTF-8">' . $embed_code);
        libxml_clear_errors();

        foreach ($document->childNodes as $item) {
            if ($item->nodeType == XML_PI_NODE) {
                $document->removeChild($item);
            }
        }

        $document->encoding = 'UTF-8';

        $iframe = $document->getElementsByTagName('iframe')->item(0);

        if ($iframe) {
            $url = $iframe->getAttribute('src');

            if (!$url) {
                throw new Exception("Iframe URL không được rỗng");
            }

            foreach (self::$_processors as $processor) {
                $embed_data = $processor->parseFromUrl($url);

                if ($embed_data) {
                    return $embed_data;
                }
            }

            if (!$allow_all_iframe) {
                return false;
            }

            return array('type' => 'iframe', 'url' => $url);
        }

        $obj_node = $document->getElementsByTagName('object')->item(0);
        $param_nodes = array();

        if ($obj_node) {
            $embed_node = $obj_node->getElementsByTagName('embed')->item(0);
            $param_nodes = $obj_node->getElementsByTagName('param');
        } else {
            $embed_node = $document->getElementsByTagName('embed')->item(0);
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

        if (isset($params['movie'])) {
            foreach (self::$_processors as $processor) {
                $embed_data = $processor->parseFromUrl($params['movie']);

                if ($embed_data) {
                    return $embed_data;
                }
            }

            return array('type' => 'flash', 'params' => $params);
        }

        foreach (self::$_processors as $processor) {
            $embed_data = $processor->parseFromDom($params['movie']);

            if ($embed_data) {
                return $embed_data;
            }
        }
    }

}
