<?php

class Helper_CatalogItemCustomize_ComponentValidator {

    public function validate(&$components) {
        foreach ($components as $idx => $component) {
            if (!is_array($component) && !isset($component['component_type'])) {
                throw new Exception('Config component is incorrect');
            }

            $validator = '_' . strtolower(substr($component['component_type'], 0, 1)) . substr($component['component_type'], 1);

            if (!method_exists($this, $validator)) {
                throw new Exception("Cannot detect component validator [{$component['component_type']}]");
            }

            $this->$validator($components[$idx]);
        }
    }

    protected function _validateLayerKey($layer_key) {
        $layer_key = preg_replace('/[^a-zA-Z0-9\_]/', '_', $layer_key);
        $layer_key = preg_replace('/_{2,}/', '_', $layer_key);
        $layer_key = preg_replace('/(^_|_$)/', '', $layer_key);

        return $layer_key;
    }

    protected function _imageUploader(&$component) {
        if (!isset($component['key']) || !isset($component['title']) || !isset($component['desc']) || !isset($component['require']) || !isset($component['min_width']) || !isset($component['min_height'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['key'] = trim($component['key']);
        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['key']) {
            throw new Exception('Component key is empty');
        }

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
        $component['min_width'] = intval($component['min_width']);
        $component['min_height'] = intval($component['min_height']);

        if ($component['min_width'] < 0) {
            $component['min_width'] = 0;
        }

        if ($component['min_height'] < 0) {
            $component['min_height'] = 0;
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
    }

    protected function _input(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _textarea(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _select(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['options']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['options']) || count($component['options']) < 1) {
            throw new Exception('Component options is empty');
        }

        foreach ($component['options'] as $idx => $option) {
            $component['options'][$idx] = trim($option);

            if ($component['options'][$idx] === '') {
                throw new Exception('Component option is empty');
            }
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _checkbox(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['options']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['options']) || count($component['options']) < 1) {
            throw new Exception('Component options is empty');
        }

        foreach ($component['options'] as $idx => $option) {
            $component['options'][$idx] = trim($option);

            if ($component['options'][$idx] === '') {
                throw new Exception('Component option is empty');
            }
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _radio(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['options']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['options']) || count($component['options']) < 1) {
            throw new Exception('Component options is empty');
        }

        foreach ($component['options'] as $idx => $option) {
            $component['options'][$idx] = trim($option);

            if ($component['options'][$idx] === '') {
                throw new Exception('Component option is empty');
            }
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _checker(&$component) {
        if (!isset($component['title']) || !isset($component['value'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['value'] = trim($component['value']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if ($component['value'] === '') {
            throw new Exception('Component value is empty');
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
    }

    protected function _imageSelector(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['images']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['images']) || count($component['images']) < 1) {
            throw new Exception('Component images is empty');
        }

        foreach ($component['images'] as $img_idx => $img) {
            if (!is_array($img) || !isset($img['title']) || !isset($img['url'])) {
                throw new Exception('Component image data is incorrect');
            }

            $img['title'] = trim($img['title']);
            $img['url'] = trim($img['url']);

            if (!$img['title']) {
                throw new Exception('Component image title is empty');
            }

            if (!$img['url']) {
                throw new Exception('Component image url is empty');
            }

            $image_path = OSC_Storage::getFilepathFromUrl($img['url']);

            if (!$image_path || !file_exists($image_path)) {
                throw new Exception('Component image is not exists');
            }

            $component['images'][$img_idx] = [
                'title' => $img['title'],
                'url' => $img['url']
            ];
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _imageGroupSelector(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['groups']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['groups']) || count($component['groups']) < 1) {
            throw new Exception('Component groups is empty');
        }

        foreach ($component['groups'] as $group_idx => $group) {
            if (!is_array($group) || !isset($group['title']) || !isset($group['images']) || !is_array($group['images']) || count($group['images']) < 1) {
                throw new Exception('Image group data is empty');
            }

            $group['title'] = trim($group['title']);

            if (!$group['title']) {
                throw new Exception('Image group title is empty');
            }

            foreach ($group['images'] as $img_idx => $img) {
                if (!is_array($img) || !isset($img['title']) || !isset($img['url'])) {
                    throw new Exception('Component image data is incorrect');
                }

                $img['title'] = trim($img['title']);
                $img['url'] = trim($img['url']);

                if (!$img['title']) {
                    throw new Exception('Component image title is empty');
                }

                if (!$img['url']) {
                    throw new Exception('Component image url is empty');
                }

                $image_path = OSC_Storage::getFilepathFromUrl($img['url']);

                if (!$image_path || !file_exists($image_path)) {
                    throw new Exception('Component image is not exists');
                }

                $group['images'][$img_idx] = [
                    'title' => $img['title'],
                    'url' => $img['url']
                ];
            }

            $component['groups'][$group_idx] = $group;
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _colorSelector(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['colors']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['colors']) || count($component['colors']) < 1) {
            throw new Exception('Component colors is empty');
        }

        foreach ($component['colors'] as $idx => $color) {
            if (!is_array($color) || !isset($color['title']) || !isset($color['hex'])) {
                throw new Exception('Component color is incorrect');
            }

            $color['title'] = trim($color['title']);
            $color['hex'] = trim($color['hex']);

            if (!$color['title'] || !$color['hex']) {
                throw new Exception('Component color is incorrect');
            }

            $component['colors'][$idx] = [
                'title' => $color['title'],
                'hex' => $color['hex']
            ];
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _listItem(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['min']) || !isset($component['max']) || !isset($component['components']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        $component['min'] = intval($component['min']);
        $component['max'] = intval($component['max']);

        if ($component['min'] < 1 || $component['max'] < 1 || $component['min'] == $component['max']) {
            throw new Exception('Component min/max value is incorrect');
        }

        if ($component['min'] > $component['max']) {
            $buff = $component['min'];
            $component['min'] = $component['max'];
            $component['max'] = $buff;
        }

        if (!is_array($component['components']) || count($component['components']) < 1) {
            throw new Exception('Component sample component is empty');
        }

        $this->validate($component['components']);

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _switcherBySelect(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['scenes']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['scenes']) || count($component['scenes']) < 1) {
            throw new Exception('Component scene is empty');
        }

        foreach ($component['scenes'] as $idx => $scene) {
            if (!is_array($scene) || !isset($scene['title'])) {
                throw new Exception('Component scene data is incorrect');
            }

            $scene['title'] = trim($scene['title']);

            if ($scene['title'] === '') {
                throw new Exception('Scene title is empty');
            }

            if (isset($scene['components']) && is_array($scene['components']) && count($scene['components']) > 0) {
                $this->validate($scene['components']);
            } else {
                $scene['components'] = [];
            }

            $component['scenes'][$idx] = $scene;
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _switcherByColor(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['scenes']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['scenes']) || count($component['scenes']) < 1) {
            throw new Exception('Component scene is empty');
        }

        foreach ($component['scenes'] as $idx => $scene) {
            if (!is_array($scene) || !isset($scene['color']) || !is_array($scene['color']) || !isset($scene['color']['title']) || !isset($scene['color']['hex'])) {
                throw new Exception('Component scene data is incorrect');
            }

            $scene['color']['title'] = trim($scene['color']['title']);
            $scene['color']['hex'] = trim($scene['color']['hex']);

            if (!$scene['color']['title'] || !$scene['color']['hex']) {
                throw new Exception('Component scene color is incorrect');
            }

            $scene['color'] = [
                'title' => $scene['color']['title'],
                'hex' => $scene['color']['hex']
            ];

            if (isset($scene['components']) && is_array($scene['components']) && count($scene['components']) > 0) {
                $this->validate($scene['components']);
            } else {
                $scene['components'] = [];
            }

            $component['scenes'][$idx] = $scene;
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

    protected function _switcherByImage(&$component) {
        if (!isset($component['title']) || !isset($component['desc']) || !isset($component['scenes']) || !isset($component['require'])) {
            throw new Exception('Component data is incorrect');
        }

        $component['title'] = trim($component['title']);
        $component['desc'] = trim($component['desc']);

        if (!$component['title']) {
            throw new Exception('Component title is empty');
        }

        if (!is_array($component['scenes']) || count($component['scenes']) < 1) {
            throw new Exception('Component scene is empty');
        }

        foreach ($component['scenes'] as $idx => $scene) {
            if (!is_array($scene) || !isset($scene['image']) || !is_array($scene['image']) || !isset($scene['image']['title']) || !isset($scene['image']['url'])) {
                throw new Exception('Component scene data is incorrect');
            }

            $scene['image']['title'] = trim($scene['image']['title']);
            $scene['image']['url'] = trim($scene['image']['url']);

            if (!$scene['image']['title'] || !$scene['image']['url']) {
                throw new Exception('Component scene image is incorrect');
            }

            $image_path = OSC_Storage::getFilepathFromUrl($scene['image']['url']);

            if (!$image_path || !file_exists($image_path)) {
                throw new Exception('Component scene image is not exists');
            }

            $scene['image'] = [
                'title' => $scene['image']['title'],
                'url' => $scene['image']['url']
            ];

            if (isset($scene['components']) && is_array($scene['components']) && count($scene['components']) > 0) {
                $this->validate($scene['components']);
            } else {
                $scene['components'] = [];
            }

            $component['scenes'][$idx] = $scene;
        }

        $component['layer_key'] = $this->_validateLayerKey($component['layer_key']);
        $component['require'] = intval($component['require']) == 1 ? 1 : 0;
    }

}
