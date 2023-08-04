<?php

class Helper_Core_Embed_Facebook extends Helper_Core_Embed_Abstract {

    public function parse($code) {
        return $this->parseFromUrl($code);
    }

    public function parseFromUrl($url) {
        $video_id = $this->_getVideoId($url);

        if (!$video_id) {
            return false;
        }

        return array(
            'code' => array('type' => 'iframe', 'url' => $this->_getIframeEmbedUrl($video_id)),
            'thumbnail_url' => $this->_getThumbUrl($video_id),
            'site_url' => $this->_getSiteUrl($video_id),
            'site_name' => 'Facebook'
        );
    }

    public function parseFromDom($document) {
        $xpath = new DOMXPath($document);

        $fb_data_node = $xpath->query("//*[@class='fb-video'][@data-href]")->item(0);

        if (!$fb_data_node) {
            return false;
        }

        $url = $fb_data_node->getAttribute('data-href');

        if (!$url) {
            return false;
        }

        return $this->parseFromUrl($url);
    }

    protected function _getVideoId($url) {
        if (!preg_match('/^(https?:)?\/\//i', $url)) {
            $url = 'https://facebook.com/' . $url;
        }

        if (preg_match('/^https?:\/\/(w{3}\.)?facebook\.com\/+plugins\/+video\.php.*[?&]href=([^&#]+)([^#]+(#.*)?)?$/i', $url, $matches)) {
            $url = urldecode($matches[2]);
        }

        if (preg_match('/^https?:\/\/(w{3}\.)?facebook\.com\/+([^?]+\/+)?video\.php\.*[\?&]v=(\d+)(&.+)?$/i', $url, $matches)) {
            return $matches[3];
        }

        if (preg_match('/^https?:\/\/(w{3}\.)?facebook\.com(\/+.*)\/+videos\/+(vb\.\d+\/+)?(\d+)([\/\?].*)?$/i', $url, $matches)) {
            return $matches[4];
        }

        return false;
    }

    protected function _getIframeEmbedUrl($video_id) {
        return 'https://www.facebook.com/plugins/video.php?href=' . urlencode($this->_getSiteUrl($video_id)) . '&show_text=0';
    }

    protected function _getSiteUrl($video_id) {
        return 'https://www.facebook.com/video/video.php?v=' . $video_id;
    }

    protected function _getThumbUrl($video_id) {
        return 'https://graph.facebook.com/' . $video_id . '/picture';
    }

}
