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

/* @var $DB OSC_Database */
$DB = OSC::core('database');

$data = [];

$DB->query("SELECT * FROM osc_catalog_item_customize_design WHERE printer_image_url != ''");

while ($row = $DB->fetchArray()) {
    $data[$row['record_id']] = ['printer_image_url' => $row['printer_image_url'], 'design_image_url' => $row['design_image_url']];
}

$line_design = [];
$design_ids = implode(',', array_keys($data));

$DB->query("SELECT * FROM osc_catalog_item_customize_order_map WHERE design_id in ({$design_ids})");

while ($row = $DB->fetchArray()) {
    $line_design[$row['order_line_id']] = $data[$row['design_id']];
}

$order_line_ids  = implode(',', array_keys($line_design));

$DB->query("SELECT order_id, item_id, custom_data, options FROM osc_catalog_order_item WHERE item_id in ({$order_line_ids}) and custom_data LIKE '%\"key\":\"customize\"%'");
$count = 0;
while ($row = $DB->fetchArray()) {
    $row['custom_data'] = OSC::decode($row['custom_data']);

    $change = 0;
    $is_mug = 0;
    foreach (OSC::decode($row['options']) as $option) {
        if (preg_match('/^.*?(\d+)\s*oz.*?$/i', strtolower($option['value']), $matches)) {
            $is_mug = 1;
            break;
        }
    }
    $custom_data = [];

    foreach ($row['custom_data'] as $key => $value) {
        if ($value['key'] == 'customize') {
            if (!isset($value['data']['design_image_urls']) || $value['data']['design_image_urls'] == '') {
                $value['data']['design_image_urls'] = $line_design[$row['item_id']]['design_image_url'];
                $change += 1;
            }
            if (!isset($value['data']['printer_image_url']) || $value['data']['printer_image_url'] == '') {
                $value['data']['printer_image_url'] = $line_design[$row['item_id']]['printer_image_url'];
                $change += 1;
            }
            if (!isset($value['data']['is_mug'])) {
                $value['data']['is_mug'] = $is_mug;
                $change += 1;
            }
        }
        $custom_data[] = $value;
    }
    $count ++;
    if ($change < 1) {
        continue;
    }

    try {
        $item_id = $row['item_id'];
        $DB->query("UPDATE osc_catalog_order_item SET custom_data = :custom_data WHERE item_id = {$item_id} Limit 1", ['custom_data' => OSC::encode($custom_data)],$item_id );
        if ($DB->getNumAffected($record_id) > 0) {
            echo $row['item_id'].': success'."\n";
            OSC::core('observer')->dispatchEvent('catalog/orderUpdate', $row['order_id']);
        } else {
            echo $row['item_id'].': error'."\n";

        }
        
    } catch (Exception $e) {
        echo $row['item_id'].': error'.$e->getMessage()."\n";
        die;
    }
}

echo "DONE";