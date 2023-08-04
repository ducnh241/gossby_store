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
class Observer_CatalogItemCustomize_Backend {

    public static function collectMenu() {
        $menus = [];

        $parent_key = '';

        if (OSC::controller()->checkPermission('catalog_item_customize/type&catalog_item_customize/design', false)) {
            $menus[] = array(
                'key' => 'catalog_item_customize',
                'icon' => 'magic-solid',
                'title' => 'Product Customize',
                'url' => OSC::getUrl('catalogItemCustomize/backend/index'),
            );

            $parent_key = 'catalog_item_customize';
        }

        if (OSC::controller()->checkPermission('catalog_item_customize/type', false)) {
            $menus[] = array(
                'key' => 'catalog_item_customize/type',
                'parent_key' => $parent_key,
                'icon' => $parent_key ? '' : 'magic-solid',
                'title' => $parent_key ? 'Manage types' : 'Manage customize type',
                'url' => OSC::getUrl('catalogItemCustomize/backend/index'),
            );
        }

        if (OSC::controller()->checkPermission('catalog_item_customize/design', false)) {
            $menus[] = array(
                'key' => 'catalog_item_customize/design',
                'parent_key' => $parent_key,
                'icon' => $parent_key ? '' : 'magic-solid',
                'title' => $parent_key ? 'Manage designs' : 'Manage customize design',
                'url' => OSC::getUrl('catalogItemCustomize/backend/designList'),
            );
        }

        return $menus;
    }

    protected function _getCustomizeData(&$row_data, $customize_data, $path = '') {
        foreach ($customize_data as $entry) {
            if (isset($entry['layer_key']) && $entry['layer_key']) {
                $header_name = $entry['layer_key'];
            } else {
                $header_name = ($path == '' ? '' : $path . ' | ') . $entry['title'];
            }

            $value = '';

            if (isset($entry['value']['selected'])) {
                $value = $entry['value']['selected'];
            } else {
                $value = $entry['value'];
            }

            if (is_array($value)) {
                if (isset($value['url'])) {
                    $value = $value['title'];
                } else {
                    $value = implode(', ', $value);
                }
            }

            $row_data[$header_name] = $value;

            if (isset($entry['value']['components']) && is_array($entry['value']['components']) && count($entry['value']['components']) > 0) {
                $this->_getCustomizeData($row_data, $entry['value']['components'], $header_name);
            }
        }
    }

    protected function _getCustomizeHeaders(&$headers, $designs) {
        foreach ($designs as $design) {
            $this->_fetchCustomizeHeaders($headers, $design->data['customize_data']);
        }
    }

    protected function _fetchCustomizeHeaders(&$headers, $customize_data, $path = '') {
        foreach ($customize_data as $entry) {
            if (isset($entry['layer_key']) && $entry['layer_key']) {
                $header_name = $entry['layer_key'];
            } else {
                $header_name = ($path == '' ? '' : $path . ' | ') . $entry['title'];
            }

            if (!in_array($header_name, $headers)) {
                $headers[] = $header_name;
            }

            if (isset($entry['value']['components']) && is_array($entry['value']['components']) && count($entry['value']['components']) > 0) {
                $this->_fetchCustomizeHeaders($headers, $entry['value']['components'], $header_name);
            }
        }
    }

    public static function productPostFrmRender($params) {
        $customize_item = null;

        if (is_array($params['model']->data['tags'])) {
            foreach ($params['model']->data['tags'] as $idx => $tag) {
                if (preg_match('/^meta:customize:(\d+)$/i', $tag, $matches)) {
                    unset($params['model']->data['tags'][$idx]);

                    if ($customize_item === null) {
                        try {
                            $customize_item = OSC::model('catalogItemCustomize/item')->load($matches[1]);
                            $customize_item = [
                                'id' => $customize_item->getId(),
                                'title' => $customize_item->data['title']
                            ];
                        } catch (Exception $ex) {
                            $customize_item = null;
                        }
                    }
                }
            }
        }

        $params['columns']['sidebar'][] = OSC::helper('backend/template')->build('catalogItemCustomize/productPostFrm', ['customize_item' => $customize_item]);
    }

    public static function productPostFrmSaveData($params) {
        $tags = $params['model']->data['tags'];

        if (!is_array($tags)) {
            $tags = [];
        }

        foreach ($tags as $idx => $tag) {
            if (preg_match('/^meta:customize:(\d+)$/i', $tag)) {
                unset($tags[$idx]);
            }
        }

        $customize_id = intval(OSC::core('request')->get('customize_id'));

        if ($customize_id > 0) {
            $tags[] = 'meta:customize:' . $customize_id;
        }

        $params['model']->setData('tags', $tags);
    }

    public static function collectPermKey($params) {
        $params['permission_map']['catalog_item_customize'] = [
            'label' => 'Access customize system',
            'items' => [
                'type' => [
                    'label' => 'Manage customize type'
                ],
                'design' => [
                    'label' => 'Manage design'
                ]
            ]
        ];
    }

    public static function orderCollectDesign($params) {
        foreach ($params['line_items'] as $key => $lineItem) {
            if (is_array($lineItem->data['custom_data']) && count($lineItem->data['custom_data']) > 0) {
                foreach ($lineItem->data['custom_data'] as $custom_data) {
                    if ($custom_data['key'] == 'customize') {
                        unset($params['line_items'][$key]);
                        if (isset($custom_data['data']['printer_image_url'])) {
                            $params['design_urls'][$lineItem->data['item_id']] =  [
                                'key' => $custom_data['data']['design_key'],
                                'url' => $custom_data['data']['printer_image_url']
                            ];
                            break;
                        }
                    }
                }
            }
        }
    }

}
