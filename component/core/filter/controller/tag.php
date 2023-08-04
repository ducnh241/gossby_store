<?php

class Controller_Filter_Tag extends Abstract_Backend_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('filter/tag');

        $this->getTemplate()
            ->setCurrentMenuItemKey('filter/tag')
            ->setPageTitle('Manage Tags');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $this->getTemplate()->setCurrentMenuItemKey('filter/tag')
            ->addBreadcrumb('Manage Tags', $this->getUrl('index'));
        $this->getTemplate()->setPageTitle('Manage Tags');

        $this->checkPermission('filter/tag/list');
        if ($this->_request->get('action')) {
            $data = $this->_request->get('data');

            if (!is_array($data) || count($data) < 1) {
                $this->addErrorMessage('Update data tag not found');
            }
            $collection = OSC::model('filter/tag')->getCollection()->load(array_column($data, 'id'));

            try {
                foreach ($data as $value) {
                    $model = $collection->getItemByPK($value['id']);
                    $value['show'] = (isset($value['show']) && $value['show']) ? 1 : 0;

                    if ($value['show'] == 0) {
                        //$value['position'] = 99;
                    }

                    $model->setData([
                        'is_show_filter' => $value['show'],
                        'position' => $value['position'],
                        'modified_timestamp' => time()
                    ])->save();
                }

                $filter_setting = OSC::helper('filter/common')->buildFilter();

                $model_filter = OSC::model('filter/collection')->getCollection()->getFilterByCollectionId(0);

                if ($model_filter == null) {
                    OSC::model('filter/collection')->setData([
                        'collection_id' => 0,
                        'filter_setting' => $filter_setting
                    ])->save();
                } else {
                    $model_filter->setData(['filter_setting' => $filter_setting])->save();
                }

                $this->addMessage(['message' => 'Update success']);
                static::redirect($this->getUrl('index'));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }
        $filter_tags = OSC::model('filter/tag')->showFilterTag();

        $output_html = $this->getTemplate()->build('filter/tag/list', ['filter_tags' => $filter_tags]);

        $this->output($output_html);
    }

    public function actionPost()
    {
        $data['id'] = intval($this->_request->get('id'));
        $data['title'] = trim($this->_request->get('title'));
        $data['type'] = intval($this->_request->get('type'));
        $data['parent_id'] = intval($this->_request->get('parent_id'));
        $data['required'] = intval($this->_request->get('required'));
        $data['is_break_down_keyword'] = intval($this->_request->get('is_break_down_keyword'));
        $data['other_title'] = trim($this->_request->get('other_title', ''), ' ,');
        $data['image'] = $this->_request->get('image', '');
        $old_form = [];

        $this->checkPermission('filter/tag/list/' . ($data['id'] > 0 ? 'edit' : 'add'));
        $filter_tag_model = OSC::model('filter/tag');

        if ($data['id'] >= 1) {
            $this->getTemplate()->addBreadcrumb('Update Tags');
            $filter_tag_model->load($data['id']);
        } else {
            $this->getTemplate()->addBreadcrumb('Create Tags');
        }

        $filter_tags = OSC::model('filter/tag')->showFilterTag();

        if ($this->_request->get('action', null) == 'post_form') {
            try {
                $this->_validateForm($data, $filter_tag_model);
                if ($data['other_title']) {
                    $list_other_title = [];
                    foreach (preg_split('/\R|,/', $data['other_title']) as $value) {
                        if (trim($value, ', ')) {
                            $list_other_title[] = trim($value, ', ');
                        }
                    }
                    $data['other_title'] = implode(',', array_unique($list_other_title));
                    $data['other_title'] = $data['other_title'] ? ',' . $data['other_title'] . ',' : '';
                }

                $is_show_filter = $filter_tag_model->data['is_show_filter'];

                if ($is_show_filter == null) {
                    $is_show_filter = Model_Filter_Tag::HIDE_FILTER;
                }

                if ($data['parent_id'] == 0) {
                    $is_show_filter = 1;
                }

                $this->_processPostImage($filter_tag_model, $data);

                $filter_tag_model->setData([
                    'title' => $data['title'],
                    'type' => $data['type'],
                    'parent_id' => $data['parent_id'],
                    'other_title' => $data['other_title'],
                    'required' => $data['required'],
                    'image' => $data['image'],
                    'is_break_down_keyword' => $data['is_break_down_keyword'],
                    'is_show_filter' => $is_show_filter
                ]);

                $filter_tag_model->save();

                if ($data['id'] > 0) {
                    $message = 'Tag #' . $filter_tag_model->getId() . ' was updated';
                } else {
                    $message = 'Tag [#' . $filter_tag_model->getId() . '] "' . $filter_tag_model->data['title'] . '" added';
                }

                $this->addMessage($message);

                static::redirect($this->getUrl('index'));
            } catch (Exception $ex) {
                $message = $ex->getMessage();
                $is_duplicate = str_contains($message, 'Duplicate entry');
                if ($is_duplicate) $message = 'Tag title "' . $data['title'] . '" already exists';
                $old_form = $data;

                $this->addErrorMessage($message);
            }
        }

        $output_html = $this->getTemplate()->build('filter/tag/postForm',
            [
                'filter_tag_model' => $filter_tag_model,
                'filter_tags' => $filter_tags,
                'old_form' => $old_form,
            ]
        );

        $this->output($output_html);
    }

    /**
     * @throws OSC_Exception_Runtime
     * @throws Exception
     */
    public function _validateForm($data, $filter_tag)
    {
        $parent_id = $data['parent_id'] ?? 0;
        $list_tag_lock = OSC::model('filter/tag')
            ->getCollection()
            ->addField('id')
            ->addCondition('lock_flag', Model_Filter_Tag::STATE_TAG_LOCK)
            ->load()
            ->toArray();
        $list_tag_lock = array_column($list_tag_lock, 'id');

        if (in_array($parent_id, $list_tag_lock)) {
            throw new Exception("Tag id #{$parent_id} is lock. Please choose other parent tag");
        }
        if ($filter_tag->getId() > 0 && $filter_tag->getId() == $parent_id) {
            throw new Exception('Parent ID cannot be itself');
        }

        if (isset($data['other_title'])) {
            $other_title_data = [];
            foreach (preg_split('/\R|,/', $data['other_title']) as $value) {
                if (trim($value, ', ')) {
                    $other_title_data[] = trim($value, ', ');
                }
            }
            $other_title_exists = [];
            if (is_array($other_title_data) && count($other_title_data)) {
                $tags = OSC::model('filter/tag')->getCollection()->addCondition('id', $filter_tag->getId(), OSC_Database::OPERATOR_NOT_EQUAL);
                $tags->addClause('validate_filter_tag', OSC_Database::RELATION_AND);
                foreach ($other_title_data as $other_title) {
                    $tags->addCondition('other_title', '%,' . trim($other_title, ', ') . ',%', OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR, 'validate_filter_tag');
                }
                $tags->load();
                if ($tags->length() > 0) {
                    foreach ($tags as $tag) {
                        $other_title_exists = array_unique(array_merge($other_title_exists, explode(',', trim($tag->data['other_title'], ','))));
                    }
                    throw new Exception('Other title <b>' . implode(', ', array_intersect($other_title_exists, $other_title_data)) . '</b> already exists');
                }
            }
        }

        $parent_children_relationship = OSC::helper('filter/common')->getParentChildrenRelationship()['parent_children_relationship'];

        if (isset($parent_children_relationship[$data['id']])) {
            $children = $parent_children_relationship[$data['id']]['children'];

            if (in_array($parent_id, $children)) {
                throw new Exception('tag #' . $parent_id . ' is currently a child of tag #' . $data['id'] . ', so it can\'t be saved');
            }
        }
    }

    public function _processPostImage($model, &$data) {
        $image_url = $data['image'];

        if (!$image_url || $image_url === $model->data['image']) {
            return false;
        }

        if (!OSC_Storage::tmpUrlIsExists($image_url)) {
            $this->_ajaxError('Image url error ' . $image_url);
        }

        $image_file_extension = preg_replace('/^.*(\.[a-zA-Z0-9]+)$/', '\\1', $image_url);
        $image_file_extension = strtolower($image_file_extension);

        $image_file_name = 'filter/tag/' . md5($image_url) . $image_file_extension;
        $image_file_name_s3 = OSC::core('aws_s3')->getStoragePath($image_file_name);

        $image_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($image_url);

        if (!OSC::core('aws_s3')->doesObjectExist($image_file_name_s3)) {
            OSC::core('aws_s3')->copy($image_tmp_file_name_s3, $image_file_name_s3);
        }

        $data['image'] = $image_file_name;
    }

    public function actionAddImage() {
        try {
            $id = $this->_request->get('id', '');
            $title = $this->_request->get('title', '');
            $image_url = $this->_request->get('image', '');

            if (!$id) {
                $this->_ajaxError('Tag ID is invalid.');
            }

            if (!$title) {
                $this->_ajaxError('Title is required.');
            }

            $model = OSC::model('filter/tag')->load($id);

            if ($image_url && $image_url !== $model->data['image']) {
                if (!OSC_Storage::tmpUrlIsExists($image_url)) {
                    $this->_ajaxError('Image url error ' . $image_url);
                }

                $image_file_extension = preg_replace('/^.*(\.[a-zA-Z0-9]+)$/', '\\1', $image_url);
                $image_file_extension = strtolower($image_file_extension);

                $image_file_name = 'filter/tag/' . md5($image_url) . $image_file_extension;
                $image_file_name_s3 = OSC::core('aws_s3')->getStoragePath($image_file_name);

                $image_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($image_url);

                if (!OSC::core('aws_s3')->doesObjectExist($image_file_name_s3)) {
                    OSC::core('aws_s3')->copy($image_tmp_file_name_s3, $image_file_name_s3);
                }

                $image_url = $image_file_name;
            }

            $data = [
                'title' => $title,
                'image' => $image_url,
            ];

            if ($title !== $model->data['title'] || $image_url !== $model->data['image']) {
                $model->setData($data)->save();
                $data['updated'] = true;
                $data['image_url'] = OSC::core('aws_s3')->getStorageUrl($image_url);
            }

            $this->_ajaxResponse($data);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionUploadImage() {
        try {
            $uploader = new OSC_Uploader();

            $tmp_file_path = OSC::getTmpDir() . '/' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $uploader->getExtension();

            try {
                $uploader->save($tmp_file_path, true);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }

            try {
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage(), 500);
            }
        } catch (Exception $ex) {
            if ($ex->getCode() === 500) {
                $this->_ajaxError($ex->getMessage());
            }

            $image_url = trim(strval($this->_request->decodeValue($this->_request->get('image_url'))));

            try {
                if (!$image_url) {
                    throw new Exception($this->_('core.err_data_incorrect'));
                }

                $tmp_file_path = OSC::getTmpDir() . '/' . md5($image_url);

                if (!file_exists($tmp_file_path)) {
                    $url_info = OSC::core('network')->curl($image_url, array('browser'));

                    if (!$url_info['content']) {
                        throw new Exception($this->_('core.err_data_incorrect'));
                    }

                    if (OSC::writeToFile($tmp_file_path, $url_info['content']) === false) {
                        throw new Exception($this->_('core.err_tmp_save_failed'));
                    }
                }
                $extension = OSC_File::verifyImage($tmp_file_path);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        try {
            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(3000);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'filter_tag.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tmp_url = $tmp_url ?? OSC_Storage::tmpGetFileUrl($file_name);

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ]);
    }
}
