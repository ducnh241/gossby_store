<?php

class Model_PersonalizedDesign_Design extends Abstract_Core_Model {

    protected $_table_name = 'personalized_design';
    protected $_pk_field = 'design_id';
    protected $_ukey_field = 'ukey';

    protected $_allow_write_log = true;

    const TYPE_DESIGN_DEFAULT = 0;
    const TYPE_DESIGN_AWZ = 1;

    const TYPE_DESIGN_AWZ_DRAFT = 1;
    const SPOTIFY_DISPLAY_STYLE = [
        'QR_CODE' => 'qr_code',
        'SPOTIFY_BARCODE' => 'spotify_barcode',
    ];

    const AMZ_MODE = [
        0 => 'surface',
        1 => '15option'
    ];

    /**
     *
     * @var Model_User_Member
     */
    protected $_member = null;

    public function getImageFileName() {
        return 'personalizedDesign/designImage/' . $this->getId() . '.png';
    }

    public function getImageUrl() {
        return OSC::core('aws_s3')->getStorageUrl($this->getImageFileName());
    }

    public function getImagePath() {
        return OSC_Storage::getStoragePath($this->getImageFileName());
    }

    public function getVersion() {
        return !empty($this->data['design_data']['version']) ? intval($this->data['design_data']['version']) : 1;
    }

    /**
     *
     * @return Model_User_Member
     */
    public function getMember($reset = false) {
        try {
            if ($this->data['member_id'] > 0 && ($this->_member === null || $reset)) {
                $this->_member = static::getPreLoadedModel('user/member', $this->data['member_id']);
            }
        } catch (Exception $ex) {

        }
        return $this->_member;
    }

