<?php

class Helper_PersonalizedDesign_DataScanProcess extends OSC_Object {
    protected $_design_data = [];
    public function extractPersonalizedFormData($design_data) {
        $this->_design_data = $design_data;
        $config_frm_data = [];
        $image_data = [];
        $extra_layer = [];

        $this->_extractConfigFrmData($config_frm_data, $image_data, $this->_design_data['objects'], $extra_layer);

        $this->_reorderConfigFrm($config_frm_data);

        return [
            //'extra_layer' => $extra_layer,
            'components' => $config_frm_data,
            //'image_data' => $image_data
        ];
    }

    protected function _reorderConfigFrm(&$config_frm_data) {
        uasort($config_frm_data, function($a, $b) {
            return $a['position'] < $b['position'] ? -1 : ($a['position'] > $b['position'] ? 1 : 0);
        });

        foreach ($config_frm_data as $idx => $component) {
            if (!in_array($component['component_type'], ['switcherByImage', 'switcherBySelect', 'tab'], true)) {
                continue;
            }

            if ($component['component_type'] == 'tab') {
                foreach ($component['tabs'] as $tab_idx => $tab) {
                    $this->_reorderConfigFrm($config_frm_data[$idx]['tabs'][$tab_idx]['components']);
                }
            } else {
                foreach ($component['scenes'] as $scene_idx => $scene) {
                    $this->_reorderConfigFrm($config_frm_data[$idx]['scenes'][$scene_idx]['components']);
                }
            }

        }
    }

    protected function _extractConfigFrmData(&$config_frm_data, &$image_data, $objects, &$extra_layer) {

        foreach (array_reverse($objects) as $object) {
            if (!$object['showable']) {
                continue;
            }

            if (isset($object['personalized'])) {
                // if(is_array($object['personalized']) && isset($object['personalized']['type']) && $object['personalized']['type'] === 'switcher') {
                //     dd($object);
                // }
                if (is_array($object['personalized']) && isset($object['personalized']['type']) && isset($object['personalized']['config'])) {
                    $personalized_frm = '_personalizedForm_' . lcfirst($object['personalized']['type']);

                    if (in_array($object['personalized']['type'], ['switcher', 'imageSelector', 'tab'])) {
                        if (method_exists($this, $personalized_frm)) {
                            $this->$personalized_frm($config_frm_data, $image_data, $object, $extra_layer);
                        }
                    } else {
                        $extra_layer[$object['key']] = [];

                        if (method_exists($this, $personalized_frm)) {
                            $this->$personalized_frm($config_frm_data, $image_data, $object);
                        }
                    }

                    continue;
                }
            }

            if ($object['type'] == 'group') {
                $this->_extractConfigFrmData($config_frm_data, $image_data, $object['type_data']['children'], $extra_layer[$object['key']]);
            } else {
                $extra_layer[$object['key']] = [];
            }
        }
    }

    protected function _personalizedForm_checker(&$config_frm_data, $image_data, $object) {
        $config_frm_data[$object['key']] = [
            'component_type' => 'checker',
            'position' => $object['personalized']['position'],
            'title' => $object['personalized']['config']['title'],
            'default_value' => $object['personalized']['config']['default_value']
        ];
    }

