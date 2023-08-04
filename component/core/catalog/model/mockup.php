<?php

class Model_Catalog_Mockup extends Abstract_Core_Model {

    protected $_table_name = 'mockup';
    protected $_pk_field = 'id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if(isset($data['config'])) {
            $data['config'] = OSC::encode($data['config']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if(isset($data['config'])) {
            $data['config'] = OSC::decode($data['config'], true);
        }
    }
}