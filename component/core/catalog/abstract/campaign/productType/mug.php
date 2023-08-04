<?php

abstract class Abstract_Catalog_Campaign_ProductType_Mug extends Abstract_Catalog_Campaign_ProductType {

    public function getImageData() {
        $campaign_root_name = $this->_getImageRootName();

        $data = [];

        $config = static::getConfig();
        
        if (isset($this->_data['option']) && is_array($this->_data['option'])) {
            foreach ($this->_data['option'] as $value_key => $value_designs) {
                $unique_design_segment_checker = [];
                $_data = [];
                
                if (is_array($value_designs) && count($value_designs) > 0) {
                    if (count($value_designs) > 1 && count($value_designs) == count(static::getMockupConfig())) {                        
                        $image_id = md5($this->_campaign_id . ':' . $config['key'] . ':' . $value_key);

                        $_data[$image_id] = [
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
                        $unique_design_segment_checker[] = $design_config['type'] . ':' . ($design_config['type'] == 'personalizedDesign' ? $design_config['design_id'] : $design_config['item_id']);
                            
                        $image_id = md5($this->_campaign_id . ':' . $config['key'] . ':' . $value_key . '/' . $design_key);

                        $_data[$image_id] = [
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

                        $_data[$image_id] = [
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
                        $unique_design_segment_checker[] = $design_config['type'] . ':' . ($design_config['type'] == 'personalizedDesign' ? $design_config['design_id'] : $design_config['item_id']);
                        
                        $image_id = md5($this->_campaign_id . ':' . $config['key'] . ':' . $value_key . '/' . $design_key);

                        $_data[$image_id] = [
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
                
                $unique_design_segment_checker = array_unique($unique_design_segment_checker);

                if(count($unique_design_segment_checker) < 2) {
                    if(count($_data) == 3) {
                        $image = reset($_data);
                        unset($_data[$image['image_id']]);
                    }
                }
                
                foreach($_data as $image_id => $image) {
                    $data[$image_id] = $image;   
                }
            }
        } else {
            $unique_design_segment_checker = [];

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
                $unique_design_segment_checker[] = $design_config['type'] . ':' . ($design_config['type'] == 'personalizedDesign' ? $design_config['design_id'] : $design_config['item_id']);
                
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
            
            $unique_design_segment_checker = array_unique($unique_design_segment_checker);
        
            if(count($unique_design_segment_checker) < 2) {
                if(count($data) == 3) {
                    $image = reset($data);
                    unset($data[$image['image_id']]);
                }
            }
        }
                
        return $data;
    }

}
