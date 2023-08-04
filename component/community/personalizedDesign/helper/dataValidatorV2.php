<?php

class Helper_PersonalizedDesign_DataValidatorV2 extends OSC_Object {
    protected $_image_data = [];
    protected $_character_index = '';

    public function validate(&$data) {
        if (!is_array($data) || !isset($data['document']) || !isset($data['objects']) || !isset($data['bbox'])) {
            throw new Exception('Data format is incorrect');
        }

        if (!is_array($data['objects']) || count($data['objects']) < 1) {
            throw new Exception('Object is empty');
        }

        if (!is_array($data['document']) || !isset($data['document']['width']) || !isset($data['document']['height']) || !isset($data['document']['ratio']) || !isset($data['document']['type'])) {
            throw new Exception('Document format is incorrect');
        }

        $data['document']['type'] = trim($data['document']['type']);
        $data['document']['width'] = intval($data['document']['width']);
        $data['document']['height'] = intval($data['document']['height']);
        $data['document']['ratio'] = floatval($data['document']['ratio']);

        if ($data['document']['width'] <= 0 || $data['document']['height'] <= 0 || $data['document']['ratio'] <= 0) {
            throw new Exception('Document data is incorrect');
        }

        if (!is_array($data['bbox']) || !isset($data['bbox']['width']) || !isset($data['bbox']['height']) || !isset($data['bbox']['x']) || !isset($data['bbox']['y'])) {
            throw new Exception('BBox format is incorrect');
        }

        $data['bbox']['width'] = floatval($data['bbox']['width']);
        $data['bbox']['height'] = floatval($data['bbox']['height']);
        $data['bbox']['x'] = floatval($data['bbox']['x']);
        $data['bbox']['y'] = floatval($data['bbox']['y']);

        if ($data['bbox']['width'] <= 0 || $data['bbox']['height'] <= 0) {
            throw new Exception('BBox data is incorrect');
        }

        $data['document']['custom_document_safe_area_width'] = intval($data['document']['custom_document_safe_area_width']);
        $data['document']['custom_document_safe_area_height'] = intval($data['document']['custom_document_safe_area_height']);

        if (!isset($data['image_data']) || !is_array($data['image_data'])) {
            throw new Exception('image_data is incorrect');
        }

        $this->_validateImageData($data['image_data']);

        $this->_image_data = $data['image_data'];

        if ($data['document']['custom_document_safe_area_height'] <= 0 || $data['document']['custom_document_safe_area_width'] <= 0) {
            unset($data['document']['custom_document_safe_area_width']);
            unset($data['document']['custom_document_safe_area_height']);
        }

        foreach ($data['document'] as $k => $v) {
            if (!in_array($k, ['width', 'height', 'ratio', 'type', 'custom_document_safe_area_width', 'custom_document_safe_area_height'], true)) {
                unset($data['document'][$k]);
            }
        }

        foreach ($data['bbox'] as $k => $v) {
            if (!in_array($k, ['width', 'height', 'x', 'y'], true)) {
                unset($data['bbox'][$k]);
            }
        }

        foreach ($data as $k => $v) {
            if (!in_array($k, ['document', 'objects', 'bbox', 'image_data', 'version'], true)) {
                unset($data[$k]);
            }
        }

        $indexes = [];

        foreach ($data['objects'] as $idx => $object) {
            $this->_validateObject($data['objects'][$idx], $indexes);
        }

        $this->_image_data = [];
    }

    protected function _validateImageData(&$image_data) {
        foreach ($image_data as $idx => $v) {
            try {
                $img_data = &$image_data[$idx];

                if(!is_array($img_data) || !isset($img_data['url']) || !isset($img_data['original_size'])) {
                    throw new Exception('Image data is incorrect');
                }

                $img_data['url'] = trim($img_data['url']);

                if (!$img_data['url']) {
                    throw new Exception('Image url is empty');
                }

                if (strpos($img_data['url'], 'blob:') !== false) {
                    throw new Exception('Image url is incorrect');
                }

                if (!is_array($img_data['original_size']) || !isset($img_data['original_size']['width']) || !isset($img_data['original_size']['height'])) {
                    throw new Exception('Image original size is incorrect');
                }

                $img_data['original_size']['width'] = floatval($img_data['original_size']['width']);
                $img_data['original_size']['height'] = floatval($img_data['original_size']['height']);

                $img_data['original_size'] = [
                    'width' => $img_data['original_size']['width'],
                    'height' => $img_data['original_size']['height']
                ];

                if ($img_data['original_size']['width'] <= 0 || $img_data['original_size']['height'] <= 0) {
                    throw new Exception('Image original size is incorrect');
                }

                if(isset($img_data['hash']) && !OSC::helper('personalizedDesign/common')->verifyImgHash($img_data['hash'])) {
                    throw new Exception('Image hash is incorrect');
                }
            } catch (Exception $ex) {
                //throw $th;
                throw new Exception("Image ID {$idx} Error: {$ex->getMessage()}");
            }

        }
    }

