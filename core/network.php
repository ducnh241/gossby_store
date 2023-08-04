<?php

class OSC_Network {

    public static function isValidDomainName($domain_name) {
        return preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) && preg_match("/^.{1,253}$/", $domain_name) && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name);
    }

    /**
     * 
     * @param string $url
     * @param boolean $reload_cache
     * @return string
     * @throws Exception
     */
    public function traceUrl($url, $reload_cache = false) {
        static $cached = array();

        if (!isset($cached[$url]) || $reload_cache) {
            try {
                $response = $this->curl($url, array('browser', 'use_proxy', 'custom_opts' => array(CURLOPT_NOBODY => true, CURLOPT_HEADER => false)));
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), $ex->getCode());
            }

            $cached[$url] = $response['effective_url'];
        }

        return $cached[$url];
    }

    public function isMobileUrl($url) {
        return preg_match('/^((http|ftp)s?:)?\/\/(m|touch|mobile)\..*/i', $url);
    }

    public function curl($url, $config = array()) {
        if (!is_array($config)) {
            $config = array();
        }

        if (isset($config['request_params']) && is_array($config['request_params']) && count($config['request_params']) > 0) {
            if (!isset($config['data']) || !is_array($config['data'])) {
                $config['data'] = array();
            }

            $config['data'] = $config['request_params'] + $config['data'];

            unset($config['request_params']);
        }

        $cookie_data = array();
        $headers = array();

        if (in_array('cookie', $config, true)) {
            foreach ($_COOKIE as $name => $value) {
                $cookie_data[$name] = $name . '=' . addslashes($value);
            }
        }

        if (isset($config['cookie_data']) && is_array($config['cookie_data']) && count($config['cookie_data']) > 0) {
            foreach ($config['cookie_data'] as $name => $value) {
                $cookie_data[$name] = $name . '=' . addslashes(strval($value));
            }
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (in_array('browser', $config, true) || in_array('chrome', $config, true)) {
            $config[] = $this->isMobileUrl($url) ? 'chrome_mobile' : 'chrome_desktop';
        } else if (in_array('browser_desktop', $config, true)) {
            $config[] = 'chrome_desktop';
        } else if (in_array('browser_mobile', $config, true)) {
            $config[] = 'chrome_mobile';
        }

        if (in_array('chrome_desktop', $config, true)) {
            $config['user_agent'] = 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/41.0.2228.0 Safari/537.36';
        } else if (in_array('chrome_mobile', $config, true)) {
            $config['user_agent'] = 'Mozilla/5.0 (Linux; U; Android 4.0.3; ko-kr; LG-L160L Build/IML74K) AppleWebkit/534.30 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.30';
        }

        if (isset($config['user_agent'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $config['user_agent']);
        }

        $proxy = OSC::systemRegistry('network_proxy');

        if (!is_array($proxy)) {
            $proxy = [];
        }

        if (isset($config['proxy']) && is_array($config['proxy']) && isset($config['proxy']['ip']) && isset($config['proxy']['port'])) {
            $proxy = $config['proxy'];
        }

        if (isset($proxy['ip']) && isset($proxy['port'])) {
            if (isset($proxy['user'])) {
                $proxy['userpwd'] = $proxy['user'] . ':';

                if (isset($proxy['password'])) {
                    $proxy['userpwd'] .= $proxy['password'];
                }
            }

            curl_setopt($ch, CURLOPT_PROXY, $proxy['ip']);
            curl_setopt($ch, CURLOPT_PROXYPORT, $proxy['port']);
            if (isset($proxy['type'])) {
                curl_setopt($ch, CURLOPT_PROXYTYPE, $proxy['type']);
            }
            if (isset($proxy['userpwd'])) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy['userpwd']);
            }
        }

        if (!in_array('no_redirect', $config, true)) {
            if (isset($config['max_redirect'])) {
                $config['max_redirect'] = intval($config['max_redirect']);

                if ($config['max_redirect'] < 1) {
                    $config['max_redirect'] = 1;
                } else if ($config['max_redirect'] > 100) {
                    $config['max_redirect'] = 100;
                }
            } else {
                $config['max_redirect'] = 10;
            }

            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, $config['max_redirect']);
        }

        if (isset($config['timeout'])) {
            $config['timeout'] = intval($config['timeout']);

            if ($config['timeout'] < 1) {
                $config['timeout'] = 0;
            }
        } else {
            $config['timeout'] = 5;
        }

        if (isset($config['connect_timeout'])) {
            $config['connect_timeout'] = intval($config['connect_timeout']);

            if ($config['connect_timeout'] < 1) {
                $config['connect_timeout'] = 0;
            }
        } else {
            $config['connect_timeout'] = 5;
        }

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $config['connect_timeout']);
        curl_setopt($ch, CURLOPT_TIMEOUT, $config['timeout']);

        if (count($cookie_data) > 0) {
            curl_setopt($ch, CURLOPT_COOKIE, implode(';', $cookie_data));
        }

        if (isset($config['files']) && is_array($config['files']) && count($config['files']) > 0) {
            if (!isset($config['data']) || !is_array($config['data'])) {
                $config['data'] = array();
            }

            foreach ($config['files'] as $field_name => $file) {
                if (!is_array($file)) {
                    $file = (string) $file;
                    $file = array('path' => $file, 'name' => preg_replace('/^(.*\/+)?([^\/]+)$/', '\\2', $file));
                }

                if (!file_exists($file['path'])) {
                    throw new Exception('File ' . $file['path'] . ' is not exist');
                }

                if (!is_file($file['path'])) {
                    throw new Exception('The path ' . $file['path'] . ' is not file');
                }

                if (!isset($file['name']) || !$file['name']) {
                    $file['name'] = preg_replace('/^(.*\/+)?([^\/]+)$/', '\\2', $file['path']);
                }

                $config['data'][$field_name] = class_exists('CURLFile') ? (new CURLFile($file['path'], '', $file['name'])) : ('@' . $file['path'] . ';filename=' . $file['name']);
            }

            if (defined('CURLOPT_SAFE_UPLOAD')) {
                curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
            }

            $config[] = 'post_raw_data';
        }

        if (isset($config['data'])) {
            if (in_array('json', $config, true)) {
                $config['json'] = $config['data'];
            } else if (in_array('xml', $config, true)) {
                $config['xml'] = $config['data'];
            } else if (!in_array('post_raw_data', $config, true) && is_array($config['data'])) {
                $config['data'] = http_build_query($config['data']);
            }
        }

        if (isset($config['json'])) {
            $config['data'] = OSC::encode($config['json']);

            $headers['Content-Type'] = 'application/json';

            unset($config['json']);
        } else if (isset($config['xml'])) {
            $config['data'] = $config['xml'];

            $headers['Content-Type'] = 'application/xml';

            unset($config['xml']);
        }

        if (isset($config['data'])) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $config['data']);
        }

        if (isset($config['request_method'])) {
            $config['request_method'] = strtoupper(trim($config['request_method']));

            if (!in_array($config['request_method'], array('GET', 'POST', 'HEAD', 'PUT', 'DELETE', 'TRACE', 'CONNECT', 'OPTIONS', 'PATCH'))) {
                $config['request_method'] = 'GET';
            }

            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $config['request_method']);
        }

        if (isset($config['headers']) && is_array($config['headers']) && count($config['headers']) > 0) {
            foreach ($config['headers'] as $key => $value) {
                $headers[$key] = $value;
            }
        }

        if (count($headers) > 0) {
            foreach ($headers as $key => $value) {
                $headers[$key] = $key . ': ' . $value;
            }

            $headers = array_values($headers);

            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        if (isset($config['custom_opts']) && is_array($config['custom_opts']) && count($config['custom_opts']) > 0) {
            foreach ($config['custom_opts'] as $key => $val) {
                curl_setopt($ch, $key, $val);
            }
        }

        OSC::core('debug')->startProcess('CURL', OSC::registry('osc-request-id') . '::' . $url);
        $raw_response = curl_exec($ch);
        OSC::core('debug')->endProcess();

        if (curl_errno($ch) != 0) {
            throw new Exception(curl_error($ch) . ' : ' . $url . ' : ' . print_r($config['data'], 1));
        }

        $effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

        $request_raw_headers = curl_getinfo($ch, CURLINFO_HEADER_OUT);
        $response_raw_headers = substr($raw_response, 0, $header_size);

        $request_headers = static::parseHeaders($request_raw_headers);
        $response_headers = static::parseHeaders($response_raw_headers);

        $response_code = null;
        $response_reason_phrase = null;
        $response_protocal_version = null;

        if (isset($response_headers[0]) && preg_match('/^HTTP\/(\d+(\.\d+)?)\s+(\d+)(\s+(.+))?$/i', $response_headers[0], $matches)) {
            $response_code = intval($matches[3]);
            $response_reason_pharse = $matches[5];
            $response_protocal_version = $matches[1];
        } else {
            
        }

        $response = substr($raw_response, $header_size);

        if (isset($response_headers['Content-Type']) && preg_match('/^\s*application\/json\s*(;.+)?$/i', $response_headers['Content-Type'])) {
            $response = json_decode($response, true);
        }

        if (isset($response_headers['Content-Encoding']) && $response_headers['Content-Encoding'] == 'gzip' && !in_array('skip_auto_decompress', $config, true)) {
            $response = gzdecode($response);
        }

        return array(
            'request_data' => isset($config['data']) ? $config['data'] : array(),
            'request_raw_headers' => $request_raw_headers,
            'request_headers' => $request_headers,
            'response_raw_headers' => $response_raw_headers,
            'response_headers' => $response_headers,
            'response_code' => $response_code,
            'response_reason_phrase' => $response_reason_pharse,
            'response_protocol_version' => $response_protocal_version,
            'content' => $response,
            'raw_response' => $raw_response,
            'effective_url' => $effective_url
        );
    }

    public static function parseHeaders($header_string) {
        $header_string = str_replace("\r\n", "\n", $header_string);
        $header_string = str_replace("\n\t", "\n", $header_string);
        $header_string = explode("\n\n", trim($header_string));
        $header_string = $header_string[count($header_string) - 1];

        $headers = array();
        $key = '';

        foreach (explode("\n", $header_string) as $i => $h) {
            $h = explode(':', $h, 2);

            if (isset($h[1])) {
                if (!isset($headers[$h[0]])) {
                    $headers[$h[0]] = trim($h[1]);
                } else if (is_array($headers[$h[0]])) {
                    $headers[$h[0]] = array_merge($headers[$h[0]], array(trim($h[1])));
                } else {
                    $headers[$h[0]] = array_merge(array($headers[$h[0]]), array(trim($h[1])));
                }

                $key = $h[0];
            } else {
                if (substr($h[0], 0, 1) == "\t") {
                    $headers[$key] .= "\r\n\t" . trim($h[0]);
                } else if (!$key) {
                    $headers[0] = trim($h[0]);
                    trim($h[0]);
                }
            }
        }

        return $headers;
    }

    public function forwardRequest($url) {
        $headers = OSC::core('request')->headerGetAll();
        
        if(isset($headers['Cookie'])) {
            unset($headers['Cookie']);
        } 

        if(isset($headers['cookie'])) {
            unset($headers['cookie']);
        }

        unset($headers['Accept-Encoding']);
        
        $headers['client-ip'] = OSC::getClientIp(true);
        $headers['X-Forwarded-For'] = OSC::getClientIp(true);
        $headers['osc-site-key'] = OSC_SITE_KEY;
        $headers['osc-request-id'] = OSC::registry('osc-request-id');

        $request_logging['osc_request_id'] = $headers['osc-request-id'];
        $request_logging['osc_request_url'] = 'BEFORE_CURL:' . $url;
        $request_logging['osc_request_start_time'] = time();
        $request_logging['osc_request_end_time'] = time();
        OSC::helper('catalog/react_common')->writeLogNextJS($request_logging);

        $response = OSC::core('network')->curl($url, ['cookie', 'headers' => $headers, 'timeout' => 10]);

        $request_logging['osc_request_url'] = 'AFTER_CURL:' . $url;
        $request_logging['osc_request_end_time'] = time();
        OSC::helper('catalog/react_common')->writeLogNextJS($request_logging);

        return $response;
    }

    public function forwardRequestResponse($response) {

        if (isset($response['response_code']) && $response['response_code'] == 404) {
            header("HTTP/1.0 404 Not Found");
        }

        if(isset($response['response_headers']['Set-Cookie'])) {
            if(! is_array($response['response_headers']['Set-Cookie'])) {
                $response['response_headers']['Set-Cookie'] = [$response['response_headers']['Set-Cookie']];
            }
            
            foreach($response['response_headers']['Set-Cookie'] as $cookie) {
                $cookie = explode(';', $cookie);
                $cookie = array_map(function($val){ return explode('=', trim($val), 2);}, $cookie);
                $cookie = array_filter($cookie, function($val){ return count($val) == 2; });
                
                $key = $cookie[0][0];
                $value = $cookie[0][1];
                
                unset($cookie[0]);
                
                $buff = [];
                
                foreach($cookie as $pair) {
                    $buff[$pair[0]] = $pair[1];
                }
                
                $cookie = $buff;
                
                $cookie['expires'] = strtotime($cookie['expires']);
                
                setcookie ($key, $value, $cookie['expires'], $cookie['path']??'', $cookie['domain']??'', $cookie['secure']??false, $cookie['httponly']??false);
            }
            
            unset($response['response_headers']['Set-Cookie']);
        }       
    
        unset($response['response_headers'][0]);
        
        foreach($response['response_headers'] as $key => $value) {
            header($key . ': ' . $value);
        }

        echo $response['content'];
        
        die;
    }

}