    protected function _personalizedForm_input(&$config_frm_data, $image_data, $object) {
        if ($object['type'] !== 'text') {
            return;
        }
        $config_frm_data[$object['key']] = [
            'component_type' => 'input',
            'position' => $object['personalized']['position'],
            'require' => $object['personalized']['config']['require'],
            'title' => $object['personalized']['config']['title'],
            'description' => $object['personalized']['config']['description'],
            'input_display_default_text' => $object['personalized']['config']['input_display_default_text'],
            'input_disable_all_uppercase' => $object['personalized']['config']['input_disable_all_uppercase'],
            'is_dynamic_input' => $object['personalized']['config']['is_dynamic_input'],
            'max_lines' => $object['personalized']['config']['max_lines'],
            'default_text' => $object['type_data']['content'],
            'min_length' => $object['personalized']['config']['min_length'],
            'max_length' => $object['personalized']['config']['max_length']
        ];

        if ($object['personalized']['config']['is_dynamic_input'] === 1) {
            $config_frm_data[$object['key']]['size'] = $object['type_data']['size'];
            $config_frm_data[$object['key']]['style'] = $object['type_data']['style'];
            $config_frm_data[$object['key']]['font_size'] = $object['type_data']['style']['font_size'];

            $font_name = $object['type_data']['style']['font_name'];
            $font_key = preg_replace('/^(.+)\.[a-zA-Z0-9]+$/', '\\1', $font_name);
            $font_key = preg_replace('/[^a-zA-Z0-9]/', '_', $font_key);
            $font_key = preg_replace('/(^_+|_+$)/', '', $font_key);
            $font_key = preg_replace('/_{2,}/', '_', $font_key);
            $font_key = strtolower($font_key);

            $font_path_ttf = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.ttf';
            $font_path_woff2 = 'personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.woff2';
            $font_path_ttf_s3 = OSC::core('aws_s3')->getStoragePath($font_path_ttf);

            $config_frm_data[$object['key']]['font_url'] = OSC::core('aws_s3')->doesObjectExist($font_path_ttf_s3) ?
                OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($font_path_ttf)) :
                OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($font_path_woff2));
        }
    }

    protected function _personalizedForm_imageUploader(&$config_frm_data, $image_data, $object) {
        if (!in_array($object['type'], ['rect', 'ellipse', 'path'])) {
            return;
        }

        if ($object['type'] == 'rect') {
            $bbox = [
                'x' => $object['type_data']['position']['x'],
                'y' => $object['type_data']['position']['y'],
                'width' => $object['type_data']['size']['width'],
                'height' => $object['type_data']['size']['height']
            ];
        } else if ($object['type'] == 'ellipse') {
            $bbox = [
                'x' => $object['type_data']['center']['x'] - $object['type_data']['rx'],
                'y' => $object['type_data']['center']['y'] - $object['type_data']['ry'],
                'width' => $object['type_data']['rx'] * 2,
                'height' => $object['type_data']['ry'] * 2
            ];
        } else {
            $bbox = $object['type_data']['bbox'];
        }
        $config_frm_data[$object['key']] = [
            'component_type' => 'imageUploader',
            'position' => $object['personalized']['position'],
            'require' => $object['personalized']['config']['require'],
            'title' => $object['personalized']['config']['title'],
            'description' => $object['personalized']['config']['description'],
            'bbox' => $bbox
        ];
    }

    protected function _personalizedForm_imageSelector(&$config_frm_data, &$image_data, $object, &$extra_layer) {
        if ($object['type'] !== 'image') {
            return;
        }

        $groups = [];
        $image_layer_key = [];

        foreach ($object['personalized']['config']['groups'] as $group) {
            if (count($group['images']) < 1) {
                continue;
            }

            $images = [];

            foreach ($group['images'] as $image_key => $image) {
                if ($image_key == $object['personalized']['config']['default_key']) {
                    $image['data'] = ['type_data' => $object['type_data']];
                }

                $id = $image['data']['type_data']['id'];
                $images[$image_key] = [
                    'title' => $image['label'],
                    'id' => $id,
                    // 'url' => $short_url
                ];
                if(!empty($id) && isset($this->_design_data['image_data']) && is_array($this->_design_data['image_data'])) {
                    $img_data = $this->_design_data['image_data'][$id];
                    $full_url = OSC::helper('personalizedDesign/common')->getDesignImageFullUrl(preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $img_data['url']));
                    $optimized_url = OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($full_url, 140, 140, false, false, false, true));

                    $image_data[$id] = [
                        'url' => $optimized_url
                    ];
                }

                $image_layer_key[$image_key] = [];

                if ($image['linking_condition']) {
                    $images[$image_key]['linking_condition'] = $image['linking_condition'];
                }
            }

            $groups[] = [
                'title' => $group['label'],
                'images' => $images
            ];
        }

        if (count($groups) == 1) {
            $group = current($groups);

            $config_frm_data[$object['key']] = [
                'component_type' => 'imageSelector',
                'position' => $object['personalized']['position'],
                'images' => $group['images'],
                'require' => $object['personalized']['config']['require'],
                'title' => $object['personalized']['config']['title'],
                'description' => $object['personalized']['config']['description']
            ];
        } else if (count($groups) > 1) {
            $config_frm_data[$object['key']] = [
                'component_type' => 'imageGroupSelector',
                'position' => $object['personalized']['position'],
                'groups' => $groups,
                'require' => $object['personalized']['config']['require'],
                'title' => $object['personalized']['config']['title'],
                'description' => $object['personalized']['config']['description']
            ];
        }

        $extra_layer[$object['key']] = $image_layer_key;

        if ($object['personalized']['config']['linking_condition']) {
            $config_frm_data[$object['key']]['linking_condition'] = $object['personalized']['config']['linking_condition'];
        }
    }

    protected function _personalizedForm_switcher(&$config_frm_data, &$image_data, $object, &$extra_layer) {
        $version = $this->_getVersion();
        $form_func = '_personalizedForm_switcher_v' . $version;

        if (method_exists($this, $form_func)) {
            $this->$form_func($config_frm_data, $image_data, $object, $extra_layer);
        }
    }

    protected function _personalizedForm_switcher_v1(&$config_frm_data, &$image_data, $object, &$extra_layer) {
        if ($object['type'] !== 'group') {
            return;
        }

        $scenes = [];

        $layers = [];

        foreach ($object['personalized']['config']['options'] as $option_key => $option) {
            $scene = ['components' => []];
            $layer = [];

            if ($option['linking_condition']) {
                $scene['linking_condition'] = $option['linking_condition'];
            }

            if ($object['personalized']['config']['image_mode']) {
                $full_url = OSC::helper('personalizedDesign/common')->getDesignImageFullUrl($option['image']);
                $optimized_url = OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($full_url, 140, 140, false, false, false, true));

                $scene['image'] = [
                    'title' => $option['label'],
                    'url' => $option['image'] ? $optimized_url : '',
                ];
            } else {
                $scene['title'] = $option['label'];
            }

            if ($option_key == $object['personalized']['config']['default_option_key']) {
                $option['data'] = ['objects' => $object['type_data']['children']];
            }

            $this->_extractConfigFrmData($scene['components'], $image_data, $option['data']['objects'], $layer);

            $scenes[$option_key] = $scene;
            $layers[$option_key] = $layer;
        }

        $extra_layer[$object['key']] = $layers;

        $config_frm_data[$object['key']] = [
            'layer' => $object['name'],
            'component_type' => $object['personalized']['config']['image_mode'] ? 'switcherByImage' : 'switcherBySelect',
            'position' => $object['personalized']['position'],
            'scenes' => $scenes,
            'auto_select' => in_array($object['name'], ['dpi_flexible_mug', 'flexible_mug_size']) ? 1 : 0,
            'default_option_key' => $object['personalized']['config']['default_option_key'],
            'search_mode' => $object['personalized']['config']['search_mode'] ?? 0,
            'require' => $object['personalized']['config']['require'],
            'title' => $object['personalized']['config']['title'],
            'description' => $object['personalized']['config']['description']
        ];

        if ($object['personalized']['config']['linking_condition']) {
            $config_frm_data[$object['key']]['linking_condition'] = $object['personalized']['config']['linking_condition'];
        }
    }

    protected function _personalizedForm_switcher_v2(&$config_frm_data, &$image_data, $object, &$extra_layer) {
        if ($object['type'] !== 'group') {
            return;
        }

        $scenes = [];
        $layers = [];

        foreach ($object['personalized']['config']['options'] as $option_key => $option) {
            $scene = ['components' => []];
            $layer = [];

            if ($option['linking_condition']) {
                $scene['linking_condition'] = $option['linking_condition'];
            }

            if ($object['personalized']['config']['image_mode']) {
                $full_url = OSC::helper('personalizedDesign/common')->getDesignImageFullUrl($option['image']);
                $optimized_url = OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($full_url, 140, 140, false, false, false, true));

                $scene['image'] = [
                    'title' => $option['label'],
                    'url' => $option['image'] ? $optimized_url : '',
                ];
            } else {
                $scene['title'] = $option['label'];
            }

            if ($option_key == $object['personalized']['config']['default_option_key']) {
                $option['objects'] = $object['type_data']['children'];
            }

            $this->_extractConfigFrmData($scene['components'], $image_data, $option['objects'], $layer);

            $scenes[$option_key] = $scene;
            $layers[$option_key] = $layer;
        }

        $extra_layer[$object['key']] = $layers;

        $config_frm_data[$object['key']] = [
            'layer' => $object['name'],
            'component_type' => $object['personalized']['config']['image_mode'] ? 'switcherByImage' : 'switcherBySelect',
            'position' => $object['personalized']['position'],
            'scenes' => $scenes,
            'auto_select' => in_array($object['name'], ['dpi_flexible_mug', 'flexible_mug_size']) ? 1 : 0,
            'default_option_key' => $object['personalized']['config']['default_option_key'],
            'search_mode' => $object['personalized']['config']['search_mode'] ?? 0,
            'require' => $object['personalized']['config']['require'],
            'title' => $object['personalized']['config']['title'],
            'description' => $object['personalized']['config']['description']
        ];

        if ($object['personalized']['config']['linking_condition']) {
            $config_frm_data[$object['key']]['linking_condition'] = $object['personalized']['config']['linking_condition'];
        }
    }
    protected function _personalizedForm_spotify(&$config_frm_data, $image_data, $object) {
        if ($object['type'] !== 'rect') {
            return;
        }

        $access_token = OSC::helper('personalizedDesign/spotify')->generateAccessToken();

        $config_frm_data[$object['key']] = [
            'component_type' => 'spotify',
            'position' => $object['personalized']['position'],
            'require' => $object['personalized']['config']['require'],
            'title' => $object['personalized']['config']['title'],
            'description' => $object['personalized']['config']['description'],
            'access_token' => $access_token ?? ''
        ];
    }

    protected function _personalizedForm_tab(&$config_frm_data, &$image_data, $object, &$extra_layer) {
        if ($object['type'] !== 'group') {
            return;
        }

        $children = $object['type_data']['children'];

        if (!is_array($children)) {
            $children = [];
        }

        $tab_items = [];

        $layers = [];

        foreach ($children as $item) {

            if (!$item['showable']) {
                continue;
            }

            if (isset($item['personalized']) && is_array($item['personalized']) && isset($item['personalized']['type']) && isset($item['personalized']['config'])) {
                $personalized_frm = '_personalizedForm_' . lcfirst($item['personalized']['type']);

                if (in_array($item['personalized']['type'], ['switcher', 'imageSelector', 'tab'])) {
                    if (method_exists($this, $personalized_frm)) {
                        $this->$personalized_frm($config_frm_data, $image_data, $item, $extra_layer);
                    }
                } else {
                    $extra_layer[$item['key']] = [];

                    if (method_exists($this, $personalized_frm)) {
                        $this->$personalized_frm($config_frm_data, $image_data, $item);
                    }
                }

                continue;
            }

            $tab_item = [
                'components' => [],
                'title' => $item['name']
            ];

            $layer = [];

            if ($item['type'] === 'group' && !isset($item['personalized'])) {
                $this->_extractConfigFrmData($tab_item['components'], $image_data, $item['type_data']['children'], $layer);
                $tab_items[$item['key']] = $tab_item;
                $layers[$item['key']] = $layer;
            }else {
                $layers[$item['key']]  = [];
            }
        }

        $arr_keys = [];

        if (isset($object['personalized']['config']['order'])) {

            usort($object['personalized']['config']['order'], function($item1, $item2) {
                $order1 = $item1['order'] ?? 0;
                $order2 = $item2['order'] ?? 0;
                return intval($order1) - intval($order2);
            });

            foreach($object['personalized']['config']['order'] as $item) {
                if (isset($item['key'])) {
                    $arr_keys[] = $item['key'];
                }
            }
        }

        $extra_layer[$object['key']] = $layers;

        $config_frm_data[$object['key']] = [
            'component_type' => 'tab',
            'position' => $object['personalized']['position'],
            'title' => $object['personalized']['config']['title'],
            'order' => $arr_keys,
            'tabs' => $tab_items
        ];
    }

    protected function _getVersion() {
        return !empty($this->_design_data['version']) ? intval($this->_design_data['version']) : 1;
    }

}
