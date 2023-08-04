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

$collection = OSC::model('catalog/product')->getCollection()->load();

echo '<pre>';

set_time_limit(0);
ini_set('memory_limit', '1024M');

foreach ($collection as $product) {
    $updated = false;

    if (preg_match('/^(.+)\s+((T-Shirt|Hoodie|Sweatshirt)\/?){1,}\s*$/', $product->data['title'], $matches)) {
        $product->setData('title', $matches[1]);
        $updated = true;
    }

    $option_updated = false;

    $options = $product->data['options'];

    foreach ($options as $option_index => $option) {
        if (!is_array($option)) {
            continue;
        }

        foreach ($option['values'] as $value) {
            if (in_array(strtolower($value), ['s', 'm', 'l', 'xl', '2xl', '3xl', '4xl', '5xl', '6xl'], true) && $options[$option_index]['type'] != 'clothing_size') {
                $options[$option_index]['type'] = 'clothing_size';
                $options[$option_index]['position'] = 2;
                $option_updated = true;
                break;
            }

            if (in_array(strtolower($value), ['t-shirt', 'tshirt', 'hoodie', 'sweatshirt', 'tanktop'], true) && $options[$option_index]['type'] != 'product_type') {
                $options[$option_index]['type'] = 'product_type';
                $options[$option_index]['position'] = 1;
                $option_updated = true;
                break;
            }

            if (preg_match('/\d+\s*(inch|cm|mm|m|\")?\s*x\s*\d+(inch|cm|mm|m|\")/i') && $options[$option_index]['type'] != 'poster_size') {
                $options[$option_index]['type'] = 'poster_size';
                $options[$option_index]['position'] = 1;
                $option_updated = true;
                break;
            }
        }
    }

    if ($option_updated) {
        $updated = true;
        $product->setData('options', $options);
    }

    if ($updated) {
        try {
            $product->save();
        } catch (Exception $ex) {
            echo $product->getId() . ": " . $ex->getMessage() . "\n";
        }
    }
}