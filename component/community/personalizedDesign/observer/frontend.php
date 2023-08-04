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
class Observer_PersonalizedDesign_Frontend {

    public static function preCustomerEditOrder(Model_Catalog_Order $order) {
        if (!$order->ableToEdit()) {
            return;
        }

        foreach ($order->getLineItems() as $line_item) {
            $custom_data_entries = $line_item->data['custom_data'];

            foreach ($custom_data_entries as $idx => $custom_data) {
                if ($custom_data['key'] == 'personalized_design') {
                    $editBtn = '';
                    if ($order->ableToEdit()) {
                        $editBtn = '<div class="edit-btn" data-order="' . $order->getOrderUkey() . '" data-line-item="' . $line_item->getId() . '" data-insert-cb="orderPersonalizedDesignEdit">Edit design</div>';
                    }
                    $custom_data_entries[$idx]['text'] = <<<EOF
<div class="order-personalized-design">
    {$custom_data_entries[$idx]['text']}
    {$editBtn}
</div>
EOF;

                    break;
                }
            }

            $line_item->setData('custom_data', $custom_data_entries);
        }

        OSC::helper('frontend/template')->push('personalizedDesign/common.scss', 'css')->push(['personalizedDesign/common.js', '[core]community/jquery.serialize-object.js'], 'js');
    }

    public static function validate($params) {
        if (!isset($params['custom_data']['personalized_design'])) {
            return null;
        }

        $options = ['remove_pattern_layer'];

        if (isset($params['options']) && !empty($params['options'])) {
            $options = array_merge($options, $params['options']);
        }

        $data_return = [
            'key' => 'personalized_design',
            'title' => 'Personalized',
            'type' => 'semitest'
        ];

        foreach ($params['custom_data']['personalized_design'] as $design_id) {
            if ($design_id < 1) {
                throw new Exception('Personalized design ID is incorrect');
            }

            try {
                $design = OSC_Database_Model::getPreLoadedModel('personalizedDesign/design', $design_id);
            } catch (Exception $ex) {
                throw new Exception('Cannot load personalized design');
            }

            if (!($design instanceof Model_PersonalizedDesign_Design)) {
                throw new Exception('Cannot load personalized design');
            }

            $config = $params['custom_data']['personalized_config'][$design_id];
            if (!is_array($config)) {
                $config = [];
            }

            $form_data = $design->extractPersonalizedFormData();
            $forms = $form_data['components'];

            $validated_config = [];

            if (is_array($forms) && count($forms) > 0) {
                static::validateConfig($forms, $config, $validated_config);
            }

            $json_data = OSC::core('template')->getJSONTag(['id' => $design_id, 'config' => $validated_config], 'config');

            $svg_content = <<<EOF
<div data-insert-cb="personalizedDesignLoadPreview" class="personalized-design-preview">{$json_data}</div>            
EOF;

            $config_preview = OSC::helper('personalizedDesign/common')->fetchConfigPreview($design, $config);

            $data_return['data'][$design_id] = [
                'text' => $svg_content,
                'design_id' => $design->getId(),
                'width' => $design->data['design_data']['document']['width'],
                'height' => $design->data['design_data']['document']['height'],
                'design_svg' => OSC::helper('personalizedDesign/common')->renderSvg($design, $config),
                'design_svg_beta' => OSC::helper('personalizedDesign/common')->renderSvg($design, $config, $options),
                'config' => $validated_config,
                'config_preview' => $config_preview,
                'design_last_update' => $design->data['modified_timestamp']
            ];
        }
        if (is_array($params['variant']->data['design_id']) && count($params['variant']->data['design_id']) > 0) {
            $data_return['data'] = static::sortSegmentsByDesignIdVariant($data_return['data'], $params['variant']->data['design_id']);
        }

        return $data_return;
    }

    public static function sortSegmentsByDesignIdVariant(array $data, array $data_sort) {
        $ordered = [];
        foreach ($data_sort as $key) {
            if (array_key_exists($key, $data)) {
                $ordered[$key] = $data[$key];
                unset($data[$key]);
            }
        }
        return $ordered + $data;
    }

    public static function validateConfig($forms, &$config, &$validated_config, $prefix = '') {
        foreach ($forms as $form_key => $form) {
            if (!isset($config[$form_key])) {
                if ($form['require']) {
                    throw new Exception('Config [' . $prefix . $form_key . '] is required');
                }
            }

            $validator = '_validate' . $form['component_type'];

            if (!method_exists(Observer_PersonalizedDesign_Frontend, $validator)) {
                throw new Exception('Config [' . $prefix . $form_key . '] validator is not exists');
            }

            static::$validator($form_key, $form, $config, $validated_config, $prefix);
        }
    }

