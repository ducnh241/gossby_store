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
class Model_Post_Post extends Abstract_Core_Model
{

    protected $_table_name = 'post';
    protected $_pk_field = 'post_id';
    protected $_allow_write_log = true;
    const POST_PREVIEW_CODE = 'preview=1';

    public function getDetailUrl($get_absolute_url = true)
    {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $post_url = '/' . $current_lang_key . '/blog/' . $this->data['slug'];
        return $get_absolute_url ? OSC_FRONTEND_BASE_URL . $post_url : $post_url;
    }


    public function getPostCollection(){
        return $this->getPreLoadedModel('post/collection', $this->data['collection_id']);
    }

    public function getAuthor() {
        try {
            if ($this->data['author_id']){
                $author = OSC::model('post/author')->getCollection()
                    ->addCondition('author_id' ,$this->data['author_id'])
                    ->load()
                    ->first();
                return [
                    'name' => $author->data['name'],
                    'slug' => $author->data['slug'],
                    'avatar' => $author->getAvatarUrl()
                ];
            }
        } catch (Exception $ex){
            throw new Exception($ex->getMessage());
        }
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

    public function getFooterBanner()
    {
        if (OSC::helper('core/setting')->get('post/footer_banner_post/enable') == 1) {
            $pc_banner = $this->data['footer_banner_image']['pc'];
            $mobile_banner = $this->data['footer_banner_image']['mobile'];

            $result = [
                'pc' => OSC::core('aws_s3')->getStorageUrl(OSC::helper('core/setting')->get('post/footer_banner_post/pc_image')['file']) ?? '',
                'mobile' => OSC::core('aws_s3')->getStorageUrl(OSC::helper('core/setting')->get('post/footer_banner_post/mobile_image')['file']) ?? '',
                'url' => $this->data['footer_banner_url'] ?: OSC::helper('core/setting')->get('post/footer_banner_post/url')
            ];
            if (!empty($pc_banner)) {
                $result['pc'] = OSC::core('aws_s3')->getStorageUrl($pc_banner);
            }

            if (!empty($mobile_banner)) {
                $result['mobile'] = OSC::core('aws_s3')->getStorageUrl($mobile_banner);
            }

            return $result;
        }
    }

    public function getRecentPosts($limit = 7)
    {
        return $this->getCollection()
            ->addCondition('published_flag', 1)
            ->sort('priority', 'desc')
            ->sort('post_id', 'desc')
            ->setLimit($limit)
            ->load();
    }

    public function getOtherPosts($exclude_post_id, $collection_ids = [], $limit = 3)
    {
        $posts = $this->getCollection()->addCondition('published_flag', 1)
            ->addCondition('post_id', $exclude_post_id, OSC_Database::OPERATOR_NOT_EQUAL);
        if (count($collection_ids) > 0) {
            $selected_collections = OSC::model('post/postCollectionRel')->getCollection()->addField('post_id')->addCondition('collection_id', $collection_ids, OSC_Database::OPERATOR_IN)->load()->toArray();
            $post_ids = array_map(function ($item) {
                return intval($item['post_id']);
            }, $selected_collections);
            $posts->addCondition('post_id', array_unique($post_ids), OSC_Database::OPERATOR_IN);
        }

        return $posts->setRandom()
            ->setLimit($limit)
            ->load();
    }

    public function trackingVisits($referer) {
        try {
            /* @var $DB OSC_Database */
            $DB = OSC::core('database');

            $cookie_key = OSC_Controller::makeRequestChecksum('_fp', OSC_SITE_KEY);

            $tracking_key = OSC::cookieGet($cookie_key);

            if (!$tracking_key) {
                throw new Exception('Not have tracking key');
            }

            $time = time();

            $DB->select('*', 'post_unique_visit', ['condition' => '`track_key` = :track_key AND post_id = :post_id', 'params' => ['track_key' => $tracking_key, 'post_id' => $this->getId()]], null, 1, 'fetch_post_unique_visit');

            $record = $DB->fetchArray('fetch_post_unique_visit');

            if ($record && $record['visited_timestamp'] >= $time - 3) {
                throw new Exception('Not save data before 3 seconds');
            }

            $this->increment('visits');

            if (!$record) {
                $DB->insert(
                    'post_unique_visit', [
                    'track_key' => $tracking_key,
                    'post_id' => $this->getId(),
                    'added_timestamp' => $time,
                    'visited_timestamp' => $time,
                ], 'insert_post_unique_visit'
                );

                $this->increment('unique_visits');
            } else {
                $DB->update('post_unique_visit', ['visited_timestamp' => $time] , 'record_id = '. $record['record_id'], 1, 'update_post_unique_visit');
            }

            try {
                $DB->insert('post_referer', ['referer' => $referer,'post_id' => $this->getId(), 'report_value' => 1, 'added_timestamp' => time()], 'insert_report_record');
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                    try {
                        $DB->query("UPDATE osc_post_referer SET report_value = (report_value + 1) WHERE referer = :referer AND post_id = :post_id LIMIT 1", ['referer' => $referer, 'post_id' => $this->getId()], 'update_report_record');
                    } catch (Exception $ex) {
                        throw new Exception($ex->getMessage());
                    }
                }
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getReferer() {
        $referer = OSC::cookieGet($this->_getRefererCookieKey());

        return $referer ? OSC::decode($referer) : '';
    }


    protected function _beforeSave()
    {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        $block_image_config = [
            'url_processor' => [Helper_Core_Editor, 'imageUrlProcessor'],
            'control_align_enable' => true,
            'control_align_level_enable' => true,
            'control_align_overflow_mode' => true,
            'control_align_full_mode' => true,
            'control_zoom_enable' => true,
            'control_caption_enable' => true
        ];
        $embed_block_config = [
            'control_zoom_enable' => false,
            'control_align_level_enable' => true,
            'control_caption_enable' => true
        ];

        if (isset($data['content'])) {
            try {
                $data['content'] = OSC::core('editor')->config(['image_enable' => false])
                    ->addPlugins([
                        'name' => 'textColor'
                    ],
                        ['name' => 'highlight'],
                        ['name' => 'blockImage', 'config' => $block_image_config],
                        ['name' => 'embedBlock', 'config' => $embed_block_config]
                    )->clean($data['content']);

                if (!$data['content']) {
                    $errors[] = 'Post content is empty';
                }
            } catch (Exception $ex) {
                $errors[] = 'Post content get error: ' . $ex->getMessage();
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

        foreach (['published_flag'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'title' => 'Post title is empty',
                    'slug' => 'Slug is empty',
                    'content' => 'Post content is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'meta_tags' => [],
                    'published_flag' => 1,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

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
        if (isset($data['footer_banner_image'])) {
            $data['footer_banner_image'] = OSC::encode($data['footer_banner_image']);
        }
        if (!isset($data['modified_timestamp'])) {
            $data['modified_timestamp'] = time();
        }
    }

    protected function _preDataForUsing(&$data)
    {
        parent::_preDataForUsing($data);

        if (isset($data['meta_tags'])) {
            $data['meta_tags'] = OSC::decode($data['meta_tags'], true);
        }
        if (isset($data['footer_banner_image'])) {
            $data['footer_banner_image'] = OSC::decode($data['footer_banner_image'], true);
        }
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('alias', "destination='post/{$this->getId()}'", 1);
        } catch (Exception $ex){

        }
    }
}
