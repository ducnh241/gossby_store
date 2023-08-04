<?php

abstract class Helper_Core_Embed_Abstract {

    protected $_width = 0;
    protected $_height = 0;

    /**
     * 
     * @param int $width
     * @return \Helper_Core_Embed_Abstract
     */
    public function setWidth($width) {
        $this->_width = $width;
        return $this;
    }

    /**
     * 
     * @param int $height
     * @return \Helper_Core_Embed_Abstract
     */
    public function setHeight($height) {
        $this->_height = $height;
        return $this;
    }

    public function getName() {
        return strtolower(trim(preg_replace('/(^|.*_+)([^_]+)$/', '\\2', get_class($this))));
    }

    public function parse($code) {
        return false;
    }

    public function parseFromUrl($url) {
        return false;
    }

    public function parseFromDom($document) {
        return false;
    }

}