    protected function _validateObject(&$object, &$indexes) {
        if (!is_array($object)) {
            throw new Exception('Some object is incorrect format');
        }

        $object_data_keys = ['key', 'type', 'name', 'showable', 'locked', 'type_data', 'index'];

        foreach ($object_data_keys as $key) {
            if (!isset($object[$key])) {
                throw new Exception('Some object is incorrect format: ' . $key);
            }
        }

        foreach (['key', 'type'] as $key) {
            if (preg_match('/[^a-zA-Z0-9\_]/', $object[$key]) || !$object[$key]) {
                throw new Exception('Some object is incorrect format');
            }
        }

        if (in_array($object['key'], $indexes)) {
            throw new Exception('Object key is already exists [' . $object['key'] . '][' . $object['name'] . ']');
        }

        $indexes[] = $object['key'];

        foreach (['showable', 'locked'] as $key) {
            $object[$key] = intval($object[$key]) == 1 ? true : false;
        }

        $object['name'] = trim($object['name']);
        $object['index'] = intval($object['index']);

        $detected_character_index = false;
        if (!$this->_character_index && !empty($object['personalized']['config']['tags']) && preg_match('/(^#\d)\s*/', $object['personalized']['config']['tags'], $matches)) {
            if (!empty($matches[1])) {
                $this->_character_index = $matches[1];
                $detected_character_index = true;
            }
        }

        $type_validator = '_typeValidator_' . lcfirst($object['type']);

        if (!method_exists($this, $type_validator)) {
            throw new Exception("Cannot detect object type validator [{$object['type']}]");
        }

        $this->$type_validator($object['type_data'], $indexes, $object['name']);

        if (isset($object['personalized'])) {
            if (is_array($object['personalized']) && isset($object['personalized']['type']) && isset($object['personalized']['config'])) {
                $object['personalized'] = [
                    'type' => $object['personalized']['type'],
                    'position' => intval($object['personalized']['position']),
                    'config' => $object['personalized']['config']
                ];

                $personalized_validator = '_validatePersonalized_' . lcfirst($object['personalized']['type']);

                if (!method_exists($this, $personalized_validator)) {
                    throw new Exception("Cannot detect personalized type validator [{$object['personalized']['type']}]");
                }

                $this->$personalized_validator($object['type'], $object['personalized']['config'], $indexes, $object['name']);
            } else {
                throw new Exception('Object personalized data is incorrect format');
            }
        }

        if ($detected_character_index) {
            $this->_character_index = '';
        }
    }

    protected function _validatePersonalized_checker($object_type, &$config, &$indexes, $layer_name = '') {
        if (!is_array($config) || !isset($config['title']) || !isset($config['default_value'])) {
            throw new Exception('Personalized checker config is incorrect');
        }

        $config = [
            'title' => trim($config['title']),
            'default_value' => $config['default_value'] ? 1 : 0
        ];

        if (strlen($config['title']) < 1) {
            throw new Exception('Personalized checker config missing title');
        }
    }

    protected function _validatePersonalized_input($object_type, &$config, &$indexes, $layer_name = '') {
        if ($object_type !== 'text') {
            throw new Exception('The personalized input is unable to use with ' . $object_type);
        }

        if (!is_array($config) || !isset($config['title']) || !isset($config['description']) || !isset($config['require'])) {
            throw new Exception('Personalized input config is incorrect');
        }

        $config = [
            'title' => trim($config['title']),
            'description' => trim($config['description']),
            'min_length' => intval($config['min_length']),
            'max_length' => intval($config['max_length']),
            'tags' => trim($config['tags']),
            'input_display_default_text' => $config['input_display_default_text'] ? 1 : 0,
            'is_dynamic_input' => $config['is_dynamic_input'] ? 1 : 0,
            'max_lines' => intval($config['max_lines']),
            'input_disable_all_uppercase' => $config['input_disable_all_uppercase'] ? 1: 0,
            'require' => $config['require'] ? 1 : 0
        ];

        if (!$config['tags']) {
            unset($config['tags']);
        } else if ($this->_character_index) {
            $config['tags'] = $this->_character_index . ' ' . preg_replace('/(^#\d)\s*/', '', $config['tags']);
        }

        if (strlen($config['title']) < 1) {
            throw new Exception('Personalized input config missing title');
        }

        if ($config['min_length'] < 0) {
            $config['min_length'] = 0;
        }

        if ($config['max_lines'] <= 0) {
            $config['max_lines'] = 1;
        }

        if ($config['max_length'] > 150) {
            $config['max_length'] = 150;
        }

        if ($config['min_length'] > $config['max_length']) {
            $buff = $config['min_length'];
            $config['min_length'] = $config['max_length'];
            $config['max_length'] = $buff;
        }
    }

    protected function _validatePersonalized_imageUploader($object_type, &$config, &$indexes, $layer_name = '') {
        if (!in_array($object_type, ['rect', 'ellipse', 'path'])) {
            throw new Exception('The personalized image uploader is unable to use with ' . $object_type);
        }

        if (!is_array($config) || !isset($config['title']) || !isset($config['description']) || !isset($config['require'])) {
            throw new Exception('Personalized image uploader config is incorrect');
        }

        $config = [
            'title' => trim($config['title']),
            'description' => trim($config['description']),
            'require' => $config['require'] ? 1 : 0,
            'flow_id' => $config['flow_id'] ?? null,
        ];

        if (strlen($config['title']) < 1) {
            throw new Exception('Personalized image uploader config missing title');
        }
    }

