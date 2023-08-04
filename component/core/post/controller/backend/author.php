<?php

class Controller_Post_Backend_Author extends Abstract_Backend_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->checkPermission('post/author');

        $this->getTemplate()->setCurrentMenuItemKey('post/author')
            ->setPageTitle('Manage Post Authors');
    }

    public function actionIndex()
    {
        $this->forward('*/*/list');
    }

    public function actionList()
    {
        $collections = OSC::model('post/author')->getCollection();

        $this->getTemplate()->addBreadcrumb(['post', 'Manage Post Authors']);

        $collections->sort('author_id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->load();

        $this->output($this->getTemplate()
            ->build('post/author/list',
                [
                    'collections' => $collections
                ]
            )
        );
    }

    public function actionPost()
    {
        $id = intval($this->_request->get('id'));

        $this->checkPermission('post/author/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Post_Post */
        $model = OSC::model('post/author');
        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(['post', 'Edit AuthorProfile']);
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Post AuthorProfile is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()->addBreadcrumb(['post', 'Create AuthorProfile']);
        }
        if ($this->_request->get('name')) {
            $data = [];

            $data['name'] = trim($this->_request->get('name'));
            $data['description'] = trim($this->_request->getRaw('description'));
            $data['avatar'] = $this->_request->get('avatar');
            $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
            $data['added_timestamp'] = time();
            $data['modified_timestamp'] = time();
            if (!$data['slug']) {
                $data['slug'] = OSC::core('string')->cleanAliasKey($data['name']);
            }

            if ($model->checkExistSlug($data['slug'])){
                $this->addErrorMessage('Slug "'.$data['slug'].'" has existed already. Please change name/slug');
            } else {
                $image_to_rmv = '';
                if ($data['avatar'] != $model->data['avatar']) {
                    if (!$data['avatar']) {
                        $data['avatar'] = '';
                        $image_to_rmv = $model->data['avatar'];
                    } else {
                        $tmp_image = OSC::core('aws_s3')->getTmpFilePath($data['avatar']);
                        if (!OSC::core('aws_s3')->doesObjectExist($tmp_image)) {
                            $data['avatar'] = $model->data['avatar'];
                        } else {
                            $filename = 'author/' . str_replace('author.', '', $data['avatar']);
                            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

                            try {
                                OSC::core('aws_s3')->copy($tmp_image, $storage_filename_s3);

                                $data['avatar'] = $filename;
                            } catch (Exception $ex) {
                                $data['avatar'] = $model->data['avatar'];
                            }
                        }
                    }
                }

                // set meta seo post
                $data['meta_tags'] = [
                    'title' => trim($this->_request->get('seo-title')),
                    'description' => trim($this->_request->get('seo-description')),
                    'keywords' => trim($this->_request->get('seo-keyword')),
                    'image' => $this->_request->get('seo-image') ?? ""
                ];

                $meta_image = OSC::helper('backend/backend/images/common')
                    ->saveMetaImage($data['meta_tags']['image'], $model->data['meta_tags']['image'], 'meta/post/author/', 'author');

                if ($meta_image['data_meta_image']) {
                    $data['meta_tags']['image'] = $meta_image['data_meta_image'];
                }

                try {
                    $model->setData($data)->save();

                    if ($image_to_rmv) {
                        unlink(OSC_Storage::getStoragePath($image_to_rmv));
                    }
                    if ($id > 0) {
                        $message = 'AuthorProfile #' . $model->getId() . ' updated';
                    } else {
                        $message = 'AuthorProfile [#' . $model->getId() . '] "' . $model->data['name'] . '" added';
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
        }
        $this->output($this->getTemplate()->build('post/author/postForm', [
            'form_title' => $model->getId() > 0 ? ('Edit author #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new post author',
            'model' => $model,
            'form_data' => isset($data) ? $data : null
        ]));


    }

    public function actionDelete()
    {
        $this->checkPermission('post/author/delete');
        $id = intval($this->_request->get('id'));

        if ( $id > 0 ){
            $model = OSC::model('post/author');
            try {
                $model->load($id);
                $posts = OSC::model('post/post')->getCollection()->addField('post_id')->addCondition('author_id', $id)->load();

                if ($posts->length() > 0) {
                    throw new Exception('Please delete/change the post of the author before deleting the author');
                }

                $model->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    $this->addErrorMessage($ex->getMessage());
                    static::redirect($this->getUrl('list'));
                }
            }
        }
        static::redirect($this->getUrl('list'));
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

            $file_name = 'author.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tmp_url = $tmp_url ?? OSC_Storage::tmpGetFileUrl($file_name);

        $this->_ajaxResponse(array(
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height
        ));
    }

}
