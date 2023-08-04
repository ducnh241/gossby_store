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

$collection = OSC::model('catalog/order')->getCollection();

$collection->addField('order_id', 'customer_id');

$range = OSC::core('request')->get('range');

if ($range) {
    $range = explode('-', $range);

    if (count($range) != 2) {
        $range = null;
    } else {
        $range[0] = intval($range[0]);
        $range[1] = intval($range[1]);

        if ($range[0] == $range[1]) {
            $range = null;
        } else if ($range[0] > $range[1]) {
            $buff = $range[0];
            $range[0] = $range[1];
            $range[1] = $buff;
        }
    }
}

if ($range) {
    $collection->addCondition('order_id', $range[0], OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL)->addCondition('order_id', $range[1], OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL);
}

$collection->load();

$customer_ids = [];

foreach ($collection as $order) {
    $customer_ids[] = $order->data['customer_id'];
    OSC::helper('masterSync/common')->addQueue('catalog/order', $order->getId(), ['overwrite', 'ukey' => 'catalog/order:' . $order->getId(), 'running_time' => 60]);
}

$customer_ids = array_unique($customer_ids);

foreach ($customer_ids as $customer_id) {
    OSC::helper('masterSync/common')->addQueue('catalog/customer', $customer_id, ['overwrite', 'ukey' => 'catalog/customer:' . $customer_id, 'running_time' => 60]);
}

echo 'DONE';