    protected function _validateLinkingCondition($condition_data) {
        if (!is_array($condition_data) || !isset($condition_data['matching_all']) || !isset($condition_data['condition']) || !is_array($condition_data['condition']) || count($condition_data['condition']) < 1) {
            return null;
        }

        $operators = ['equals', 'not_equals', 'greater_than', 'less_than', 'starts_with', 'ends_with', 'contains', 'not_contains'];

        foreach ($condition_data['condition'] as $idx => $condition) {
            if (!is_array($condition) || !isset($condition['operator']) || !isset($condition['value'])) {
                unset($condition_data['condition'][$idx]);
                continue;
            }

            $condition['operator'] = trim($condition['operator']);
            $condition['value'] = trim($condition['value']);

            if (!in_array($condition['operator'], $operators, true) || $condition['value'] === '') {
                unset($condition_data['condition'][$idx]);
                continue;
            }

            $condition_data['condition'][$idx] = [
                'operator' => $condition['operator'],
                'value' => $condition['value']
            ];
        }

        if (count($condition_data['condition']) < 1) {
            return null;
        }

        $condition_data['condition'] = array_values($condition_data['condition']);

        return [
            'matching_all' => $condition_data['matching_all'] ? true : false,
            'condition' => $condition_data['condition']
        ];
    }

    protected function _validatePersonalized_switcher($object_type, &$config, &$indexes, $layer_name = '') {
        if ($object_type !== 'group') {
            throw new Exception('The personalized switcher is unable to use with ' . $object_type);
        }

        if (!is_array($config) || !isset($config['title']) || !isset($config['description']) || !isset($config['image_mode']) || !isset($config['options']) || !isset($config['default_option_key']) || !isset($config['require'])) {
            throw new Exception('Personalized switcher config is incorrect');
        }

        $config = [
            'title' => trim($config['title']),
            'tags' => trim($config['tags']),
            'description' => trim($config['description']),
            'image_mode' => $config['image_mode'] ? 1 : 0,
            'tags_mode' => $config['tags_mode'] ? 1 : 0,
            'search_mode' => $config['search_mode'] ? 1 : 0,
            'options' => $config['options'],
            'default_option_key' => $config['default_option_key'],
            'require' => $config['require'] ? 1 : 0,
            'linking_condition' => isset($config['linking_condition']) ? $this->_validateLinkingCondition($config['linking_condition']) : null
        ];

        if (!$config['tags_mode'] || !$config['tags']) {
            unset($config['tags_mode']);
            unset($config['tags']);
        } else if ($this->_character_index) {
            $config['tags'] = $this->_character_index . ' ' . preg_replace('/(^#\d)\s*/', '', $config['tags']);
        }

        if (strlen($config['title']) < 1) {
            throw new Exception('Personalized switcher config missing title');
        }

        if (!is_array($config['options']) || count($config['options']) < 2) {
            throw new Exception('Personalized switcher options is incorrect data');
        }

        if (!isset($config['options'][$config['default_option_key']])) {
            throw new Exception('Personalized switcher :: default option [' . $config['default_option_key'] . '] is not exists');
        }

        if (!$config['linking_condition']) {
            unset($config['linking_condition']);
        }

        $label_list = [];

        foreach ($config['options'] as $option_key => $option) {
            if (!is_array($option) || !isset($option['label']) || !isset($option['objects'])) {
                throw new Exception('Personalized switcher option [' . $option_key . '] :: format is incorrect');
            }

            $option['label'] = trim($option['label']);

            if (strlen($option['label']) < 1) {
                throw new Exception('Personalized switcher option [' . $option_key . '] :: label is empty');
            }

            if (in_array($option['label'], $label_list)) {
                throw new Exception('Personalized switcher option [' . $option_key . '] :: label is already taken by another option');
            }

            $label_list[] = $option['label'];

            $option['image'] = isset($option['image']) ? trim($option['image']) : '';

            if (!$option['image']) {
                unset($option['image']);
            }

            $option['image_hash'] = isset( $option['image_hash']) ? trim($option['image_hash']) : '';

            if (!empty($option['image_hash']) && !OSC::helper('personalizedDesign/common')->verifyImgHash($option['image_hash'])) {
                throw new Exception('Personalized switcher option [' . $option_key . '] :: Image hash is incorrect');
            } else if(empty($option['image_hash'])) {
                unset($option['image_hash']);
            }

            if ($config['default_option_key'] == $option_key) {
                $option['objects'] = [];
            } else if (!isset($option['objects']) || !is_array($option['objects'])) {
                throw new Exception('Personalized switcher option [' . $option_key . '] :: objects format is incorrect');
            } else if (count($option['objects']) > 0) {
                foreach (array_keys($option['objects']) as $_object_idx) {
                    try {
                        $this->_validateObject($option['objects'][$_object_idx], $indexes);
                    } catch (Exception $ex) {
                        throw new Exception('Personalized switcher option [' . $option_key . '] :: data incorrect - ' . $ex->getMessage());
                    }
                }
            }

            $config['options'][$option_key] = [
                'label' => $option['label'],
                'image' => $option['image'],
                'image_hash' => $option['image_hash'],
                'objects' => $option['objects'],
                'tags' => $option['tags'],
                'linking_condition' => isset($option['linking_condition']) ? $this->_validateLinkingCondition($option['linking_condition']) : null
            ];

            if (!$config['tags']) {
                unset($config['options'][$option_key]['tags']);
            }

            if (!isset($config['linking_condition']) || !$config['linking_condition'] || !$config['options'][$option_key]['linking_condition']) {
                unset($config['options'][$option_key]['linking_condition']);
            }
        }
    }

