<?php

class Controller_Post_Backend_Post extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('post');

        $this->getTemplate()->setCurrentMenuItemKey('post/post')
            ->setPageTitle('Manage Posts');
    }

    protected function _getFilterConfig($filter_value = null)
    {
        $filter_config = [
            'status' => [
                'title' => 'Status',
                'type' => 'radio',
                'data' => [
                    '1' => 'Publish',
                    '0' => 'Don\'t publish'
                ],
                'field' => 'published_flag'
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp',
                'prefix' => 'Added date'
            ],
            'mdate' => [
                'title' => 'Modified date',
                'type' => 'daterange',
                'field' => 'modified_timestamp',
                'prefix' => 'Modified date'
            ]
        ];

        if ($filter_value !== null) {
            if (!is_array($filter_value)) {
                $filter_value = [];
            }

            foreach ($filter_config as $k => $v) {
                unset($v['field']);

                if (isset($filter_value[$k])) {
                    $v['value'] = $filter_value[$k];
                }

                $filter_config[$k] = $v;
            }
        }

        return $filter_config;
    }

    public function actionSearch()
    {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
            'post/post/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
        ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_Post_Post_Collection $collection): void
    {
        $search = OSC::sessionGet('post/post/search');

        if ($search) {
            $keywords = trim($search['keywords']);

            $condition = [];
            $params = [];
            if (!empty($keywords)) {
                $condition_search = [
                    "title LIKE :keywords",
                    "slug LIKE :keywords",
                    "post_id LIKE :keywords",
                ];

                $condition[] = '(' . implode(' OR ', $condition_search) . ')';
                $params = array_merge($params, [
                    'keywords' => '%' . $keywords . '%'
                ]);
            }
            $filter_config = $this->_getFilterConfig();
            $filter_value = $search['filter_value'];

            if (count($filter_value) > 0) {
                foreach ($filter_value as $key => $value) {
                    if (!isset($filter_config[$key])) {
                        continue;
                    }

                    if (is_array($value) && count($value) == 1) {
                        $value = $value[0];
                    }

                    if (is_array($value)) {
                        foreach ($value as $v) {
                            $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                            $params = array_merge($params, [
                                $filter_config[$key]['field'] => $v
                            ]);
                        }
                    } else if ($filter_config[$key]['type'] == 'daterange') {
                        preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $value, $matches);

                        $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                        if ($matches[5]) {
                            $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                        } else {
                            $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                        }

                        $condition[] = "(" . $filter_config[$key]['field'] . " >= :start_timestamp AND " . $filter_config[$key]['field'] . " <= :end_timestamp)";
                        $params = array_merge($params, [
                            "start_timestamp" => $start_timestamp,
                            "end_timestamp" => $end_timestamp,
                        ]);
                    } else {
                        $condition[] = $filter_config[$key]['field'] . " = :" . $filter_config[$key]['field'];
                        $params = array_merge($params, [
                            $filter_config[$key]['field'] => $value
                        ]);
                    }
                }
            }

            $condition = [
                'condition' => implode(' AND ', $condition),
                'params' => $params
            ];

            $collection->setCondition($condition);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $collection = OSC::model('post/post')->getCollection();

        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->sort('post_id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $authors = OSC::model('post/author')->getCollection()
            ->addField('author_id', 'name')
            ->load()
            ->toArray();
        $author_convert = [];
        foreach ($authors as $author) {
            $author_convert[$author['author_id']] = $author['name'];
        }

        foreach ($collection as $coll) {
            $coll->data['author'] = $author_convert[$coll->data['author_id']];
            if ($coll->data['added_timestamp'] == $coll->data['modified_timestamp']) {
                $coll->data['modified_timestamp'] = null;
            }
        }

        $this->getTemplate()->addBreadcrumb(['post', 'Manage Posts']);
        $this->output($this->getTemplate()
            ->build('post/list',
                [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
                ]
            )
        );
    }

    public function actionPost()
    {
        $id = intval($this->_request->get('id'));

        $this->checkPermission('post/post/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Post_Post */
        $model = OSC::model('post/post');

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(['post', 'Edit Post']);
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Post is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(['post', 'Create Post']);
        }

        if ($this->_request->get('submit_form')){
            $data = [];
            if ($this->_request->get('modified_date') != null) {
                $date = explode("/", $this->_request->get('modified_date'));
                $time = explode(":", $this->_request->get('modified_time'));
                $data['modified_timestamp'] = mktime(intval($time[0]), intval($time[1]), 0, intval($date[1]), intval($date[0]), intval($date[2]));
            }

            $data['title'] = $this->_request->get('title');
            $data['priority'] = intval($this->_request->get('priority'));
            $data['description'] = $this->_request->get('description');
            $data['content'] = $this->_request->getRaw('content');
            $data['image'] = $this->_request->get('image');
            $data['footer_banner_url'] = trim($this->_request->get('footer_banner_url'));
            $data['published_flag'] = 0;
            $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
            if (!$data['slug']) {
                $data['slug'] = OSC::core('string')->cleanAliasKey($data['title']);
            }
            if ($this->_request->get('author_id') != "0") {
                $data['author_id'] = intval($this->_request->get('author_id'));
            }

            $data['footer_banner_image'] = [
                'pc' => OSC::helper('post/post')->saveFooterBannerOnS3($this->_request->get('pc_footer_banner') ?? '', $model->data['footer_banner_image']['pc']),
                'mobile' => OSC::helper('post/post')->saveFooterBannerOnS3($this->_request->get('mobile_footer_banner') ?? '', $model->data['footer_banner_image']['mobile'])
            ];

            $image_to_rmv = '';
            if ($data['image'] != $model->data['image']) {
                if (!$data['image']) {
                    $data['image'] = '';
                    $image_to_rmv = $model->data['image'];
                } else {
                    $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($data['image']);
                    if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                        $data['image'] = $model->data['image'];
                    } else {
                        $filename = 'post/' . str_replace('post.', '', $data['image']);
                        $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);
                        try {
                            OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
                            $data['image'] = $filename;
                        } catch (Exception $ex) {
                            $data['image'] = $model->data['image'];
                        }
                    }
                }
            }

            $publish_mode = intval($this->_request->get('publish_mode'));

            // set meta seo post
            $seo_title = trim($this->_request->get('seo-title'));
            $seo_description = trim($this->_request->get('seo-description'));
            $seo_keyword = trim($this->_request->get('seo-keyword'));
            $seo_image = $this->_request->get('seo-image');

            $data['meta_tags'] = [
                'title' => $seo_title,
                'description' => $seo_description,
                'keywords' => $seo_keyword,
                'image' => $seo_image
            ];

            $meta_image = OSC::helper('backend/backend/images/common')->saveMetaImage($data['meta_tags']['image'], $model->data['meta_tags']['image'], 'meta/post/', 'post');

            if ($meta_image['data_meta_image']) {
                $data['meta_tags']['image'] = $meta_image['data_meta_image'];
            }

            if ($publish_mode === 1) {
                $data['published_flag'] = 1;
            }

            try {
                if (!OSC::isUrl($data['footer_banner_url']) && !empty($data['footer_banner_url'])) {
                    throw new Exception('Footer banner url is invalid');
                }

                $model->setData($data)->save();

                $data['slug'] = OSC::helper('alias/common')->renameSlugDuplicate($seo_title, $data['slug'], $model->getId(), 'post');
                try {
                    OSC::helper('alias/common')->save($data['slug'], 'post', $model->getId());
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

                $model->setData(['slug' => $data['slug']])->save();

                if (count($this->_request->get('collection_ids', [])) === 0) {
                    throw new Exception('Please select least a collection');
                }

                $DB = OSC::core('database')->getWriteAdapter();
                $DB->begin();

                try {
                    if ($id > 0) {
                        OSC::model('post/postCollectionRel')->getCollection()->addCondition('post_id', $id)->delete();
                    }

                    foreach ($this->_request->get('collection_ids', []) as $collection_id) {
                        OSC::model('post/postCollectionRel')->setData([
                            'collection_id' => $collection_id,
                            'post_id' => $model->getId(),
                        ])->save();
                    }
                    $DB->commit();
                } catch (Exception $e) {
                    $DB->rollback();
                }

                if ($image_to_rmv) {
                    unlink(OSC_Storage::getStoragePath($image_to_rmv));
                }

                if ($meta_image['image_to_rm'] != null) {
                    unlink(OSC_Storage::getStoragePath($meta_image['image_to_rm']));
                }

                if ($id > 0) {
                    $message = 'Post #' . $model->getId() . ' updated';
                } else {
                    $message = 'Post [#' . $model->getId() . '] "' . $model->data['title'] . '" added';
                }

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirect($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, array('id' => $model->getId())));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $selected_collections = OSC::model('post/postCollectionRel')->getCollection()->addField('collection_id')->addCondition('post_id', $id)->load()->toArray();

        $authors = OSC::model('post/author')->getCollection()->addField('author_id', 'name')->load();
        $selected_collection_ids = array_map(function ($item) {
            return intval($item['collection_id']);
        }, $selected_collections);

        $all_collection = OSC::helper('post/collection')->getAllCollection();
        if ($model->data['added_timestamp'] == $model->data['modified_timestamp']) {
            $model->data['modified_timestamp'] = null;
        }

        $output_html = $this->getTemplate()->build('post/postForm', array(
            'selected_collection_ids' => $selected_collection_ids,
            'all_collection' => $all_collection,
            'form_title' => $model->getId() > 0 ? ('Edit post #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new post',
            'model' => $model,
            'authors' => $authors,
        ));

        $this->output($output_html);
    }

    public function actionDelete()
    {
        $this->checkPermission('post/post/delete');
        $id = intval($this->_request->get('id'));
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        if ($id > 0) {
            /* @var $model Model_Post_Post */
            $model = OSC::model('post/post');

            $DB->begin();
            $locked_key = OSC::makeUniqid();
            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                $model->load($id);
                $model->delete();
                OSC::model('post/postCollectionRel')->getCollection()->addCondition('post_id', $id)->load()->delete();
                $DB->commit();
                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();
                OSC_Database_Model::unlockPreLoadedModel($locked_key);
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }

            $this->addMessage('Deleted the post #' . $id . ' [' . $model->data['title'] . ']');
        }

        static::redirect($this->getUrl('list'));
    }

    public function actionBulkDelete()
    {
        $this->checkPermission('post/post/delete/bulk');

        $ids = $this->_request->get('ids');
        if (!is_array($ids) || !$ids) {
            $ids = [];
        }
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $posts = OSC::model('post/post')->getCollection();

            if (count($ids) < 1) {
                throw new Exception('Please select least a post to delete');
            }

            $ids = array_map(function ($id) {
                return intval($id);
            }, $ids);

            $ids = array_filter($ids, function ($id) {
                return $id > 0;
            });

            $posts->load($ids)->delete();
            OSC::model('post/postCollectionRel')->getCollection()->addCondition('post_id', $ids, OSC_Database::OPERATOR_IN)->load()->delete();
            $DB->commit();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Delete success posts selected']);
    }

    public function actionUploadImage()
    {
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
            if ($ex->getCode() == 500) {
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

            $file_name = 'post.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

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

    public function actionTrackingPost() {
        try {
            $trackings = OSC::model('post/referer')->getCollection()->loadByPostId(intval($this->_request->get('post_id')));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getCode() == 404 ? 'Tracking Post not found' : $ex->getMessage());
            static::redirect($this->getUrl('*/*/list'));
        }

        $html = $this->getTemplate()->build('post/tracking', ['trackings' => $trackings]);

        $this->_ajaxResponse($html);
    }


}
