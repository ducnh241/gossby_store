<?php

abstract class  Abstract_Catalog_Campaign_ProductType extends OSC_Object {

    public static function getConfig() {
        $config = [
            'helper' => static::getOSCObjectType()['name'],
            'key' => static::getKey(),
            'group' => static::getGroup(),
            'type' => static::getType(),
            'identifier' => static::getIdentifier(),
            'title' => static::getTitle(),
            'short_title' => static::getShortTitle(),
            'design' => static::getDesignConfig(),
            'design_merge' => static::getDesignMergeConfig(),
            'mockup_config' => static::getMockupConfig(),
            'option' => static::getOption(),
            'other_option' => static::getOtherOption(),
            'price' => static::getPrice(),
            'description' => static::getDescription()
        ];

        if (!$config['option']) {
            unset($config['option']);
        }

        return $config;
    }

    public static function getOtherOption() {
        return [];
    }

    abstract public static function getMockupConfig();

    abstract public static function getDescription();

    abstract public static function getDesignConfig();

    abstract public static function getDesignMergeConfig();

    abstract public static function getOption();

    abstract public static function getTitle();

    abstract public static function getShortTitle();

    abstract public static function getKey();

    abstract public static function getType();

    abstract public static function getIdentifier();

    abstract public static function getGroup();

    abstract public static function getPrice();

    abstract protected function _getMockupCommand($designs, $option_value_key = null);

    protected $_data = null;
    protected $_campaign_id = null;

    public function setData($campaign_id, $data) {
        $this->_campaign_id = $campaign_id;
        $this->_data = $data;

        return $this;
    }

    protected function _getImageRootName() {
        return 'catalog/campaign/mockup/' . $this->_campaign_id;
    }

    public function getImageData() {
        $campaign_root_name = $this->_getImageRootName();

        $data = [];

        $config = static::getConfig();

        if (isset($this->_data['option']) && is_array($this->_data['option'])) {
            foreach ($this->_data['option'] as $value_key => $value_designs) {
                if (is_array($value_designs) && count($value_designs) > 0) {
                    if (count($value_designs) > 1 && count($value_designs) == count(static::getMockupConfig())) {
                        $image_id = md5($this->_campaign_id . ':' . $config['key'] . ':' . $value_key);

                        $data[$image_id] = [
                            'image_id' => $image_id,
                            'product_id' => $this->_campaign_id,
                            'product_type' => $config['key'],
                            'option_value' => $value_key,
                            'extension' => 'png',
                            'filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '.png',
                            'json_filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '.json'
                        ];
                    }

                    foreach ($value_designs as $design_key => $design_config) {
                        $image_id = md5($this->_campaign_id . ':' . $config['key'] . ':' . $value_key . '/' . $design_key);

                        $data[$image_id] = [
                            'image_id' => $image_id,
                            'product_id' => $this->_campaign_id,
                            'product_type' => $config['key'],
                            'option_value' => $value_key,
                            'extension' => 'png',
                            'filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '/' . $design_key . '.png',
                            'json_filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '/' . $design_key . '.json'
                        ];
                    }
                } else {
                    if (count($this->_data['design']) > 1 && count($this->_data['design']) == count(static::getMockupConfig())) {
                        $image_id = md5($this->_campaign_id . ':' . $config['key'] . ':' . $value_key);

                        $data[$image_id] = [
                            'image_id' => $image_id,
                            'product_id' => $this->_campaign_id,
                            'product_type' => $config['key'],
                            'option_value' => $value_key,
                            'extension' => 'png',
                            'filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '.png',
                            'json_filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '.json'
                        ];
                    }

                    foreach ($this->_data['design'] as $design_key => $design_config) {
                        $image_id = md5($this->_campaign_id . ':' . $config['key'] . ':' . $value_key . '/' . $design_key);

                        $data[$image_id] = [
                            'image_id' => $image_id,
                            'product_id' => $this->_campaign_id,
                            'product_type' => $config['key'],
                            'option_value' => $value_key,
                            'extension' => 'png',
                            'filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '/' . $design_key . '.png',
                            'json_filename' => $campaign_root_name . '/' . $config['key'] . '/' . $value_key . '/' . $design_key . '.json'
                        ];
                    }
                }
            }
        } else {
            if (count($this->_data['design']) > 1 && count($this->_data['design']) == count(static::getMockupConfig())) {
                $image_id = md5($this->_campaign_id . ':' . $config['key']);

                $data[$image_id] = [
                    'image_id' => $image_id,
                    'product_id' => $this->_campaign_id,
                    'product_type' => $config['key'],
                    'option_value' => '',
                    'extension' => 'png',
                    'filename' => $campaign_root_name . '/' . $config['key'] . '.png',
                    'json_filename' => $campaign_root_name . '/' . $config['key'] . '.json'
                ];
            }

            foreach ($this->_data['design'] as $design_key => $design_config) {
                $image_id = md5($this->_campaign_id . ':' . $config['key'] . '/' . $design_key);

                $data[$image_id] = [
                    'image_id' => $image_id,
                    'product_id' => $this->_campaign_id,
                    'product_type' => $config['key'],
                    'option_value' => '',
                    'extension' => 'png',
                    'filename' => $campaign_root_name . '/' . $config['key'] . '/' . $design_key . '.png',
                    'json_filename' => $campaign_root_name . '/' . $config['key'] . '/' . $design_key . '.json'
                ];
            }
        }

        return $data;
    }

