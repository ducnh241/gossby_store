<?php
include_once dirname(__FILE__) . '/rec_helper.php';

try {
    if (!defined('OSC_SITE_PATH')) {
        define('OSC_SITE_PATH', dirname(__FILE__));
    }


    $config_file = OSC_SITE_PATH . '/' . 'rec.config.php';

    if (file_exists($config_file)) {
        include_once $config_file;
    }

    if (!defined('OSC_SITE_KEY')) {
        define('OSC_SITE_KEY', 'OSC');
    }

    $domain = $_SERVER['SERVER_NAME'];

    $cookie_key = makeRequestChecksum('_fp', OSC_SITE_KEY);

    $track_key = $_COOKIE[$cookie_key];

    if (!$track_key) {
        throw new Exception('No track_ukey');
    }

    if (!isset($mongodb_config)) {
        $mongodb_config = [
            'username' => '',
            'password' => '',
            'host' => 'localhost',
            'port' => 27017,
            'dbname' => 'store_dev'
        ];
    }

    $action = $_GET['action'];

    if (!isset($action)) {
        throw new Exception('No recording action');
    }

    $mongodb = new OSC_Mongodb($mongodb_config);

    switch ($action) {
        case 'fingerprint':
            $mongodb->insert('record', [
                'domain' => $domain,
                'track_ukey' => $track_key,
                'page_url' => $_REQUEST['url'],
                'battery' => $_REQUEST['CHR_BATTERY'],
                'debug_tools' => $_REQUEST['CHR_DEBUG_TOOLS'],
                'memory' => $_REQUEST['CHR_MEMORY'],
                'chrome_obj' => $_REQUEST['HEADCHR_CHROME_OBJ'],
                'iframe' => $_REQUEST['HEADCHR_IFRAME'],
                'permissions' => $_REQUEST['HEADCHR_PERMISSIONS'],
                'plugins' => $_REQUEST['HEADCHR_PLUGINS'],
                'headchr_ua' => $_REQUEST['HEADCHR_UA'],
                'media_query' => $_REQUEST['MQ_SCREEN'],
                'etsl' => $_REQUEST['PHANTOM_ETSL'],
                'language' => $_REQUEST['PHANTOM_LANGUAGE'],
                'overflow' => $_REQUEST['PHANTOM_OVERFLOW'],
                'properties' => $_REQUEST['PHANTOM_PROPERTIES'],
                'phantom_ua' => $_REQUEST['PHANTOM_UA'],
                'websocket' => $_REQUEST['PHANTOM_WEBSOCKET'],
                'window_height' => $_REQUEST['PHANTOM_WINDOW_HEIGHT'],
                'selenium_driver' => $_REQUEST['SELENIUM_DRIVER'],
                'sequentum' => $_REQUEST['SEQUENTUM'],
                'transparent_pixel' => $_REQUEST['TRANSPARENT_PIXEL'],
                'video_codes' => $_REQUEST['VIDEO_CODECS'],
                'webdriver' => $_REQUEST['WEBDRIVER'],
                'history' => $_REQUEST['history'],
                'added_timestamp' => time()
            ]);
            break;
        case 'path':
            $mongodb->insert('record', [
                'domain' => $domain,
                'track_ukey' => $track_key,
                'page_url' => $_REQUEST['url'],
                'history' => intval($_REQUEST['history']),
                'is_curve' => $_REQUEST['is_curve_path'],
                'added_timestamp' => time()
            ]);
            break;
        case 'behavior':
            $mongodb->insert('record', [
                'domain' => $domain,
                'track_ukey' => $track_key,
                'page_url' => $_REQUEST['url'],
                'event' => $_REQUEST['type'],
                'target' => $_REQUEST['target'],
                'pointer' => json_encode($_REQUEST['pointer']),
                'history' => intval($_REQUEST['history']),
                'added_timestamp' => time()
            ]);
            break;
        default:
            // echo "No records";
            break;
    }

    echo 'OK';
} catch (Exception $ex) {
    echo $ex->getMessage();
}
