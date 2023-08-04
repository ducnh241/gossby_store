<?php

class Model_Facebook_Pixel extends Abstract_Core_Model
{

    protected $_table_name = 'facebook_pixel';
    protected $_pk_field = 'id';

    protected $_allow_write_log = true;

    public function loadByPixelId($pixel_id)
    {
        return $this->setCondition(['field' => 'pixel_id', 'value' => $pixel_id, 'operator' => OSC_Database::OPERATOR_EQUAL])->load();
    }

    public function getAllFacebookPixel()
    {
        return OSC::model('facebook/pixel')->getCollection()->load();
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();
        $data = $this->_collectDataForSave();

        $errors = [];

        foreach (['added_timestamp', 'modified_timestamp'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }
        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'title' => 'Pixel title is empty',
                    'pixel_id' => 'Pixel title is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            }

            if (!Observer_Facebook_Common::validateFacebookPixel($data['pixel_id'])) {
                $errors[] = 'Pixel id invalid';
            }

            try {
                OSC::model('facebook/pixel')->setCondition(
                    [
                        'condition' => 'pixel_id = :pixel_id AND id != :id',
                        'params' => ['pixel_id' => $data['pixel_id'], 'id' => $this->getId()]
                    ]
                )->load();
                $errors[] = 'Pixel Id already exists';
            } catch (Exception $ex) {

            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();
    }

    protected function _afterDelete()
    {
        parent::_afterDelete();

        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('facebook_pixel_product_type_rel', 'pixel_id = ' . $this->data['pixel_id']);
        } catch (Exception $ex) {

        }
    }

}
