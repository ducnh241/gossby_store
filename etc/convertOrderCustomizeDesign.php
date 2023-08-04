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

$DB->query("SELECT product_id, variant_id, order_id, item_id, image_url, custom_data FROM osc_catalog_order_item WHERE custom_data LIKE '%\"key\":\"customize\"%'");

$order_items = [];
$products = [];
$variants = [];
$designs = [];
$order_maps = [];
$customize_types = [];

while ($row = $DB->fetchArray()) {
    $row['custom_data'] = OSC::decode($row['custom_data']);

    $order_items[] = $row;
    $products[$row['product_id']] = null;
    $variants[$row['variant_id']] = null;
}

$DB->query("SELECT item_id, title FROM osc_catalog_item_customize");

while ($row = $DB->fetchArray()) {
    $customize_types[$row['item_id']] = $row['title'];
}

$DB->query("SELECT * FROM osc_catalog_item_customize_design");

while ($row = $DB->fetchArray()) {
    $row['customize_data'] = OSC::decode($row['customize_data']);
    $designs[$row['ukey']] = $row;
}

$DB->query("SELECT order_line_id, design_id FROM osc_catalog_item_customize_order_map");

while ($row = $DB->fetchArray()) {
    $order_maps[$row['order_line_id']] = $row['design_id'];
}

$DB->query("SELECT product_id, title, tags FROM osc_catalog_product WHERE FIND_IN_SET(product_id, :product_ids) LIMIT " . count($products), ['product_ids' => implode(',', array_keys($products))]);

while ($row = $DB->fetchArray()) {
    $row['tags'] = explode(',', $row['tags']);

    $products[$row['product_id']] = $row;
}

$DB->query("SELECT variant_id, image_id FROM osc_catalog_product_variant WHERE FIND_IN_SET(variant_id, :variant_ids) LIMIT " . count($variants), ['variant_ids' => implode(',', array_keys($variants))]);

while ($row = $DB->fetchArray()) {
    $row['image_id'] = explode(',', $row['image_id']);

    foreach ($row['image_id'] as $image_id) {
        $image_id = intval($image_id);

        if ($image_id > 0) {
            $row['image_id'] = $image_id;
            break;
        }
    }

    if (is_array($row['image_id'])) {
        $row['image_id'] = 0;
    }

    $variants[$row['variant_id']] = $row;
}

$storage = [];

foreach ($order_items as $order_item) {
    if (!isset($variants[$order_item['variant_id']])) {
        echo "VARIANT NOT FOUND: {$order_item['item_id']}\n";
        continue;
    }

    $product = $products[$order_item['product_id']];

    $new_customize = false;

    foreach ($order_item['custom_data'] as $idx => $custom_entry) {
        if ($custom_entry['key'] == 'customize') {
            if (!isset($custom_entry['data']['customize_id'])) {
                $customize_id = 0;

                foreach ($product['tags'] as $tag) {
                    if (preg_match('/^meta:customize:(\d+)$/', $tag, $matches)) {
                        $customize_id = $matches[1];
                    }
                }

                $custom_entry['data'] = [
                    'customize_id' => $customize_id,
                    'customize_title' => isset($customize_types[$customize_id]) ? $customize_types[$customize_id] : 'Unknown',
                    'customize_data' => $custom_entry['data']
                ];

                $order_item['custom_data'][$idx] = $custom_entry;

                $DB->update('catalog_order_item', ['custom_data' => OSC::encode($order_item['custom_data'])], "item_id = {$order_item['item_id']}", 1);

                echo "HERE\n";
            }

            if ($custom_entry['data']['customize_id'] < 1) {
                var_dump($order_item);
                continue;
            }

            $design_ukey = $custom_entry['data']['customize_id'] . '_' . $order_item['product_id'] . '_' . md5(OSC::encode($custom_entry['data']['customize_data']));

            if (!isset($designs[$design_ukey])) {
                $model = OSC::model('catalogItemCustomize/design')->setData([
                            'ukey' => $design_ukey,
                            'order_id' => $order_item['order_id'],
                            'product_id' => $order_item['product_id'],
                            'product_title' => $product['title'],
                            'product_image_url' => $order_item['image_url'],
                            'customize_id' => $custom_entry['data']['customize_id'],
                            'customize_title' => $custom_entry['data']['customize_title'],
                            'design_image_url' => null,
                            'customize_info' => $custom_entry['text'],
                            'customize_data' => $custom_entry['data']['customize_data'],
                            'state' => 1,
                            'member_id' => 0,
                            'added_timestamp' => time(),
                        ])->save();

                $designs[$design_ukey] = $model->data;
            }

            if (!isset($order_maps[$order_item['item_id']]) && $designs[$design_ukey]['state'] != 3) {
                OSC::model('catalogItemCustomize/orderMap')->setData([
                    'design_id' => $designs[$design_ukey]['record_id'],
                    'order_line_id' => $order_item['item_id']
                ])->save();

                $order_maps[$order_item['item_id']] = $designs[$design_ukey]['record_id'];
            }

            break;
        }
    }
}

echo "DONE";