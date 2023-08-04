<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Page_Page extends Abstract_Core_Model {

    protected $_table_name = 'page';
    protected $_pk_field = 'page_id';
    protected $_allow_write_log = true;
    protected $_option_conf = array('value' => 'page_id', 'label' => 'title');

    public function getDetailUrl()
    {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        return OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/page/' . $this->data['slug'];
    }

    public function isSystemPage() {
        return is_string($this->data['page_key']) && $this->data['page_key'] !== '';
    }

    public function getPageType()
    {
        $page_type = Helper_Page_Common::TYPE_OPTIONS;
        return isset($page_type[$this->data['type']]) ? $page_type[$this->data['type']] : 'Default';
    }

    public function loadByPageKey($page_key) {
        return $this->setCondition(['field' => 'page_key', 'value' => $page_key, 'operator' => OSC_Database::OPERATOR_EQUAL])->load();
    }

    public function getMetaImageUrl() {
        return $this->data['meta_tags']['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['meta_tags']['image']) : '';
    }

    public function getOgImageUrl()
    {
        $og_image = $this->getMetaImageUrl();

        if (!$og_image) {
            $og_image = $this->getImageUrl();
        }

        return $og_image ? $og_image : OSC::helper('frontend/template')->getMetaImage()->url;
    }

    public function getImageUrl() {
        return $this->data['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['image']) : '';
    }

    public function getPageParent(){
        return $this->getPreLoadedModel('page/page', $this->data['parent_id']);
    }

    public function getPageByParentId($parent_id = 0)
    {
        return OSC::model('page/page')->getCollection()
            ->addCondition('parent_id', $parent_id)
            ->sort('priority', OSC_Database::ORDER_DESC)
            ->sort('title', OSC_Database::ORDER_ASC)
            ->load();
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();
        $block_image_config = array(
            'url_processor' => array(Helper_Core_Editor, 'imageUrlProcessor'),
            'control_align_enable' => true,
            'control_align_level_enable' => true,
            'control_align_overflow_mode' => true,
            'control_align_full_mode' => true,
            'control_zoom_enable' => true,
            'control_caption_enable' => true
        );
        $embed_block_config = array(
            'control_zoom_enable' => false,
            'control_align_level_enable' => true,
            'control_caption_enable' => true
        );

        if (isset($data['content'])) {
            try {
                $data['content'] = OSC::core('editor')->config(array('image_enable' => false))
                        ->addPlugins(array('name' => 'textColor'), array('name' => 'highlight'), array('name' => 'blockImage', 'config' => $block_image_config), array('name' => 'embedBlock', 'config' => $embed_block_config))
                        ->clean($data['content']);
            } catch (Exception $ex) {
                $errors[] = 'Page content get error: ' . $ex->getMessage();
            }
        }

        if (isset($data['meta_tags']['image'])) {
            $data['meta_tags']['image'] = trim($data['meta_tags']['image']);

            if ($data['meta_tags']['image'] !== '' && !OSC::core('aws_s3')->doesStorageObjectExist($data['meta_tags']['image'])) {
                $errors[] = 'Image file is not exists';
            }
        }

        if (isset($data['meta_tags'])) {
            if (!is_array($data['meta_tags'])) {
                $data['meta_tags'] = array();
            }

            $meta_tags = array();

            foreach (array('title', 'slug', 'keywords', 'description', 'image') as $key) {
                $meta_tags[$key] = isset($data['meta_tags'][$key]) ? trim($data['meta_tags'][$key]) : '';
            }

            $data['meta_tags'] = $meta_tags;
        }

        foreach (array('publish_start_timestamp', 'publish_to_timestamp', 'added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (isset($data['page_key'])) {
            $data['page_key'] = trim($data['page_key']);

            if ($data['page_key'] === '') {
                $data['page_key'] = null;
            }
        }

        foreach (array('published_flag') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        if (isset($data['publish_start_timestamp']) || isset($data['publish_to_timestamp'])) {
            $publish_start_timestamp = isset($data['publish_start_timestamp']) ? $data['publish_start_timestamp'] : intval($this->getData('publish_start_timestamp', true));
            $publish_to_timestamp = isset($data['publish_to_timestamp']) ? $data['publish_to_timestamp'] : intval($this->getData('publish_to_timestamp', true));

            if ($publish_start_timestamp > 0 || $publish_to_timestamp > 0) {
                $data['published_flag'] = 0;
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'title' => 'Page title is empty',
                    'slug' => 'Page title is empty',
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'page_key' => null,
                    'meta_tags' => [],
                    'published_flag' => 1,
                    'publish_start_timestamp' => 0,
                    'publish_to_timestamp' => 0,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['meta_tags'])) {
            $data['meta_tags'] = OSC::encode($data['meta_tags']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['meta_tags'])) {
            $data['meta_tags'] = OSC::decode($data['meta_tags'], true);
        }
    }

    protected function _afterSave() {
        parent::_afterSave();

        $keys = array('title', 'content');

        $index_keywords = array();

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        OSC::helper('backend/common')->indexAdd('', 'page', 'page', $this->getId(), $index_keywords);
        OSC::helper('frontend/common')->indexAdd('', 'page', 'page', $this->getId(), $index_keywords);
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        OSC::helper('backend/common')->indexDelete('', 'page', 'page', $this->getId());
        OSC::helper('frontend/common')->indexDelete('', 'page', 'page', $this->getId());
        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('alias', "destination='page/{$this->getId()}'", 1);
        } catch (Exception $ex){

        }
    }

}
