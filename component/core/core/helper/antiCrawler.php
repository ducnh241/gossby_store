<?php

class Helper_Core_AntiCrawler {
    
    public static function enable() {
        return 0;OSC::systemRegistry('antiCrawler') == 1;
    }

    public static function track() {
        static $initialized = false;

        if ($initialized || ! static::enable()) {
            return;
        }

        $initialized = true;

        /* @var $DB OSC_Database */

        $DB = OSC::core('database');

        $client_ip = OSC::getClientIP();
        $timestamp = time();
        $uniqid = OSC::makeUniqid();

        try {
            OSC::core('cache')->set('antiCrawler.' . $client_ip, $uniqid);
        } catch (Exception $ex) {
            
        }

        $data = base64_encode(OSC::core('encode')->encode(OSC::encode(['t' => $timestamp]), $uniqid));

        $CODE = <<<EOF
$.ajax({
    url: $.base_url + '/core/common/atc',
    type: 'post',
    data: {
        d: '{$data}',
        k: '{$uniqid}'
    }
});
EOF;
        
        $CODE = base64_encode($CODE);

        OSC::helper('frontend/template')->push('var dtt = "' . $CODE . '"', 'js_init');
    }

    public static function update() {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;

        /* @var $DB OSC_Database */
        /* @var $request OSC_Request */

        $DB = OSC::core('database');
        $request = OSC::core('request');

        $client_ip = OSC::getClientIP();
        $timestamp = time();

        $data = OSC::decode(OSC::core('encode')->decode(base64_decode($request->get('d')), $request->get('k')));

        if (!is_array($data) || !isset($data['t'])) {
            die;
        }

        $data['t'] = intval($data['t']);

        if ($data['t'] < $timestamp - 10) {
            die;
        }

        $DB->select('*', 'anti_cralwer_data', 'ip="' . $client_ip . '"', null, 1, 'anti_crawler');

        $record = $DB->fetchArray('anti_crawler');

        if (!$record) {
            $DB->insert('anti_cralwer_data', ['failed_counter' => 0, 'ip' => $client_ip, 'added_timestamp' => $timestamp, 'last_timestamp' => $timestamp], 'anti_crawler');
            die;
        }

        $DB->update('anti_cralwer_data', ['failed_counter' => 0, 'added_timestamp' => $timestamp, 'last_timestamp' => $timestamp], 'ip="' . $client_ip . '"', 1, 'anti_crawler');

        die;
    }

    public static function checkBlocked() {
        static $initialized = false;

        if ($initialized) {
            return;
        }

        $initialized = true;
        
        /* @var $DB OSC_Database */
        
        $client_ip = OSC::getClientIP();

        $DB = OSC::core('database');

        $referer_url = $_SERVER['HTTP_REFERER'] ? $_SERVER['HTTP_REFERER'] : '';

        $referer_url = strtolower(trim(strval($referer_url)));

        if ($referer_url) {
            $referer_info = parse_url($referer_url);

            if ($referer_info['host'] && $referer_info['host'] != OSC::$domain) {
                $DB->select('*', 'anti_crawler_blocked_referer', ['condition' => 'referer_host=:referer_host', 'params' => ['referer_host' => $referer_info['host']]], null, 1, 'fetch_blocked');

                $row = $DB->fetchArray('fetch_blocked');

                if($row) {
                    try {
                        $DB->insert('anti_crawler_blocked_ip', ['ip' => $client_ip, 'added_timestamp' => time()], 'blocked_ip');
                    } catch(Exception $ex) {

                    }

                    die;
                }
            }            
        }

        $DB->select('*', 'anti_crawler_blocked_ip', 'ip="' . $client_ip . '"', null, 1, 'fetch_blocked');
        
        $row = $DB->fetchArray('fetch_blocked');
        
        if($row) {
            die;
        }
    }

    public static function check() {
        static $initialized = false;

        if ($initialized || ! static::enable()) {
            return;
        }

        $initialized = true;

        /* @var $DB OSC_Database */

        $DB = OSC::core('database');

        $client_ip = OSC::getClientIP();
        $timestamp = time();

        $DB->select('*', 'anti_cralwer_data', 'ip="' . $client_ip . '"', null, 1, 'anti_crawler');

        $record = $DB->fetchArray('anti_crawler');

        if (!$record) {
            $DB->insert('anti_cralwer_data', ['ip' => $client_ip, 'failed_counter' => 1, 'added_timestamp' => $timestamp, 'last_timestamp' => $timestamp], 'anti_crawler');
            return;
        }

        if ($record['last_timestamp'] < ($timestamp - 40)) {
            $record['failed_counter']++;
        }

        if ($record['failed_counter'] > 10) {
            die;
        }

        $DB->update('anti_cralwer_data', ['failed_counter' => $record['failed_counter']], 'ip="' . $client_ip . '"', 1, 'anti_crawler');
    }

}
