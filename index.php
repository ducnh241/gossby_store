<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

if (!defined('OSC_CACHE_TIME')) {
    define('OSC_CACHE_TIME', 7200);
}

if (!defined('OSC_CACHE_HTML_TIME')) {
    define('OSC_CACHE_HTML_TIME', 3600);
}

if (!defined('OSC_CACHE_HTML_ERROR_TIME')) {
    define('OSC_CACHE_HTML_ERROR_TIME', 300);
}

if (!defined('OSC_INNER')) {
    define('OSC_INNER', 1);
}

if (!defined('OSC_SITE_PATH')) {
    define('OSC_SITE_PATH', dirname(__FILE__));
}

if (file_exists(OSC_SITE_PATH . '/env.php')) {
    include OSC_SITE_PATH . '/env.php';
}

if (!defined('OSC_SITE_KEY')) {
    define('OSC_SITE_KEY', 'osecore');
}

if (!defined('OSC_PRIMARY_STORE')) {
    define('OSC_PRIMARY_STORE', 0);
}

if (!defined('OSC_IS_DEVELOPER_KEY')) {
    define('OSC_IS_DEVELOPER_KEY', 'is_developer');
}

if (!defined('SETTING_PERSONALIZE_SYNC_REQUEUE')) {
    define('SETTING_PERSONALIZE_SYNC_REQUEUE', 5);
}

if (!defined('SETTING_PERSONALIZE_SYNC_NEXT_TIME')) {
    define('SETTING_PERSONALIZE_SYNC_NEXT_TIME', 60);
}

if (!defined('BOX_TELEGRAM_TELEGRAM_GROUP_ID')) {
    define('BOX_TELEGRAM_TELEGRAM_GROUP_ID', '');
}

if (!defined('PRODUCT_HIDDEN_TELEGRAM_GROUP_ID')) {
    define('PRODUCT_HIDDEN_TELEGRAM_GROUP_ID', '');
}

if (!defined('S3_REGION')) {
    define('S3_REGION', '');
}

if (!defined('S3_BUCKET')) {
    define('S3_BUCKET', '');
}

if (!defined('S3_CREDENTIALS_KEY')) {
    define('S3_CREDENTIALS_KEY', '');
}

if (!defined('S3_CREDENTIALS_SECRET')) {
    define('S3_CREDENTIALS_SECRET', '');
}

include dirname(__FILE__) . '/app.php';

try {
    OSC::process();
} catch (Exception $ex) {
    if (in_array(trim(strtolower($ex->getMessage())), ['controller is not defined', 'controller is not exist', 'action is not exist'], true)) {
        OSC_Controller::notFound();
    }

    throw new Exception($ex->getMessage());
}
