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

$DB = OSC::core('database');

$DB->query("SELECT order_id FROM osc_catalog_order");

echo '<pre>';

while ($row = $DB->fetchArray()) {
    try {
        OSC::helper('masterSync/common')->addQueue('catalog/order', $row['order_id'], ['overwrite', 'ukey' => 'catalog/order:' . $row['order_id'], 'running_time' => 60]);
        
        echo "Sync: " . $row['order_id'] . "\n";
    } catch (Exception $ex) {
        echo "Sync error: {$row['order_id']} -> {$ex->getMessage()}\n";
    }
}

echo "DONE";