    protected function _validatePersonalized_imageSelector($object_type, &$config, &$indexes, $layer_name = '') {
        if ($object_type !== 'image') {
            throw new Exception('The personalized image selector is unable to use with ' . $object_type);
        }

        if (!is_array($config) || !isset($config['title']) || !isset($config['description']) || !isset($config['groups']) || !isset($config['default_key']) || !isset($config['require'])) {
            throw new Exception('Personalized image selector config is incorrect');
        }

        $config = [
            'title' => trim($config['title']),
            'tags' => trim($config['tags']),
            'description' => trim($config['description']),
            'groups' => $config['groups'],
            'default_key' => $config['default_key'],
            'require' => $config['require'] ? 1 : 0,
            'linking_condition' => isset($config['linking_condition']) ? $this->_validateLinkingCondition($config['linking_condition']) : null
        ];

        if (!$config['tags']) {
            unset($config['tags']);
        } else if ($this->_character_index) {
            $config['tags'] = $this->_character_index . ' ' . preg_replace('/(^#\d)\s*/', '', $config['tags']);
        }

        if (strlen($config['title']) < 1) {
            throw new Exception('Personalized image selector config missing title');
        }

        if (!is_array($config['groups']) || count($config['groups']) < 1) {
            throw new Exception('Personalized image selector groups is incorrect data');
        }

        if (!$config['linking_condition']) {
            unset($config['linking_condition']);
        }

        $default_key_not_found = true;

        $image_keys = [];
        $image_label_list = [];
        $group_label_list = [];

        foreach ($config['groups'] as $group_key => $group) {
            if (!is_array($group) || !isset($group['label']) || !isset($group['images']) || !is_array($group['images'])) {
                throw new Exception('Personalized image selector group [' . $group_key . '] :: format is incorrect');
            }

            $group['label'] = trim($group['label']);

            if (strlen($group['label']) < 1) {
                throw new Exception('Personalized image selector group [' . $group_key . '] :: label is empty');
            }

            if (in_array($group['label'], $group_label_list)) {
                throw new Exception('Personalized image selector group [' . $group_key . '] :: label is already taken by another group');
            }

            $group_label_list[] = $group['label'];

            foreach ($group['images'] as $image_key => $image) {
                if (in_array($image_key, $image_keys, true)) {
                    throw new Exception('Personalized image selector group [' . $group_key . '] :: image key [' . $image_key . '] is already taken by another image');
                }

                $image_keys[] = $image_key;

                if (!is_array($image) || !isset($image['label']) || !isset($image['data']) || !is_array($image['data'])) {
                    throw new Exception('Personalized image selector group [' . $group_key . '] - image [' . $image_key . '] :: format is incorrect');
                }

                $image['label'] = trim($image['label']);

                if (strlen($image['label']) < 1) {
                    throw new Exception('Personalized image selector group [' . $group_key . '] - image [' . $image_key . '] :: label is empty');
                }

                if (in_array($image['label'], $image_label_list)) {
                    throw new Exception('Personalized image selector group [' . $group_key . '] - image [' . $image_key . '] :: label is already taken by another image');
                }

                $image_label_list[] = $image['label'];

                if ($image_key == $config['default_key']) {
                    $default_key_not_found = false;
                    $image['data'] = [];
                } else if (!is_array($image['data']) || !isset($image['data']['type_data'])) {
                    throw new Exception('Personalized image selector group [' . $group_key . '] - image [' . $image_key . '] :: data format is incorrect');
                } else {
                    try {
                        $this->_typeValidator_image($image['data']['type_data'], $indexes, $layer_name);
                    } catch (Exception $ex) {
                        throw new Exception('Personalized image selector group [' . $group_key . '] - image [' . $image_key . '] :: data incorrect - ' . $ex->getMessage());
                    }
                }

                $group['images'][$image_key] = [
                    'label' => $image['label'],
                    'data' => $image['data'],
                    'linking_condition' => isset($image['linking_condition']) ? $this->_validateLinkingCondition($image['linking_condition']) : null
                ];

                if (!isset($config['linking_condition']) || !$config['linking_condition'] || !$group['images'][$image_key]['linking_condition']) {
                    unset($group['images'][$image_key]['linking_condition']);
                }
            }

            $config['groups'][$group_key] = [
                'label' => $group['label'],
                'images' => $group['images']
            ];
        }

        if ($default_key_not_found) {
            throw new Exception('Personalized image selector :: default image [' . $config['default_key'] . '] is not exists');
        }
    }

    protected function _validatePersonalized_spotify($object_type, &$config, &$indexes, $layer_name = '') {
        if ($object_type !== 'rect') {
            throw new Exception('The personalized spotify is unable to use with ' . $object_type);
        }

        if (!is_array($config) || !isset($config['title']) || !isset($config['require']) || !isset($config['background_color']) || !isset($config['bar_color'])) {
            throw new Exception('Personalized spotify config is incorrect');
        }

        $config = [
            'title' => trim($config['title']),
            'description' => trim($config['description']),
            'position' => intval($config['position']),
            'background_color' => trim($config['background_color']),
            'bar_color' => trim($config['bar_color']),
            'display_style' => trim($config['display_style']),
            'require' => $config['require'] ? 1 : 0
        ];

        if (strlen($config['title']) < 1) {
            throw new Exception('Personalized spotify config missing title');
        }

        //Background color is allowed to be null
        if (!empty($config['background_color']) && !preg_match('/^#([a-fA-F0-9]{6})$/', $config['background_color'])) {
            throw new Exception('Background color is incorrect format');
        }

        if (empty($config['bar_color']) || !preg_match('/^#([a-fA-F0-9]{6})$/', $config['bar_color'])) {
            throw new Exception('Bar color is incorrect format');
        }
    }

