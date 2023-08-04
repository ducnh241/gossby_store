<?php

class Helper_PersonalizedDesign_Common extends OSC_Object {

    const EX_CODE_OPT_NOT_EXISTS = 1000;
    const DEFAULT_SPOTIFY_URL = 'https://open.spotify.com/track/4eHbdreAnSOrDDsFfc4Fpm';

    public function getImgHash($img_path) {
        $return = trim(exec("python3 -c \"import imagehash; from PIL import Image; img = Image.open('{$img_path}'); print(imagehash.whash(img));\""));

        if(! preg_match('/^[a-zA-Z0-9]+$/i', $return)) {
            throw new Exception($return == '' ? 'Unable to generate image hash' : $return);
        }

        return $return;
    }

    public function getImgMainColor($img_path) {
        $return = trim(exec("python3 -c \"from colorthief import ColorThief; from PIL import Image; import io; img = Image.open('{$img_path}'); f = io.BytesIO(); img.resize((300,300)).save(f, format='PNG'); color_thief = ColorThief(f); print('#%02x%02x%02x' % color_thief.get_color(quality=1))\""));

        if(! preg_match('/^#[a-f0-9]{6}$/', strtolower($return))) {
            throw new Exception($return == '' ? 'Unable to get image main color' : $return);
        }

        return $return;
    }

    public function getImgPaletteColor($img_path, $color_count = 5) {
        $return = trim(exec("python3 -c \"from colorthief import ColorThief; from PIL import Image; import io; img = Image.open('{$img_path}'); f = io.BytesIO(); img.resize((300,300)).save(f, format='PNG'); color_thief = ColorThief(f); palette = [];\nfor color in color_thief.get_palette(quality=1, color_count={$color_count}): palette.append('#%02x%02x%02x' % color);\nprint(','.join(palette))\""));

        $palette = explode(',', $return);

        if(count($palette) < 1) {
            throw new Exception($return == '' ? 'Unable to get image main color' : $return);
        }

        foreach($palette as $color) {
            if(! preg_match('/^#[a-f0-9]{6}$/', strtolower($color))) {
                throw new Exception($return == '' ? 'Unable to get image palette color' : $return);
            }
        }

        return $palette;
    }

    public function verifyImgHash($hash) {
        return  preg_match('/^[a-zA-Z0-9]{16}_[a-fA-F0-9]{6}_[a-fA-F0-9]{6}_[a-fA-F0-9]{6}+$/i', $hash);
    }

    public function generateImgHash($img_path) {
        $file_exec = OSC_COMP_PATH.'/community/personalizedDesign/tool/generateImgHash/generate_img_hash.py';

        $output = trim(exec("python3 {$file_exec} -f {$img_path}"));
        if(!$this->verifyImgHash($output)) {
            throw new Exception($output == '' ? 'Unable to generate image hash' : $output);
        }

        return $output;
    }

    public function calculateColorLuminance($color) {
        if(substr($color, 0, 1) == '#') {
            $color = substr($color, 1);
        }

        $color = strtolower($color);

        if(preg_match('/[^a-f0-9]/', $color)) {
            throw new Exception('Color is incorrect');
        }

        if(strlen($color) === 3) {
            $color .= $color;
        } else if(strlen($color) !== 6) {
            throw new Exception('Color is incorrect');
        }

        $color = str_split($color, 2);

        $color[0] = hexdec($color[0]);
        $color[1] = hexdec($color[1]);
        $color[2] = hexdec($color[2]);

        return 0.299*$color[0] + 0.587*$color[1] + 0.114*$color[2];
    }

    public function calculateBgColor($color) {
        $light_bg = '#d9d9d9';
        $dark_bg = '#595959';

        $color_luminance = calculateColorLuminance($color);
        $light_luminance = calculateColorLuminance($light_bg);
        $dark_luminance = calculateColorLuminance($dark_bg);

        return (abs($color_luminance - $light_luminance) > abs($color_luminance - $dark_luminance)) ? $light_bg : $dark_bg;
    }

    public function imageUploaderGetDataToken($data) {
        return md5(OSC::core('encode')->encode($data['file'] . ':' . $data['url'] . ':' . $data['name'] . ':' . $data['size'] . ':' . $data['width'] . ':' . $data['height'], '35498&^*%&^@$98'));
    }

    public function fetchOrnamentTypeByLineItem(Model_Catalog_Order_Item $line_item) {
        $personalized_data_idx = $this->fetchCustomDataIndex($line_item->getOrderItemMeta()->data['custom_data']);

        if ($personalized_data_idx === null) {
            return null;
        }

        $personalized_data = $line_item->getOrderItemMeta()->data['custom_data'][$personalized_data_idx];

        foreach ($personalized_data['data']['config_preview'] as $config) {
            if (strtolower(trim($config['layer'])) == 'ornament_type') {
                $_ornament_type = strtolower(trim(strip_tags($config['value'])));

                if (in_array($_ornament_type, ['star', 'oval', 'heart', 'circle'])) {
                    return $_ornament_type;
                }

                break;
            }
        }

        return null;
    }

    public function pathFetchBBoxByDData($path_d) {
        $data = exec("python -c \"from svgpathtools import parse_path;mypath = parse_path('{$path_d}');xmin, xmax, ymin, ymax = mypath.bbox();print str(xmin) + ':' + str(xmax) + ':' + str(ymin) + ':' + str(ymax)\"");
        $data = explode(':', $data);

        return [
            'x' => floatval($data[0]),
            'y' => floatval($data[2]),
            'width' => floatval($data[1] - $data[0]),
            'height' => floatval($data[3] - $data[2])
        ];
    }

    public function pathMakeDData($points, $closed) {
        $__drawLine = function ($d, $point, $prev_point) {
            $first_point = null;
            $second_point = null;

            if (isset($prev_point['handle_out'])) {
                $first_point = [
                    'x' => $prev_point['handle_out']['x'] + $prev_point['point']['x'],
                    'y' => $prev_point['handle_out']['y'] + $prev_point['point']['y']
                ];
            }

            if (isset($point['handle_in'])) {
                if (!$first_point) {
                    $first_point = [
                        'x' => $point['handle_in']['x'] + $point['point']['x'],
                        'y' => $point['handle_in']['y'] + $point['point']['y']
                    ];
                } else {
                    $second_point = [
                        'x' => $point['handle_in']['x'] + $point['point']['x'],
                        'y' => $point['handle_in']['y'] + $point['point']['y']
                    ];
                }
            }

            if ($first_point) {
                if ($second_point) {
                    $d .= ' C' . $first_point['x'] . ',' . $first_point['y'] . ',' . $second_point['x'] . ',' . $second_point['y'] . ',' . $point['point']['x'] . ',' . $point['point']['y'];
                } else {
                    $d .= ' Q' . $first_point['x'] . ',' . $first_point['y'] . ',' . $point['point']['x'] . ',' . $point['point']['y'];
                }
            } else {
                $d .= ' L' . $point['point']['x'] . ',' . $point['point']['y'];
            }

            return $d;
        };

        $d = '';

        for ($i = 0; $i < count($points); $i++) {
            $point = $points[$i];

            if ($i == 0) {
                $d = 'M' . $point['point']['x'] . ',' . $point['point']['y'];
            } else {
                $d = $__drawLine($d, $point, $points[$i - 1]);
            }
        }

        if ($closed && count($points) > 1) {
            $d = $__drawLine($d, $points[0], $points[count($points) - 1]);
        }

        return $d;
    }

    public function fetchOrnamentType($design, $custom_config) {
        if (!preg_match('/^ornament_(.+)$/i', $design->data['design_data']['document']['type'], $matches)) {
            return null;
        }

        $ornament_type = $matches[1];

        $custom_config_preview = $this->fetchConfigPreview($design, $custom_config);

        foreach ($custom_config_preview as $config) {
            if (strtolower(trim($config['layer'])) == 'ornament_type') {
                $_ornament_type = strtolower(trim(strip_tags($config['value'])));

                if (in_array($_ornament_type, ['star', 'oval', 'heart', 'circle'])) {
                    return $_ornament_type;
                }

                break;
            }
        }

        return $ornament_type;
    }

    public function fetchFlexibleMugSizeByLineItem(Model_Catalog_Order_Item $line_item) {
        $personalized_data_idx = $this->fetchCustomDataIndex($line_item->getOrderItemMeta()->data['custom_data']);

        if ($personalized_data_idx === null) {
            return null;
        }

        $personalized_data = $line_item->getOrderItemMeta()->data['custom_data'][$personalized_data_idx];

        if (!is_array($personalized_data['data']['config_preview']) || !is_array($personalized_data['data']['config_preview']['document_type'])) {
            return null;
        }

        if ($personalized_data['data']['config_preview']['document_type']['value'] == 'flexible_mug') {
            foreach ($personalized_data['data']['config_preview'] as $config) {
                if (strtolower(trim($config['layer'])) == 'flexible_mug_size') {
                    $mug_size = preg_replace('/[^a-zA-Z0-9]/', '', strtolower(trim(strip_tags($config['value']))));

                    if (in_array($mug_size, ['11oz', '15oz'], true)) {
                        return $mug_size;
                    }

                    break;
                }
            }
        }


        if ($personalized_data['data']['config_preview']['document_type']['value'] == 'dpi_flexible_mug') {
            foreach ($personalized_data['data']['config_preview'] as $config) {
                if (strtolower(trim($config['layer'])) == 'dpi_flexible_mug') {
                    $mug_size = strtolower(trim(strip_tags($config['value'])));
                    $mug_size = preg_replace('/[^a-z0-9]+/', '', $mug_size);

                    if (in_array('dpi' . $mug_size, ['dpitraveltumbler14oz', 'dpiinsulatedcoffeemug10oz', 'dpitwotonemugceramicmugwraparound11oz', 'dpiinsulatedwinecup12oz', 'dpienamelcampfinemug12oz', 'dpimug11oz', 'dpimug15oz'], true)) {
                        return 'dpi' . $mug_size;
                    }

                    break;
                }
            }
        }

        return null;
    }

    public function fetchFlexibleMugSize($design, $custom_config) {
        if (!in_array($design->data['design_data']['document']['type'], ['flexible_mug', 'dpi_flexible_mug'], true)) {
            return null;
        }

        $custom_config_preview = $this->fetchConfigPreview($design, $custom_config);

        foreach ($custom_config_preview as $config) {
            if (in_array(strtolower(trim($config['layer'])), ['flexible_mug_size', 'dpi_flexible_mug'], true)) {
                $mug_size = preg_replace('/[^a-z0-9]+/', '', strtolower(trim(strip_tags($config['value']))));

                if (in_array($mug_size, ['11oz', '15oz'], true)) {
                    return $mug_size;
                }

                if (in_array('dpi' . $mug_size, ['dpitraveltumbler14oz', 'dpiinsulatedcoffeemug10oz', 'dpitwotonemugceramicmugwraparound11oz', 'dpiinsulatedwinecup12oz', 'dpienamelcampfinemug12oz', 'dpimug11oz', 'dpimug15oz'], true)) {
                    return 'dpi' . $mug_size;
                }

                break;
            }
        }

        return null;
    }

    public function getDesignImageFullUrl($url) {
        if (preg_match('/http(|s):\/\/+/', $url)) {
            $local_storage_url = OSC_FRONTEND_BASE_URL . '/storage';
            $s3_storage_url = OSC::core('aws_s3')->getStorageDirUrl();

            return str_replace($local_storage_url, $s3_storage_url, $url);
        }

        return OSC::core('aws_s3')->getStorageUrl($url);
    }

    public function getDesignThumbnailFilename($design_file_name) {
        return preg_replace_callback('/^(.+?)\/([^\/]+)\.png$/', function($matches) {
            return $matches[1] . '/' . md5($matches[2]) . '.thumb.png';
        }, $design_file_name);
    }

    public function getDesignImageUrl(Model_Catalog_Order_Item $line_item) {
        $personalized_data = $line_item->getOrderItemMeta()->data['custom_data'][$this->fetchCustomDataIndex($line_item->getOrderItemMeta()->data['custom_data'])];

        $file_name = 'personalizedDesign/render/' . $personalized_data['data']['design_id'] . '/' . $line_item->data['order_id'] . '/' . $line_item->getId() . '/' . md5(OSC::encode($personalized_data['data']['config'])) . '.png';

        return OSC::getServiceUrlPersonalizedDesign() . '/storage/' . $file_name;
    }

    public function getDesignImageThumbnailUrl(Model_Catalog_Order_Item $line_item) {
        return $this->getDesignThumbnailFilename($this->getDesignImageUrl($line_item));
    }

    public function fetchCustomDataIndex($custom_data_entries) {
        if (!is_array($custom_data_entries)) {
            return null;
        }

        foreach ($custom_data_entries as $idx => $custom_data) {
            if ($custom_data['key'] == 'personalized_design') {
                return $idx;
            }
        }
    }

    public function hasEmoji($text) {
        return preg_match('/([*#0-9](?>\\xEF\\xB8\\x8F)?\\xE2\\x83\\xA3|\\xC2[\\xA9\\xAE]|\\xE2..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?(?>\\xEF\\xB8\\x8F)?|\\xE3(?>\\x80[\\xB0\\xBD]|\\x8A[\\x97\\x99])(?>\\xEF\\xB8\\x8F)?|\\xF0\\x9F(?>[\\x80-\\x86].(?>\\xEF\\xB8\\x8F)?|\\x87.\\xF0\\x9F\\x87.|..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?|(((?<zwj>\\xE2\\x80\\x8D)\\xE2\\x9D\\xA4\\xEF\\xB8\\x8F\k<zwj>\\xF0\\x9F..(\k<zwj>\\xF0\\x9F\\x91.)?|(\\xE2\\x80\\x8D\\xF0\\x9F\\x91.){2,3}))?))/', $text);
    }

    public function escapeString($text) {
        $text = str_replace('‘', '\'', $text);
        $text = str_replace('’', '\'', $text);
        $text = str_replace('‚', ',', $text);
        $text = str_replace('“', '"', $text);
        $text = str_replace('”', '"', $text);
        $text = str_replace('„', ',,', $text);
        return $text;
    }

    public function checkTabFlag($design) {
        $tab_flag = 0;
        $this->_extractPersonalizedLayerDataCheckTab($design['objects'], $tab_flag);

        return $tab_flag;
    }

