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

$DB->query("SELECT product_id, variant_id, order_id, item_id FROM osc_catalog_order_item WHERE image_url IS NULL");

$order_items = [];
$products = [];
$variants = [];
$images = [];

while ($row = $DB->fetchArray()) {
    $order_items[] = $row;
    $products[$row['product_id']] = null;
    $variants[$row['variant_id']] = null;
}

$DB->query("SELECT product_id, title, tags FROM osc_catalog_product WHERE FIND_IN_SET(product_id, :product_ids) LIMIT " . count($products), ['product_ids' => implode(',', array_keys($products))]);

while ($row = $DB->fetchArray()) {
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

$DB->query("SELECT image_id, product_id, filename FROM osc_catalog_product_image WHERE FIND_IN_SET(product_id, :product_ids) ORDER BY position ASC LIMIT " . count($products), ['product_ids' => implode(',', array_keys($products))]);

while ($row = $DB->fetchArray()) {
    if (!isset($images[$row['product_id']])) {
        $images[$row['product_id']] = [];
    }

    $images[$row['product_id']][$row['image_id']] = $row['filename'];
}

foreach ($order_items as $order_item) {
    if (!isset($variants[$order_item['variant_id']])) {
        echo "VARIANT NOT FOUND: {$order_item['item_id']}\n";
        continue;
    }

    $variant = $variants[$order_item['variant_id']];

    $image_id = ($variant['image_id'] > 0 && isset($images[$order_item['product_id']][$variant['image_id']])) ? $variant['image_id'] : (isset($images[$order_item['product_id']]) ? array_key_first($images[$order_item['product_id']]) : 0);

    if ($image_id < 1) {
        echo "IMAGE NOT FOUND [{$order_item['item_id']}]\n";
    } else {
        $image_path = OSC_STORAGE_PATH . '/' . $images[$order_item['product_id']][$image_id];

        $line_image_name = 'order/' . $order_item['product_id'] . '/' . preg_replace('/^.+\/([^\/]+)$/', '\\1', $image_path);
        $line_image_path = OSC_Storage::getStoragePath($line_image_name);

        if (!file_exists($line_image_path)) {
            if (file_exists($image_path)) {
                try {
                    OSC_Storage::storageSendFile($image_path, $line_image_name);

                    $image_processor = new OSC_Image();
                    $image_processor->setImage($line_image_path)->setJpgQuality(65)->resize(250)->save();
                } catch (Exception $ex) {
                    echo "PROCESS IMAGE ERROR [{$order_item['item_id']}]: {$ex->getMessage()}\n";
                    $line_image_name = null;
                }
            }
        }

        if ($line_image_name) {
            $DB->update('catalog_order_item', ['image_url' => $line_image_name], 'item_id=' . $order_item['item_id'], 1);
        }
    }
}

die;