    protected function _validatePersonalized_tab($object_type, &$config, &$indexes, $layer_name = '') {
        if ($object_type !== 'group') {
            throw new Exception('The personalized Tab is unable to use with ' . $object_type);
        }
        if (!is_array($config) || !isset($config['title'])) {
            throw new Exception('Personalized tab config is incorrect');
        }
        $config = $config;
    }

    protected function _validateMask(&$type_data, &$indexes, $layer_name = '') {
        if (!isset($type_data['mask'])) {
            return;
        } else if (!is_array($type_data['mask']) || count($type_data['mask']) < 1) {
            unset($type_data['mask']);
            return;
        }

        foreach ($type_data['mask'] as $idx => $mask_data) {
            if (!is_array($mask_data) || !isset($mask_data['type']) || !isset($mask_data['data']) || !isset($mask_data['target_bbox'])) {
                throw new Exception('Mask object data is incorrect');
            }

            if (!is_array($mask_data['target_bbox']) || !isset($mask_data['target_bbox']['x']) || !isset($mask_data['target_bbox']['y']) || !isset($mask_data['target_bbox']['width']) || !isset($mask_data['target_bbox']['height'])) {
                throw new Exception('Mask object target BBox is incorrect');
            }

            $mask_data['target_bbox']['x'] = floatval($mask_data['target_bbox']['x']);
            $mask_data['target_bbox']['y'] = floatval($mask_data['target_bbox']['y']);
            $mask_data['target_bbox']['width'] = floatval($mask_data['target_bbox']['width']);
            $mask_data['target_bbox']['height'] = floatval($mask_data['target_bbox']['height']);

            $mask_data['target_bbox'] = [
                'x' => $mask_data['target_bbox']['x'],
                'y' => $mask_data['target_bbox']['y'],
                'width' => $mask_data['target_bbox']['width'],
                'height' => $mask_data['target_bbox']['height']
            ];

            if ($mask_data['target_bbox']['width'] <= 0 || $mask_data['target_bbox']['height'] <= 0) {
                throw new Exception('Mask object target BBox size is incorrect');
            }

            $mask_data['target_rotation'] = floatval($mask_data['target_rotation']);

            foreach ($mask_data as $k => $v) {
                if (!in_array($k, ['type', 'data', 'target_bbox', 'target_rotation'], true)) {
                    unset($mask_data[$k]);
                }
            }

            $type_validator = '_typeValidator_' . lcfirst($mask_data['type']);

            if (!method_exists($this, $type_validator)) {
                throw new Exception("Cannot detect object type validator [{$mask_data['type']}]");
            }

            $this->$type_validator($mask_data['data'], $indexes, $layer_name);

            $type_data['mask'][$idx] = $mask_data;
        }
    }

    protected function _validateOutline(&$type_data, &$indexes, $layer_name = '') {
        if (!isset($type_data['outline'])) {
            return;
        } else if (!is_array($type_data['outline']) || !isset($type_data['outline']['width']) || !isset($type_data['outline']['color'])) {
            unset($type_data['outline']);
            return;
        }

        $type_data['outline']['width'] = floatval($type_data['outline']['width']);
        $type_data['outline']['color'] = trim($type_data['outline']['color']);

        if ($type_data['outline']['width'] === 0 || $type_data['outline']['color'] === 'none') {
            unset($type_data['outline']);
            return;
        }

        $type_data['outline'] = [
            'width' => $type_data['outline']['width'],
            'color' => $type_data['outline']['color']
        ];
    }

    protected function _typeValidator_rect(&$type_data, &$indexes, $layer_name = '') {
        if (!is_array($type_data) || !isset($type_data['fill']) || !isset($type_data['stroke']) || !isset($type_data['position']) || !isset($type_data['size'])) {
            throw new Exception('Rect data is incorrect format');
        }

        $this->_validateMask($type_data, $indexes, $layer_name);

        $type_data['fill'] = trim($type_data['fill']);

        if ($type_data['fill'] === '') {
            $type_data['fill'] = 'none';
        }

        if (!is_array($type_data['stroke']) || !isset($type_data['stroke']['color']) || !isset($type_data['stroke']['width'])) {
            throw new Exception('Rect stroke data is incorrect');
        }

        $type_data['stroke']['color'] = trim($type_data['stroke']['color']);

        if ($type_data['stroke']['color'] === '') {
            $type_data['stroke']['color'] = 'none';
        }

        $type_data['stroke']['width'] = floatval($type_data['stroke']['width']);

        if (!is_array($type_data['position']) || !isset($type_data['position']['x']) || !isset($type_data['position']['y'])) {
            throw new Exception('Rect position is incorrect');
        }

        $type_data['position']['x'] = floatval($type_data['position']['x']);
        $type_data['position']['y'] = floatval($type_data['position']['y']);

        $type_data['position'] = [
            'x' => $type_data['position']['x'],
            'y' => $type_data['position']['y']
        ];

        if (!is_array($type_data['size']) || !isset($type_data['size']['width']) || !isset($type_data['size']['height'])) {
            throw new Exception('Rect size is incorrect in layer ' . $layer_name);
        }

        $type_data['size']['width'] = floatval($type_data['size']['width']);
        $type_data['size']['height'] = floatval($type_data['size']['height']);

        $type_data['size'] = [
            'width' => $type_data['size']['width'],
            'height' => $type_data['size']['height']
        ];

        if ($type_data['size']['width'] <= 0 || $type_data['size']['height'] <= 0) {
            throw new Exception('Rect size is incorrect in layer ' . $layer_name);
        }

        $type_data['rotation'] = floatval($type_data['rotation']);

        foreach ($type_data as $k => $v) {
            if (!in_array($k, ['fill', 'stroke', 'position', 'size', 'rotation', 'mask', 'blend_mode'], true)) {
                unset($type_data[$k]);
            }
        }

        $this->_validateBlendMode($type_data);
    }

