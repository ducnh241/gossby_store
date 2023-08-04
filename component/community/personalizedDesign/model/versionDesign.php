<?php

class Model_PersonalizedDesign_VersionDesign extends Abstract_Core_Model
{

    protected $_table_name = 'personalized_design_version';
    protected $_pk_field = 'record_id';

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if (isset($data['design_data'])) {
            $data['design_data'] = OSC::encode($data['design_data']);
        }

        if (isset($data['meta_data'])) {
            $data['meta_data'] = OSC::encode($data['meta_data']);
        }

        $errors = array();

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

    protected function _beforeDelete()
    {
        parent::_beforeDelete();
    }

}