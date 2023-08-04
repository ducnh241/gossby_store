<?php

class Helper_Post_Common {

    public function getUrlAllPost($get_absolute_url = true): string {
        $url = '/' . (OSC::helper('core/setting')->get("post/config_post/slug") ?? '');

        if ($get_absolute_url) {
            $url = OSC_FRONTEND_BASE_URL . $url;
        }

        return $url;
    }
}