    protected function _typeValidator_ellipse(&$type_data, &$indexes, $layer_name = '') {
        if (!is_array($type_data) || !isset($type_data['fill']) || !isset($type_data['stroke']) || !isset($type_data['center']) || !isset($type_data['rx']) || !isset($type_data['ry'])) {
            throw new Exception('Ellipse data is incorrect format');
        }

        $this->_validateMask($type_data, $indexes, $layer_name);

        $type_data['fill'] = trim($type_data['fill']);

        if ($type_data['fill'] === '') {
            $type_data['fill'] = 'none';
        }

        if (!is_array($type_data['stroke']) || !isset($type_data['stroke']['color']) || !isset($type_data['stroke']['width'])) {
            throw new Exception('Ellipse stroke data is incorrect');
        }

        $type_data['stroke']['color'] = trim($type_data['stroke']['color']);

        if ($type_data['stroke']['color'] === '') {
            $type_data['stroke']['color'] = 'none';
        }

        $type_data['stroke']['width'] = floatval($type_data['stroke']['width']);

        if (!is_array($type_data['center']) || !isset($type_data['center']['x']) || !isset($type_data['center']['y'])) {
            throw new Exception('Ellipse center point is incorrect');
        }

        $type_data['center']['x'] = floatval($type_data['center']['x']);
        $type_data['center']['y'] = floatval($type_data['center']['y']);

        $type_data['center'] = [
            'x' => $type_data['center']['x'],
            'y' => $type_data['center']['y']
        ];

        $type_data['rx'] = floatval($type_data['rx']);
        $type_data['ry'] = floatval($type_data['ry']);

        if ($type_data['rx'] <= 0 || $type_data['ry'] <= 0) {
            throw new Exception('Ellipse rx/ry is incorrect');
        }

        $type_data['rotation'] = floatval($type_data['rotation']);

        foreach ($type_data as $k => $v) {
            if (!in_array($k, ['fill', 'stroke', 'center', 'rx', 'ry', 'rotation', 'mask', 'blend_mode'], true)) {
                unset($type_data[$k]);
            }
        }

        $this->_validateBlendMode($type_data);
    }

    protected function _typeValidator_path(&$type_data, &$indexes, $layer_name = '') {
        if (!is_array($type_data) || !isset($type_data['points']) || !isset($type_data['bbox']) || !isset($type_data['fill']) || !isset($type_data['stroke'])) {
            throw new Exception('Path data is incorrect format');
        }

        $this->_validateMask($type_data, $indexes, $layer_name);

        if (!is_array($type_data['bbox']) || !isset($type_data['bbox']['width']) || !isset($type_data['bbox']['height']) || !isset($type_data['bbox']['x']) || !isset($type_data['bbox']['y'])) {
            throw new Exception('Path BBox format is incorrect');
        }

        $type_data['bbox']['width'] = floatval($type_data['bbox']['width']);
        $type_data['bbox']['height'] = floatval($type_data['bbox']['height']);
        $type_data['bbox']['x'] = floatval($type_data['bbox']['x']);
        $type_data['bbox']['y'] = floatval($type_data['bbox']['y']);

        if ($type_data['bbox']['width'] <= 0 || $type_data['bbox']['height'] <= 0) {
            throw new Exception('Path BBox data is incorrect');
        }

        foreach ($type_data['bbox'] as $k => $v) {
            if (!in_array($k, ['width', 'height', 'x', 'y'], true)) {
                unset($type_data['bbox'][$k]);
            }
        }

        $type_data['fill'] = trim($type_data['fill']);

        if ($type_data['fill'] === '') {
            $type_data['fill'] = 'none';
        }

        if (!is_array($type_data['stroke']) || !isset($type_data['stroke']['color']) || !isset($type_data['stroke']['width'])) {
            throw new Exception('Path stroke data is incorrect');
        }

        $type_data['stroke']['color'] = trim($type_data['stroke']['color']);

        if ($type_data['stroke']['color'] === '') {
            $type_data['stroke']['color'] = 'none';
        }

        $type_data['stroke']['width'] = floatval($type_data['stroke']['width']);

        if (!is_array($type_data['points']) || count($type_data['points']) < 1) {
            throw new Exception('Path points is empty');
        }

        $points = [];
        $point_index = 0;

        foreach ($type_data['points'] as $idx => $point) {
            if (!is_array($point) || !isset($point['point'])) {
                throw new Exception('Path points is incorrect');
            }

            if (!isset($point['point']['x']) || !isset($point['point']['y'])) {
                throw new Exception('Path points is incorrect');
            }

            $point['point']['x'] = floatval($point['point']['x']);
            $point['point']['y'] = floatval($point['point']['y']);

            $point['point'] = [
                'x' => $point['point']['x'],
                'y' => $point['point']['y']
            ];

            foreach (['handle_in', 'handle_out'] as $handle_key) {
                if (!isset($point[$handle_key])) {
                    continue;
                }

                if (!isset($point[$handle_key]['x']) || !isset($point[$handle_key]['y'])) {
                    throw new Exception('Path point ' . $handle_key . ' is incorrect');
                }

                $point[$handle_key]['x'] = floatval($point[$handle_key]['x']);
                $point[$handle_key]['y'] = floatval($point[$handle_key]['y']);

                $point[$handle_key] = [
                    'x' => $point[$handle_key]['x'],
                    'y' => $point[$handle_key]['y']
                ];
            }

            foreach ($point as $k => $v) {
                if (!in_array($k, ['point', 'handle_in', 'handle_out'], true)) {
                    unset($point[$k]);
                }
            }

            $point['index'] = $point_index++;

            $points[] = $point;
        }

        $type_data['points'] = $points;
        $type_data['closed'] = $type_data['closed'] || ($type_data['points'][0]['point']['x'] == $type_data['points'][count($type_data['points']) - 1]['point']['x'] && $type_data['points'][0]['point']['y'] == $type_data['points'][count($type_data['points']) - 1]['point']['y']);

        $type_data['rotation'] = floatval($type_data['rotation']);

        foreach ($type_data as $k => $v) {
            if (!in_array($k, ['fill', 'stroke', 'closed', 'bbox', 'points', 'rotation', 'mask', 'blend_mode'], true)) {
                unset($type_data[$k]);
            }
        }

        $this->_validateBlendMode($type_data);
    }