    public function getNameCreator() {
        try {
            $member = $this->getMember();
            if (!($member instanceof Model_User_Member) || $member->getId() < 1) {
                throw new Exception('Not found member');
            }
            return $member->data['username'];
        } catch (Exception $ex) {
            return 'Unidentified';
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['design_data', 'palette_color', 'meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['design_data', 'palette_color', 'meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key]);
            }
        }
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);

            if (!$data['title']) {
                $errors[] = 'Title is empty';
            }
        }

        if (isset($data['design_data'])) {
            if (!is_array($data['design_data'])) {
                $errors[] = 'Design data is incorrect data';
            } else {
                try {
                    if ($this->getActionFlag() == static::INSERT_FLAG) {
                        $data['design_data']['version'] = 2;
                    }

                    $version = !empty($data['design_data']['version']) ? intval($data['design_data']['version']) : 1;
                    if ($version == 1) {
                        OSC::helper('personalizedDesign/dataValidator')->validate($data['design_data']);
                    } else {
                        OSC::helper("personalizedDesign/dataValidatorV$version")->validate($data['design_data']);
                    }
                } catch (Exception $ex) {
                    $errors[] = $ex->getMessage();
                }
            }

            $data['is_uploader'] = strpos(OSC::encode($data['design_data']), '"type":"imageUploader"') === false ? 0 : 1;
        }

        if (isset($data['locked_flag'])) {
            $data['locked_flag'] = intval($data['locked_flag']);

            if ($data['locked_flag'] < 0) {
                $data['locked_flag'] = 0;
            } else if ($data['locked_flag'] > 2) {
                $data['locked_flag'] = 2;
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'title' => 'Title is empty',
                    'member_id' => 'Member is empty',
                    'design_data' => 'Design data is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'locked_flag' => 0,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time(),
                    'ukey' => OSC::makeUniqid()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                $data['modified_timestamp'] = time();
                unset($data['ukey']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    private $_mockup_default_option = [];
    public function extractPersonalizedFormData($options = []) {
        $config_frm_data = [];
        $image_data = [];
        $extra_layer = [];

        if (isset($options['is_live_priview'])) {
            $this->_mockup_default_option = $options['is_live_priview']['mockup_default_option'];
        }

        $this->_extractConfigFrmData($config_frm_data, $image_data ,$this->data['design_data']['objects'], $extra_layer);

        $this->_reorderConfigFrm($config_frm_data);

        return [
            'extra_layer' => $extra_layer,
            'components' => $config_frm_data,
            'image_data' => $image_data
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

    protected function _extractConfigFrmData(&$config_frm_data, &$image_data,  $objects, &$extra_layer) {

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
                            $config_frm_data[$object['key']]['layer_name'] = $object['name'];
                        }
                    } else {
                        $extra_layer[$object['key']] = [];

                        if (method_exists($this, $personalized_frm)) {
                            $this->$personalized_frm($config_frm_data, $image_data, $object);
                            $config_frm_data[$object['key']]['layer_name'] = $object['name'];
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

    protected function _getMockupDefaultOptiop($key, $design_default_option) {
        // if product detail is live_preview. Mockup default option is priority;
        $default_option_key = $design_default_option;
        if (count($this->_mockup_default_option) && isset($this->_mockup_default_option[$key])) {
            $default_option_key = $this->_mockup_default_option[$key];
        }

        return $default_option_key;
    }

    protected function _personalizedForm_checker(&$config_frm_data, $image_data, $object) {
        $config_frm_data[$object['key']] = [
            'component_type' => 'checker',
            'position' => $object['personalized']['position'],
            'title' => $object['personalized']['config']['title'],
            'default_value' => $this->_getMockupDefaultOptiop($object['key'], $object['personalized']['config']['default_value']) ? 1 : 0
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
            'default_text' => $this->_getMockupDefaultOptiop($object['key'], $object['type_data']['content']),
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
                if(!empty($id) && isset($this->data['design_data']['image_data']) && is_array($this->data['design_data']['image_data'])) {
                    $img_data = $this->data['design_data']['image_data'][$id];
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

        $default_option_key = $this->_getMockupDefaultOptiop($object['key'], $object['personalized']['config']['default_key']);

        if (count($groups) == 1) {
            $group = current($groups);

            $config_frm_data[$object['key']] = [
                'component_type' => 'imageSelector',
                'position' => $object['personalized']['position'],
                'images' => $group['images'],
                'require' => $object['personalized']['config']['require'],
                'title' => $object['personalized']['config']['title'],
                'description' => $object['personalized']['config']['description'],
                'default_option_key' => $default_option_key
            ];
        } else if (count($groups) > 1) {
            $config_frm_data[$object['key']] = [
                'component_type' => 'imageGroupSelector',
                'position' => $object['personalized']['position'],
                'groups' => $groups,
                'require' => $object['personalized']['config']['require'],
                'title' => $object['personalized']['config']['title'],
                'description' => $object['personalized']['config']['description'],
                'default_option_key' => $default_option_key
            ];
        }

        $extra_layer[$object['key']] = $image_layer_key;

        if ($object['personalized']['config']['linking_condition']) {
            $config_frm_data[$object['key']]['linking_condition'] = $object['personalized']['config']['linking_condition'];
        }
    }

    protected function _personalizedForm_switcher(&$config_frm_data, &$image_data, $object, &$extra_layer) {
        $version = $this->getVersion();
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
        $default_option_key = $this->_getMockupDefaultOptiop($object['key'], $object['personalized']['config']['default_option_key']);

        $config_frm_data[$object['key']] = [
            'layer' => $object['name'],
            'component_type' => $object['personalized']['config']['image_mode'] ? 'switcherByImage' : 'switcherBySelect',
            'position' => $object['personalized']['position'],
            'scenes' => $scenes,
            'auto_select' => in_array($object['name'], ['dpi_flexible_mug', 'flexible_mug_size']) ? 1 : 0,
            'default_option_key' => $default_option_key,
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
        $default_option_key = $this->_getMockupDefaultOptiop($object['key'], $object['personalized']['config']['default_option_key']);

        $config_frm_data[$object['key']] = [
            'layer' => $object['name'],
            'component_type' => $object['personalized']['config']['image_mode'] ? 'switcherByImage' : 'switcherBySelect',
            'position' => $object['personalized']['position'],
            'scenes' => $scenes,
            'auto_select' => in_array($object['name'], ['dpi_flexible_mug', 'flexible_mug_size']) ? 1 : 0,
            'default_option_key' => $default_option_key,
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

    protected function _afterSave() {
        parent::_afterSave();

        $keys = ['ukey', 'title'];

        $index_keywords = [];

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        OSC::helper('backend/common')->indexAdd('', 'personalizedDesign', 'design', $this->getId(), $index_keywords);
    }

    protected function _beforeDelete() {
        parent::_beforeDelete();
        OSC::helper('backend/common')->indexDelete('', 'personalizedDesign', 'design', $this->getId());
    }

    public function getPaletteColor() {
        if (isset($this->data['palette_color']) && is_array($this->data['palette_color']) && count($this->data['palette_color']) > 0) {
            return $this->data['palette_color'];
        }

        try {
            return OSC::helper('personalizedDesign/common')->getImgPaletteColor($this->getImagePath());
        } catch (Exception $ex) {
            return [];
        }

    }
}