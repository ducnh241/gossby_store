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
 * @copyright	Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Observer_CatalogItemCustomize_Frontend {

    public static function orderCreate(Model_Catalog_Order $order) {
        $customizes = [];

        $order_map = [];

        foreach ($order->getLineItems() as $line_item) {
            foreach ($line_item->data['custom_data'] as $custom_data) {
                if ($custom_data['key'] == 'customize') {
                    $design_ukey = $custom_data['data']['customize_id'] . '_' . $line_item->data['product_id'] . '_' . md5(OSC::encode($custom_data['data']['customize_data']));

                    $customizes[$design_ukey] = [
                        'ukey' => $design_ukey,
                        'order_id' => $order->getId(),
                        'product_id' => $line_item->data['product_id'],
                        'product_title' => $line_item->getProduct()->getProductTitle(),
                        'product_image_url' => $line_item->data['image_url'],
                        'customize_id' => $custom_data['data']['customize_id'],
                        'customize_title' => $custom_data['data']['customize_title'],
                        'design_image_url' => null,
                        'customize_info' => $custom_data['text'],
                        'customize_data' => $custom_data['data']['customize_data'],
                        'state' => 1,
                        'member_id' => 0,
                        'added_timestamp' => time(),
                    ];

                    $order_map[$line_item->getId()] = $design_ukey;

                    break;
                }
            }
        }

        /* @var $collection Model_CatalogItemCustomize_Design_Collection */
        /* @var $model Model_CatalogItemCustomize_Design */

        $collection = OSC::model('catalogItemCustomize/design')->getCollection()->loadByUkey(array_keys($customizes));

        $design_map = [];

        foreach ($collection as $model) {
            unset($customizes[$model->getUkey()]);

            if ($model->isCompleted()) {
                foreach ($order_map as $order_line_id => $design_ukey) {
                    if ($design_ukey == $model->getUkey()) {
                        $order->getLineItems()->getItemByKey($order_line_id)->setData('image_url', $model->data['design_image_url'])->save();

                        unset($order_map[$order_line_id]);
                    }
                }
            } else {
                $design_map[$model->getUkey()] = $model->getId();
            }
        }

        foreach ($customizes as $customize) {
            $model = OSC::model('catalogItemCustomize/design')->setData($customize)->save();
            $design_map[$model->getUkey()] = $model->getId();
        }

        foreach ($order_map as $order_line_id => $design_ukey) {
            OSC::model('catalogItemCustomize/orderMap')->setData([
                'design_id' => $design_map[$design_ukey],
                'order_line_id' => $order_line_id
            ])->save();
        }
    }

    public static function validate($params) {
        if (!isset($params['custom_data']['customize'])) {
            return null;
        }

        $customize_id = array_key_first($params['custom_data']['customize']);

        try {
            $customize_model = OSC::model('catalogItemCustomize/item')->load($customize_id);
        } catch (Exception $ex) {
            throw new Exception('Cannot load customize config');
        }

        $customize_data = $params['custom_data']['customize'][$customize_id];

        $data = $this->_validate($customize_model->data['config'], $customize_data);

        if (!$data['label']) {
            return null;
        }

        return [
            'key' => 'customize',
            'title' => 'Customize',
            'text' => $data['label'],
            'data' => [
                'customize_id' => $customize_id,
                'customize_title' => $customize_model->data['title'],
                'customize_data' => $data['data']
            ]
        ];
    }

    protected function _validate($config, $customize_data) {
        $label = [];
        $data = [];

        foreach ($config as $idx => $config_data) {
            if (!isset($customize_data[$idx])) {
                if ($config_data['require']) {
                    throw new Exception('Component is required');
                }
            }

            $validator = '_validate' . $config_data['component_type'];

            if (!method_exists($this, $validator)) {
                throw new Exception('Component validator is not exists');
            }

            $this->$validator($config_data, $customize_data[$idx], $label, $data);
        }

        if (count($label) < 1) {
            $label = '';
        } else {
            $label = '<ul><li>' . implode('</li><li>', $label) . '</li></ul>';
        }

        return [
            'label' => $label,
            'data' => $data
        ];
    }

    protected function _validateImageUploader($config, $value, &$label, &$validated_data) {
        if (is_array($value) && isset($value['url']) && isset($value['thumb_url']) && isset($value['name'])) {
            $value['url'] = trim($value['url']);
            $value['thumb_url'] = trim($value['thumb_url']);
            $value['name'] = trim($value['name']);

            $image_path = OSC_Storage::getFilepathFromUrl($value['url']);
            $thumb_image_path = OSC_Storage::getFilepathFromUrl($value['thumb_url']);

            if (!$image_path || !$thumb_image_path || !file_exists($image_path) || !file_exists($thumb_image_path)) {
                throw new Exception('Component image is not exists');
            }

            if ($config['min_width'] > 0 || $config['min_height'] > 0) {
                list($width, $height) = getimagesize($image_path);

                if ($config['min_width'] > 0 && $width < $config['min_width']) {
                    throw new Exception('Image width is need greater than or equal ' . $config['min_width'] . 'px');
                } else if ($config['min_height'] > 0 && $height < $config['min_height']) {
                    throw new Exception('Image height is need greater than or equal ' . $config['min_height'] . 'px');
                }
            }

            $value = [
                'url' => $value['url'],
                'thumb_url' => $value['thumb_url'],
                'name' => $value['name']
            ];
        } else {
            $value = null;
        }

        if (!$value) {
            if ($config['require'] == 1) {
                throw new Exception('Component is required');
            }

            return;
        }

        $label[] = $config['title'] . ': ' . OSC::safeString($value['name']) . ' <a data-type="image" style="background-image: url(' . $value['thumb_url'] . ')" target="_blank" href="' . OSC::safeString($value['url']) . '" title="' . OSC::safeString($value['url']) . '">&nbsp;</a>';
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateTextarea($config, $value, &$label, &$validated_data) {
        $value = trim(strval($value));

        if ($config['require'] == 1 && $value === '') {
            throw new Exception('Component is required');
        }

        if ($value === '') {
            return;
        }

        $label[] = $config['title'] . ': ' . $value;
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateInput($config, $value, &$label, &$validated_data) {
        $value = trim(strval($value));

        if ($config['require'] == 1 && $value === '') {
            throw new Exception('Component is required');
        }

        if ($value === '') {
            return;
        }

        $label[] = $config['title'] . ': ' . $value;
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateSelect($config, $value, &$label, &$validated_data) {
        $value = trim(strval($value));

        if ($config['require'] == 1 && $value === '') {
            throw new Exception('Component is required');
        }

        if ($value === '') {
            return;
        }

        if (!in_array($value, $config['options'])) {
            throw new Exception('Component value is incorrect');
        }

        $label[] = $config['title'] . ': ' . $value;
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateRadio($config, $value, &$label, &$validated_data) {
        $value = trim(strval($value));

        if ($config['require'] == 1 && $value === '') {
            throw new Exception('Component is required');
        }

        if ($value === '') {
            return;
        }

        if (!in_array($value, $config['options'])) {
            throw new Exception('Component value is incorrect');
        }

        $label[] = $config['title'] . ': ' . $value;
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateCheckbox($config, $value, &$label, &$validated_data) {
        if (!is_array($value)) {
            throw new Exception('Component value is incorrect');
        }

        $value = array_map(function($_value) {
            return trim(strval($_value));
        }, $value);

        if ($config['require'] == 1 && count($value) < 1) {
            throw new Exception('Component is required');
        }

        if (count($value) < 1) {
            return;
        }

        foreach ($value as $_value) {
            if (!in_array($_value, $config['options'])) {
                throw new Exception('Component value is incorrect');
            }
        }

        $label[] = $config['title'] . ': ' . implode(', ', $value);
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateChecker($config, $value, &$label, &$validated_data) {
        $value = trim(strval($value));

        if ($value == $config['value']) {
            $label[] = $value;
            $validated_data[] = [
                'title' => $config['title'],
                'layer_key' => $config['layer_key'],
                'value' => $value
            ];
        }
    }

    protected function _validateImageSelector($config, $value, &$label, &$validated_data) {
        $value = trim(strval($value));

        if ($config['require'] == 1 && $value === '') {
            throw new Exception('Component is required');
        }

        if ($value === '') {
            return;
        }

        $not_found = true;

        foreach ($config['images'] as $image) {
            if ($value == $image['title']) {
                $not_found = false;
                $value = $image;
                break;
            }
        }

        if ($not_found) {
            throw new Exception('Component value is incorrect');
        }

        $label[] = $config['title'] . ': ' . OSC::safeString($value['title']) . ' <a data-type="image" style="background-image: url(' . $value['url'] . ')" target="_blank" href="' . OSC::safeString($value['url']) . '" title="' . OSC::safeString($value['title']) . '"></a>';
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateImageGroupSelector($config, $value, &$label, &$validated_data) {
        if (!is_array($value)) {
            $value = [
                'group' => '',
                'image' => ''
            ];
        } else {
            if (!isset($value['group'])) {
                $value['group'] = '';
            }

            if (!isset($value['image'])) {
                $value['image'] = '';
            }
        }

        $value['group'] = trim($value['group']);
        $value['image'] = trim($value['image']);

        if (!$value['group'] || count($value['image']) < 1) {
            if ($config['require'] == 1) {
                throw new Exception('Component is required');
            }

            return;
        }

        if (!isset($config['groups'][$value['group']])) {
            throw new Exception('Component group key is not exist');
        }

        $not_found = true;

        foreach ($config['groups'][$value['group']]['images'] as $image) {
            if ($value['image'] == $image['title']) {
                $not_found = false;
                $value = $image;
                break;
            }
        }

        if ($not_found) {
            throw new Exception('Component value is incorrect');
        }

        $label[] = $config['title'] . ': ' . OSC::safeString($value['title']) . ' <a data-type="image" style="background-image: url(' . $value['url'] . ')" target="_blank" href="' . OSC::safeString($value['url']) . '" title="' . OSC::safeString($value['title']) . '"></a>';
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateColorSelector($config, $value, &$label, &$validated_data) {
        $value = trim(strval($value));

        if ($config['require'] == 1 && $value === '') {
            throw new Exception('Component is required');
        }

        if ($value === '') {
            return;
        }

        $found = false;

        foreach ($config['colors'] as $color) {
            if ($color['title'] === $value) {
                $found = true;
                $value = $color;
                break;
            }
        }

        if (!$found) {
            throw new Exception('Component value is incorrect');
        }

        $label[] = $config['title'] . ': ' . OSC::safeString($value['title']) . ' <span data-type="color" style="color: ' . $value['hex'] . '; background-color: ' . $value['hex'] . '" title="' . OSC::safeString($value['title']) . '"></span>';
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $value
        ];
    }

    protected function _validateListItem($config, $value, &$label, &$validated_data) {
        if (!is_array($value)) {
            $value = [];
        }

        if ($config['require'] == 1 && count($value) < 1) {
            throw new Exception('Component is required');
        }

        $idx = 0;

        $validated_data[$config['title']] = [];

        $items = ['label' => [], 'data' => []];

        foreach ($value as $components) {
            $item_data = $this->_validate($config['components'], $components);

            if ($item_data['label']) {
                $idx ++;
                $items['label'][] = '<li>Item #' . $idx . $item_data['label'] . '</li>';
                $items['data'][] = $item_data['data'];
            }
        }

        if (count($items['label']) < $config['min'] || count($items['label']) > $config['max']) {
            throw new Exception('Component data is incorrect');
        }

        $label[] = $config['title'] . '<ul>' . implode('', $items['label']) . '</ul>';
        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => $items['data']
        ];
    }

    protected function _validateSwitcherBySelect($config, $value, &$label, &$validated_data) {
        if (!is_array($value)) {
            $value = [
                'selected' => '',
                'components' => []
            ];
        } else {
            if (!isset($value['components']) || !is_array($value['components'])) {
                $value['components'] = [];
            }
        }

        $value['selected'] = trim($value['selected']);

        if (!$value['selected']) {
            if ($config['require'] == 1) {
                throw new Exception('Component is required');
            }

            return;
        }

        $components_config = null;

        foreach ($config['scenes'] as $scene) {
            if ($value['selected'] == $scene['title']) {
                $components_config = $scene['components'];
            }
        }

        if ($components_config === null) {
            throw new Exception('Component value is not matched');
        } else if (count($components_config) > 0) {
            $item_data = $this->_validate($components_config, $value['components']);
        } else {
            $item_data = [
                'label' => '',
                'data' => []
            ];
        }

        $label[] = $config['title'] . ': ' . $value['selected'] . ($item_data['label'] ? $item_data['label'] : '');

        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => [
                'selected' => $value['selected'],
                'components' => $item_data['data']
            ]
        ];
    }

    protected function _validateSwitcherByColor($config, $value, &$label, &$validated_data) {
        if (!is_array($value)) {
            $value = [
                'selected' => '',
                'components' => []
            ];
        } else {
            if (!isset($value['components']) || !is_array($value['components'])) {
                $value['components'] = [];
            }
        }

        $value['selected'] = trim($value['selected']);

        if (!$value['selected']) {
            if ($config['require'] == 1) {
                throw new Exception('Component is required');
            }

            return;
        }

        $components_config = null;

        foreach ($config['scenes'] as $scene) {
            if ($value['selected'] == $scene['color']['title']) {
                $components_config = $scene['components'];
                $value['selected'] = $scene['color'];
            }
        }

        if ($components_config === null) {
            throw new Exception('Component value is not matched');
        } else if (count($components_config) > 0) {
            $item_data = $this->_validate($components_config, $value['components']);
        } else {
            $item_data = [
                'label' => '',
                'data' => []
            ];
        }

        $label[] = $config['title'] . ': ' . OSC::safeString($value['selected']['title']) . ' <span data-type="color" style="color: ' . $value['selected']['hex'] . '; background-color: ' . $value['selected']['hex'] . '" title="' . OSC::safeString($value['selected']['title']) . '"></span>' . ($item_data['label'] ? $item_data['label'] : '');

        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => [
                'selected' => $value['selected'],
                'components' => $item_data['data']
            ]
        ];
    }

    protected function _validateSwitcherByImage($config, $value, &$label, &$validated_data) {
        if (!is_array($value)) {
            $value = [
                'selected' => '',
                'components' => []
            ];
        } else {
            if (!isset($value['components']) || !is_array($value['components'])) {
                $value['components'] = [];
            }
        }

        $value['selected'] = trim($value['selected']);

        if (!$value['selected']) {
            if ($config['require'] == 1) {
                throw new Exception('Component is required');
            }

            return;
        }

        $components_config = null;

        foreach ($config['scenes'] as $scene) {
            if ($value['selected'] == $scene['image']['title']) {
                $components_config = $scene['components'];
                $value['selected'] = $scene['image'];
            }
        }

        if ($components_config === null) {
            throw new Exception('Component value is not matched');
        } else if (count($components_config) > 0) {
            $item_data = $this->_validate($components_config, $value['components']);
        } else {
            $item_data = [
                'label' => '',
                'data' => []
            ];
        }

        $label[] = $config['title'] . ': ' . OSC::safeString($value['selected']['title']) . ' <a data-type="image" style="background-image: url(' . $value['selected']['url'] . ')" target="_blank" href="' . OSC::safeString($value['url']) . '" title="' . OSC::safeString($value['selected']['title']) . '"></a>' . ($item_data['label'] ? $item_data['label'] : '');

        $validated_data[] = [
            'title' => $config['title'],
            'layer_key' => $config['layer_key'],
            'value' => [
                'selected' => $value['selected'],
                'components' => $item_data['data']
            ]
        ];
    }

}