    public function getMockupCommand($config) {
        $command_data = [
            'designs' => [],
            'mockups' => []
        ];

        if (is_array(static::getOption())) {
            foreach ($config['option'] as $value_key => $value_designs) {
                $design_mapping = [];

                if (is_array($value_designs) && count($value_designs) > 0) {
                    foreach ($value_designs as $design_key => $design_config) {
                        $design_mapping[$design_key] = $value_key . '--' . $design_key;

                        $design_config['design_area'] = ['area' => static::getDesignConfig()[$design_key]['area']];

                        $command_data['designs'][$value_key . '--' . $design_key] = $design_config;
                    }
                } else {
                    foreach ($config['design'] as $design_key => $design_config) {
                        $design_mapping[$design_key] = $design_key;

                        $design_config['design_area'] = ['area' => static::getDesignConfig()[$design_key]['area']];

                        $command_data['designs'][$design_key] = $design_config;
                    }
                }

                $command_data['mockups'][$value_key] = $this->_getMockupCommand($design_mapping, $value_key);
            }
        } else {
            $design_mapping = [];

            foreach ($config['design'] as $design_key => $design_config) {
                $design_mapping[$design_key] = $design_key;

                $design_config['design_area'] = ['area' => static::getDesignConfig()[$design_key]['area']];

                $command_data['designs'][$design_key] = $design_config;
            }

            $command_data['mockups']['default'] = $this->_getMockupCommand($design_mapping);
        }

        return $command_data;
    }

