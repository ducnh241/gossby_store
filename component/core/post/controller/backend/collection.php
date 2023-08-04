<?php

class Controller_Post_Backend_Collection extends Abstract_Backend_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('post');

        $this->getTemplate()->setCurrentMenuItemKey('post/collection')
            ->setPageTitle('Manage Post Collections');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $collection = OSC::model('post/collection')->getCollection();
        $collection->sort('priority', OSC_Database::ORDER_DESC)->sort('added_timestamp', OSC_Database::ORDER_DESC)->load();

        $this->getTemplate()->addBreadcrumb(['post', 'Manage Post Collections']);

        $this->output($this->getTemplate()->build('post/collection/list', array('collection' => $collection)));
    }

    public function actionPost()
    {
        $id = intval($this->_request->get('id'));

        $this->checkPermission('post/collection/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Post_Post */
        $model = OSC::model('post/collection');

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(['post', 'Edit Post Collection']);
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Post collection is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(['post', 'Create Post Collection']);
        }

        if ($this->_request->get('title')) {
            $data = [];

            $data['title'] = $this->_request->get('title');
            $data['priority'] = intval($this->_request->get('priority', 0));
            $data['description'] = $this->_request->getRaw('description');
            $data['image'] = $this->_request->get('image');
            $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
            if (!$data['slug']) {
                $data['slug'] = OSC::core('string')->cleanAliasKey($data['title']);
            }

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
                        $filename = 'post/collection/' . str_replace('post_collection.', '', $data['image']);
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

            $meta_image = OSC::helper('backend/backend/images/common')->saveMetaImage($data['meta_tags']['image'], $model->data['meta_tags']['image'], 'meta/post/collection/', 'meta_post_collection');

            if ($meta_image['data_meta_image']) {
                $data['meta_tags']['image'] = $meta_image['data_meta_image'];
            }

            try {
                $model->setData($data)->save();
                $data['slug'] = OSC::helper('alias/common')->renameSlugDuplicate($seo_title, $data['slug'], $model->getId(), 'post/collection');

                try {
                    OSC::helper('alias/common')->save($data['slug'], 'post_collection', $model->getId());
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
                $model->setData(['slug' => $data['slug']])->save();

                if ($image_to_rmv) {
                    unlink(OSC_Storage::getStoragePath($image_to_rmv));
                }

                if ($meta_image['image_to_rm'] != null) {
                    unlink(OSC_Storage::getStoragePath($meta_image['image_to_rm']));
                }

                if ($id > 0) {
                    $message = 'Post collection #' . $model->getId() . ' updated';
                } else {
                    $message = 'Post collection [#' . $model->getId() . '] "' . $model->data['title'] . '" added';
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

        $output_html = $this->getTemplate()->build('post/collection/postForm', array(
            'form_title' => $model->getId() > 0 ? ('Edit post collection #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new post collection',
            'model' => $model,
            'form_data' => isset($data) ? $data : null
        ));

        $this->output($output_html);
    }

    public function actionDelete()
    {
        $this->checkPermission('post/collection/delete');
        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            /* @var $model Model_Post_Collection */
            $model = OSC::model('post/collection');

            try {
                $model->load($id);

                $posts = OSC::model('post/postCollectionRel')->getCollection()->addField('post_id')->addCondition('collection_id', $id)->load();
                if ($posts->length() > 0) {
                    throw new Exception('Please delete the post of the collection before deleting the collection');
                }

                $model->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }

            $this->addMessage('Deleted the post collection #' . $id . ' [' . $model->data['title'] . ']');
        }

        static::redirect($this->getUrl('list'));
    }

    public function actionBrowse()
    {
        /* @var $search OSC_Search_Adapter */
        $search = OSC::core('search')->getAdapter('backend');

        $page_size = intval($this->_request->get('page_size'));

        if ($page_size == 0) {
            $page_size = 25;
        } else if ($page_size < 5) {
            $page_size = 5;
        } else if ($page_size > 100) {
            $page_size = 100;
        }

        try {
            $search->setKeywords($this->_request->get('keywords'));
            $search->addFilter('module_key', 'post', OSC_Search::OPERATOR_EQUAL)
                ->addFilter('item_group', 'post_collection', OSC_Search::OPERATOR_EQUAL);

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $search->setOffset(($page - 1) * $page_size)->setPageSize($page_size);

            $result = $search->fetch(['allow_no_keywords', 'auto_fix_page']);

            $post_collections = [];

            if (count($result['docs']) > 0) {
                $post_collection_ids = array();

                foreach ($result['docs'] as $doc) {
                    $post_collection_ids[] = $doc['item_id'];
                }

                $collections = OSC::model('post/collection')->getCollection()->load($post_collection_ids);

                /* @var $post Model_Post_Post */
                foreach ($collections as $post_collection) {
                    $post_collections[] = array(
                        'id' => $post_collection->getId(),
                        'title' => $post_collection->data['title'],
                        'url' => $post_collection->getDetailUrl(),
                        'icon' => 'file-regular'
                    );
                }
            }

            $this->_ajaxResponse(array(
                'keywords' => $result['keywords'],
                'total' => $result['total_item'],
                'offset' => $result['offset'],
                'current_page' => $result['current_page'],
                'page_size' => $result['page_size'],
                'items' => $post_collections
            ));
        } catch (OSC_Search_Exception_Condition $e) {
            $this->_ajaxError($e->getMessage(), $e->getCode());
        }
    }
}

