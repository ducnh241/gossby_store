<?php

abstract class Abstract_Catalog_Campaign_ProductType_MugFullprint extends Abstract_Catalog_Campaign_ProductType {

    public function getImageData() {
        $campaign_root_name = $this->_getImageRootName();

        $data = [];

        $config = static::getConfig();

        if (isset($this->_data['option']) && is_array($this->_data['option'])) {
            foreach ($this->_data['option'] as $value_key => $value_designs) {
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

                foreach (['front', 'center', 'back'] as $design_key) {
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
        } else {
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

            foreach (['front', 'center', 'back'] as $design_key) {
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

}
