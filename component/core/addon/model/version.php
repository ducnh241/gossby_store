<?php

class Model_Addon_Version extends Abstract_Core_Model
{
    protected $_table_name = 'addon_service_version';
    protected $_pk_field = 'id';

    protected function _preDataForSave(&$data)
    {
        parent::_preDataForSave($data);

        foreach (['data', 'videos', 'images'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }

    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if ($this->getActionFlag() == static::INSERT_FLAG) {
            $data['added_timestamp'] = time();
        }
        $data['modified_timestamp'] = time();

        $this->resetDataModifiedMap()->setData($data);
    }

    protected function _preDataForUsing(&$data)
    {
        parent::_preDataForUsing($data);

        foreach (['data', 'videos', 'images'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }

        if (!empty($data['data']['options'])) {
            foreach ($data['data']['options'] as $option_key => $option) {
                if ($option['image']) {
                    $data['data']['options'][$option_key]['image_url'] = OSC::core('aws_s3')->getStorageUrl($option['image']);
                } else {
                    $data['data']['options'][$option_key]['image_url'] = '';
                }
            }
        }

        if (!empty($data['images'])) {
            foreach ($data['images'] as $key => $image) {
                $data['images'][$key]['url'] = OSC::core('aws_s3')->getStorageUrl($image['url']);
            }
        }

        if (!empty($data['videos'])) {
            foreach ($data['videos'] as $key => $video) {
                $data['videos'][$key]['url'] = OSC::core('aws_s3')->getStorageUrl($video['url']);
                $data['videos'][$key]['thumbnail'] = OSC::core('aws_s3')->getStorageUrl($video['thumbnail']);
            }
        }
    }
}