    protected function _validateTab($form_key, $form, &$config, &$validated_config, $prefix) {
        foreach ($form['tabs'] as $key => $value) {
            if (is_array($value['components']) && count($value['components']) > 0) {
                foreach ($value['components'] as $key_personalize => $component) {
                    if (!isset($config[$key_personalize])) {
                        if ($component['require']) {
                            throw new Exception('Config [' . $prefix . $key_personalize . '] is required');
                        }
                    }

                    $validator = '_validate' . $component['component_type'];

                    if (!method_exists(Observer_PersonalizedDesign_Frontend, $validator)) {
                        throw new Exception('Config [' . $prefix . $key_personalize . '] validator is not exists');
                    }

                    static::$validator($key_personalize, $component, $config, $validated_config, $prefix);
                }
            }

        }
    }

    protected function _validateInput($form_key, $form, &$config, &$validated_config, $prefix) {
        $value = trim(strval($config[$form_key]));

        if ($value === '' && $form['require']) {
            throw new Exception('Config [' . $prefix . $form_key . '] is required');
        } elseif ($form['input_disable_all_uppercase'] == 1) {
            $value = OSC::helper('personalizedDesign/common')->inputDisableAllUppercase($value);
            $config[$form_key] = $value;
        }

        $validated_config[$form_key] = $value;
    }

    protected function _validateImageUploader($form_key, $form, $config, &$validated_config, $prefix) {
        $value = trim(strval($config[$form_key]));

        if ($value === '' && $form['require']) {
            throw new Exception('Config [' . $prefix . $form_key . '] is required');
        }

        if ($value) {
            $value = OSC::decode($value);

            if (!is_array($value) || !isset($value['url']) || !isset($value['file']) || !isset($value['name']) || !isset($value['size']) || !isset($value['width']) || !isset($value['height']) || !isset($value['token'])) {
                throw new Exception('Config [' . $prefix . $form_key . '] is incorrect format');
            }

            if ($value['token'] != OSC::helper('personalizedDesign/common')->imageUploaderGetDataToken($value)) {
                throw new Exception('Config [' . $prefix . $form_key . '] is corrupt');
            }

            $validated_config[$form_key] = OSC::encode($value);
        } else {
            $validated_config[$form_key] = $value;
        }
    }

    protected function _validateChecker($form_key, $form, $config, &$validated_config, $prefix) {
        $value = intval($config[$form_key]);

        if (in_array($value, [0, 1])) {
            $validated_config[$form_key] = $value;
        }
    }

    protected function _validateImageSelector($form_key, $form, $config, &$validated_config, $prefix) {
        $value = trim(strval($config[$form_key]));

        if ($value === '' && $form['require']) {
            throw new Exception('Config [' . $prefix . $form_key . '] is required');
        }

        if ($value && !isset($form['images'][$value])) {
            throw new Exception('Config [' . $prefix . $form_key . ']: selected value is not exists');
        }

        $validated_config[$form_key] = $value;
    }

    protected function _validateImageGroupSelector($form_key, $form, $config, &$validated_config, $prefix) {
        $value = trim(strval($config[$form_key]));

        if ($value === '' && $form['require']) {
            throw new Exception('Config [' . $prefix . $form_key . '] is required');
        }

        if ($value) {
            $found = false;

            foreach ($form['groups'] as $group) {
                if (isset($group['images'][$value])) {
                    $found = true;
                    break;
                }
            }

            if (!$found) {
                throw new Exception('Config [' . $prefix . $form_key . ']: selected value is not exists');
            }
        }

        $validated_config[$form_key] = $value;
    }

    protected function _validateSwitcherBySelect($form_key, $form, &$config, &$validated_config, $prefix) {
        $value = trim(strval($config[$form_key]));

        if ($value === '' && $form['require']) {
            throw new Exception('Config [' . $prefix . $form_key . '] is required');
        }

        if ($value && !isset($form['scenes'][$value])) {
            throw new Exception('Config [' . $prefix . $form_key . ']: selected value is not exists');
        }

        $validated_config[$form_key] = $value;

        if ($value) {
            static::validateConfig($form['scenes'][$value]['components'], $config, $validated_config, $prefix . $form_key . '/');
        }
    }

    protected function _validateSwitcherByImage($form_key, $form, $config, &$validated_config, $prefix) {
        static::_validateSwitcherBySelect($form_key, $form, $config, $validated_config, $prefix);
    }

    protected function _validateSpotify($form_key, $form, $config, &$validated_config, $prefix) {
        $value = trim(strval($config[$form_key]));

        if ($form['require']) {
            if ($value) {
                $value = OSC::decode($value);

                if (!is_array($value) || !isset($value['uri'])) {
                    throw new Exception('Config [' . $prefix . $form_key . '] is incorrect format');
                }

                $validated_config[$form_key] = OSC::encode($value);
            } else {
                throw new Exception('Config [' . $prefix . $form_key . '] is required');
            }
        } else {
            $validated_config[$form_key] = $value ?? '';
        }
    }
    
    public static function checkOverflowPersonalized(Model_Catalog_Order $order){
        foreach ($order->getLineItems() as $line_item) {
            try {
                OSC::helper('personalizedDesign/common')->checkOverflowPersonalizedItem($line_item);
            } catch (Exception $ex) {

            }
        }
    }
}