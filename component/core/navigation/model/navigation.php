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
class Model_Navigation_Navigation extends Abstract_Core_Model {

    protected $_table_name = 'navigation';
    protected $_pk_field = 'navigation_id';
    protected $_allow_write_log = true;

    protected $_option_conf = array('value' => 'navigation_id', 'label' => 'title');

    /**
     * @param bool $get_absolute_image_url [true, false]
     * @param string $tracking_key
     * @return array
     */
    public function getOrderedItems($get_absolute_image_url = false) {
        $map = array();

        foreach ($this->data['items'] as $item_id => $item) {
            if ($get_absolute_image_url) {
                $item['image'] = (isset($item['image']) && $item['image']) ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($item['image'])) : '';
            }
            if (!isset($map[$item['parent_id']])) {
                $map[$item['parent_id']] = array();
            }

            $item['id'] = $item_id;

            $map[$item['parent_id']][] = $item;
        }

        return $this->_mapItems('root', $map);
    }

    protected function _mapItems($key, $map) {
        $items = array();

        if (isset($map[$key])) {
            foreach ($map[$key] as $item) {
                $item['children'] = $this->_mapItems($item['id'], $map);

                $items[] = $item;
            }
        }

        return $items;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);

            if (!$data['title']) {
                $errors[] = 'Navigation title is empty';
            }
        }

        if (isset($data['items'])) {
            if (!is_array($data['items'])) {
                $data['items'] = array();
            }

            $map = array();
            $items = array();

            $counter = 0;

            foreach ($data['items'] as $item_id => $item) {
                if (!is_array($item) || !isset($item['title']) || !isset($item['url'])) {
                    continue;
                }

                $item['url'] = trim($item['url']);
                $item['title'] = trim($item['title']);

                if ($item['url'] === '' || $item['title'] === '') {
                    continue;
                }

                if (!isset($item['source_icon'])) {
                    $item['source_icon'] = '';
                }

                if (!isset($item['source_title'])) {
                    $item['source_title'] = '';
                }

                $item['source_icon'] = trim($item['source_icon']);
                $item['source_title'] = trim($item['source_title']);

                $map[$item_id] = $counter;

                $items[] = array(
                    'title' => $item['title'],
                    'url' => $item['url'],
                    'source_icon' => $item['source_icon'],
                    'source_title' => $item['source_title'],
                    'parent_id' => $item['parent_id'],
                    'custom_class' => $item['custom_class'],
                    'image' => $item['image']
                );

                $counter ++;
            }

            foreach ($items as $item_id => $item) {
                $item['parent_id'] = isset($map[$item['parent_id']]) ? $map[$item['parent_id']] : 'root';

                $items[$item_id] = $item;
            }

            $data['items'] = $items;
        }

        foreach (array('added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'title' => 'Navigation title is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'items' => array(),
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['items'])) {
            $data['items'] = OSC::encode($data['items']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['items'])) {
            $data['items'] = OSC::decode($data['items'], true);
        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        $keys = array('title');

        $index_keywords = array();

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        OSC::helper('backend/common')->indexAdd('', 'navigation', 'navigation', $this->getId(), $index_keywords);
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        OSC::helper('backend/common')->indexDelete('', 'navigation', 'navigation', $this->getId());
    }

}