    protected function _typeValidator_text(&$type_data, &$indexes, $layer_name = '') {
        if (!is_array($type_data) || !isset($type_data['content']) || !isset($type_data['fill']) || !isset($type_data['stroke']) || !isset($type_data['position']) || !isset($type_data['size']) || !isset($type_data['style']) || !isset($type_data['offset'])) {
            throw new Exception('Text data is incorrect format');
        }

        $this->_validateMask($type_data, $indexes, $layer_name);
        $this->_validateOutline($type_data, $indexes, $layer_name);

        $type_data['content'] = trim($type_data['content']);

        if ($type_data['content'] === '') {
            //throw new Exception('Text content is empty');
        }

        $type_data['fill'] = trim($type_data['fill']);

        if ($type_data['fill'] === '') {
            $type_data['fill'] = 'none';
        }

        if (!is_array($type_data['stroke']) || !isset($type_data['stroke']['color']) || !isset($type_data['stroke']['width'])) {
            throw new Exception('Text stroke data is incorrect');
        }

        $type_data['stroke']['color'] = trim($type_data['stroke']['color']);

        if ($type_data['stroke']['color'] === '') {
            $type_data['stroke']['color'] = 'none';
        }

        $type_data['stroke']['width'] = floatval($type_data['stroke']['width']);

        if (!is_array($type_data['position']) || !isset($type_data['position']['x']) || !isset($type_data['position']['y'])) {
            throw new Exception('Text position is incorrect');
        }

        $type_data['position']['x'] = floatval($type_data['position']['x']);
        $type_data['position']['y'] = floatval($type_data['position']['y']);

        $type_data['position'] = [
            'x' => $type_data['position']['x'],
            'y' => $type_data['position']['y']
        ];

        if (!is_array($type_data['size']) || !isset($type_data['size']['width']) || !isset($type_data['size']['height'])) {
            throw new Exception('Text size is incorrect');
        }

        $type_data['size']['width'] = floatval($type_data['size']['width']);
        $type_data['size']['height'] = floatval($type_data['size']['height']);

        $type_data['size'] = [
            'width' => $type_data['size']['width'],
            'height' => $type_data['size']['height']
        ];

        if ($type_data['size']['width'] <= 0 || $type_data['size']['height'] <= 0) {
            throw new Exception('Text size is incorrect');
        }

        if (!is_array($type_data['style']) || !isset($type_data['style']['text_align']) || !isset($type_data['style']['font_size']) || !isset($type_data['style']['font_name']) || !isset($type_data['style']['font_style']) || !isset($type_data['style']['color']) || !isset($type_data['style']['line_height']) || !isset($type_data['style']['letter_spacing']) || !isset($type_data['style']['word_spacing'])) {
            throw new Exception('Text style is incorrect');
        }

        if (!in_array($type_data['style']['text_align'], ['left', 'right', 'center'], true)) {
            throw new Exception('Text align is incorrect');
        }

        if (!$type_data['style']['vertical_align']) {
            $type_data['style']['vertical_align'] = 'bottom';
        }

        if (!in_array($type_data['style']['vertical_align'], ['top', 'middle', 'bottom'], true)) {
            throw new Exception('Vertical align is incorrect');
        }

        if (!in_array($type_data['style']['font_style'], ['Regular', 'Bold', 'Italic', 'Bold Italic'], true)) {
            throw new Exception('Font style is incorrect');
        }

        $type_data['style']['font_size'] = floatval($type_data['style']['font_size']);
        if(isset($type_data['style']['dynamic_font_size'])) {
            $type_data['style']['dynamic_font_size'] = floatval($type_data['style']['dynamic_font_size']);
        }
        $type_data['style']['line_height'] = floatval($type_data['style']['line_height']);
        $type_data['style']['letter_spacing'] = floatval($type_data['style']['letter_spacing']);
        $type_data['style']['word_spacing'] = floatval($type_data['style']['word_spacing']);

        if ($type_data['style']['font_size'] <= 0) {
            throw new Exception('Text font size is incorrect');
        }

        $type_data['style']['color'] = trim($type_data['style']['color']);

        if ($type_data['style']['color'] === '') {
            $type_data['style']['color'] = 'none';
        }

        $type_data['style']['font_name'] = trim($type_data['style']['font_name']);

        if (!$type_data['style']['font_name']) {
            throw new Exception('Text font name is incorrect');
        }

        foreach ($type_data['style'] as $k => $v) {
            if (!in_array($k, ['text_align', 'vertical_align', 'dynamic_font_size', 'font_size', 'font_name', 'font_style', 'color', 'line_height', 'letter_spacing', 'word_spacing'])) {
                unset($type_data['style'][$k]);
            }
        }

        if (isset($type_data['path'])) {
            if (!is_array($type_data['path'])) {
                unset($type_data['path']);
            } else if (!isset($type_data['path']['type']) || !isset($type_data['path']['data']) || !isset($type_data['path']['key'])) {
                throw new Exception('Text path is incorrect');
            } else {
                if (in_array($type_data['path']['key'], $indexes)) {
                    throw new Exception('Text path key is already exists');
                }

                $indexes[] = $type_data['path']['key'];

                $type_validator = '_typeValidator_' . lcfirst($type_data['path']['type']);

                if (!method_exists($this, $type_validator)) {
                    throw new Exception("Cannot detect object type validator [{$type_data['path']['type']}]");
                }

                $this->$type_validator($type_data['path']['data'], $indexes, $layer_name);

                $type_data['path'] = [
                    'key' => $type_data['path']['key'],
                    'type' => $type_data['path']['type'],
                    'data' => $type_data['path']['data']
                ];
            }
        }

        $type_data['offset'] = min(max(0, floatval($type_data['offset'])), 100);
        $type_data['rotation'] = floatval($type_data['rotation']);

        foreach ($type_data as $k => $v) {
            if (!in_array($k, ['content', 'fill', 'stroke', 'position', 'size', 'style', 'offset', 'rotation', 'path', 'mask', 'outline', 'blend_mode'])) {
                unset($type_data[$k]);
            }
        }

        $this->_validateBlendMode($type_data);
    }