    protected function _extractPersonalizedLayerDataCheckTab($objects, &$tab_flag) {
        foreach (array_reverse($objects) as $object) {
            if (!$object['showable']) {
                continue;
            }

            if (isset($object['personalized'])) {
                if (is_array($object['personalized']) && isset($object['personalized']['type']) && isset($object['personalized']['config'])) {
                    if ($object['personalized']['type'] === 'tab') {
                        $tab_flag = 1;
                        break;
                    }
                    if ($object['personalized']['type'] === 'switcher') {
                        $this->_extractPersonalizedLayerCheckTab_switcher($object, $tab_flag);
                    }
                    if ($object['type'] === 'group') {
                        $this->_extractPersonalizedLayerDataCheckTab($object['type_data']['children'], $tab_flag);
                    }
                    continue;
                }
            }

            if ($object['type'] == 'group') {
                $this->_extractPersonalizedLayerDataCheckTab($object['type_data']['children'], $tab_flag);
            }
        }
    }

    protected function _extractPersonalizedLayerCheckTab_switcher($object, &$tab_flag) {
        if ($object['type'] !== 'group') {
            return;
        }
        foreach ($object['personalized']['config']['options'] as $option_key => $option) {
            if ($option_key == $object['personalized']['config']['default_option_key']) {
                $option['data'] = ['objects' => $object['type_data']['children']];
            }
            $this->_extractPersonalizedLayerDataCheckTab($option['data']['objects'], $tab_flag);
        }
    }

    public function extractPersonalizedLayerData($design) {
        $personalized_layer_data = [];
        $this->_image_data = $design->data['design_data']['image_data'];
        $this->_design_version = $design->getVersion();
        $this->_extractPersonalizedLayerData($personalized_layer_data, $design->data['design_data']['objects']);

        return $personalized_layer_data;
    }

    protected function _extractPersonalizedLayerData(&$personalized_layer_data, $objects) {
        foreach (array_reverse($objects) as $object) {
            if (!$object['showable']) {
                continue;
            }

            if (isset($object['personalized'])) {
                if (is_array($object['personalized']) && isset($object['personalized']['type']) && isset($object['personalized']['config'])) {
                    $personalized_frm = '_extractPersonalizedLayer_' . lcfirst($object['personalized']['type']);

                    if (method_exists($this, $personalized_frm)) {
                        $this->$personalized_frm($personalized_layer_data, $object);
                    }

                    continue;
                }
            }

            if ($object['type'] == 'group') {
                $this->_extractPersonalizedLayerData($personalized_layer_data, $object['type_data']['children']);
            }
        }
    }

    protected function _extractPersonalizedLayer_checker(&$personalized_layer_data, $object) {
        $personalized_layer_data[$object['key']] = [
            'type' => 'checker',
            'frm_title' => $object['personalized']['config']['title'],
            'layer' => $object['name']
        ];
    }

    protected function _extractPersonalizedLayer_input(&$personalized_layer_data, $object) {
        if ($object['type'] !== 'text') {
            return;
        }

        $personalized_layer_data[$object['key']] = [
            'type' => 'input',
            'frm_title' => $object['personalized']['config']['title'],
            'layer' => $object['name'],
            'require' => $object['personalized']['config']['require'],
            'min_length' => $object['personalized']['config']['min_length'],
            'max_length' => $object['personalized']['config']['max_length'],
            'input_disable_all_uppercase' => $object['personalized']['config']['input_disable_all_uppercase'],
            'is_dynamic_input' => $object['personalized']['config']['is_dynamic_input'],
            'max_lines' => $object['personalized']['config']['max_lines'] ?? 1,
        ];
    }

    protected function _extractPersonalizedLayer_imageUploader(&$personalized_layer_data, $object) {
        if (!in_array($object['type'], ['rect', 'ellipse', 'path'])) {
            return;
        }

        $value = [
            'type' => 'imageUploader',
            'frm_title' => $object['personalized']['config']['title'],
            'layer' => $object['name'],
            'require' => $object['personalized']['config']['require'],
        ];

        //flow_id of d2 processing order flow
        if (!empty($object['personalized']['config']['flow_id'])) {
            $value['flow_id'] = $object['personalized']['config']['flow_id'];
        }

        $personalized_layer_data[$object['key']] = $value;
    }

