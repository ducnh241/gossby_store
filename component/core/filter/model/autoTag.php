<?php

class Model_Filter_AutoTag extends Abstract_Core_Model {

    protected $_table_name = 'filter_auto_tag';
    protected $_pk_field = 'id';

    const KEY_CONFIG_SETTING_FIELDS = 'filter/autoTag/settingField';

    protected $_tags = null;

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        foreach (['auto_tag', 'deleted_tag', 'new_tag'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }

        if ($this->getActionFlag() == self::INSERT_FLAG) {

            $data['added_timestamp'] = time();
            $data['modified_timestamp'] = time();

        } else {
            $data['modified_timestamp'] = time();
        }

        $this->resetDataModifiedMap()->setData($data);

    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['auto_tag', 'deleted_tag', 'new_tag'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    public function analyticAutoTag($product_tag_ids) {

        if (empty($product_tag_ids)) {
            return;
        }

        $product_tag_ids = array_map('intval', $product_tag_ids);

        try {
            $new_tag = array_diff($product_tag_ids, $this->data['auto_tag']);
            $deleted_tag = array_diff($this->data['auto_tag'], $product_tag_ids);

            $this->setData(
                [
                    'new_tag' => array_values($new_tag),
                    'deleted_tag' => array_values($deleted_tag)
                ]
            )->save();
        } catch (Exception $ex) {}
    }

    public function setTags(Model_Filter_Tag_Collection $tags): Model_Filter_AutoTag
    {
        $this->_tags = $tags;
        return $this;
    }

    public function getTags() {
        if ($this->_tags == null) {
            $tag_ids = array_merge($this->data['auto_tag'], $this->data['delete_tag'] ?? [], $this->data['new_tag'] ?? []);
            $tags = OSC::model('filter/tag')->getCollection()->load($tag_ids);
            $this->_tags = $tags;
        }

        return $this->_tags;
    }

}
