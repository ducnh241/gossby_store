<?php

class Helper_PersonalizedDesign_Spotify {
    protected $_access_token = null;

    public function generateAccessToken($use_cache = true) {
        if ($this->_access_token === null || !$use_cache) {
            $client_id = OSC::helper('core/setting')->get('spotify_api/client_id');
            $client_secret = OSC::helper('core/setting')->get('spotify_api/client_secret');

            $cache_key = "SpotifyAccessToken.{$client_id}.{$client_secret}";
            $adapter = OSC::core('cache')->getAdapter();

            $this->_access_token = $adapter->get($cache_key);

            if ($this->_access_token === null || !$this->_access_token || !$use_cache) {
                if ($client_id && $client_secret) {
                    $url = 'https://accounts.spotify.com/api/token';

                    try {
                        $credentials = "{$client_id}:{$client_secret}";

                        $headers = array(
                            "Accept: */*",
                            "Content-Type: application/x-www-form-urlencoded",
                            "User-Agent: runscope/0.1",
                            "Authorization: Basic " . base64_encode($credentials));
                        $data = 'grant_type=client_credentials';

                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        curl_setopt($ch, CURLOPT_POST, 1);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

                        $response = curl_exec($ch);
                        curl_close($ch);

                        $result = json_decode($response);

                        if (isset($result->access_token) && isset($result->expires_in)) {
                            $this->_access_token = $result->access_token;
                            $adapter->set($cache_key, $this->_access_token, (int) $result->expires_in - 100);
                        }
                    } catch (Exception $ex) {
                        throw $ex;
                    }
                }
            }
        }

        return $this->_access_token;
    }

    public function generatePreview($uri, $config) {
        try {
            if (!$uri) {
                return '';
            }

            $result = '';
            $svg_content = file_get_contents('https://scannables.scdn.co/uri/plain/svg/000000/white/640/' . trim($uri));

            if ($svg_content && preg_match('#<svg[^>]+>(.*?)</svg>#is', $svg_content, $match)) {
                $svg_content = $match[1];
                $svg_content = str_replace(['fill="#000000"', 'fill="#ffffff"'], ['fill="#_BACKGROUND_COLOR_#"', 'fill="#_BAR_COLOR_#"'], $svg_content);
                $background_color = empty($config['background_color']) ? 'none' : (preg_match('/^#([a-fA-F0-9]{6})$/', $config['background_color']) ? $config['background_color'] : '#000000');
                $svg_content = str_replace('fill="#_BACKGROUND_COLOR_#"', 'fill="' . $background_color . '"', $svg_content);

                $bar_color = !empty($config['bar_color']) && preg_match('/^#([a-fA-F0-9]{6})$/', $config['bar_color']) ? $config['bar_color'] : '#ffffff';
                $svg_content = str_replace('fill="#_BAR_COLOR_#"', 'fill="' . $bar_color . '"', $svg_content);

                $result = $svg_content;
            }

            return $result;
        } catch (Exception $ex) {
            return '';
        }
    }

    public function generateQrCodeSvg($url, $options = []) {
        try {
            if (!$url) {
                throw new Exception('Spotify URL is not valid');
            }

            $store_info = OSC::getStoreInfo();

            if (isset($options['background_color'])) {
                $options['background'] = $options['background_color'];
                unset($options['background_color']);
            }

            if (isset($options['bar_color'])) {
                $options['color'] = $options['bar_color'];
                unset($options['bar_color']);
            }

            $request_data = [
                'qr_code_data' => $url,
                'options' => $options,
            ];

            $response = OSC::core('network')->curl(
                OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/getQrCodeSvg', [
                    'headers' => ['Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_data), $store_info['secret_key'])],
                    'json' => $request_data,
                ]
            );

            if (!isset($response['content']['result']) || $response['content']['result'] !== 'OK') {
                throw new Exception('Get QR Code Svg failed');
            }

            $svg_content = $response['content']['data'];

            return $svg_content;
        } catch (Exception $ex) {
            return '';
        }
    }
}
