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
 * @copyright    Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Post_Collection extends Abstract_Core_Model
{
    protected $_table_name = 'post_collection';
    protected $_pk_field = 'collection_id';
    protected $_allow_write_log = true;
    protected $_sort_options = [
        'priority' => 'Priority DESC',
        'newest' => 'Newest',
        'oldest' => 'Oldest'
    ];

    public function getDetailUrl($get_absolute_url = true)
    {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $url = '/' . $current_lang_key . '/blog/' . $this->data['slug'];
        return $get_absolute_url ? OSC_FRONTEND_BASE_URL . $url : $url;
    }

    public function getMetaImageUrl()
    {
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

    public function getImageUrl()
    {
        return $this->data['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['image']) : '';
    }

    public function getSortOptions()
    {
        return $this->_sort_options;
    }

    public function getPosts($collection_id, $sort = 'newest', $limit = 3)
    {
        $posts = OSC::model('post/post')->getCollection()
            ->addCondition('published_flag', 1);

        if ($collection_id > 0) {
            $selected_collections = OSC::model('post/postCollectionRel')->getCollection()->addField('post_id')->addCondition('collection_id', $collection_id)->load()->toArray();
            $post_ids = array_column($selected_collections, 'post_id');
            if (count($post_ids) > 0) {
                $posts->addCondition('post_id', $post_ids, OSC_Database::OPERATOR_IN);
            }
        }

        switch ($sort) {
            case 'oldest':
                $posts->sort('post_id', 'ASC');
                break;
            case 'priority':
                $posts->sort('priority', 'DESC');
                break;
            default:
                $posts->sort('post_id', 'DESC');
                break;
        }

        return $posts->setLimit($limit)->load();
    }

    protected function _beforeSave()
    {
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
                $data['content'] = OSC::core('editor')->config(['image_enable' => false])
                    ->addPlugins(
                        ['name' => 'textColor'],
                        ['name' => 'highlight'],
                        ['name' => 'blockImage', 'config' => $block_image_config],
                        ['name' => 'embedBlock', 'config' => $embed_block_config])
                    ->clean($data['content']);

                if (!$data['content']) {
                    $errors[] = 'Collection content is empty';
                }
            } catch (Exception $ex) {
                $errors[] = 'Collection content get error: ' . $ex->getMessage();
            }
        }

        if (isset($data['priority'])) {
            $data['priority'] = intval($data['priority']);
        }

        if (isset($data['meta_tags']['image'])) {
            $data['meta_tags']['image'] = trim($data['meta_tags']['image']);

            if ($data['meta_tags']['image'] !== '' && !OSC::core('aws_s3')->doesStorageObjectExist($data['meta_tags']['image'])) {
                $errors[] = 'Image file is not exists';
            }
        }

        if (isset($data['meta_tags'])) {
            if (!is_array($data['meta_tags'])) {
                $data['meta_tags'] = [];
            }

            $meta_tags = [];

            foreach (['title', 'slug', 'keywords', 'description', 'image'] as $key) {
                $meta_tags[$key] = isset($data['meta_tags'][$key]) ? trim($data['meta_tags'][$key]) : '';
            }

            $data['meta_tags'] = $meta_tags;
        }

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
                $require_fields = array(
                    'title' => 'Collection title is empty',
                    'slug' => 'Slug is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'meta_tags' => [],
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

    protected function _preDataForSave(&$data)
    {
        parent::_preDataForSave($data);

        if (isset($data['meta_tags'])) {
            $data['meta_tags'] = OSC::encode($data['meta_tags']);
        }
    }

    protected function _preDataForUsing(&$data)
    {
        parent::_preDataForUsing($data);

        if (isset($data['meta_tags'])) {
            $data['meta_tags'] = OSC::decode($data['meta_tags'], true);
        }
    }

    protected function _afterSave()
    {
        parent::_afterSave();

        $keys = ['title'];

        $index_keywords = [];

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        OSC::helper('backend/common')->indexAdd('', 'post', 'post_collection', $this->getId(), $index_keywords);
        OSC::helper('frontend/common')->indexAdd('', 'post', 'post_collection', $this->getId(), $index_keywords);

    }

    protected function _afterDelete()
    {
        parent::_afterDelete();

        OSC::helper('backend/common')->indexDelete('', 'post', 'post_collection', $this->getId());
        OSC::helper('frontend/common')->indexDelete('', 'post', 'post_collection', $this->getId());
        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('alias', "destination='post/collection/{$this->getId()}'", 1);
        } catch (Exception $ex){

        }
    }

}