    protected function _extractPersonalizedLayer_imageSelector(&$personalized_layer_data, $object) {
        if ($object['type'] !== 'image') {
            return;
        }

        $images = [];

        foreach ($object['personalized']['config']['groups'] as $group) {
            if (count($group['images']) < 1) {
                continue;
            }

            foreach ($group['images'] as $image_key => $image) {
                if ($image_key == $object['personalized']['config']['default_key']) {
                    $image['data'] = ['type_data' => $object['type_data']];
                }
                $id = $image['data']['type_data']['id'];
                $img_data = $id ? $this->_image_data[$id] : null;

                $images[$image_key] = [
                    'title' => $group['label'] . ($image['label'] ? (' - ' . $image['label']) : ''),
                    'url' => ($img_data && $img_data['url'] ) ? $this->getDesignImageFullUrl(preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.thumb.\\2', $img_data['url']))  :''
                ];
            }
        }

        if (count($images) > 0) {
            $personalized_layer_data[$object['key']] = [
                'type' => 'image',
                'require' => $object['personalized']['config']['require'],
                'frm_title' => $object['personalized']['config']['title'],
                'layer' => $object['name'],
                'images' => $images
            ];
        }
    }

    protected function _extractPersonalizedLayer_switcher(&$personalized_layer_data, $object) {
        if ($object['type'] !== 'group') {
            return;
        }

        $scenes = [];

        foreach ($object['personalized']['config']['options'] as $option_key => $option) {
            $scene = [
                'image' => $option['image'] ? $this->getDesignImageFullUrl($option['image']) : $option['image'],
                'title' => $option['label']
            ];

            switch ($this->_design_version) {
                case 1:
                    if ($option_key == $object['personalized']['config']['default_option_key']) {
                        $option['data'] = ['objects' => $object['type_data']['children']];
                    }

                    $this->_extractPersonalizedLayerData($personalized_layer_data, $option['data']['objects']);
                    break;

                case 2:
                    if ($option_key == $object['personalized']['config']['default_option_key']) {
                        $option['objects'] = $object['type_data']['children'];
                    }

                    $this->_extractPersonalizedLayerData($personalized_layer_data, $option['objects']);
                    break;

                default:
                    # code...
                    break;
            }

            $scenes[$option_key] = $scene;
        }

        if (count($scenes) > 0) {
            $personalized_layer_data[$object['key']] = [
                'type' => 'switcher',
                'require' => $object['personalized']['config']['require'],
                'frm_title' => $object['personalized']['config']['title'],
                'layer' => $object['name'],
                'image_mode' => $object['personalized']['config']['image_mode'],
                'scenes' => $scenes
            ];
        }
    }

    protected function _extractPersonalizedLayer_spotify(&$personalized_layer_data, $object) {
        if ($object['type'] !== 'rect') {
            return;
        }

        $personalized_layer_data[$object['key']] = [
            'type' => 'spotify',
            'frm_title' => $object['personalized']['config']['title'],
            'layer' => $object['name'],
            'background_color' => $object['personalized']['config']['background_color'],
            'bar_color' => $object['personalized']['config']['bar_color'],
            'require' => $object['personalized']['config']['require']
        ];
    }

    protected function _extractPersonalizedLayer_tab(&$personalized_layer_data, $object) {
        if ($object['type'] !== 'group') {
            return;
        }
        $children = $object['type_data']['children'];
        foreach ($children as $item) {

            if (isset($item['personalized'])) {
                if (is_array($item['personalized']) && isset($item['personalized']['type']) && isset($item['personalized']['config'])) {
                    $personalized_frm = '_extractPersonalizedLayer_' . lcfirst($item['personalized']['type']);

                    if (method_exists($this, $personalized_frm)) {
                        $this->$personalized_frm($personalized_layer_data, $item);
                    }

                    continue;
                }
            }

            $this->_extractPersonalizedLayerData($personalized_layer_data, $item['type_data']['children']);
        }
    }

    public function fetchConfigPreview($design, $custom_config) {
        if (!is_array($custom_config) || count($custom_config) < 1) {
            return [];
        }

        $config_preview = [
            'document_type' => [
                'layer' => 'Document Type',
                'form' => 'Document Type',
                'type' => 'document',
                'value' => $design->data['design_data']['document']['type']
            ]
        ];

        $layer_data = $this->extractPersonalizedLayerData($design);

        foreach ($custom_config as $k => $v) {
            if (!isset($layer_data[$k])) {
                $config_preview[$k] = [
                    'layer' => 'Unknown',
                    'form' => 'Unknown',
                    'type' => 'unknown',
                    'hash' => '',
                    'value' => $v
                ];

                continue;
            }

            $value = [
                'layer' => $layer_data[$k]['layer'],
                'form' => $layer_data[$k]['frm_title'],
                'type' => $layer_data[$k]['type'],
                'value' => $v
            ];

            if (!empty($layer_data[$k]['flow_id'])) {
                $value['flow_id'] = $layer_data[$k]['flow_id'];
            }

            $config_preview[$k] = $value;

            switch ($layer_data[$k]['type']) {
                case 'image':
                    if (isset($layer_data[$k]['images'][$v])) {
                        $config_preview[$k]['value'] = '<img src="' . $layer_data[$k]['images'][$v]['url'] . '" title="' . $v . '" /> ' . $layer_data[$k]['images'][$v]['title'];
                    }
                    break;
                case 'switcher':
                    if (isset($layer_data[$k]['scenes'][$v])) {
                        $config_preview[$k]['value'] = ($layer_data[$k]['scenes'][$v]['image'] ? '<img src="' . $layer_data[$k]['scenes'][$v]['image'] . '" title="' . $v . '" /> ' : '') . $layer_data[$k]['scenes'][$v]['title'];
                    }
                    break;
            }
        }

        return $config_preview;
    }

    public function clearData($data) {
        $result = [];

        foreach ($data as $item) {
            $v = intval($item);

            if ($v > 0 && !in_array($v, $result, true)) {
                $result[] = $v;
            }

        }
        return $result;
    }

    public function verifyCustomConfig($design, $custom_config) {
        if (!is_array($custom_config) || count($custom_config) < 1) {
            return $this;
        }

        $layer_data = $this->extractPersonalizedLayerData($design);

        foreach ($custom_config as $k => $v) {
            if (!isset($layer_data[$k])) {
                throw new Exception("The config [{$k}] is not exists");
            }

            switch ($layer_data[$k]['type']) {
                case 'image':
                    $flag_show_error = false;

                    if (trim($v) == '') {
                        if ($layer_data[$k]['require'] == 1) {
                            $flag_show_error = true;
                        }
                    } else {
                        if (!isset($layer_data[$k]['images'][$v])) {
                            $flag_show_error = true;
                        }
                    }

                    if ($flag_show_error) {
                        throw new Exception('The value of field "' . $layer_data[$k]['frm_title'] . '" is not exists');
                    }

                    break;
                case 'switcher':
                    $flag_show_error = false;

                    if (trim($v) == '') {
                        if ($layer_data[$k]['require'] == 1) {
                            $flag_show_error = true;
                        }
                    } else {
                        if (!isset($layer_data[$k]['scenes'][$v])) {
                            $flag_show_error = true;
                        }
                    }

                    if ($flag_show_error) {
                        throw new Exception('The value of field "' . $layer_data[$k]['frm_title'] . '" is not exists');
                    }

                    break;
                case 'input':
                    if (intval($layer_data[$k]['is_dynamic_input']) === 1) {
                        $data = OSC::decode($v);
                        if (!is_numeric($data['fontSize']) || $data['fontSize'] <= 0) {
                            throw new Exception('FontSize is invalid');
                        }
                        $lines = preg_split('/\n/', $data['value']);
                        $max_lines = intval($layer_data[$k]['max_lines']);

                        if ($max_lines > 1 && count($lines) > $max_lines) {
                            throw new Exception('Value has a maximum of [' . $max_lines . '] lines');
                        }
                        $v = $data['value'];
                    }

                    if ($this->hasEmoji($v)) {
                        throw new Exception('We unable to process emoji, please clear emoji in the value of the field "' . $layer_data[$k]['frm_title'] . '"');
                    }

                    $length = OSC::core('string')->strlen($v);

                    if ($layer_data[$k]['min_length'] > 0 && $length > 0 && $length < $layer_data[$k]['min_length']) {
                        throw new Exception('The value of field "' . $layer_data[$k]['frm_title'] . '" need has min ' . $layer_data[$k]['min_length'] . ' characters');
                    }

                    if ($length > ($layer_data[$k]['max_length'] > 0 ? $layer_data[$k]['max_length'] : 150)) {
                        throw new Exception('The value of field "' . $layer_data[$k]['frm_title'] . '" unable to exceed ' . $layer_data[$k]['max_length'] . ' characters');
                    }

                    break;
            }
        }

        return $this;
    }

    public function renderSvgByAPI($design, $custom_config = []) {
        $store_info = OSC::getStoreInfo();

        $request_data = [
            'svg_content' => $this->renderSvg($design, $custom_config)
        ];

        $response = OSC::core('network')->curl(
                OSC::getServiceUrlPersonalizedDesign() . '/personalizedDesign/api/renderSvg', [
            'timeout' => 900,
            'headers' => ['Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_data), $store_info['secret_key'])],
            'json' => $request_data
        ]);

        if (!is_array($response['content']) || !isset($response['content']['result'])) {
            throw new Exception('RenderSVG :: Response data is incorrect [' . print_r($response['content'], 1) . ']');
        }

        if ($response['content']['result'] != 'OK') {
            throw new Exception('RenderSVG :: ' . $response['content']['message']);
        }

        return $response['content']['data']['url'];
    }

    public function renderSvgToFile($png_file_path, $design, $custom_config = []) {
        if (!file_exists($png_file_path)) {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $svg_content = $this->renderSvg($design, $custom_config);
            $svg_content = $this->_convertSvgContentForRender($svg_content);

            $svg_file_path = OSC::core('aws_s3')->tmpSaveFile($svg_content, 'personalizedDesign/svg/.' . OSC::makeUniqid() . '.svg');

            $this->_execRender($png_file_path, $svg_file_path, '-w 500');
        }
    }

    protected function _convertSvgContentForRender($svg_content) {
        $svg_content = preg_replace('/xlink\:href=\"https?\:\/\/[^\/]+\/([^\"]+?)(\.(thumb|preview))?\.(png|jpg|gif|svg)\"/i', 'xlink:href="' . OSC_SITE_PATH . '/\\1.preview.\\4"', $svg_content);
        $svg_content = preg_replace('/url\(https?\:\/\/[^\/]+\/([^\)]+?)\.css\)/i', 'url(' . OSC_SITE_PATH . '/\\1.css)', $svg_content);
        $svg_content = preg_replace('/(alignment-baseline|dominant-baseline)="hanging"/i', '\\1="top"', $svg_content);

        preg_match_all('/(xlink:href="|url\()(\/[^\(\)\'"]+)("|\))/', $svg_content, $matches);

        if (isset($matches[2]) && is_array($matches[2]) && count($matches[2]) > 0) {
            foreach ($matches[2] as $file_path) {
                if (!file_exists($file_path)) {
                    throw new Exception("File [{$file_path}] is not exists");
                }
            }
        }

        return $svg_content;
    }

    /**
     *
     * @param string $png_file_path
     * @param string $svg_file_path
     * @param string $extra_params
     * @throws Exception
     */
    protected function _execRender($png_file_path, $svg_file_path, $extra_params = '') {
        $extra_params = trim($extra_params);

        if ($extra_params) {
            $extra_params = ' ' . $extra_params;
        }

        if (!file_exists($png_file_path)) {
            exec("inkscape {$svg_file_path} --export-text-to-path --export-plain-svg={$svg_file_path}");
            exec("inkscape {$svg_file_path}{$extra_params} -e {$png_file_path}");
        }

        if (file_exists($png_file_path)) {
            $extension = strtolower(preg_replace('/^.+\.([^\.]+)$/', '\\1', $png_file_path));

            if ($extension == 'jpg') {
                $img = @imagecreatefromjpeg($png_file_path);
            } else if ($extension == 'png') {
                $img = @imagecreatefrompng($png_file_path);
            } else if ($extension == 'gif') {
                $img = @imagecreatefromgif($png_file_path);
            } else {
                $img = false;
            }

            if (!$img) {
                throw new Exception("Invalid Rendered Image");
            }

            if (file_exists($svg_file_path)) {
                @unlink($svg_file_path);
            }

            @chown($png_file_path, OSC_FS_USERNAME);
            @chgrp($png_file_path, OSC_FS_USERNAME);
        } else {
            throw new Exception('Cannot render PNG');
        }
    }

    protected $_design_version = 1;
    protected $_svg_uploader_object = [];
    protected $_original_render = false;
    protected $_render_personalized_trigger = false;
    protected $_skip_validate_config = false;
    protected $_svg_layer_data = false;
    protected $_image_data = [];
    protected $_remove_pattern_layer = false;
    protected $_linking_config = [];
    protected $_linking_segments = [];
    protected $_get_linking_segments = false;
    protected $_mapping_config = [];
    protected $_ps_clipart_data = [];
    protected $_ps_clipart_root = [];
    protected $_is_ps_clipart = false;
    protected $_is_live_preview = false;

    public function renderSvg($design, $custom_config = [], $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        $doc_width = $design->data['design_data']['document']['width'];
        $doc_height = $design->data['design_data']['document']['height'];

        $design_id = $design->getId();

        $this->_linking_config = empty($options['linking_config']) ? [] : $options['linking_config'];
        $this->_linking_segments = [];
        $this->_mapping_config = [];
        $this->_is_live_preview = false;

        $this->_ps_clipart_root = [];
        $this->_is_ps_clipart = false;

        if (in_array('get_linking_segments', $options, true)) {
            $this->_get_linking_segments = true;
        } else {
            $this->_get_linking_segments = false;
        }

        if (in_array('original_render', $options, true)) {
            $this->_original_render = true;
        }

        if (in_array('render_personalized_trigger', $options, true)) {
            $this->_render_personalized_trigger = true;
        }

        if (in_array('skip_validate_config', $options, true)) {
            $this->_skip_validate_config = true;
        }

        if (in_array('is_live_preview', $options, true)) {
            $this->_is_live_preview = true;
        }

        $option_build_svg = [];

        if (in_array('render_design', $options, true)) {
            $option_build_svg['render_design'] = 1;
        }

        if (in_array('get_clipart', $options, true)) {
            $this->_is_ps_clipart = true;
        } else {
            $this->_is_ps_clipart = false;
        }

        if (in_array('remove_pattern_layer', $options, true)) {
            $this->_remove_pattern_layer = true;
        }

        try {
            if (!$this->_skip_validate_config) {
                $this->verifyCustomConfig($design, $custom_config);
            }

            $this->_svg_uploader_object = [];

            $def_content = [];

            if ($design_id != $design->getId()) {
                $design = OSC::model('personalizedDesign/design')->load($design_id);
            }

            if (in_array('layer_data', $options, true)) {
                $this->_svg_layer_data = true;
            }

            if (!isset($design->data['design_data']['image_data']) || !is_array($design->data['design_data']['image_data'])) {
                throw new Exception("Design {$design->getId()} doesn't have image_data, please update design first!");
            }

            $this->_image_data = $design->data['design_data']['image_data'];
            $this->_design_version = $design->getVersion();

            $svg_content = $this->_build($def_content, $design->data['design_data']['objects'], $design->data['design_data']['document']['ratio'], 1, $custom_config, $option_build_svg);

            if ($this->_get_linking_segments) {
                return $this->_processLinkingSegments($options['billing_name']);
            }

            if ($this->_linking_config) {
                return $this->_mapping_config;
            }

            $document_type = OSC::safeString($design->data['design_data']['document']['type']);

            $svg_image_uploader = implode('', $this->_svg_uploader_object);

            if ($svg_image_uploader) {
                $svg_image_uploader = <<<EOF
<g data-svg-uploader-group="1" style="display: none">{$svg_image_uploader}</g>
EOF;
            }
        } catch (Exception $ex) {
            $this->_original_render = false;
            $this->_render_personalized_trigger = false;
            $this->_svg_layer_data = false;

            throw new Exception($ex->getMessage(), $ex->getCode());
        }

        $this->_original_render = false;
        $this->_render_personalized_trigger = false;

        if ($this->_svg_layer_data) {
            $this->_svg_layer_data = false;

            return [
                'width' => $doc_width,
                'height' => $doc_height,
                'def_element' => $def_content,
                'elements' => $svg_content
            ];
        }

        $def_content = implode('', $def_content);

        return <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" focusable="false" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 {$doc_width} {$doc_height}" >
    <defs>{$def_content}</defs>
    <g data-document="{$document_type}">{$svg_content}</g>{$svg_image_uploader}
</svg>
EOF;
    }

    public function renderSvgIsPersonalized($design, $custom_config = []) {
        try {
            if (count($custom_config) < 1) {
                throw new Exception('Not have personalized custom data');
            }
            $doc_width = $design->data['design_data']['document']['width'];
            $doc_height = $design->data['design_data']['document']['height'];

            $this->verifyCustomConfig($design, $custom_config);

            $this->_svg_uploader_object = [];

            $def_content = [];
            $result = [];

            $objects =  static::_getLayerPersonalized($result, $design->data['design_data']['objects']);

            if (count($objects) < 1) {
                throw new Exception('Not exist layer personalized');
            }

            $svg_content = $this->_build($def_content, $result, $design->data['design_data']['document']['ratio'], 1, $custom_config, false);

            $def_content = implode('', $def_content);

            $document_type = OSC::safeString($design->data['design_data']['document']['type']);

            $svg_image_uploader = implode('', $this->_svg_uploader_object);

            if ($svg_image_uploader) {
                $svg_image_uploader = <<<EOF
<g data-svg-uploader-group="1" style="display: none">{$svg_image_uploader}</g>
EOF;
            }

            $doc_width_overflow = $doc_width + 300;
            $doc_height_overflow = $doc_height + 300;
            $svg =  <<<EOF
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE svg PUBLIC "-//W3C//DTD SVG 1.1//EN" "http://www.w3.org/Graphics/SVG/1.1/DTD/svg11.dtd">
<svg version="1.1" focusable="false" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="-300 -300 {$doc_width_overflow} {$doc_height_overflow}" >
    <defs>{$def_content}</defs>
    <g data-document="{$document_type}">{$svg_content}</g>{$svg_image_uploader}
</svg>
EOF;
            return ['width' => $doc_width,'height' => $doc_height, 'width_overflow' => $doc_width_overflow, 'height_overflow' => $doc_height_overflow, 'svg_content' => $svg ];
        } catch (Exception $ex) {
            return false;
        }

    }

    public static function _getLayerPersonalized(&$result, $objects) {
        foreach (array_reverse($objects) as $object) {
            if (!$object['showable']) {
                continue;
            }

            if (isset($object['personalized'])) {
                $result[] = $object;
            }

            if ($object['type'] == 'group') {
                static::_getLayerPersonalized($result, $object['type_data']['children']);
            }
        }

        return $result;
    }

    protected function _processLinkingSegments($billing_name) {
        $result = [];
        $lower_billing_name = strtolower($billing_name);
        foreach($this->_linking_segments as $segment_key => $segment_value) {
            $matches = [];
            $character_index = '';
            $is_input = false;

            if (strpos($segment_key, '__') === 0) {
                $segment_key = substr($segment_key, 2);
                $is_input = true;
            }
            if (preg_match('/(^#\d)\s*/', $segment_key, $matches)) {
                if (!empty($matches[1])) {
                    $character_index = $matches[1];
                    $segment_key = preg_replace('/(^#\d)\s*/', '', $segment_key);
                }
            }

            if ($is_input) {
                $result[$character_index][$segment_key] = $segment_value;
                continue;
            }

            $keys = preg_replace('/\s+/', ' ', strtolower($segment_key));
            $keys = explode(' ', $keys);
            $values = preg_replace('/\s+/', ' ', strtolower($segment_value));
            $values = explode(' ', $values);

            $tmp = [];

            foreach ($values as $value) {
                $pair = explode(':', $value);
                if (count($pair) > 1) {
                    $tmp[$pair[0]][] = $pair[1];
                } else {
                    $tmp['list'][] = $pair[0];
                }
            }

            foreach ($keys as $key) {
                foreach ($tmp as $tmp_key => $tmp_value) {
                    if ($tmp_key === 'list') {
                        $result[$character_index][$key] = $tmp_value;
                    } else {
                        $result[$character_index][$key . '_' . $tmp_key] = $tmp_value;
                    }
                }
            }
        }

        $character_data = [];
        $character_names = [];
        $detected_name = '';

        if (!empty($result[''])) {
            foreach ($result[''] as $key => $value) {
                $result[$key] = $value;
            }
            unset($result['']);
        }

        foreach ($result as $key => $value) {
            $matches = [];
            $character_index = '';

            if (preg_match('/^#\d$/', $key, $matches)) {
                $character_index = $key;
            }

            if (!$character_index) continue;

            $character_data[$character_index] = $result[$character_index];
            $name_matches = [];
            $name = '';

            preg_match('/[A-Z]*[a-z]*/', $result[$character_index]['name'], $name_matches);

            if (!empty($name_matches[0])) {
                $name = strtolower($name_matches[0]);
            }

            $character_names[] = $result[$character_index]['name'];

            if (
                $name &&
                $lower_billing_name &&
                (strpos($name, $lower_billing_name) !== false || strpos($lower_billing_name, $name) !== false)
            ) {
                $detected_name = $character_index;
            }

            unset($result[$character_index]);
        }

        if (empty($character_data)) {
            return $result;
        } else if (count($character_data) === 1) {
            foreach ($character_data as $key => $value) {
                $detected_name = $key;
            }
        }

        if (!$detected_name) {
            throw new Exception('Can not detect billing name: "' . $billing_name . '" inside character name list: ' . implode(', ', $character_names));
        }

        if (!empty($character_data[$detected_name])) {
            foreach ($character_data[$detected_name] as $key => $value) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    public function getAllClipArt() {
        return array_reverse($this->_ps_clipart_root);
    }
    protected function _build(&$def_content, $objects, $old_ratio, $new_ratio, $custom_config, $options = []) {
        $content = $this->_svg_layer_data ? [] : '';
        $clip_art_key = 'ps_clipart_';

        foreach (array_reverse($objects) as $object) {
            if ($object['is_not_render_design'] === true && isset($options['render_design'])) {
                continue;
            }

            if (!$object['showable']) {
                continue;
            }

            if (!empty($this->_ps_clipart_data) && !in_array($object['key'], $this->_ps_clipart_data) && $object['type'] != 'group') {
                continue;
            }

            if ($this->_is_ps_clipart && is_numeric(stripos($object['name'], $clip_art_key)) && stripos($object['name'], $clip_art_key) == 0) {
                $this->_ps_clipart_root[$object['name']][] = $object['key'];
            }

            if ($this->_remove_pattern_layer == true && preg_match('#ps_remove#is', $object['name'], $matches)) {
                continue;
            }

            if (isset($object['personalized']) && is_array($object['personalized']) && isset($object['personalized']['type']) && isset($object['personalized']['config'])) {
                try {
                    if (($object['personalized']['type'] == 'checker' && (!is_array($custom_config) || !isset($custom_config[$object['key']]))) || ($object['personalized']['type'] == 'spotify' && OSC::registry('default_spotify') == 1)) {
                        if (!is_array($custom_config)) {
                            $custom_config = [];
                        }

                        if (!isset($custom_config[$object['key']])) {
                            $custom_config[$object['key']] = 0;
                        }
                    }

                    if (
                        !empty($this->_linking_config) ||
                        $this->_get_linking_segments ||
                        is_array($custom_config) && (
                            isset($custom_config[$object['key']]) || (
                                $object['personalized']['type'] == 'input' &&
                                $this->_is_live_preview
                            )
                        )
                    ) {
                        // only type == checker, filter boolean config
                        if ($object['personalized']['type'] == 'checker') $custom_config[$object['key']] = filter_var($custom_config[$object['key']], FILTER_VALIDATE_BOOLEAN);

                        $personalized_applier = '_personalizedApplier_' . lcfirst($object['personalized']['type']);

                        if (method_exists($this, $personalized_applier)) {
                            $this->$personalized_applier($object, $custom_config[$object['key']] ?? '', $old_ratio, $new_ratio);
                        }
                    }
                } catch (Exception $ex) {
                    if (!$this->_skip_validate_config) {
                        throw new Exception($ex->getMessage(), $ex->getCode());
                    }
                }
            }

            if (!$object) {
                continue;
            }

            $option_render_design = [];

            if (isset($options['render_design'])) {
                $option_render_design['render_design'] = 1;
            }

            if ($this->_design_version == 1) {
                $this->_objectApplyNewRatio($object['type'], $object['type_data'], $old_ratio, $new_ratio);
            }

            $object['type_data']['layer_key'] = preg_replace('/[^a-zA-Z0-9]/', '_', $object['key']);
            $object['type_data']['layer_name'] = preg_replace('/[^a-zA-Z0-9]/', '_', $object['name']);

            if (isset($object['personalized']) && is_array($object['personalized']) && isset($object['personalized']['type']) && isset($object['personalized']['config']) && $object['personalized']['type'] == 'imageUploader' && !$this->_original_render && (!isset($custom_config[$object['key']]) || $this->_render_personalized_trigger)) {
                $personalized = $object['personalized'];
                $uploaded_image = $object['type_data']['uploaded_image'];

                unset($object['personalized']);
                unset($object['type_data']['uploaded_image']);

                $this->_svg_uploader_object[] = $this->_objectRender($def_content, $object['type'], $object['type_data'], $old_ratio, $new_ratio, $custom_config, $option_render_design);

                $object['personalized'] = $personalized;

                if ($uploaded_image) {
                    $object['type_data']['uploaded_image'] = $uploaded_image;
                }

                if (!isset($object['type_data']['stroke'])) {
                    $object['type_data']['stroke'] = ['width' => 1];
                }

                $object['type_data']['stroke']['color'] = 'none';
                $object['type_data']['fill'] = 'none';
            }

            $element = $this->_objectRender($def_content, $object['type'], $object['type_data'], $old_ratio, $new_ratio, $custom_config, $option_render_design);

            if ($this->_svg_layer_data) {
                $content[] = $element;
            } else {
                $content .= $element;
            }
        }

        return $content;
    }

    protected function _objectApplyNewRatioToAll(&$objects, $old_ratio, $new_ratio) {
        if ($this->_design_version != 1) {
            return;
        }
        foreach (array_keys($objects) as $idx) {
            $this->_objectApplyNewRatio($objects[$idx]['type'], $objects[$idx]['type_data'], $old_ratio, $new_ratio);

            if ($objects[$idx]['type'] == 'group') {
                $this->_objectApplyNewRatioToAll($objects[$idx]['type_data']['children'], $old_ratio, $new_ratio);
            }
        }
    }

    protected function _personalizedApplier_input(&$object, $config_value, $old_ratio, $new_ratio) {
        if (
            $this->_get_linking_segments &&
            $config_value &&
            !empty($object['personalized']['config']['tags'])
        ) {
            $this->_linking_segments['__' . $object['personalized']['config']['tags']] = $config_value;
        }

        if (
            !empty($this->_linking_config['name']) &&
            !empty($object['personalized']['config']['tags']) &&
            strpos(strtolower($object['personalized']['config']['tags']), 'name') !== false
        ) {
            // this currently work for design with 1 character only
            $config_value = $this->_linking_config['name'];
        }
        $this->_mapping_config[$object['key']] = $config_value;

        if ($object['personalized']['config']['is_dynamic_input'] == 1) {
            $data = OSC::decode($config_value);
            $config_value = $data['value'];
            $font_size = floatval($data['fontSize']);
            if (isset($object['type_data']['style']['original_font_size'])) {
                $font_size *= $object['type_data']['style']['font_size'] / $object['type_data']['style']['original_font_size'];
                unset($object['type_data']['style']['original_font_size']);
            }

            if ($font_size == 0) {
                $font_size = $object['type_data']['style']['font_size'];
            }

            $object['type_data']['outline']['width'] *= $font_size / $object['type_data']['style']['font_size'];
            $object['type_data']['style']['dynamic_font_size'] = $font_size;
        } else {
            unset($object['type_data']['style']['dynamic_font_size']);
        }

        $config_value = trim($config_value);

        if ($object['personalized']['config']['input_disable_all_uppercase'] == 1 && $config_value != '') {
            $config_value = $this->inputDisableAllUppercase($config_value);
        }

        if (
            !empty($config_value) ||
            !$this->_is_live_preview ||
            (
                empty($config_value) &&
                $object['personalized']['config']['input_display_default_text'] == 0
            )
        ) {
            $object['type_data']['content'] = $config_value;
        }
    }

    protected function _personalizedApplier_imageUploader(&$object, $config_value, $old_ratio, $new_ratio) {
        if (is_array($config_value)) {
            $object['type_data']['uploaded_image'] = $config_value;
        } else {
            $object['type_data']['uploaded_image'] = OSC::decode($config_value);
        }
    }

    protected function _personalizedApplier_switcher(&$object, $config_value, $old_ratio, $new_ratio) {
        if (
            $this->_get_linking_segments &&
            $object['personalized']['config']['tags_mode'] &&
            !empty($object['personalized']['config']['tags'])
        ) {
            $this->_linking_segments[$object['personalized']['config']['tags']] = $object['personalized']['config']['options'][$config_value]['tags'];
        }

        if (!empty($this->_linking_config) && !empty($object['personalized']['config']['tags'])) {
            $tag_titles = preg_replace('/\s+/', ' ', $object['personalized']['config']['tags']);
            $tag_titles = explode(' ', $tag_titles);

            $linking_points = [];

            foreach ($tag_titles as $tag_title) {
                foreach ($object['personalized']['config']['options'] as $key => $option) {
                    $point = $this->_getLinkingPoint_switcher($option, $tag_title);
                    if ($point) {
                        $linking_points[] = [
                            'point' => $point,
                            'key' => $key,
                        ];
                    }
                }
            }

            if (count($linking_points)) {
                usort($linking_points, function($a, $b) {
                    return $b['point'] <=> $a['point'];
                });

                $config_value = $linking_points[0]['key'];
            }
        }

        if ($config_value) {
            $this->_mapping_config[$object['key']] = $config_value;
        } else {
            $this->_mapping_config[$object['key']] = $object['personalized']['config']['default_option_key'];
        }

        if (!$config_value || $config_value == $object['personalized']['config']['default_option_key']) {
            return;
        }

        if (!isset($object['personalized']['config']['options'][$config_value])  && $object['personalized']['config']['require']) {
            throw new Exception('Switcher option is not exists', static::EX_CODE_OPT_NOT_EXISTS);
        }

        switch ($this->_design_version) {
            case 1:
                $object['type_data']['children'] = $object['personalized']['config']['options'][$config_value]['data']['objects'];

                $this->_objectApplyNewRatioToAll($object['type_data']['children'], $object['personalized']['config']['options'][$config_value]['data']['ratio'], $old_ratio);
                break;

            case 2:
                $object['type_data']['children'] = $object['personalized']['config']['options'][$config_value]['objects'];
                break;

            default:
                break;
        }

    }

    protected function _getLinkingPoint_switcher($option, $tag_title) {
        $labels = preg_replace('/\s+/', ' ', $option['tags']);
        $labels = explode(' ', $labels);

        $matching_point = 0;

        foreach ($labels as $idx => $label) {
            $tmp = explode(':', $label);
            if (count($tmp) > 1) {
                $segment = $tag_title . '_' . $tmp[0];
                $segment_value = $tmp[1];
            } else {
                $segment = $tag_title;
                $segment_value = $tmp[0];
            }

            foreach ($this->_linking_config[$segment] as $idx1 => $val1) {
                if ($val1 === $segment_value) {
                    $matching_point += 1 / (pow(10, intval($idx)) * pow(10, intval($idx1)));
                }
            }
        }

        return $matching_point;
    }

    protected function _personalizedApplier_imageSelector(&$object, $config_value, $old_ratio, $new_ratio) {
        if (!empty($object['personalized']['config']['tags'])) {
            $selectedImageLabel = '';
            foreach ($object['personalized']['config']['groups'] as $group) {
                if (!empty($group['images'][$config_value])) {
                    $selectedImageLabel = $group['images'][$config_value]['label'];
                }
            }

            $this->_linking_segments[$object['personalized']['config']['tags']] = $selectedImageLabel;
        }

        if (!empty($this->_linking_config) && !empty($object['personalized']['config']['tags'])) {
            $tag_titles = preg_replace('/\s+/', ' ', $object['personalized']['config']['tags']);
            $tag_titles = explode(' ', $tag_titles);

            $linking_points = [];

            foreach ($tag_titles as $tag_title) {
                foreach ($object['personalized']['config']['groups'] as $group) {
                    foreach ($group['images'] as $key => $image) {
                        $point = $this->_getLinkingPoint_imageSelector($image, $tag_title);
                        if ($point) {
                            $linking_points[] = [
                                'point' => $point,
                                'key' => $key,
                                'label' => $image['label'],
                            ];
                        }
                    }
                }
            }

            if (count($linking_points)) {
                usort($linking_points, function($a, $b) {
                    return $b['point'] <=> $a['point'];
                });
                $config_value = $linking_points[0]['key'];
            }
        }

        if ($config_value) {
            $this->_mapping_config[$object['key']] = $config_value;
        } else {
            $this->_mapping_config[$object['key']] = $object['personalized']['config']['default_key'];
        }

        if (!$config_value || $config_value == $object['personalized']['config']['default_key']) {
            return;
        }

        $matched = false;

        foreach ($object['personalized']['config']['groups'] as $group) {
            if (isset($group['images'][$config_value])) {
                $object['type_data'] = $group['images'][$config_value]['data']['type_data'];
                if ($this -> _design_version == 1) {
                    $this->_objectApplyNewRatio($object['type'], $object['type_data'], $group['images'][$config_value]['data']['ratio'], $old_ratio);
                }

                $matched = true;

                break;
            }
        }

        if (!$matched  && $object['personalized']['config']['require']) {
            throw new Exception('Image option is not exists', static::EX_CODE_OPT_NOT_EXISTS);
        }
    }

    protected function _getLinkingPoint_imageSelector($image, $tag_title) {
        $labels = preg_replace('/\s+/', ' ', $image['label']);
        $labels = explode(' ', $labels);

        $matching_point = 0;

        foreach ($labels as $idx => $label) {
            $tmp = explode(':', $label);
            if (count($tmp) > 1) {
                $segment = $tag_title . '_' . $tmp[0];
                $segment_value = $tmp[1];
            } else {
                $segment = $tag_title;
                $segment_value = $tmp[0];
            }

            foreach ($this->_linking_config[$segment] as $idx1 => $val1) {
                if ($val1 === $segment_value) {
                    $matching_point += 1 / (pow(10, intval($idx)) * pow(10, intval($idx1)));
                }
            }
        }

        return $matching_point;
    }

    protected function _personalizedApplier_checker(&$object, $config_value, $old_ratio, $new_ratio) {
        if (!$config_value) {
            $object = null;
        }
    }

    protected function _personalizedApplier_spotify(&$object, $config_value, $old_ratio, $new_ratio) {
        $config_value = OSC::decode($config_value);

        if (OSC::registry('default_spotify') == 1) {
            if (
                isset($object['personalized']['config']['display_style']) &&
                $object['personalized']['config']['display_style'] === Model_PersonalizedDesign_Design::SPOTIFY_DISPLAY_STYLE['QR_CODE']
            ) {
                $svg_content = $this->renderQrCodeSvgDefault($object['personalized']['config']['background_color'] ?? 'none', $object['personalized']['config']['bar_color'] ?? '#000000');
            } else {
                $svg_content = $this->renderSvgSpotifyDefault($object['personalized']['config']['background_color'] ?? 'none',$object['personalized']['config']['bar_color'] ?? '#000000');
            }
        } else if ($object['personalized']['config']['display_style'] === Model_PersonalizedDesign_Design::SPOTIFY_DISPLAY_STYLE['QR_CODE']) {
            $svg_content = OSC::helper('personalizedDesign/spotify')->generateQrCodeSvg($config_value['url'] ?? '', [
                'background_color' => $object['personalized']['config']['background_color'] ?? '',
                'bar_color' => $object['personalized']['config']['bar_color'] ?? ''
            ]);
        } else {
            $svg_content = OSC::helper('personalizedDesign/spotify')->generatePreview($config_value['uri'] ?? '', [
                'background_color' => $object['personalized']['config']['background_color'] ?? '',
                'bar_color' => $object['personalized']['config']['bar_color'] ?? ''
            ]);
        }

        $object['type_data']['display_style'] = $object['personalized']['config']['display_style'];
        $object['type_data']['svg_content'] = $svg_content;
    }

    protected function _objectRender(&$def_content, $object_type, $type_data, $old_ratio, $new_ratio, $custom_config = [], $options = []) {
        $type_builder = '_object' . ucfirst($object_type) . '_render';

        if (!method_exists($this, $type_builder)) {
            return;
        }

        if (!is_array($options)) {
            $options = [];
        }

        $filter_data = ['filter' => [], 'node' => []];

        if (isset($type_data['outline']) && ! $this->_svg_layer_data) {
            $filter_data['filter'][] = <<<EOF
<feMorphology in="SourceAlpha" result="OUTLINE_DILATED" operator="dilate" radius="{$type_data['outline']['width']}"></feMorphology>
<feFlood flood-color="{$type_data['outline']['color']}" flood-opacity="1" result="OUTLINE_COLOR"></feFlood>
<feComposite in="OUTLINE_COLOR" in2="OUTLINE_DILATED" operator="in" result="OUTLINE"></feComposite>
EOF;
            $filter_data['node'][] = 'OUTLINE';
        }

        if (count($filter_data['filter']) > 0) {
            $filter_key = $type_data['layer_key'] . '-filter';

            $filter_data['filter'] = implode('', $filter_data['filter']);

            if (count($filter_data['node']) > 0) {
                $filter_data['node'][] = 'SourceGraphic';

                foreach ($filter_data['node'] as $k => $v) {
                    $filter_data['node'][$k] = '<feMergeNode in="' . $v . '"></feMergeNode>';
                }

                $filter_data['node'] = implode('', $filter_data['node']);
                $filter_data['node'] = <<<EOF
<feMerge>{$filter_data['node']}</feMerge>
EOF;
            } else {
                $filter_data['node'] = '';
            }

            $def_content['filter-' . $filter_key] = <<<EOF
<filter id="filter-{$filter_key}">
{$filter_data['filter']}
{$filter_data['node']}
</filter>
EOF;

            if (!isset($options['attributes'])) {
                $options['attributes'] = [];
            }

            $options['attributes']['filter'] = 'url(#filter-' . $filter_key . ')';
        }

        $element = $this->$type_builder($def_content, $type_data, $old_ratio, $new_ratio, $custom_config, $options);

        if (isset($type_data['outline']) && $this->_svg_layer_data) {
            $element['outline'] = ['color' => $type_data['outline']['color'], 'width' => $type_data['outline']['width']];
        }

        return $element;
    }

    protected function _objectApplyNewRatio($object_type, &$type_data, $old_ratio, $new_ratio) {
        if ($this->_design_version != 1) {
            return;
        }
        $type_applier = '_object' . ucfirst($object_type) . '_applyNewRatioToData';

        if (method_exists($this, $type_applier)) {
            $this->$type_applier($type_data, $old_ratio, $new_ratio);
        }

        $ratio = $new_ratio / $old_ratio;

        if (isset($type_data['outline']) && is_array($type_data['outline']) && isset($type_data['outline']['width'])) {
            $type_data['outline']['width'] *= $ratio;
        }

        if (isset($type_data['mask']) && is_array($type_data['mask'])) {
            foreach (array_keys($type_data['mask']) as $mask_idx) {
                $this->_objectApplyNewRatio($type_data['mask'][$mask_idx]['type'], $type_data['mask'][$mask_idx]['data'], $old_ratio, $new_ratio);

                $type_data['mask'][$mask_idx]['target_bbox']['x'] *= $ratio;
                $type_data['mask'][$mask_idx]['target_bbox']['y'] *= $ratio;
                $type_data['mask'][$mask_idx]['target_bbox']['width'] *= $ratio;
                $type_data['mask'][$mask_idx]['target_bbox']['height'] *= $ratio;
            }
        }
    }

    protected function _objectSetTransform($bbox, $scale, $translate, $rotation = 0, $flip = null) {
        if (!is_array($scale)) {
            $scale = [];
        }

        if (!isset($scale['x'])) {
            $scale['x'] = 1;
        }

        if (!isset($scale['y'])) {
            $scale['y'] = 1;
        }

        if (!is_array($translate)) {
            $translate = [];
        }

        if (!isset($translate['x'])) {
            $translate['x'] = 0;
        }

        if (!isset($translate['y'])) {
            $translate['y'] = 0;
        }

        if (!is_array($flip)) {
            $flip = [];
        }

        if (!isset($flip['x'])) {
            $flip['x'] = 0;
        }

        if (!isset($flip['y'])) {
            $flip['y'] = 0;
        }

        $transform = [];

        if ($rotation !== 0) {
            $transform[] = 'rotate(' . $rotation . ' ' . ($bbox['x'] + ($bbox['width'] * $scale['x'] / 2) + $translate['x']) . ' ' . ($bbox['y'] + ($bbox['height'] * $scale['y'] / 2) + $translate['y']) . ')';
        }

        $translate['x'] += ($scale['x'] - 1) * -$bbox['x'];
        $translate['y'] += ($scale['y'] - 1) * -$bbox['y'];

        if ($translate['x'] !== 0 || $translate['y'] !== 0) {
            $transform[] = 'translate(' . $translate['x'] . ' ' . $translate['y'] . ')';
        }

        if ($scale['x'] !== 1 || $scale['y'] !== 1) {
            $transform[] = 'scale(' . $scale['x'] . ' ' . $scale['y'] . ')';
        }

        if ($flip['x'] || $flip['y']) {
            $transform[] = 'scale(' . ($flip['x'] ? -1 : 1) . ' ' . ($flip['y'] ? -1 : 1) . ')';
            $transform[] = 'translate(' . ($flip['x'] ? -($bbox['width'] + $bbox['x'] * 2) : 0) . ' ' . ($flip['y'] ? -($bbox['height'] + $bbox['y'] * 2) : 0) . ')';
        }

        return implode(' ', $transform);
    }

    protected function _getPointApplyRotation($point = [], $bbox = [], $rotation = 0) {
        $rotation = floatval($rotation);

        if ($rotation !== 0) {
            $center_pt = ['x' => 0, 'y' => 0];

            $center_pt['x'] = $bbox['x'] + $bbox['width'] / 2;
            $center_pt['y'] = $bbox['y'] + $bbox['height'] / 2;

            $degress = atan2($point['y'] - $center_pt['y'], $point['x'] - $center_pt['x']) * 180 / pi();

            $degress += $rotation;

            $distance = $this->_pointGetDistance($center_pt, $point);

            $radian = $degress * pi() / 180;

            $point['x'] = floatval($center_pt['x']) + floatval($distance * cos($radian));
            $point['y'] = floatval($center_pt['y']) + floatval($distance * sin($radian));
        }

        return $point;
    }

    protected function _pointGetDistance($p1, $p2) {
        return sqrt(pow($p2['x'] - $p1['x'], 2) + pow($p2['y'] - $p1['y'], 2));
    }

    protected function _objectImage_render(&$def_content, $type_data, $old_ratio, $new_ratio, $custom_config, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        foreach ($custom_config as $key => $value) {
            if ($type_data['layer_key'] == $key && $value == '') {
                return;
            }
        }

        $img_data = isset($type_data['id']) ? $this->_image_data[$type_data['id']] : null;

        $url = $img_data ? $this->getDesignImageFullUrl(preg_replace('/^(.+)\.([a-zA-Z0-9]+)$/', '\\1.preview.\\2', $img_data['url'])) : '';

        if ($this->_svg_layer_data) {
            return [
                'type' => 'image',
                'data-key' => $type_data['layer_key'],
                'url' => $url,
                'width' => $type_data['size']['width'],
                'height' => $type_data['size']['height'],
                'x' => $type_data['position']['x'],
                'y' => $type_data['position']['y'],
                'rotation' => $type_data['rotation'],
                'flip_vertical' => isset($type_data['flip_vertical']) ? 1 : 0,
                'flip_horizontal' => isset($type_data['flip_horizontal']) ? 1 : 0,
                'blend_mode' => $type_data['blend_mode']
            ];
        }

        $attributes = [
            'data-key' => $type_data['layer_key'],
            'data-layer' => $type_data['layer_name'],
            'width' => $img_data['original_size']['width'],
            'height' => $img_data['original_size']['height'],
            'transform' => $this->_objectSetTransform(['x' => 0, 'y' => 0, 'width' => $img_data['original_size']['width'], 'height' => $img_data['original_size']['height']], ['x' => $type_data['size']['width'] / $img_data['original_size']['width'], 'y' => $type_data['size']['height'] / $img_data['original_size']['height']], ['x' => $type_data['position']['x'], 'y' => $type_data['position']['y']], $type_data['rotation'], ['x' => isset($type_data['flip_vertical']) ? 1 : 0, 'y' => isset($type_data['flip_horizontal']) ? 1 : 0])
        ];

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        if (isset($type_data['blend_mode'])) {
            if (isset($attributes['style'])) {
                //$attributes['style'] .= '; mix-blend-mode: ' . $type_data['blend_mode'];
            } else {
                //$attributes['style'] = 'mix-blend-mode: ' . $type_data['blend_mode'];
            }
        }

        foreach ($attributes as $k => $v) {
            $attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $attributes = implode(' ', $attributes);

        return <<<EOF
<image xlink:href="{$url}" {$attributes} />
EOF;
    }

    protected function _objectImage_applyNewRatioToData(&$type_data, $old_ratio, $new_ratio) {
        $type_data['position']['x'] *= $new_ratio / $old_ratio;
        $type_data['position']['y'] *= $new_ratio / $old_ratio;
        $type_data['size']['width'] *= $new_ratio / $old_ratio;
        $type_data['size']['height'] *= $new_ratio / $old_ratio;
    }

    protected function _objectRect_render(&$def_content, $type_data, $old_ratio, $new_ratio, $custom_config, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        if($this->_svg_layer_data) {
            $return = [
                'type' => 'rect',
                'data-key' => $type_data['layer_key'],
                'width' => $type_data['size']['width'],
                'height' => $type_data['size']['height'],
                'x' => $type_data['position']['x'],
                'y' => $type_data['position']['y'],
                'rotation' => $type_data['rotation'],
                'blend_mode' => $type_data['blend_mode']
            ];

            if (isset($type_data['svg_content'])) {
                $return['type'] = 'spotify';

                if ($type_data['display_style'] === Model_PersonalizedDesign_Design::SPOTIFY_DISPLAY_STYLE['QR_CODE']) {
                    $viewBox = "0 0 500 500";
                } else {
                    $viewBox = "0 0 400 100";
                }

                $return['svg_content'] = <<<EOF
<svg viewBox="{$viewBox}">
    {$type_data['svg_content']}
</svg>
EOF;
            } else {
                if (!isset($type_data['uploaded_image'])) {
                    $this->_processFillColorAttribute($return, $type_data['fill']);

                    if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
                        $return['stroke'] = ['width' => $type_data['stroke']['width']];
                        $this->_processStrokeColorAttribute($return['stroke'], $type_data['stroke']['color']);
                    }

                    return $return;
                }

                return $this->_personalizedImageUploaderApplyMask($def_content, $return, $type_data['uploaded_image'], [
                    'x' => $type_data['position']['x'],
                    'y' => $type_data['position']['y'],
                    'width' => $type_data['size']['width'],
                    'height' => $type_data['size']['height'],
                    'rotation' => $type_data['rotation']
                ]);
            }

            return $return;
        }

        $attributes = [
            'data-key' => $type_data['layer_key'],
            'data-layer' => $type_data['layer_name'],
            'x' => $type_data['position']['x'],
            'y' => $type_data['position']['y'],
            'width' => $type_data['size']['width'],
            'height' => $type_data['size']['height']
        ];

        $this->_processFillColorAttribute($attributes, $type_data['fill']);

        if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
            $this->_processStrokeColorAttribute($attributes, $type_data['stroke']['color']);
            $attributes['stroke-width'] = $type_data['stroke']['width'];
        }

        if ($type_data['rotation'] != 0) {
            $attributes['transform'] = $this->_objectSetTransform(['x' => $type_data['position']['x'], 'y' => $type_data['position']['y'], 'width' => $type_data['size']['width'], 'height' => $type_data['size']['height']], null, null, $type_data['rotation']);
        }

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        if (isset($type_data['blend_mode'])) {
            if (isset($attributes['style'])) {
                //$attributes['style'] .= '; mix-blend-mode: ' . $type_data['blend_mode'];
            } else {
                //$attributes['style'] = 'mix-blend-mode: ' . $type_data['blend_mode'];
            }
        }

        foreach ($attributes as $k => $v) {
            $attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        if (isset($type_data['svg_content'])) {
            $attributes['stroke'] = 'stroke="none"';
            $attributes['stroke-width'] = 'stroke-width="0"';

            $transform_attr = $attributes['transform'] ?? '';
            unset($attributes['transform']);

            if ($type_data['display_style'] === Model_PersonalizedDesign_Design::SPOTIFY_DISPLAY_STYLE['QR_CODE']) {
                $attributes[] = 'viewBox="0 0 500 500"';
            } else {
                $attributes[] = 'viewBox="0 0 400 100"';
            }

            $attributes['preserveAspectRatio'] = 'xMidYMid';

            $attributes = implode(' ', $attributes);

            return <<<EOF
<g {$transform_attr}>
    <svg {$attributes}>
        {$type_data['svg_content']}
    </svg>
</g>
EOF;
        } else {
            $attributes = implode(' ', $attributes);
            $rect_content = <<<EOF
<rect {$attributes} />
EOF;

            if (!isset($type_data['uploaded_image'])) {
                return $rect_content;
            }

            return $this->_personalizedImageUploaderApplyMask($def_content, $rect_content, $type_data['uploaded_image'], [
                'x' => $type_data['position']['x'],
                'y' => $type_data['position']['y'],
                'width' => $type_data['size']['width'],
                'height' => $type_data['size']['height'],
                'rotation' => $type_data['rotation']
            ]);
        }
    }

    protected function _personalizedImageUploaderApplyMask(&$def_content, $mask_content, $uploaded_image, $bbox) {
        $image_width = $uploaded_image['width'];
        $image_height = $uploaded_image['height'];

        $box_size = max($image_width, $image_height);

        $image_x = ($box_size - $image_width) / 2;
        $image_y = ($box_size - $image_height) / 2;


        $mask_unique_id = OSC::makeUniqid();

        $def_content['mask-' . $mask_unique_id] = <<<EOF
<clipPath id="mask-{$mask_unique_id}">{$mask_content}</clipPath>
EOF;

        $image_attributes = [
            'x' => $image_x,
            'y' => $image_y,
            'data-customer-uploaded' => 1,
            'width' => $image_width,
            'height' => $image_height
        ];

        $image_rotation_w = $image_width;
        $image_rotation_h = $image_height;

        if (isset($uploaded_image['rotation'])) {
            $image_attributes['transform'] = $this->_objectSetTransform(['x' => $image_x, 'y' => $image_y, 'width' => $image_width, 'height' => $image_height], ['x' => 1, 'y' => 1], ['x' => 0, 'y' => 0], $uploaded_image['rotation'], ['x' => 0, 'y' => 0]);

            if (in_array($uploaded_image['rotation'], [90, 270])) {
                $image_rotation_w = $image_height;
                $image_rotation_h = $image_width;
            }
        }

        $image_rotation_x = ($box_size - $image_rotation_w) / 2;
        $image_rotation_y = ($box_size - $image_rotation_h) / 2;

        if (isset($uploaded_image['coords'])) {
            $viewport_width = $uploaded_image['coords']['x2'] - $uploaded_image['coords']['x1'];
            $viewport_height = $uploaded_image['coords']['y2'] - $uploaded_image['coords']['y1'];

            $viewport_x = $image_rotation_x + $uploaded_image['coords']['x1'];
            $viewport_y = $image_rotation_y + $uploaded_image['coords']['y1'];
        } else {
            $img_ratio = $image_rotation_w / $image_rotation_h;
            $viewport_ratio = $bbox['width'] / $bbox['height'];

            if ($viewport_ratio < $img_ratio) {
                $viewport_width = $image_rotation_h * $viewport_ratio;
                $viewport_height = $image_rotation_h;
            } else {
                $viewport_width = $image_rotation_w;
                $viewport_height = $image_rotation_w / $viewport_ratio;
            }

            $viewport_x = ($box_size - $viewport_width) / 2;
            $viewport_y = ($box_size - $viewport_height) / 2;
        }

        $scale = $bbox['width'] / $viewport_width;

        $rotation = $bbox['rotation'];

        $position = [
            'x' => $bbox['x'] - ($viewport_x * $scale),
            'y' => $bbox['y'] - ($viewport_y * $scale)
        ];

        if ($rotation != 0) {
            $scale_half_size = ($box_size * $scale) / 2;

            $center_point = ['x' => $position['x'] + $scale_half_size, 'y' => $position['y'] + $scale_half_size];

            $center_point = $this->_getPointApplyRotation($center_point, $bbox, $rotation);

            $position['x'] = $center_point['x'] - $scale_half_size;
            $position['y'] = $center_point['y'] - $scale_half_size;
        }

        if($this->_svg_layer_data) {
            $return = [
                'type' => 'image-upload',
                'data-key' => isset($mask_content['data-key']) ? $mask_content['data-key'] : '',
                'x' => $position['x'],
                'y' => $position['y'],
                'width' => $box_size * $scale,
                'height' => $box_size * $scale,
                'rotation' => $rotation,
                'image' => [
                    'url' => $uploaded_image['url'],
                    'effect' => $uploaded_image['effect_config']['type'],
                    'x' => $image_x * $scale,
                    'y' => $image_y * $scale,
                    'width' => $image_width * $scale,
                    'height' => $image_height * $scale,
                    'rotation' => $uploaded_image['rotation']
                ],
                'mask' => $mask_content
            ];

            return $return;
        }

        $box_attributes = [
            'x' => 0,
            'y' => 0,
            'width' => $box_size,
            'height' => $box_size,
            'transform' => $this->_objectSetTransform(
                    [
                        'x' => 0,
                        'y' => 0,
                        'width' => $box_size,
                        'height' => $box_size
                    ],
                    [
                        'x' => $scale,
                        'y' => $scale
                    ],
                    $position,
                    $rotation,
                    [
                        'x' => 0,
                        'y' => 0
                    ]
            )
        ];

        if (isset($uploaded_image['effect_config']) && is_array($uploaded_image['effect_config']) && isset($uploaded_image['effect_config']['type'])) {
            $filter_id = OSC::makeUniqid();

            /**
              contrast: slope = amount, intercept = - (0.5 * amount) + 0.5
             */
            switch ($uploaded_image['effect_config']['type']) {
                case 'bw':
                    $image_attributes['filter'] = 'url(#filter-' . $filter_id . ')';
                    $def_content['filter-' . $filter_id] = <<<EOF
<filter id="filter-{$filter_id}" color-interpolation-filters="sRGB">
    <feColorMatrix type="matrix" in="SourceGraphic" result="bw-filter" values="0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0.3333 0.3333 0.3333 0 0 0 0 0 1 0" />
    <feComponentTransfer in="bw-filter" result="contrast-filter">
        <feFuncR type="linear" slope="1.5" intercept="-0.25"/>
        <feFuncG type="linear" slope="1.5" intercept="-0.25"/>
        <feFuncB type="linear" slope="1.5" intercept="-0.25"/>
    </feComponentTransfer>
</filter>
EOF;
                    break;
                case 'sepia':
                    $image_attributes['filter'] = 'url(#filter-' . $filter_id . ')';
                    $def_content['filter-' . $filter_id] = <<<EOF
<filter id="filter-{$filter_id}" color-interpolation-filters="sRGB">
    <feColorMatrix type="matrix" in="SourceGraphic" result="sepia-filter" values="0.39 0.769 0.189 0 0 0.349 0.686 0.168 0 0 0.272 0.534 0.131 0 0 0 0 0 1 0" />
    <feComponentTransfer in="sepia-filter" result="contrast-filter">
        <feFuncR type="linear" slope="1.25" intercept="-0.125"/>
        <feFuncG type="linear" slope="1.25" intercept="-0.125"/>
        <feFuncB type="linear" slope="1.25" intercept="-0.125"/>
    </feComponentTransfer>
</filter>
EOF;
                    break;
                case 'saturate':
                    $image_attributes['filter'] = 'url(#filter-' . $filter_id . ')';
                    $def_content['filter-' . $filter_id] = <<<EOF
<filter id="filter-{$filter_id}" color-interpolation-filters="sRGB">
    <feColorMatrix type="saturate" in="SourceGraphic" result="saturate-filter" values="1.75"/>
</filter>
EOF;
                    break;
                case 'contrast':
                    $image_attributes['filter'] = 'url(#filter-' . $filter_id . ')';
                    $def_content['filter-' . $filter_id] = <<<EOF
<filter id="filter-{$filter_id}" color-interpolation-filters="sRGB">
    <feComponentTransfer in="SourceGraphic" result="contrast-filter">
        <feFuncR type="linear" slope="1.5" intercept="-0.25"/>
        <feFuncG type="linear" slope="1.5" intercept="-0.25"/>
        <feFuncB type="linear" slope="1.5" intercept="-0.25"/>
    </feComponentTransfer>
</filter>
EOF;
                    break;
            }
        }

        foreach ($image_attributes as $k => $v) {
            $image_attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $image_attributes = implode(' ', $image_attributes);

        $type_data['url'] = $uploaded_image['url'];

        foreach ($box_attributes as $k => $v) {
            $box_attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $box_attributes = implode(' ', $box_attributes);

        return <<<EOF
<g clip-path="url(#mask-{$mask_unique_id})">
    <g {$box_attributes}>
        <rect x="0" y="0" width="{$box_size}" height="{$box_size}" opacity="0"></rect>
        <image xlink:href="{$type_data['url']}" {$image_attributes} />
    </g>
</g>
EOF;
    }

    protected function _objectRect_applyNewRatioToData(&$type_data, $old_ratio, $new_ratio) {
        $type_data['position']['x'] *= $new_ratio / $old_ratio;
        $type_data['position']['y'] *= $new_ratio / $old_ratio;
        $type_data['size']['width'] *= $new_ratio / $old_ratio;
        $type_data['size']['height'] *= $new_ratio / $old_ratio;
    }

    protected function _objectEllipse_render(&$def_content, $type_data, $old_ratio, $new_ratio, $custom_config, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        if($this->_svg_layer_data) {
            $return = [
                'type' => 'ellipse',
                'data-key' => $type_data['layer_key'],
                'cx' => $type_data['center']['x'],
                'cy' => $type_data['center']['y'],
                'rx' => $type_data['rx'],
                'ry' => $type_data['ry'],
                'rotation' => $type_data['rotation'],
                'blend_mode' => $type_data['blend_mode']
            ];

            if (!isset($type_data['uploaded_image'])) {
                $this->_processFillColorAttribute($return, $type_data['fill']);

                if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
                    $return['stroke'] = ['width' => $type_data['stroke']['width']];
                    $this->_processStrokeColorAttribute($return['stroke'], $type_data['stroke']['color']);
                }

                return $return;
            }

            return $this->_personalizedImageUploaderApplyMask($def_content, $return, $type_data['uploaded_image'], [
                'x' => $type_data['center']['x'] - $type_data['rx'],
                'y' => $type_data['center']['y'] - $type_data['ry'],
                'width' => $type_data['rx'] * 2,
                'height' => $type_data['ry'] * 2,
                'rotation' => $type_data['rotation']
            ]);
        }

        $attributes = [
            'data-key' => $type_data['layer_key'],
            'data-layer' => $type_data['layer_name'],
            'cx' => $type_data['center']['x'],
            'cy' => $type_data['center']['y'],
            'rx' => $type_data['rx'],
            'ry' => $type_data['ry']
        ];

        $this->_processFillColorAttribute($attributes, $type_data['fill']);

        if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
            $this->_processStrokeColorAttribute($attributes, $type_data['stroke']['color']);
            $attributes['stroke-width'] = $type_data['stroke']['width'];
        }

        if ($type_data['rotation'] != 0) {
            $attributes['transform'] = $this->_objectSetTransform(['x' => $type_data['center']['x'] - $type_data['rx'], 'y' => $type_data['center']['y'] - $type_data['ry'], 'width' => $type_data['rx'] * 2, 'height' => $type_data['ry'] * 2], null, null, $type_data['rotation']);
        }

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        if (isset($type_data['blend_mode'])) {
            if (isset($attributes['style'])) {
                //$attributes['style'] .= '; mix-blend-mode: ' . $type_data['blend_mode'];
            } else {
                //$attributes['style'] = 'mix-blend-mode: ' . $type_data['blend_mode'];
            }
        }

        foreach ($attributes as $k => $v) {
            $attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $attributes = implode(' ', $attributes);

        $ellipse_content = <<<EOF
<ellipse {$attributes} />
EOF;

        if (!isset($type_data['uploaded_image'])) {
            return $ellipse_content;
        }

        return $this->_personalizedImageUploaderApplyMask($def_content, $ellipse_content, $type_data['uploaded_image'], [
                    'x' => $type_data['center']['x'] - $type_data['rx'],
                    'y' => $type_data['center']['y'] - $type_data['ry'],
                    'width' => $type_data['rx'] * 2,
                    'height' => $type_data['ry'] * 2,
                    'rotation' => $type_data['rotation']
        ]);
    }

    protected function _objectEllipse_applyNewRatioToData(&$type_data, $old_ratio, $new_ratio) {
        $type_data['center']['x'] *= $new_ratio / $old_ratio;
        $type_data['center']['y'] *= $new_ratio / $old_ratio;
        $type_data['rx'] *= $new_ratio / $old_ratio;
        $type_data['ry'] *= $new_ratio / $old_ratio;
    }

    protected function _objectGroup_render(&$def_content, $type_data, $old_ratio, $new_ratio, $custom_config, $options = []) {
        $option_render_design = [];

        if (isset($options['render_design'])) {
            $option_render_design['render_design'] = 1;
        }

        if($this->_svg_layer_data) {
            return [
                'type' => 'group',
                'data-key' => $type_data['layer_key'],
                'blend_mode' => $type_data['blend_mode'],
                'elements' => $this->_build($def_content, $type_data['children'], $old_ratio, $new_ratio, $custom_config, $option_render_design)
            ];
        }

        $attributes = [
            'data-key' => $type_data['layer_key'],
            'data-layer' => $type_data['layer_name']
        ];

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        if (isset($type_data['blend_mode'])) {
            if (isset($attributes['style'])) {
                //$attributes['style'] .= '; mix-blend-mode: ' . $type_data['blend_mode'];
            } else {
                //$attributes['style'] = 'mix-blend-mode: ' . $type_data['blend_mode'];
            }
        }

        foreach ($attributes as $k => $v) {
            $attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $attributes = implode(' ', $attributes);

        return <<<EOF
<g {$attributes}>{$this->_build($def_content, $type_data['children'], $old_ratio, $new_ratio, $custom_config, $option_render_design)}</g>
EOF;
    }

    protected function _objectPath_render(&$def_content, $type_data, $old_ratio, $new_ratio, $custom_config, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        if($this->_svg_layer_data) {
            $return = [
                'type' => 'path',
                'data-key' => $type_data['layer_key'],
                'd' => $this->pathMakeDData($type_data['points'], $type_data['closed']),
                'rotation' => $type_data['rotation'],
                'blend_mode' => $type_data['blend_mode']
            ];

            if (!isset($type_data['uploaded_image'])) {
                $this->_processFillColorAttribute($return, $type_data['fill']);

                if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
                    $return['stroke'] = ['width' => $type_data['stroke']['width']];
                    $this->_processStrokeColorAttribute($return['stroke'], $type_data['stroke']['color']);
                }

                return $return;
            }

            return $this->_personalizedImageUploaderApplyMask($def_content, $return, $type_data['uploaded_image'], [
                'x' => $type_data['bbox']['x'],
                'y' => $type_data['bbox']['y'],
                'width' => $type_data['bbox']['width'],
                'height' => $type_data['bbox']['height'],
                'rotation' => $type_data['rotation']
            ]);
        }

        $ddata = $this->pathMakeDData($type_data['points'], $type_data['closed']);

        $attributes = [
            'data-key' => $type_data['layer_key'],
            'data-layer' => $type_data['layer_name'],
            'd' => $ddata
        ];

        $this->_processFillColorAttribute($attributes, $type_data['fill']);

        if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
            $this->_processStrokeColorAttribute($attributes, $type_data['stroke']['color']);
            $attributes['stroke-width'] = $type_data['stroke']['width'];
        }

        if ($type_data['rotation'] != 0) {
            $attributes['transform'] = $this->_objectSetTransform($type_data['bbox'], null, null, $type_data['rotation']);
        }

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        if (isset($type_data['blend_mode'])) {
            if (isset($attributes['style'])) {
                //$attributes['style'] .= '; mix-blend-mode: ' . $type_data['blend_mode'];
            } else {
                //$attributes['style'] = 'mix-blend-mode: ' . $type_data['blend_mode'];
            }
        }

        foreach ($attributes as $k => $v) {
            $attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $attributes = implode(' ', $attributes);

        $path_content = <<<EOF
<path {$attributes} />
EOF;

        if (!isset($type_data['uploaded_image'])) {
            return $path_content;
        }

        return $this->_personalizedImageUploaderApplyMask($def_content, $path_content, $type_data['uploaded_image'], [
                    'x' => $type_data['bbox']['x'],
                    'y' => $type_data['bbox']['y'],
                    'width' => $type_data['bbox']['width'],
                    'height' => $type_data['bbox']['height'],
                    'rotation' => $type_data['rotation']
        ]);
    }

    protected function _objectPath_renderByEllipse($rect_type_data, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        $attributes = [
            'd' => 'M' . ($rect_type_data['center']['x'] - $rect_type_data['rx']) . ',' . $rect_type_data['center']['y'] . 'a' . $rect_type_data['rx'] . ',' . $rect_type_data['ry'] . ' 0 1,0 ' . (2 * $rect_type_data['rx']) . ',0a' . $rect_type_data['rx'] . ',' . $rect_type_data['ry'] . ' 0 1,0 ' . (-2 * $rect_type_data['rx']) . ',0'
        ];

        if($this->_svg_layer_data) {
            $return = [
                'type' => 'path',
                'd' => $attributes['d'],
                'rotation' => $rect_type_data['rotation']
            ];

            $this->_processFillColorAttribute($return, $rect_type_data['fill']);

            if ($rect_type_data['stroke'] && $rect_type_data['stroke']['color'] != 'none') {
                $return['stroke'] = ['width' => $rect_type_data['stroke']['width']];
                $this->_processStrokeColorAttribute($return['stroke'], $rect_type_data['stroke']['color']);
            }

            return $return;
        }

        $this->_processFillColorAttribute($attributes, $rect_type_data['fill']);

        if ($rect_type_data['stroke'] && $rect_type_data['stroke']['color'] != 'none') {
            $this->_processStrokeColorAttribute($attributes, $rect_type_data['stroke']['color']);
            $attributes['stroke-width'] = $rect_type_data['stroke']['width'];
        }

        if ($rect_type_data['rotation'] != 0) {

        }

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        foreach ($attributes as $k => $v) {
            $attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $attributes = implode(' ', $attributes);

        return <<<EOF
<path {$attributes} />
EOF;
    }

    protected function _objectPath_renderByRect($rect_type_data, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }

        $attributes = [
            'd' => 'M' . $rect_type_data['position']['x'] . ',' . $rect_type_data['position']['y'] . 'l' . $rect_type_data['size']['width'] . ',0l0,' . $rect_type_data['size']['height'] . 'l' . (-$rect_type_data['size']['width']) . ',0l0,' . (-$rect_type_data['size']['height']) . 'z'
        ];

        if($this->_svg_layer_data) {
            $return = [
                'type' => 'path',
                'd' => $attributes['d'],
                'rotation' => $rect_type_data['rotation']
            ];

            $this->_processFillColorAttribute($return, $rect_type_data['fill']);

            if ($rect_type_data['stroke'] && $rect_type_data['stroke']['color'] != 'none') {
                $return['stroke'] = ['width' => $rect_type_data['stroke']['width']];
                $this->_processStrokeColorAttribute($return['stroke'], $rect_type_data['stroke']['color']);
            }

            return $return;
        }

        $this->_processFillColorAttribute($attributes, $rect_type_data['fill']);

        if ($rect_type_data['stroke'] && $rect_type_data['stroke']['color'] != 'none') {
            $this->_processStrokeColorAttribute($attributes, $rect_type_data['stroke']['color']);
            $attributes['stroke-width'] = $rect_type_data['stroke']['width'];
        }

        if ($rect_type_data['rotation'] != 0) {

        }

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $attributes[$k] = $v;
            }
        }

        foreach ($attributes as $k => $v) {
            $attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $attributes = implode(' ', $attributes);

        return <<<EOF
<path {$attributes} />
EOF;
    }

    protected function _objectPath_applyNewRatioToData(&$type_data, $old_ratio, $new_ratio) {
        $ratio = $new_ratio / $old_ratio;

        for ($i = 0; $i < count($type_data['points']); $i++) {
            $point = & $type_data['points'][$i];

            $point['point']['x'] *= $ratio;
            $point['point']['y'] *= $ratio;

            if (isset($point['handle_in'])) {
                $point['handle_in']['x'] *= $ratio;
                $point['handle_in']['y'] *= $ratio;
            }

            if (isset($point['handle_out'])) {
                $point['handle_out']['x'] *= $ratio;
                $point['handle_out']['y'] *= $ratio;
            }
        }

        $type_data['bbox']['x'] *= $ratio;
        $type_data['bbox']['y'] *= $ratio;
        $type_data['bbox']['width'] *= $ratio;
        $type_data['bbox']['height'] *= $ratio;
    }

    protected function _objectText_render(&$def_content, $type_data, $old_ratio, $new_ratio, $custom_config, $options = []) {
        if (!is_array($options)) {
            $options = [];
        }
        $line_height = $type_data['style']['line_height'] ?? 1.5;
        $font_key = preg_replace('/^(.+)\.[a-zA-Z0-9]+$/', '\\1', $type_data['style']['font_name']);
        $font_key = preg_replace('/[^a-zA-Z0-9]/', '_', $font_key);
        $font_key = preg_replace('/(^_+|_+$)/', '', $font_key);
        $font_key = preg_replace('/_{2,}/', '_', $font_key);
        $font_key = strtolower($font_key);

        if ($this->_svg_layer_data) {
            if (!in_array($font_key, ['arial', 'tahoma']) && !isset($def_content['font/' . $font_key])) {
                $font_path = OSC::core('aws_s3')->getStorageUrl('personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.css');
                $def_content['font/' . $font_key] = ['type' => 'font', 'name' => $type_data['style']['font_name'], 'path' => $font_path];
            }

            $return = [
                'type' => 'text',
                'data-key' => $type_data['layer_key'],
                'content' => $type_data['content'],
                'font-family' => $type_data['style']['font_name'],
                'font-size' =>  $type_data['style']['dynamic_font_size'] ?? $type_data['style']['font_size'],
                'font-weight' => strpos($type_data['style']['font_style'], 'Bold') !== false ? 'bold' : 'normal',
                'font-style' => strpos($type_data['style']['font_style'], 'Italic') !== false ? 'italic' : 'normal',
                'letter-spacing' => $type_data['style']['letter_spacing'],
                'word-spacing' => $type_data['style']['word_spacing'],
                'vertical-align' => $type_data['style']['vertical_align'],
                'text-anchor' => $type_data['style']['text_align'] == 'center' ? 'middle' : ($type_data['style']['text_align'] == 'left' ? 'start' : 'end'),
                'line-height' => $line_height,
                'x' => $type_data['position']['x'],
                'y' => $type_data['position']['y'],
                'width' => $type_data['size']['width'],
                'height' => $type_data['size']['height'],
                'rotation' => $type_data['rotation'],
                'blend_mode' => $type_data['blend_mode']
            ];

            $this->_processFillColorAttribute($return, $type_data['fill']);

            if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
                $return['stroke'] = ['width' => $type_data['stroke']['width']];
                $this->_processStrokeColorAttribute($return['stroke'], $type_data['stroke']['color']);
            }

            if (isset($type_data['path']) && is_array($type_data['path']) && isset($type_data['path']['type'])) {
                if ($type_data['path']['type'] == 'rect') {
                    $return['path'] = $this->_objectPath_renderByRect($type_data['path']['data']);
                } else if ($type_data['path']['type'] == 'ellipse') {
                    $return['path'] = $this->_objectPath_renderByEllipse($type_data['path']['data']);
                } else {
                    $return['path'] = $this->_objectRender($def_content, $type_data['path']['type'], $type_data['path']['data'], $old_ratio, $new_ratio, $custom_config, []);
                }

                // $return['dominant-baseline'] = 'text-after-edge';
                $return['dominant-baseline'] = 'auto';

                if (!isset($type_data['offset'])) {
                    $type_data['offset'] = 0;
                } else {
                    $type_data['offset'] = intval($type_data['offset']);

                    $type_data['offset'] = min(100, max(0, $type_data['offset']));
                }

                $return['path']['start-offset'] = $type_data['offset'];
            }

            return $return;
        }

        $text_attributes = [
            'data-key' => $type_data['layer_key'],
            'data-layer' => $type_data['layer_name'],
            'font-family' => $type_data['style']['font_name'],
            'font-size' => $type_data['style']['dynamic_font_size'] ?? $type_data['style']['font_size'],
            'font-weight' => strpos($type_data['style']['font_style'], 'Bold') !== false ? 'bold' : 'normal',
            'font-style' => strpos($type_data['style']['font_style'], 'Italic') !== false ? 'italic' : 'normal',
            'letter-spacing' => $type_data['style']['letter_spacing'],
            'word-spacing' => $type_data['style']['word_spacing'],
            'text-anchor' => $type_data['style']['text_align'] == 'center' ? 'middle' : ($type_data['style']['text_align'] == 'left' ? 'start' : 'end')
        ];

        if (!in_array($font_key, ['arial', 'tahoma']) && !isset($def_content['font/' . $font_key])) {
            $font_path = OSC::core('aws_s3')->getStorageUrl('personalizedDesign/fonts/' . $font_key . '/' . $font_key . '.css');

            $def_content['font/' . $font_key] = <<<EOF
<style type="text/css">@import url({$font_path})</style>
EOF;
        }

        if (isset($options['attributes']) && is_array($options['attributes'])) {
            foreach ($options['attributes'] as $k => $v) {
                $text_attributes[$k] = $v;
            }
        }

        $this->_processFillColorAttribute($text_attributes, $type_data['fill']);

        if ($type_data['stroke'] && $type_data['stroke']['color'] != 'none') {
            $this->_processStrokeColorAttribute($text_attributes, $type_data['stroke']['color']);
            $text_attributes['stroke-width'] = $type_data['stroke']['width'];
        }

        if ($type_data['rotation'] != 0) {
            $text_attributes['transform'] = $this->_objectSetTransform(['x' => $type_data['position']['x'], 'y' => $type_data['position']['y'], 'width' => $type_data['size']['width'], 'height' => $type_data['size']['height']], null, null, $type_data['rotation']);
        }

        $type_data['content'] = OSC::safeString($type_data['content']);

        if (isset($type_data['path']) && is_array($type_data['path']) && isset($type_data['path']['type'])) {
            $unique_id = $type_data['layer_key'] . '-path';

            if ($type_data['path']['type'] == 'rect') {
                $def_content['text-path-' . $unique_id] = $this->_objectPath_renderByRect($type_data['path']['data'], ['attributes' => ['id' => 'text-path-' . $unique_id]]);
            } else if ($type_data['path']['type'] == 'ellipse') {
                $def_content['text-path-' . $unique_id] = $this->_objectPath_renderByEllipse($type_data['path']['data'], ['attributes' => ['id' => 'text-path-' . $unique_id]]);
            } else {
                $def_content['text-path-' . $unique_id] = $this->_objectRender($def_content, $type_data['path']['type'], $type_data['path']['data'], $old_ratio, $new_ratio, [], ['attributes' => ['id' => 'text-path-' . $unique_id]]);
            }

            // $text_attributes['dominant-baseline'] = 'text-after-edge';
            $text_attributes['dominant-baseline'] = 'auto';

            if (!isset($type_data['offset'])) {
                $type_data['offset'] = 0;
            } else {
                $type_data['offset'] = intval($type_data['offset']);

                $type_data['offset'] = min(100, max(0, $type_data['offset']));
            }

            $text_content = <<<EOF
<textPath xlink:href="#text-path-{$unique_id}" startOffset="{$type_data['offset']}%">{$type_data['content']}</textPath>
EOF;
        } else {
            $baseline = '';
            $text_y = $type_data['position']['y'];
            $lines = preg_split('/\n/', $type_data['content']);
            $first_dy = 0;

            switch ($type_data['style']['vertical_align']) {
                case 'top':
                    $baseline = 'hanging';
                    break;
                case 'middle':
                    $text_y = $text_y + $type_data['size']['height'] / 2;
                    $baseline = 'middle';
                    $first_dy -= ((count($lines) - 1) / 2) * $line_height;
                    break;
                default:
                    $text_y = $text_y + $type_data['size']['height'];
                    $baseline = 'baseline';
                    $first_dy -= (count($lines) - 1) * $line_height;
                    break;
            }

            $text_attributes['y'] = $text_y;
            $text_attributes['dominant-baseline'] = $baseline;
            $text_x = $type_data['position']['x'];

            switch ($type_data['style']['text_align']) {
                case 'center':
                    $text_x += $type_data['size']['width'] / 2;
                    break;
                case 'right':
                    $text_x += $type_data['size']['width'];
                    break;
                default:
                    break;
            }

            $first_tspan_attributes = [
                'x' => $text_x,
                'dy' => $first_dy. 'em',
                'alignment-baseline' => $baseline,
            ];

            foreach ($first_tspan_attributes as $k => $v) {
                $first_tspan_attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
            }

            $first_tspan_attributes = implode(' ', $first_tspan_attributes);

            $text_content = <<<EOF
<tspan {$first_tspan_attributes}>{$lines[0]}</tspan>
EOF;

            for ($i = 1; $i < count($lines); $i++) {
                $tspan_attributes = [
                    'x' => $text_x,
                    'dy' => $line_height. 'em',
                    'alignment-baseline' => $baseline,
                ];
                foreach ($tspan_attributes as $k => $v) {
                    $tspan_attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
                }
                $tspan_attributes = implode(' ', $tspan_attributes);
                $tspan_content = $lines[$i] != "" ? $lines[$i] : ' ';
                $text_content .= <<<EOF
<tspan {$tspan_attributes}>{$tspan_content}</tspan>
EOF;
            }
        }

        if (isset($type_data['blend_mode'])) {
            if (isset($text_attributes['style'])) {
                //$text_attributes['style'] .= '; mix-blend-mode: ' . $type_data['blend_mode'];
            } else {
                //$text_attributes['style'] = 'mix-blend-mode: ' . $type_data['blend_mode'];
            }
        }

        foreach ($text_attributes as $k => $v) {
            $text_attributes[$k] = $k . '="' . OSC::safeString($v) . '"';
        }

        $text_attributes = implode(' ', $text_attributes);

        return <<<EOF
<text {$text_attributes}>
    {$text_content}
</text>
EOF;
    }

    protected function _objectText_applyNewRatioToData(&$type_data, $old_ratio, $new_ratio) {
        $ratio = $new_ratio / $old_ratio;
        $type_data['style']['original_font_size'] = $type_data['style']['original_font_size'] ?? $type_data['style']['font_size'];
        $type_data['style']['font_size'] *= $ratio;
        if (isset($type_data['style']['dynamic_font_size'])) {
            $type_data['style']['dynamic_font_size'] *= $ratio;
        }
        $type_data['style']['letter_spacing'] *= $ratio;
        $type_data['style']['word_spacing'] *= $ratio;

//            $type_data['offset'] *= $ratio;

        $type_data['position']['x'] *= $ratio;
        $type_data['position']['y'] *= $ratio;
        $type_data['size']['width'] *= $ratio;
        $type_data['size']['height'] *= $ratio;

        if ($this->_design_version == 1 && isset($type_data['path']) && is_array($type_data['path']) && isset($type_data['path']['type'])) {
            $this->_objectApplyNewRatio($type_data['path']['type'], $type_data['path']['data'], $old_ratio, $new_ratio);
        }
    }

    protected function _processFillColorAttribute(&$attributes, $color) {
        if (!$color) {
            $color = 'none';
        } else if (preg_match('/^\s*rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*([0-9\.]+)\s*\)\s*$/i', $color, $matches)) {
            $color = 'rgb(' . $matches[1] . ',' . $matches[2] . ',' . $matches[3] . ')';
            $attributes['fill-opacity'] = $matches[4];
        }

        $attributes['fill'] = $color;
    }

    protected function _processStrokeColorAttribute(&$attributes, $color) {
        if (!$color) {
            $color = 'none';
        } else if (preg_match('/^\s*rgba\s*\(\s*(\d+)\s*,\s*(\d+)\s*,\s*(\d+)\s*,\s*([0-9\.]+)\s*\)\s*$/i', $color, $matches)) {
            $color = 'rgb(' . $matches[1] . ',' . $matches[2] . ',' . $matches[3] . ')';
            $attributes['stroke-opacity'] = $matches[4];
        }

        $attributes['stroke'] = $color;
    }

    public function checkOverflowPersonalizedItem($line_item)
    {
        /*QuanNV*/
        return;

        $DB = OSC::core('database');

        $campaign_data = $line_item->getCampaignData();

        $svgs = [];
        if ($campaign_data) {
            foreach ($campaign_data['designs'] as $data) {
                if ($data['type'] != 'personalizedDesign') {
                    continue;
                }
                $design = OSC::model('personalizedDesign/design')->load($data['design_id']);
                $svg = OSC::helper('personalizedDesign/common')->renderSvgIsPersonalized($design, $data['personalizedDesign']['config']);
                if ($svg) {
                    $svgs[] = $svg;
                }
            }
        } else {
            foreach ($line_item->data['custom_data'] as $custom_data) {
                if ($custom_data['key'] == 'personalized_design') {
                    $design = OSC::model('personalizedDesign/design')->load($custom_data['data']['design_id']);
                    $svg = OSC::helper('personalizedDesign/common')->renderSvgIsPersonalized($design, $custom_data['data']['config']);
                    if ($svg) {
                        $svgs[] = $svg;
                    }
                }
            }
        }
        if (count($svgs) < 1) {
            return false;
        }

        $DB->insert('catalog_item_overflow_queue', [
            'data' => OSC::encode(['order_id' => $line_item->getOrder()->getId(), 'order_line_id' => $line_item->getId(), 'svg_content' => $svgs]),
            'queue_flag' => 1,
            'added_timestamp' => time(),
            'modified_timestamp' => 0
        ], 'insert_customize_order_map');
    }

    /**
     * @param $id
     * @return bool|mixed
     * @throws OSC_Database_Model_Exception
     */
    public function getAccountName($id) {
        if (!$id) {
            return false;
        }
        /* @var $model Model_User_Member */
        try {
            $collection = OSC::model('user/member')->load(intval($id));
            return $collection->data['username'];
        } catch (Exception $ex) {

        }
    }

    public function renderSvgSpotifyDefault($background_color = 'none',$bar_color = '#000000') {
        $svg_content = <<<EOF
<rect x="0" y="0" width="400" height="100" fill="{$background_color}"/>
<rect x="100.00" y="44.50" width="6.71" height="11.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="112.42" y="30.50" width="6.71" height="39.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="124.84" y="37.50" width="6.71" height="25.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="137.27" y="41.00" width="6.71" height="18.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="149.69" y="44.50" width="6.71" height="11.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="162.11" y="34.00" width="6.71" height="32.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="174.53" y="30.50" width="6.71" height="39.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="186.96" y="44.50" width="6.71" height="11.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="199.38" y="44.50" width="6.71" height="11.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="211.80" y="23.50" width="6.71" height="53.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="224.22" y="44.50" width="6.71" height="11.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="236.64" y="20.00" width="6.71" height="60.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="249.07" y="34.00" width="6.71" height="32.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="261.49" y="37.50" width="6.71" height="25.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="273.91" y="23.50" width="6.71" height="53.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="286.33" y="23.50" width="6.71" height="53.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="298.76" y="41.00" width="6.71" height="18.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="311.18" y="37.50" width="6.71" height="25.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="323.60" y="34.00" width="6.71" height="32.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="336.02" y="27.00" width="6.71" height="46.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="348.44" y="34.00" width="6.71" height="32.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="360.87" y="23.50" width="6.71" height="53.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<rect x="373.29" y="44.50" width="6.71" height="11.00" rx="3.36" ry="3.36" fill="{$bar_color}"/>
<g transform="translate(20,20)"><path fill="{$bar_color}" d="M30,0A30,30,0,1,1,0,30,30,30,0,0,1,30,0M43.73,43.2a1.85,1.85,0,0,0-.47-2.43,5,5,0,0,0-.48-.31,30.64,30.64,0,0,0-5.92-2.72,37.07,37.07,0,0,0-11.56-1.84c-1.33.07-2.67.12-4,.23a52.44,52.44,0,0,0-7.08,1.12,3.45,3.45,0,0,0-.54.16,1.83,1.83,0,0,0-1.11,2.08A1.79,1.79,0,0,0,14.37,41a4.29,4.29,0,0,0,.88-.12,48.93,48.93,0,0,1,8.66-1.15,35.33,35.33,0,0,1,6.75.37,28.29,28.29,0,0,1,10.25,3.61,4.77,4.77,0,0,0,.5.27,1.85,1.85,0,0,0,2.33-.74M47.41,35a2.34,2.34,0,0,0-.78-3.19l-.35-.21a35.72,35.72,0,0,0-7.38-3.3,45.39,45.39,0,0,0-15.7-2.13,41.19,41.19,0,0,0-7.39.92c-1,.22-2,.48-2.94.77A2.26,2.26,0,0,0,11.29,30a2.32,2.32,0,0,0,1.44,2.2,2.47,2.47,0,0,0,1.67,0,37,37,0,0,1,10.38-1.46,43,43,0,0,1,7.91.74,35.46,35.46,0,0,1,9.58,3.18c.66.34,1.3.72,1.95,1.08A2.33,2.33,0,0,0,47.41,35m.35-8.49A2.79,2.79,0,0,0,52,24.11c0-.2,0-.4-.08-.6a2.78,2.78,0,0,0-1.4-1.85,35.91,35.91,0,0,0-6.41-2.91,56.19,56.19,0,0,0-16.86-2.89,58.46,58.46,0,0,0-7,.21,48.31,48.31,0,0,0-6.52,1c-.87.2-1.73.42-2.58.7a2.73,2.73,0,0,0-1.85,2.68,2.79,2.79,0,0,0,2,2.61,2.9,2.9,0,0,0,1.6,0c.87-.23,1.75-.47,2.63-.66a45.52,45.52,0,0,1,7.26-.91,57.42,57.42,0,0,1,6.4,0,53.7,53.7,0,0,1,6.11.72,42.63,42.63,0,0,1,8.49,2.35,33.25,33.25,0,0,1,4,2"/></g>
EOF;
        return $svg_content;
    }

    public function renderQrCodeSvgDefault($background_color = 'none', $bar_color = '#000000') {
        return OSC::helper('personalizedDesign/spotify')->generateQrCodeSvg(static::DEFAULT_SPOTIFY_URL, [
            'background_color' => $background_color,
            'bar_color' => $bar_color,
        ]);
    }

    public function getPersonalizedDesign($design_ids) {
        if (is_array($design_ids)) {
            sort($design_ids);
            $str_design_ids = implode(',', $design_ids);
        } else {
            $str_design_ids = $design_ids;
        }
        $_list_personalized_design = OSC::model('personalizedDesign/design')->getCollection();

        $cache_key = "getPersonalizedDesign|helper.personalizedDesign.common|design_ids:,{$str_design_ids},|";
        $cache = OSC::core('cache')->get($cache_key);
        if ($cache !== false) {
            foreach ($cache as $item) {
                $_list_personalized_design->addItem(OSC::model('personalizedDesign/design')->bind($item));
            }
        } else {
            $design_ids = is_array($design_ids) ? $design_ids : explode(',', $design_ids);
            $_list_personalized_design = OSC::model('personalizedDesign/design')->getCollection()->load($design_ids);

            OSC::core('cache')->set($cache_key, $_list_personalized_design->toArray(), OSC_CACHE_TIME);
        }

        return $_list_personalized_design;
    }

    public function inputDisableAllUppercase($text) {
        $text = trim($text);

        if ($text == '') {
            return '';
        }

        $tmp_text = OSC::decode($text);

        if ($tmp_text && is_array($tmp_text) && isset($tmp_text['value'])) {
            $tmp_text['value'] = $this->inputDisableAllUppercase($tmp_text['value']);
            return OSC::encode($tmp_text);
        }

        $words = explode(' ', $text);

        foreach ($words as &$word) {
            if ($word == ''){
                continue;
            }

            $word = $word[0] . strtolower(substr($word, 1));
        }

        unset($word);

        return join(' ', $words);
    }
    public function lockDesignByIds($design_ids = [])
    {
        $design_ids = array_unique($design_ids);

        if (count($design_ids) > 0) {
            $design_collection = OSC::model('personalizedDesign/design')
                ->getCollection()
                ->addCondition('design_id', $design_ids, OSC_Database::OPERATOR_IN)
                ->addCondition('locked_flag', 0)
                ->load();

            if ($design_collection->length() > 0) {
                foreach ($design_collection as $design) {
                    $design->setData('locked_flag', 1)->save();
                }
            }
        }
    }

    public function getTmpDir($file_name) {
        $tmp_path = 'tmp';

        if (!OSC::makeDir(OSC_VAR_PATH . '/' . $tmp_path, 0755, true)) {
            throw new Exception('Cannot make storage temporary directory');
        }

        $tmpDirPath = (OSC_VAR_PATH) . '/' . $tmp_path;
        $dest_path = $tmpDirPath . '/' . $file_name;

        OSC::makeDir(dirname($dest_path));

        return $dest_path;
    }

    public function mapDesignGetConfig($old_design_id, $new_design_id, $config = [], $billing_name = '', &$linking_config = []) {
        static $design_models = [];

        if (intval($old_design_id) <= 0) {
            throw new Exception('Design ID: #' . $old_design_id . ' is not correct!');
        }

        if (intval($new_design_id) <= 0) {
            throw new Exception('Target Design ID: #' . $new_design_id . ' is not correct!');
        }

        if (!$billing_name) {
            throw new Exception('Billing name is required!');
        }

        if ($design_models[$old_design_id]) {
            $old_design = OSC::model('personalizedDesign/design')->bind($design_models[$old_design_id]);
        } else {
            $old_design = OSC::model('personalizedDesign/design')->getCollection()->addCondition('design_id', $old_design_id, OSC_Database::OPERATOR_EQUAL)->load()->first();
        }

        if (!$old_design) {
            throw new Exception("Design ID: #" . $old_design_id . " not found!");
        }

        $design_models[$old_design_id] = $old_design->toArray();
        $linking_config = OSC::helper('personalizedDesign/common')->renderSvg($old_design, $config, [
            'get_linking_segments',
            'billing_name' => $billing_name,
        ]);

        if ($design_models[$new_design_id]) {
            $new_design = OSC::model('personalizedDesign/design')->bind($design_models[$new_design_id]);
        } else {
            $new_design = OSC::model('personalizedDesign/design')->getCollection()->addCondition('design_id', $new_design_id, OSC_Database::OPERATOR_EQUAL)->load()->first();
        }

        if (!$new_design) {
            throw new Exception("Design ID: #" . $new_design_id . " not found!");
        }
        $design_models[$new_design_id] = $new_design->toArray();
        $new_design_config = OSC::helper('personalizedDesign/common')->renderSvg($new_design, [], [
            'linking_config' => $linking_config
        ]);

        return $new_design_config;
    }

    public function renderClipArtSvg($params) {
        $clip_art_layers = OSC::helper('personalizedDesign/common')->getClipArtLayers($params['design'], $params['custom_config'], $params['options']);

        $result = [];

        foreach ($clip_art_layers as $clip_art => $clip_art_keys) {
            $this->_ps_clipart_data = $clip_art_keys;

            $options = $params['options'];

            $result[$clip_art] = $this->renderSvg($params['design'], $params['custom_config'], $options);

            $this->_ps_clipart_data = [];
        }

        return $result;
    }

    public function getClipArtLayers($design, $custom_config, $options) {
        $options = array_merge(['get_clipart'], $options);
        $this->renderSvg($design, $custom_config, $options);
        $clip_arts = $this->getAllClipArt();
        $form_data = $design->extractPersonalizedFormData();

        $clip_art_data = [];

        foreach ($clip_arts as $clip_art_title => $clip_art) {
            $clip_art_data_item = [];
            $clip_art_data[$clip_art_title] = [];

            foreach ($clip_art as $key) {
                $clip_art_data_item = OSC::helper('personalizedDesign/common')->getAllTreeLayerByKey($key, $form_data['extra_layer']);
                $clip_art_data[$clip_art_title] = array_merge($clip_art_data[$clip_art_title], array_unique($clip_art_data_item));
            }
        }

        return $clip_art_data;
    }

    public function getChildrenLayerByKey($key = null, $extra_layer, &$children) {
        if (!isset($key)) {
            return [];
        }


        foreach ($extra_layer as $_key => $value) {
            if ($_key === $key) {
                $children = $value;
                break;
            }

            if (is_array($value) && count($value) > 0) {
                $this->getChildrenLayerByKey($key, $value, $children);
            }
        }
    }

    public function getTreeLayerByKey($extra_layer, &$children) {
        foreach ($extra_layer as $_key => $value) {
            $children[] = $_key;

            if (is_array($value) && count($value) > 0) {
                $this->getTreeLayerByKey($value, $children);
            }
        }
    }

    public function getAllTreeLayerByKey($key, $extra_layer) {
        if (!isset($key)) {
            return [];
        }

        $children = null;

        $this->getChildrenLayerByKey($key, $extra_layer, $children);

        if ($children == null || !is_array($children)) {
            return [];
        }

        $tree = [];

        $this->getTreeLayerByKey($children, $tree);

        if (!is_array($children) || count($tree) == 0) {
            return [];
        }

        return $tree;
    }

    /**
     * @param $queue_data
     * @param $status
     * @param $error_message
     * @return void
     */
    public function updateLogRerender($queue_data, $status, $error_message) {

        try {
            $data = [
                'member_id' => $queue_data['member_id'] ?? 1,
                'order_id' => $queue_data['order_master_record_id'] ?? 0,
                'order_item_id' => $queue_data['item_master_record_id'] ?? 0,
                'design_id' => $queue_data['design_id'] ?? 0,
                'status' => $status,
                'message' => $error_message
            ];
            /* @var $exists_log Model_PersonalizedDesign_RerenderLog */
            $exists_log = OSC::model('personalizedDesign/rerenderLog')
                ->getCollection()
                ->addCondition('order_id', $data['order_id'])
                ->addCondition('order_item_id', $data['order_item_id'])
                ->addCondition('design_id', $data['design_id'])
                ->load()->first();

            if ($exists_log) {
                $exists_log->setData($data)->save();
            } else {
                OSC::model('personalizedDesign/rerenderLog')->setData($data)->save();
            }
        } catch (Exception $ex) {}
    }

    protected function _converObjectV1ToV2(&$object, $parent_old_ratio = 1) {
        $this->_objectApplyNewRatio($object['type'], $object['type_data'], $parent_old_ratio, 1);

        if ($object['type'] === 'group') {
            foreach ($object['type_data']['children'] as &$child) {
                $this->_converObjectV1ToV2($child, $parent_old_ratio);
            }
        }
        if(!$object['personalized']) return;

        switch ($object['personalized']['type']) {
            case 'imageSelector':
                foreach (array_keys($object['personalized']['config']['groups']) as $group_key) {
                    $group = &$object['personalized']['config']['groups'][$group_key];
                    foreach (array_keys($group['images']) as $image_key) {
                        $image = &$group['images'][$image_key];
                        if (isset($image['data']) && isset($image['data']['type_data']) && isset($image['data']['ratio'])) {
                            $this->_objectApplyNewRatio('image', $image['data']['type_data'], $image['data']['ratio'], 1);

                            unset($image['data']['ratio']);
                        }
                    }
                }
                break;
            case 'switcher':
                foreach (array_keys($object['personalized']['config']['options']) as $option_key) {
                    $option = &$object['personalized']['config']['options'][$option_key];
                    if (isset($option['data']) && isset($option['data']['objects']) && isset($option['data']['ratio'])) {
                        $old_ratio = $option['data']['ratio'];
                        $option['objects'] = &$option['data']['objects'];
                        $option['data'];
                        foreach ($option['objects'] as &$child) {
                            $this->_converObjectV1ToV2($child, $old_ratio);
                        }
                    } else {
                        $option['objects'] = [];
                        unset($option['data']);
                    }
                }
                break;
            default:
                break;
        }
    }

    public function convertDesignDataV1ToV2(&$design_data) {
        $this->_design_version = 1;

        foreach ($design_data['objects'] as &$object) {
            $this->_converObjectV1ToV2($object, 1);
        }

        $design_data['version'] = 2;
    }
}
