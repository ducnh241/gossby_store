<?php

class Model_Filter_Tag extends Abstract_Core_Model {

    const TYPE_MULTIPLE_CHOICE = 0;
    const TYPE_ONE_CHOICE = 1;

    const STATE_TAG_LOCK = 1;
    const STATE_TAG_UNLOCK = 0;

    const SHOW_FILTER = 1;
    const HIDE_FILTER = 0;

    protected $_table_name = 'filter_tag';
    protected $_pk_field = 'id';

    protected $_title_old = null;

    public function showFilterTag(&$results = [], $categories = null, $parent_id = 0, $char = '', $level = 0, $add_class = [])
    {
        if (!$categories) {
            $categories = OSC::model('filter/tag')->getCollection()
                ->sort('parent_id')
                ->sort('position')
                ->sort('title')
                ->load()
                ->toArray();
        }

        $children = [];

        foreach ($categories as $key => $category) {
            if ($category['parent_id'] == $parent_id) {
                $children[] = $category;
                unset($categories[$key]);
            }
        }

        foreach ($children as $key => $category) {
            $category['add_class'] = implode(' ', $add_class);
            $category['level'] = $level;
            $category['prefixed_title'] = $char . ($category['prefixed_title'] ?? $category['title']);

            if ($key === count($children) - 1) {
                $category['is_last_item'] = 1;
                $add_class[] = 'last_item_level_' . $level;
            }

            $results[$category['id']] = $category;
            $this->showFilterTag($results, $categories, $category['id'], '&brvbar;--- ' . $char, $level + 1, $add_class);
        }

        return $results;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['added_timestamp'])) {
            $data['added_timestamp'] = intval($data['added_timestamp']);

            if ($data['added_timestamp'] < 1) {
                unset($data['added_timestamp']);
            }
        }

        if (isset($data['modified_timestamp'])) {
            $data['modified_timestamp'] = intval($data['modified_timestamp']);

            if ($data['modified_timestamp'] < 1) {
                unset($data['modified_timestamp']);
            }
        }

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);
            $data['title'] = OSC::core('string')->removeInvalidCharacter($data['title']);
            if (empty($data['title'])) {
                $errors[] = 'Tag title is empty';
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = time();
                }

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = 0;
                }
            } else {
                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = time();
                }

                $this->_title_old = md5(strtolower($this->_orig_data['title'] . ' ' . $this->_orig_data['other_title']));
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }

    }

    protected function _afterSave() {
        parent::_afterSave();

        // update tag product if title or other title change
        if ($this->getLastActionFlag() == static::UPDATE_FLAG &&
            $this->_title_old &&
            ($this->_title_old != md5(strtolower($this->data['title'] . ' ' . $this->data['other_title'])))
        ) {
            try {
                $product_tags = OSC::model('filter/tagProductRel')
                    ->getCollection()
                    ->addField('tag_id', 'product_id')
                    ->addCondition('tag_id', $this->getId())
                    ->load();

                foreach ($product_tags as $tag) {
                    OSC::core('observer')->dispatchEvent('catalog/algoliaSyncProduct',
                        [
                            'product_id' => $tag->data['product_id'],
                            'sync_type' => Helper_Catalog_Algolia_Product::SYNC_TYPE_UPDATE_PRODUCT
                        ]
                    );
                }
            } catch (Exception $ex) {}
        }
    }

}
