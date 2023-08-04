<?php

class Controller_Catalog_Backend_Review extends Abstract_Catalog_Controller_Backend {

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog/super|catalog/review');

        $this->getTemplate()
            ->setPageTitle('Manage Reviews')
            ->setCurrentMenuItemKey('catalog/review');
    }

    public function actionIndex() {
        $this->forward('*/*/list');
    }

    protected function _getFilterConfig($filter_value = null) {
        $filter_config = [
            'status' => [
                'title' => 'Status',
                'type' => 'checkbox',
                'data' => [
                    '0' => 'Hidden',
                    '1' => 'Pending',
                    '2' => 'Approved'
                ],
                'field' => 'state'
            ],
            'vote' => [
                'title' => 'Rating',
                'type' => 'checkbox',
                'data' => [
                    '1' => '1 star',
                    '2' => '2 star',
                    '3' => '3 star',
                    '4' => '4 star',
                    '5' => '5 star'
                ],
                'field' => 'vote_value'
            ],
            'has_comment' => [
                'title' => 'Have comment',
                'type' => 'checkbox',
                'data' => [
                    '1' => 'Have comment',
                    '0' => 'Don\'t Have comment'
                ],
                'field' => 'has_comment'
            ],
            'has_photo' => [
                'title' => 'Have picutres',
                'type' => 'checkbox',
                'data' => [
                    '1' => 'Have picutres',
                    '0' => 'Don\'t Have picutres'
                ],
                'field' => 'has_photo'
            ],
            'date' => [
                'title' => 'Added date',
                'type' => 'daterange',
                'field' => 'added_timestamp'
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

    public function actionSearch() {
        $filter_value = $this->_request->get('filter');

        if (is_array($filter_value) && count($filter_value) > 0) {
            $filter_value = $this->_processFilterValue($this->_getFilterConfig(), $filter_value);
        } else {
            $filter_value = [];
        }

        OSC::sessionSet(
                'catalog/review/search', [
            'keywords' => $this->_request->get('keywords'),
            'filter_value' => $filter_value
                ], 60 * 60
        );

        static::redirect($this->getUrl('*/*/list', ['search' => 1]));
    }

    protected function _applyListCondition(Model_Catalog_Product_Review_Collection $collection): void {
        $search = OSC::sessionGet('catalog/review/search');

        if ($search) {
            /* @var $search_analyzer OSC_Search_Analyzer */
            $search_analyzer = OSC::core('search_analyzer');

            $condition = $search_analyzer->addKeyword('id', 'record_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                    ->addKeyword('order', 'order_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                    ->addKeyword('product', 'product_id', OSC_Search_Analyzer::TYPE_INT, true, true)
                    ->addKeyword('name', 'customer_name', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('email', 'customer_email', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->addKeyword('content', 'review', OSC_Search_Analyzer::TYPE_STRING, true, true)
                    ->parse($search['keywords']);

            $collection->setCondition($condition);

            $this->_applyFilter($collection, $this->_getFilterConfig(), $search['filter_value']);

            $collection->register('search_keywords', $search['keywords'])->register('search_filter', $search['filter_value']);
        }
    }

    public function actionList() {
        $collection = OSC::model('catalog/product_review')->getCollection();

        $this->getTemplate()->addBreadcrumb(array('star', 'Manage Reviews'));
        if ($this->_request->get('search')) {
            $this->_applyListCondition($collection);
        }

        $collection->addCondition('parent_id', 0, OSC_Database::OPERATOR_EQUAL)
            ->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output(
            $this->getTemplate()->build(
                'catalog/review/list', [
                    'collection' => $collection,
                    'search_keywords' => $collection->registry('search_keywords'),
                    'in_search' => $collection->registry('search_keywords') || (is_array($collection->registry('search_filter')) && count($collection->registry('search_filter')) > 0),
                    'filter_config' => $this->_getFilterConfig($collection->registry('search_filter'))
                ]
            )
        );
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));

        $this->checkPermission('catalog/super|catalog/review/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Catalog_Product_Review */
        $model = OSC::model('catalog/product_review');

        if ($id > 0) {
            $this->getTemplate()->addBreadcrumb(array('star', 'Edit Review'));
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Review is not exist' : $ex->getMessage());

                static::redirectLastListUrl($this->getUrl('list'));
            }
        } else {
            $this->getTemplate()
                ->addBreadcrumb(array('star', 'Create Review'));
        }

        if ($this->_request->get('save')) {
            $order_item_id = intval($this->_request->get('order_item_id'));
            $order_item = OSC::model('catalog/order_item')
                ->getCollection()
                ->addField('master_record_id', 'product_type', 'variant_id', 'product_type_variant_id')
                ->load([$order_item_id])
                ->first();
            
            $data = [
                'order_id' => $this->_request->get('order_id', 0),
                'order_item_id' => $order_item_id,
                'country_code' => $this->_request->get('country_code', null),
                'product_type' => $order_item->data['product_type'],
                'product_type_variant_id' => $order_item->data['product_type_variant_id'],
                'variant_id' => $order_item->data['variant_id'],
                'product_id' => $this->_request->get('product_id', null),
                'customer_id' => $this->_request->get('customer_id', null),
                'customer_name' => $this->_request->get('customer_name', null),
                'customer_email' => $this->_request->get('customer_email', null),
                'vote_value' => $this->_request->get('vote', 5),
                'review' => $this->_request->get('review'),
            ];

			$images = $this->_request->get('images');
			if (!is_array($images)) {
				$images = [];
			}

            $added_date = explode('/', strval($this->_request->get('added_date')));

            if (count($added_date) == 3) {
                $added_date = array_map(function($segment) {
                    return intval($segment);
                }, $added_date);

                if (checkdate($added_date[1], $added_date[0], $added_date[2])) {
                    $added_date[0] = str_pad($added_date[0], 2, '0', STR_PAD_LEFT);
                    $added_date[1] = str_pad($added_date[1], 2, '0', STR_PAD_LEFT);

                    if (implode('/', $added_date) != date('d/m/Y', $model->data['added_timestamp'])) {
                        $data['added_timestamp'] = mktime(0, 0, 0, $added_date[1], $added_date[0], $added_date[2]);
                    }
                }
            }

            try {
                $model->setData($data)->save();

                if ($id > 0) {
                    $message = 'Review #' . $model->getId() . ' has been updated';
                } else {
                    $message = 'Review [#' . $model->getId() . '] has been added';
                }

                $this->_processPostImages($model, $images);

                $this->addMessage($message);

                if (!$this->_request->get('continue')) {
                    static::redirectLastListUrl($this->getUrl('list'));
                } else {
                    static::redirect($this->getUrl(null, array('id' => $model->getId())));
                }
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('catalog/review/postForm', [
            'form_title' => $model->getId() > 0 ? ('Edit review #' . $model->getId()) : 'Add new review',
            'model' => $model
        ]);

        $this->output($output_html);
    }

    public function actionImageUpload() {
        $this->checkPermission('catalog/super|catalog/review/add|catalog/review/edit');
        
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
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(800);

            $width = $img_processor->getWidth();
            $height = $img_processor->getHeight();

            $file_name = 'catalog_review.' . $this->getAccount()->getId() . '.' . OSC::makeUniqid() . '.' . $extension;

            $tmp_url = OSC::core('aws_s3')->tmpSaveFile($img_processor, $file_name);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $tmp_url = $tmp_url ?? OSC_Storage::tmpGetFileUrl($file_name);

        $this->_ajaxResponse([
            'file' => $file_name,
            'url' => $tmp_url,
            'width' => $width,
            'height' => $height,
			'extension' => $extension
        ]);
    }

    protected function _processPostImages($review, $images) {
        $counter = 0;

        foreach ($images as $image_id => $item) {
            $images[$image_id] = array(
                'position' => ++$counter
            );
        }

        $image_collection = $review->getImages();

        $image_map = array();

        foreach ($image_collection as $image_model) {
            $image_id = $image_model->getId();

            if (!isset($images[$image_id])) {
                try {
                    $image_collection->removeItemByKey($image_id);
                    $image_model->delete();
                } catch (Exception $ex) {
                    $this->addErrorMessage($ex->getMessage());
                }
            } else {
                $image_new_data = $images[$image_id];

                if ($image_model->data['position'] != $image_new_data['position']) {
                    try {
                        $image_model->setData(array('position' => $image_new_data['position']))->save();
                    } catch (Exception $ex) {
                        $this->addErrorMessage($ex->getMessage());
                    }
                }

                $image_map[$image_id] = $image_id;
                unset($images[$image_id]);
            }
        }

        foreach ($images as $image_tmp_name => $image_data) {
            $tmp_image_path_s3 = OSC::core('aws_s3')->getTmpFilePath($image_tmp_name);
            if (!OSC::core('aws_s3')->doesObjectExist($tmp_image_path_s3)) {
                continue;
            }

            $filename = 'catalog/review/' . date('d.m.Y') . '/' . str_replace('catalog_review.', '', $image_tmp_name);
            $storage_filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

            try {
                OSC::core('aws_s3')->copy($tmp_image_path_s3, $storage_filename_s3);
            } catch (Exception $ex) {
                continue;
            }

            $image_model = $image_collection->getNullModel();

            try {
                $image_model->setData([
                    'review_id' => $review->getId(),
                    'position' => $image_data['position'],
                    'filename' => $filename
                ])->save();

                $image_collection->addItem($image_model);

                $image_map[$image_tmp_name] = $image_model->getId();
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        return $image_map;
    }

    public function actionSwitchState() {
        /* @var $model Model_Catalog_Product_Review */

        $this->checkPermission('catalog/super|catalog/review/approve');

        try {
            $id = intval($this->_request->get('id'));

            if ($id < 1) {
                throw new Exception('Review ID is empty');
            }

            $new_state = $this->_request->get('state');

            $model = OSC::model('catalog/product_review')->load($id);

            if ($model->data['state'] != $new_state) {
                $model->setData('state', $new_state)->save();
            }

            if ($this->_request->isAjax()) {
                $this->_ajaxResponse(['item' => $this->getTemplate()->build('catalog/review/item', ['review' => $model])]);
            }

            $this->addMessage('Review state is updated');
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getCode() == 404 ? 'Review is not exist' : $ex->getMessage());
        }

        static::redirectLastListUrl($this->getUrl('list'));
    }

    public function actionDelete() {
        /* @var $model Model_Catalog_Product_Review */

        $this->checkPermission('catalog/super|catalog/review/delete');

        $id = intval($this->_request->get('id'));

        if ($id > 0) {
            try {
                $model = OSC::model('catalog/product_review')->load($id);

                $model->delete();

                if ($model->data['photo_filename']) {
                    try {
                        OSC::core('aws_s3')->deleteStorageFile($model->data['photo_filename']);
                    } catch (Exception $ex) {
                        
                    }
                }

                $this->addMessage('Deleted the review #' . $model->getId() . ' of ' . $model->data['customer_name']);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionReply() {
        $this->getTemplate()
            ->addBreadcrumb(array('star', 'Reply Review'));
        $id = intval($this->_request->get('id'));

        $this->checkPermission('catalog/super|catalog/review/reply');

        /* @var $model Model_Catalog_Product_Review */
        $model = OSC::model('catalog/product_review');

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'Review is not exist' : $ex->getMessage());

                static::redirectLastListUrl($this->getUrl('list'));
            }
        }

        if ($this->_request->get('save')) {
            $images = $this->_request->get('images');
            if (!is_array($images)) {
                $images = [];
            }
            $parent_id = $this->_request->get('parent_id');

            $data = [
                'parent_id' => $parent_id,
                'role' => Model_Catalog_Product_Review::ROLE_ADMIN,
                'order_id' => $model->data['order_id'],
                'product_id' => $model->data['product_id'],
                'customer_name' => OSC::helper('core/setting')->get('theme/contact/name'),
                'customer_email' => OSC::helper('core/setting')->get('theme/contact/email'),
                'vote_value' => $model->data['vote_value'],
                'review' => $this->_request->get('review'),
                'state' => Model_Catalog_Product_Review::STATE_APPROVED,
                //Check if review has images => has_photo = 1
                'has_photo' => !empty($images) ? 1 : 0
            ];

            $added_date = explode('/', strval($this->_request->get('added_date')));

            if (count($added_date) == 3) {
                $added_date = array_map(function($segment) {
                    return intval($segment);
                }, $added_date);

                if (checkdate($added_date[1], $added_date[0], $added_date[2])) {
                    $added_date[0] = str_pad($added_date[0], 2, '0', STR_PAD_LEFT);
                    $added_date[1] = str_pad($added_date[1], 2, '0', STR_PAD_LEFT);

                    if (implode('/', $added_date) != date('d/m/Y', $model->data['added_timestamp'])) {
                        $data['added_timestamp'] = mktime(0, 0, 0, $added_date[1], $added_date[0], $added_date[2]);
                    }
                }
            }

            try {
                $model = OSC::model('catalog/product_review')->getNullModel();
                $model->setData($data)->save();

                $message = 'Review [#' . $model->getId() . '] has been added';

                $this->_processPostImages($model, $images);

                $this->addMessage($message);

                //If admin allow user to reply review, make a reply review request and email to customer
                $allow_customer_reply = $this->_request->get('allow_reply');
                if ($allow_customer_reply == 1) {
                    $parent_review = OSC::model('catalog/product_review')->load($parent_id);
                    //Make reply request
                    try {
                        $request = OSC::model('catalog/product_review_request')->setData([
                            'review_id' => $parent_review->getId(),
                            'order_id' => $parent_review->data['order_id'],
                            'product_id' => $parent_review->data['product_id'],
                            'customer_id' => $parent_review->data['customer_id'],
                            'customer_name' => $parent_review->data['customer_name'],
                            'customer_email' => $parent_review->data['customer_email'],
                        ])->save();

                        $email_content = <<<EOF
Your review has been replied,
Click the URL below to reply to our admin:
<a href="{OSC::helper('klaviyo/common')->addParamUrl($request->getReplyUrl())}">Reply</a>
EOF;
                        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

                        if ($klaviyo_api_key != '') {
                            OSC::helper('klaviyo/common')->create([
                                'token' => $klaviyo_api_key,
                                'event' => 'Review reply',
                                'customer_properties' => [
                                    '$email' => $parent_review->data['customer_email']
                                ],
                                'properties' => [
                                    'receiver_email' => $parent_review->data['customer_email'],
                                    'receiver_name' => $parent_review->data['customer_name'],
                                    'title' => 'Your review has been replied',
                                    'message' => implode('<br />', explode("\n", $email_content)),
                                    'text' => strip_tags($email_content, '<br>')
                                ]
                            ]);
                        }

                        $skip_amazon = intval(OSC::helper('core/setting')->get('tracking/klaviyo/skip_amazon'));
                        if ($skip_amazon != 1) {
                            OSC::helper('postOffice/email')->create([
                                'priority' => 1000,
                                'subject' => 'Your review has been replied',
                                'receiver_email' => $parent_review->data['customer_email'],
                                'receiver_name' => $parent_review->data['customer_name'],
                                'html_content' => implode('<br />', explode("\n", $email_content)),
                                'text_content' => strip_tags($email_content, '<br>')
                            ]);
                        }
                    } catch (Exception $exception) {
                        $this->addErrorMessage($exception->getMessage());
                    }
                }

                static::redirect($this->getUrl('*/*/list'));
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $output_html = $this->getTemplate()->build('catalog/review/reply', [
            'form_title' => 'Reply review',
            'model' => $model
        ]);

        $this->output($output_html);
    }
}