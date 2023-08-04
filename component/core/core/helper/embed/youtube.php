<?php

class Helper_Core_Embed_Youtube extends Helper_Core_Embed_Abstract {

    public function parse($code) {
        return $this->parseFromUrl($code);
    }

    public function parseFromUrl($url) {
        $code = $this->_getCode($url);

        if (!$code) {
            return false;
        }

        return array(
            'code' => array('type' => 'iframe', 'url' => $this->_getIframeEmbedUrl($code)),
            'thumbnail_url' => $this->_getThumbUrl($code),
            'site_url' => $this->_getSiteUrl($code),
            'site_name' => 'Youtube'
        );
    }

    protected function _getCode($url) {
        $url = preg_replace('/^([^#]+)#.*$/i', '\\1', $url);
        $url = str_replace('&amp;', '&', $url);

        if (preg_match('/https?:\/\/(w{3}\.)?(m\.)?youtube.com\/(.+\/)?v\/+([^\/\?]+)([\/\?]+.*)?$/i', $url, $matches)) {
            return $matches[4];
        }

        if (preg_match('/https?:\/\/(www\.)?(m\.)?youtube.com\/.*[?&;]v=([^&]+)(&.+)?$/i', $url, $matches)) {
            return $matches[3];
        }

        if (preg_match('/https?:\/\/(www\.)?(m\.)?youtu\.be\/([^\/]+)/i', $url, $matches)) {
            return $matches[3];
        }

        if (preg_match('/https?:\/\/(www\.)?(m\.)?youtube(-nocookie)?.com\/embed\/([^\/]+)/i', $url, $matches)) {
            return $matches[4];
        }

        return '';
    }

    protected function _getIframeEmbedUrl($code) {
        return 'https://www.youtube.com/embed/' . $code . '?showinfo=0';
    }

    protected function _getSiteUrl($code) {
        return 'https://www.youtube.com/watch?v=' . $code;
    }

    protected function _getThumbUrl($code) {
        return 'http://i2.ytimg.com/vi/' . $code . '/hqdefault.jpg';
    }

}
