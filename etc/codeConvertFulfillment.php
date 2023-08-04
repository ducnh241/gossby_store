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
define('OSC_INNER', 1);
define('OSC_SITE_PATH', dirname(__FILE__));
define('OSC_SITE_KEY', 'osecore');

include OSC_SITE_PATH . '/app.php';

set_time_limit(0);
ini_set('memory_limit', '2000M');

$tmp_file_path = OSC_SITE_PATH.'/dataConvertFulfillment.json';

if (!$tmp_file_path) {
    throw new Exception('File is not exists or removed');
}

$errors = 0;

$success = 0;

$JSON = OSC::decode(file_get_contents($tmp_file_path), true);

$codes = array_keys($JSON);

/* @var $DB OSC_Database */
$DB = OSC::core('database');

$DB->query("SELECT order_id, code FROM osc_catalog_order WHERE  FIND_IN_SET(code, :codes) LIMIT ".count($codes), ['codes' => implode(',', $codes)]);

$order_ids = [];

while ($row = $DB->fetchArray()) {
    $order_ids[$row['code']] = $row['order_id'];
}

$DB->query("SELECT record_id, order_id, line_items FROM osc_catalog_order_fulfillment WHERE  FIND_IN_SET(order_id, :order_ids) ", ['order_ids' => implode(',', $order_ids)]);

$data = [];

while ($row = $DB->fetchArray()) {
     foreach (OSC::decode($row['line_items']) as $line_id => $line) {
        $key = $row['order_id'].':'.$line_id;

        $data[$key] = $row['record_id'];
    }
}

foreach ($JSON as $code => $map) {
    $idx = '';
    foreach ($map as $order_line_id => $item) {
        $idx = $order_ids[$code].':'.$order_line_id;
        if (isset($data[$idx])) {
            break;
        }
    }
    try {
        $DB->update('catalog_order_fulfillment', ['shipping_carrier' => $item['shipping_carrier'], 'tracking_number' => $item['tracking_number'], 'tracking_url' => $item['tracking_url'] ], 'record_id=' . $data[$idx], 1, 'update_tracing_fulfillment');

        echo $data[$idx].'-'.$idx. ': success'."<br />";
    } catch (Exception $ex) {
        echo $data[$idx].' - '.$code.' - '.$idx. ' : fail. '.$ex->getMessage()."<br />";
    }
}

echo "DONE";