    protected function _typeValidator_image(&$type_data, &$indexes, $layer_name = '') {
        if (!is_array($type_data) || !isset($type_data['position']) || !isset($type_data['size']) || !isset($type_data['id'])) {
            throw new Exception('Image data is incorrect format');
        }

        $this->_validateMask($type_data, $indexes, $layer_name);

        $img_data = $this->_image_data[$type_data['id']];

        if(!isset($img_data) || !is_array($img_data)) {
            throw new Exception('Image with ID: '.$type_data['id']. " doesn't exist in image_data");
        }

//        $image_path = OSC_Storage::getFilepathFromUrl($type_data['url']);
//
//        if (!$image_path || !file_exists($image_path) || !is_file($image_path)) {
//            throw new Exception('Image file is not exists');
//        }

        if (!is_array($type_data['position']) || !isset($type_data['position']['x']) || !isset($type_data['position']['y'])) {
            throw new Exception('Image position is incorrect');
        }

        $type_data['position']['x'] = floatval($type_data['position']['x']);
        $type_data['position']['y'] = floatval($type_data['position']['y']);

        $type_data['position'] = [
            'x' => $type_data['position']['x'],
            'y' => $type_data['position']['y']
        ];

        if (!is_array($type_data['size']) || !isset($type_data['size']['width']) || !isset($type_data['size']['height'])) {
            throw new Exception('Image size is incorrect');
        }

        $type_data['size']['width'] = floatval($type_data['size']['width']);
        $type_data['size']['height'] = floatval($type_data['size']['height']);

        $type_data['size'] = [
            'width' => $type_data['size']['width'],
            'height' => $type_data['size']['height']
        ];

        if ($type_data['size']['width'] <= 0 || $type_data['size']['height'] <= 0) {
            throw new Exception('Image size is incorrect');
        }

        $type_data['rotation'] = floatval($type_data['rotation']);

        if (isset($type_data['flip_vertical'])) {
            if (!$type_data['flip_vertical']) {
                unset($type_data['flip_vertical']);
            } else {
                $type_data['flip_vertical'] = 1;
            }
        }

        if (isset($type_data['flip_horizontal'])) {
            if (!$type_data['flip_horizontal']) {
                unset($type_data['flip_horizontal']);
            } else {
                $type_data['flip_horizontal'] = 1;
            }
        }

        foreach ($type_data as $k => $v) {
            if (!in_array($k, ['id', 'position', 'size', 'rotation', 'mask', 'flip_vertical', 'flip_horizontal', 'blend_mode'], true)) {
                unset($type_data[$k]);
            }
        }

        $this->_validateBlendMode($type_data);
    }

    protected function _typeValidator_group(&$type_data, &$indexes, $layer_name = '') {
        if (!is_array($type_data) || !isset($type_data['children']) || !is_array($type_data['children'])) {
            throw new Exception('group data is incorrect');
        }

        $type_data = ['children' => $type_data['children'], 'blend_mode' => $type_data['blend_mode']];

        $this->_validateBlendMode($type_data);

        foreach ($type_data['children'] as $k => $v) {
            $this->_validateObject($type_data['children'][$k], $indexes);
        }
    }

    protected function _validateBlendMode(&$type_data) {
        if (isset($type_data['blend_mode'])) {
            throw new Exception('Please remove all applied blend mode layers before save!');
            return;
        }
    }

}