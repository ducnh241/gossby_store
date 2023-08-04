<?php

class Controller_Api_Frontend extends Abstract_Frontend_Controller
{
    public function actionCallbackAppleApi()
    {
        $id_token = $this->_request->get('id_token');
        $user = $this->_request->get('user');

        $current_language_key = OSC::core('language')->getCurrentLanguageKey();
        $base_url = OSC_FRONTEND_BASE_URL;

        static::redirect("{$base_url}/{$current_language_key}/apple/login?token={$id_token}&user_information={$user}");
    }
}