    public function getOrderLineItemMockupCommand(Model_Catalog_Order_Item $line_item) {
        $campaign_data = $line_item->getCampaignData();

        $design_mapping = [];

        foreach ($campaign_data['designs'] as $design_key => $design) {
            $design_mapping[$design_key] = $design_key;
        }

        $command_data = $this->_getOrderLineItemMockupCommand($campaign_data['designs'], $design_mapping, is_array(static::getOption()) ? $campaign_data['options'][static::getOption()['key']]['value']['key'] : null, OSC::helper('catalog/campaign')->getOrderLineItemMockupFileName($line_item));

        return $command_data;
    }

//    public static function pointGetDistance($p1, $p2) {
//        return sqrt(pow($p2['x'] - $p1['x'], 2) + pow($p2['y'] - $p1['y'], 2));
//    }
//
//    public static function getVectorIntersectionPoint($vector1, $vector2) {
//        $result = [
//            'x' => null,
//            'y' => null,
//            'onLine1' => false,
//            'onLine2' => false
//        ];
//
//        $denominator = (($vector2['point2']['y'] - $vector2['point1']['y']) * ($vector1['point2']['x'] - $vector1['point1']['x'])) - (($vector2['point2']['x'] - $vector2['point1']['x']) * ($vector1['point2']['y'] - $vector1['point1']['y']));
//
//        if ($denominator === 0) {
//            return $result;
//        }
//
//        $a = $vector1['point1']['y'] - $vector2['point1']['y'];
//        $b = $vector1['point1']['x'] - $vector2['point1']['x'];
//
//        $numerator1 = (($vector2['point2']['x'] - $vector2['point1']['x']) * $a) - (($vector2['point2']['y'] - $vector2['point1']['y']) * $b);
//        $numerator2 = (($vector1['point2']['x'] - $vector1['point1']['x']) * $a) - (($vector1['point2']['y'] - $vector1['point1']['y']) * $b);
//
//        $a = $numerator1 / $denominator;
//        $b = $numerator2 / $denominator;
//
//        $result['x'] = $vector1['point1']['x'] + ($a * ($vector1['point2']['x'] - $vector1['point1']['x']));
//        $result['y'] = $vector1['point1']['y'] + ($a * ($vector1['point2']['y'] - $vector1['point1']['y']));
//
//        if ($a > 0 && $a < 1) {
//            $result['onLine1'] = true;
//        }
//
//        if ($b > 0 && $b < 1) {
//            $result['onLine2'] = true;
//        }
//
//        return $result;
//    }
//
//    public static function getPointExceptRotation($point, $bounding_rect, $rotation) {
//        $rotation = floatval($rotation);
//
//        if ($rotation == 0) {
//            return $point;
//        }
//
//        $center_pt = [
//            'x' => $bounding_rect['x'] + $bounding_rect['width'] / 2,
//            'y' => $bounding_rect['y'] + $bounding_rect['height'] / 2
//        ];
//
//        $degress = atan2($point['y'] - $center_pt['y'], $point['x'] - $center_pt['x']) * 180 / M_PI;
//
//        $degress -= $rotation;
//
//        $distance = static::pointGetDistance($center_pt, $point);
//
//        $radian = $degress * M_PI / 180;
//
//        $point['x'] = $center_pt['x'] + $distance * cos($radian);
//        $point['y'] = $center_pt['y'] + $distance * sin($radian);
//
//
//        return $point;
//    }
//
//    public static function getPointApplyRotation($point, $bounding_rect, $rotation) {
//        $rotation = floatval($rotation);
//
//        if ($rotation == 0) {
//            return $point;
//        }
//
//        $center_pt = [
//            'x' => $bounding_rect['x'] + $bounding_rect['width'] / 2,
//            'y' => $bounding_rect['y'] + $bounding_rect['height'] / 2
//        ];
//
//        $degress = atan2($point['y'] - $center_pt['y'], $point['x'] - $center_pt['x']) * 180 / M_PI;
//
//        $degress += $rotation;
//
//        $distance = static::pointGetDistance($center_pt, $point);
//
//        $radian = $degress * M_PI / 180;
//
//        $point['x'] = $center_pt['x'] + $distance * cos($radian);
//        $point['y'] = $center_pt['y'] + $distance * sin($radian);
//
//        return $point;
//    }
//
//    public static function getNewBoundingRectByRotation($bounding_rect, $rotation) {
//        $points = [
//            'tl' => [
//                'x' => $bounding_rect['x'],
//                'y' => $bounding_rect['y']
//            ],
//            'tr' => [
//                'x' => $bounding_rect['x'] + $bounding_rect['width'],
//                'y' => $bounding_rect['y']
//            ],
//            'bl' => [
//                'x' => $bounding_rect['x'],
//                'y' => $bounding_rect['y'] + $bounding_rect['height']
//            ],
//            'br' => [
//                'x' => $bounding_rect['x'] + $bounding_rect['width'],
//                'y' => $bounding_rect['y'] + $bounding_rect['height']
//            ]
//        ];
//
//        $points['tl'] = static::getPointApplyRotation($points['tl'], $bounding_rect, $rotation);
//        $points['tr'] = static::getPointApplyRotation($points['tr'], $bounding_rect, $rotation);
//        $points['bl'] = static::getPointApplyRotation($points['bl'], $bounding_rect, $rotation);
//        $points['br'] = static::getPointApplyRotation($points['br'], $bounding_rect, $rotation);
//
//        $min_x = null;
//        $min_y = null;
//        $max_x = null;
//        $max_y = null;
//
//        foreach ($points as $point) {
//            if ($min_x === null || $point['x'] < $min_x) {
//                $min_x = $point['x'];
//            }
//
//            if ($max_x === null || $point['x'] > $max_x) {
//                $max_x = $point['x'];
//            }
//
//            if ($min_y === null || $point['y'] < $min_y) {
//                $min_y = $point['y'];
//            }
//
//            if ($max_y === null || $point['y'] > $max_y) {
//                $max_y = $point['y'];
//            }
//        }
//
//        return [
//            'x' => $min_x,
//            'y' => $min_y,
//            'width' => $max_x - $min_x,
//            'height' => $max_y - $min_y
//        ];
//    }

}
