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

$DB->query("SELECT item_id, config FROM osc_catalog_item_customize");

$customize_key = [];
while ($row = $DB->fetchArray()) {
    $config = OSC::decode($row['config']);
    foreach ($config as $key => $conf) {
        $customize_key[$row['item_id']][$conf['title']] = $conf['layer_key'];
        if (isset($conf['scenes'])) {
            foreach ($conf['scenes'] as $scenes) {
                if (isset($scenes['components'])) {
                    
                    foreach ($scenes['components'] as $component) {
                        $customize_key[$row['item_id']][$component['title']] = $component['layer_key'];
                        if (isset($component['scenes'])) {
                            foreach ($component['scenes'] as $sc) {
                                if (isset($sc['components'])) {
                                    foreach ($sc['components'] as $com) {
                                        $customize_key[$row['item_id']][$com['title']] = $com['layer_key'];

                                    }
                                }
                            }
                        }
                    }
                }
            }
            
        }
    }
}


$DB->query("SELECT * FROM osc_catalog_item_customize_design_convert");

$count = 1;
while ($row = $DB->fetchArray()) {
    $customize_datas = OSC::decode($row['customize_data']);
    $data = [];
    foreach ($customize_datas as $idx => $customize_data) {
        if (isset($customize_data['value'])) {
            $customize_data['layer_key'] = $customize_key[$row['customize_id']][$customize_data['title']];
            if (is_array($customize_data['value'])) {
                $customize_data['layer_key'] = $customize_key[$row['customize_id']][$customize_data['title']];
                if (isset($customize_data['value']['selected'])) {
                    $customize_data['layer_key'] = $customize_key[$row['customize_id']][$customize_data['title']];
                    if (isset($customize_data['value']['components'])) {
                        foreach ($customize_data['value']['components'] as $cp => $component) {
                            $customize_data['value']['components'][$cp]['layer_key'] = $customize_key[$row['customize_id']][$component['title']];
                            if (isset($component['value']['title'])) {
                                $customize_data['value']['components'][$cp]['layer_key'] = $customize_key[$row['customize_id']][$component['title']];
                                if (isset($component['value']['value'])) {
                                    $customize_data['value']['components'][$cp]['layer_key'] = $customize_key[$row['customize_id']][$component['title']];
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if (!is_array($customize_data)) {
                $customize_data['layer_key'] = $customize_key[$row['customize_id']][$customize_data];
            } else {
                $customize_data['layer_key'] = $customize_key[$row['customize_id']][$customize_data['title']];

                if (isset($customize_data['selected'])) {
                    if (isset($customize_data['components'])) {
                        foreach ($customize_data['components'] as $cp => $component) {
                            $customize_data['components'][$cp]['layer_key'] = $customize_key[$row['customize_id']][$component];
                            if (isset($component['title'])) {
                                $customize_data['components'][$cp]['layer_key'] = $customize_key[$row['customize_id']][$component['title']];
                                if (isset($component['value']['title'])) {
                                $customize_data['components'][$cp]['layer_key'] = $customize_key[$row['customize_id']][$component['value']['title']];
                                }
                            }
                        }
                    }
                }
            }
        }
        $data[$idx] = $customize_data;
    }
    $record_id = $row['record_id'];
    try {
        $DB->query("UPDATE osc_catalog_item_customize_design_convert SET customize_data = :customize_data WHERE record_id = {$record_id} Limit 1", ['customize_data' => OSC::encode($data)],$record_id );
        if ($DB->getNumAffected($record_id) > 0) {
            echo $row['record_id'].': success'."\n";
        } else {
            echo $row['record_id'].': error'."\n";

        }
        
    } catch (Exception $e) {
        echo $row['record_id'].': error'.$e->getMessage()."\n";
        
    }

    $count++;
}
echo $count;

echo "DONE";