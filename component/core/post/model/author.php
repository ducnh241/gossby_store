<?php

class Model_Post_Author extends Abstract_Core_Model
{
    protected $_table_name = 'post_author';
    protected $_pk_field = 'author_id';
    protected $_allow_write_log = true;

    public function getAvatarUrl()
    {
        return $this->data['avatar'] ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($this->data['avatar'])) : '';
    }

    public function getDetailUrl($get_absolute_url = true)
    {
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
        $author_url = '/' . $current_lang_key . '/blog/author/' . $this->data['slug'];
        return $get_absolute_url ? OSC_FRONTEND_BASE_URL . $author_url : $author_url;
    }

    public function getMetaImageUrl()
    {
        return $this->data['meta_tags']['image'] ? OSC::core('aws_s3')->getStorageUrl($this->data['meta_tags']['image']) : '';
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

    public function checkExistSlug($slug)
    {
        $countModel = OSC::model('post/author')->getCollection()
            ->addCondition('slug', $slug)
            ->addCondition('author_id', $this->getId(), OSC_Database::OPERATOR_NOT_EQUAL)
            ->load()
            ->collectionLength();

        return intval($countModel);
    }
}

