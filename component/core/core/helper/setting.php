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
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC_Core_Helper::Setting
 *
 * @package Helper_Core_Setting
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Core_Setting {

    protected $_data = array();
    protected $_default = [];

    /**
     *
     * @var Model_Core_Setting_Collection 
     */
    protected $_collection = null;

    public function __construct() {
        $setting_cache_file = OSC_VAR_PATH . '/setting/cache.php';

        if (!file_exists($setting_cache_file)) {
            $this->loadFromDB();
        } else {
            include_once $setting_cache_file;

            $this->_data = $SETTINGS['config'];
            $this->_default = $SETTINGS['default'];
        }
    }

    public function removeCache() {
        unlink(OSC_VAR_PATH . '/setting/cache.php');
        unlink(OSC_VAR_PATH . '/setting/rewriting.php');
    }

    public function get($key) {
        return isset($this->_data[$key]) ? $this->_data[$key] : (isset($this->_default[$key]) ? $this->_default[$key] : null);
    }

    public function set($key, $value, $skip_reset_cache = false) {
        $model = $this->getCollection()->getItemByUkey($key);

        if (!$model) {
            $model = $this->getCollection()->getNullModel();
            $model->setData('setting_key', $key);

            $is_new_flag = true;
        }

        $model->setData('setting_value', $value)->save();

        if ($is_new_flag) {
            $this->getCollection()->addItem($model);
        }

        if (!$skip_reset_cache) {
            $this->removeCache();
            OSC::core('observer')->dispatchEvent('setting_updated');
        }

        return $this;
    }

    public function getCollection($reset = false) {
        if ($reset || !($this->_collection instanceof Model_Core_Setting_Collection)) {
            $this->_collection = OSC::model('core/setting')->getCollection()->load();
        }

        return $this->_collection;
    }

    public function loadFromDB() {
        $this->collectSettingItems();

        $this->_data = [];

        foreach ($this->getCollection() as $model) {
            $this->_data[$model->data['setting_key']] = $model->data['setting_value'];
        }

        OSC::writeToFile(OSC_VAR_PATH . '/setting/rewriting.php', OSC::core('string')->toPHP([
                    'config' => $this->_data,
                    'default' => $this->_default
                        ], 'SETTINGS'), array('chmod' => 0600));
        
        unlink(OSC_VAR_PATH . '/setting/cache.php');
        rename(OSC_VAR_PATH . '/setting/rewriting.php', OSC_VAR_PATH . '/setting/cache.php');

        return $this;
    }

    public function collectSettingTypes() {
        static $cached = null;

        if ($cached === null) {
            $cached = [];

            $response = OSC::core('observer')->dispatchEvent('collect_setting_type');

            foreach ($response as $types) {
                if (!is_array($types)) {
                    continue;
                }

                foreach ($types as $type) {
                    if (!is_array($type) || !isset($type['key']) || !isset($type['template'])) {
                        continue;
                    }

                    try {
                        Model_Core_Setting::validateSettingKey($type['key']);
                    } catch (Exception $ex) {
                        continue;
                    }

                    $type['template'] = trim($type['template']);


                    if (!$type['key'] || !$type['template']) {
                        continue;
                    }

                    if (!OSC::helper('backend/template')->chkPath($type['template'])) {
                        continue;
                    }

                    if (!isset($type['validator']) || !is_callable($type['validator'])) {
                        $type['validator'] = null;
                    }

                    $cached[$type['key']] = $type;
                }
            }
        }

        return $cached;
    }

    public function collectSections() {
        $response = OSC::core('observer')->dispatchEvent('collect_setting_section');

        $buff = [];

        foreach ($response as $sections) {
            if (!is_array($sections)) {
                continue;
            }

            foreach ($sections as $section) {
                if (!is_array($section) || !isset($section['key']) || !isset($section['title']) || !isset($section['icon']) || !isset($section['description'])) {
                    continue;
                }

                try {
                    Model_Core_Setting::validateSettingKey($section['key']);
                } catch (Exception $ex) {
                    continue;
                }

                $section['title'] = trim($section['title']);
                $section['icon'] = trim($section['icon']);
                $section['description'] = trim($section['description']);

                if (!$section['key'] || !$section['title'] || !$section['icon'] || !$section['description']) {
                    continue;
                }

                if (!isset($section['priority'])) {
                    $section['priority'] = 0;
                } else {
                    $section['priority'] = intval($section['priority']);
                }

                $buff[$section['key']] = $section;
            }
        }

        return $buff;
    }

    public function collectSettingItems() {
        $response = OSC::core('observer')->dispatchEvent('collect_setting_item');

        $buff = [];

        $setting_types = $this->collectSettingTypes();

        $this->_default = [];

        foreach ($response as $items) {
            if (!is_array($items)) {
                continue;
            }

            foreach ($items as $item) {
                if (!is_array($item) || !isset($item['key']) || !isset($item['section']) || !isset($item['title']) || !isset($item['type'])) {
                    continue;
                }

                try {
                    Model_Core_Setting::validateSettingKey($item['key']);
                    Model_Core_Setting::validateSettingKey($item['section']);
                } catch (Exception $ex) {
                    continue;
                }

                $item['title'] = trim($item['title']);
                $item['type'] = trim($item['type']);

                if (!$item['key'] || !$item['section'] || !$item['title']) {
                    continue;
                }

                if ($item['type'] != 'group' && !isset($setting_types[$item['type']])) {
                    continue;
                }

                if (!isset($buff[$item['section']])) {
                    $buff[$item['section']] = [];
                }

                $item['description'] = !isset($item['description']) ? '' : trim($item['description']);
                $item['after'] = !isset($item['after']) ? false : trim($item['after']);
                $item['before'] = !isset($item['before']) ? false : trim($item['before']);
                $item['priority'] = !isset($item['priority']) ? $item['priority'] = 0 : intval($item['priority']);

                if ($item['type'] == 'group') {
                    if (!isset($buff[$item['section']][$item['key']])) {
                        $buff[$item['section']][$item['key']] = [
                            'items' => []
                        ];
                    }

                    unset($item['items']);

                    foreach ($item as $k => $v) {
                        $buff[$item['section']][$item['key']][$k] = $v;
                    }

                    continue;
                }

                $item['group'] = !isset($item['group']) ? '' : trim($item['group']);
                $item['require'] = isset($item['require']) && $item['require'] ? true : false;

                if ($item['group'] == '') {
                    $item['group'] = 'unknown';
                }

                if (!isset($buff[$item['section']][$item['group']])) {
                    $buff[$item['section']][$item['group']] = [
                        'items' => []
                    ];
                }

                if (isset($item['default'])) {
                    $this->_default[$item['key']] = $item['default'];
                }

                if (!isset($item['after_save']) || !is_callable($item['after_save'])) {
                    $item['after_save'] = null;
                }

                $buff[$item['section']][$item['group']]['items'][$item['key']] = $item;
            }
        }

        return $buff;
    }

    public function getLastChangeSetting()
    {
        $setting_cache_file = OSC_VAR_PATH . '/setting/cache.php';
        if (file_exists($setting_cache_file)) {
            return filemtime($setting_cache_file);
        }
        return 0;
    }

    public function getDataChange($datas = []) {
        $current_data = $datas['current_data'];
        $new_data = $datas['new_data'];
        $type = $datas['data_type'];
        $message = '';

        if ($type == 'json') {
            $current_data = !is_array($current_data) ? OSC::decode($current_data, true) : $current_data;
            $new_data = !is_array($new_data) ? OSC::decode($new_data, true) : $new_data;
            $remove_items = array_diff($current_data, $new_data);
            $add_items = array_diff($new_data, $current_data);

            if (count($remove_items) > 0) {
                $message  .= "Your changes have been saved. Removed " . implode(", ", array_values($remove_items)). " from list of countries <br/>";
            }
            if (count($add_items) > 0) {
                $message  .= 'Your changes have been saved. Added ' . implode(', ', array_values($add_items)) . ' to list of countries <br/>';
            }
        }
        return $message;

    }

}
