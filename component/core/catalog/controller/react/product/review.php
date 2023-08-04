<?php

class Controller_Catalog_React_Product_Review extends Abstract_Frontend_ReactApiController
{
    public function actionGetReviewFilter()
    {
        try {
            $product_id = intval($this->_request->get('product_id', 0));

            $this->apiOutputCaching(['product_id' => $product_id], 60 * 60 * 24);

            $review = OSC::model('catalog/product_review');
            $aggregate_review = $review->getAggregateReview($product_id);

            $this->sendSuccess([
                'show_all' => $aggregate_review['show_all'] ?? 0,
                'enabled_write_review' => intval(OSC::helper('core/setting')->get('catalog/product_review/enable_create')) === 1,
                'list_filter_review' => [
                    '5' => $aggregate_review['total_5_star'] ?? 0,
                    '4' => $aggregate_review['total_4_star'] ?? 0,
                    '3' => $aggregate_review['total_3_star'] ?? 0,
                    '2' => $aggregate_review['total_2_star'] ?? 0,
                    '1' => $aggregate_review['total_1_star'] ?? 0,
                ],
                'average_review_point' => $aggregate_review['avg_vote_value'] ?? 0,
                'total_review' => $aggregate_review['total_review'] ?? 0,
                'total_review_has_photo' => $aggregate_review['total_has_photo'] ?? 0,
                'total_review_has_comment' => $aggregate_review['total_has_comment'] ?? 0,
                'total_review_locale' => $aggregate_review['total_locale'] ?? 0,
            ]);
        } catch (Exception $exception) {
            $this->sendError($exception->getMessage(), $exception->getCode());
        }
    }

    public function actionGetReviewList()
    {
        /* @var $collection Model_Catalog_Product_Review_Collection */
        /* @var $product Model_Catalog_Product */
        $product_id = intval($this->_request->get('product_id', 0));
        $filter_option = $this->_request->get('filter_option', 'all');
        $filter_option = in_array($filter_option, ['star', 'has-comment', 'has-photo', 'all']) ? $filter_option : 'all';
        $filter_value = intval($this->_request->get('filter_value', 0));
        $filter_value = in_array($filter_value, [0, 1, 2, 3, 4, 5]) ? $filter_value : 0;
        $filter_locale = intval($this->_request->get('filter_locale', 0));
        $show_all_review = intval($this->_request->get('show_all', 1));

        $page = intval($this->_request->get('page', 1));
        $page = max($page, 1);
        $size = intval($this->_request->get('size', 10));
        $size = $size > 0 && $size <= 20 ? $size : 10;

        $cache_key = [
            'filter_option' => $filter_option,
            'filter_value' => $filter_value,
            'filter_locale' => $filter_locale,
            'page' => $page,
            'size' => $size,
            'product_id' => $product_id
        ];

        $this->apiOutputCaching($cache_key, 60 * 60 * 24);

        $collection = OSC::model('catalog/product_review')->getCollection();

        switch ($filter_option) {
            case 'star':
                $collection->addCondition('vote_value', $filter_value, OSC_Database::OPERATOR_EQUAL);
                break;
            case 'has-comment':
                $collection->addCondition('has_comment', 1, OSC_Database::OPERATOR_EQUAL);
                break;
            case 'has-photo':
                $collection->addCondition('has_photo', 1, OSC_Database::OPERATOR_EQUAL);
                break;
            default:
                break;
        }

        $collection->addCondition('state', Model_Catalog_Product_Review::STATE_APPROVED)
            ->addCondition('parent_id', 0)
            ->addCondition('role', Model_Catalog_Product_Review::ROLE_NORMAL);

        if ($filter_locale) {
            $country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();

            if ($country_code) {
                $collection->addCondition('country_code', $country_code, OSC_Database::OPERATOR_EQUAL);
            }
        }

        $product_types = OSC::helper('catalog/product_review')->getListProductTypeByProductId($product_id);

        if (!empty($product_types) && !$show_all_review) {
            $collection->addCondition('product_type', $product_types, OSC_Database::OPERATOR_IN);
        }

        $collection->sort('has_photo', OSC_Database::ORDER_DESC)
            ->sort('vote_value', OSC_Database::ORDER_DESC)
            ->sort('has_comment', OSC_Database::ORDER_DESC)
            ->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->setPageSize($size)
            ->setCurrentPage($page)
            ->load();

        $country_code = OSC::helper('core/common')->getCustomerCountryCodeCookie();

        $condition = $collection->getCondition();

        if ($country_code) {
            $condition['condition'] .= " AND `country_code` = '{$country_code}'";
        }

        $total_review_locale = OSC::model('catalog/product_review')
            ->getCollection()
            ->setCondition($condition)
            ->collectionLength();


        $this->sendSuccess([
            'show_all' => $show_all_review,
            'product_id' => $product_id,
            'collection' => OSC::helper('catalog/product_review')->renderProductReviewApi($collection),
            'page' => $page,
            'size' => $size,
            'totalPage' => $collection->getTotalPage(),
            'total_review_locale' => intval($total_review_locale),
            'total_review' => intval($collection->collectionLength())
        ]);
    }

    public function actionGetMetaDataReviewPage()
    {
        $title = OSC::helper('core/setting')->get('review/title') ?: 'Review';
        $current_lang_key = OSC::core('language')->getCurrentLanguageKey();

        $this->sendSuccess([
            'title' => $title,
            'meta_data' => [
                'title' => OSC::helper('core/setting')->get('review/meta_title') ?: $title,
                'canonical' => OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/reviews',
                'description' => OSC::helper('core/setting')->get('review/meta_description') ?: '',
                'image' => OSC::helper('catalog/product_review')->getReviewMetaImage(),
            ]
        ]);
    }

    public function actionGetReviewDetail()
    {
        $review_id = intval($this->_request->get('id'));
        $review = OSC::model('catalog/product_review');

        try {
            $review->load($review_id);
        } catch (Exception $ex) {
            $this->sendError('Review is not exist', $this::CODE_NOT_FOUND);
        }

        if (!$review->isApproved()) {
            $this->sendError('Review is not exist', $this::CODE_NOT_FOUND);
        }

        $product = null;
        try {
            $product = $review->getProduct();
        } catch (Exception $exception) {

        }

        $meta_title = $review->data['customer_name'] . ' | Review product ';
        $image_url = '';
        $product_url = '';
        if ($product) {
            $meta_title .= $product->data['title'];
            $image_url = $product->getFeaturedImageUrl();
            if ($image_url) {
                $image_url = OSC::helper('core/image')->imageOptimize($image_url, 400, 400, true);
            }
            $product_url = $product->getDetailUrl();
        }

        $this->sendSuccess([
            'review' => [
                'id' => $review->data['record_id'],
                'vote_value' => $review->data['vote_value'],
                'review' => $review->data['review'],
                'added_timestamp' => $review->data['added_timestamp'],
                'list_image' => $review->getListImage(),
                'customer' => [
                    'id' => $review->data['customer_id'],
                    'name' => $review->data['customer_name'],
                    'email' => $review->data['customer_email'],
                ],
                'product' => [
                    'id' => $review->data['product_id'],
                    'url' => $product_url,
                    'avatar' => $image_url,
                    'title' => $product ? $product->data['title'] : '',
                ],
            ],
            'meta_data' => [
                'canonical' => $review->getDetailUrl(),
                'url' => $review->getDetailUrl(),
                'seo_title' => $meta_title,
                'seo_image' => OSC::helper('catalog/product_review')->getReviewMetaImage(),
                'seo_keywords' => '',
                'seo_description' => $review->data['review']
            ],
        ]);
    }

    public function actionGetReviewPhoto()
    {
        $filter_option = $this->_request->get('filter_option', 'all');
        $filter_option = in_array($filter_option, ['star', 'has-comment', 'has-photo', 'all']) ? $filter_option : 'all';
        $filter_value = intval($this->_request->get('filter_value', 0));
        $filter_value = in_array($filter_value, [0, 1, 2, 3, 4, 5]) ? $filter_value : 0;

        $page = intval($this->_request->get('page', 1));
        $page = $page > 1 ? $page : 1;
        $size = intval($this->_request->get('size', 10));
        $size = $size > 0 && $size <= 20 ? $size : 10;

        $this->apiOutputCaching([
            'filter_option' => $filter_option,
            'filter_value' => $filter_value,
            'page' => $page,
            'size' => $size
        ], 0);

        $review = OSC::model('catalog/product_review');
        $list_slide_review_image = $review->getSlideImage(['filter_option' => $filter_option, 'filter_value' => $filter_value], $page, $size);

        $this->sendSuccess([
            'list_slide_review_image' => $list_slide_review_image,
            'page' => $page,
            'size' => $size,
            'total' => count($list_slide_review_image)
        ]);
    }

    public function actionReviewWrite()
    {
        try {
            $request = $this->_getReviewRequest();
            $result['review_enable_discount'] = Model_Catalog_Product_Review_Request::SETT_DISCOUNT_TYPE != 'none' && Model_Catalog_Product_Review_Request::SETT_DISCOUNT_VALUE > 0;
            $result['review_code_percentage'] = min(100, abs(intval(OSC::helper('core/setting')->get('catalog/product_review/review_code_percentage')))) . '%';
            $result['review_data'] = $request->data;
            $this->sendSuccess($result);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    protected function _getReviewRequest()
    {
        $request_code = trim($this->_request->get('code'));

        if (!$request_code) {
            throw new Exception('Request code is incorrect');
        }

        /* @var $request Model_Catalog_Product_Review_Request */

        $request = OSC::model('catalog/product_review_request')->loadByUkey($request_code);

        $product = $request->getProduct();

        if (!($product instanceof Model_Catalog_Product)) {
            throw new Exception('The product is not exist or deleted');
        }

        return $request;
    }

    protected function _processPostImages($review, $images)
    {
        $image_collection = OSC::model('catalog/product_review_image')->getCollection();
        foreach ($images as $image_tmp_name) {
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
                    'filename' => $filename
                ])->save();

                $image_collection->addItem($image_model);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }
    }

    public function actionReviewSave()
    {
        try {
            $request = $this->_getReviewRequest();

            $review = OSC::model('catalog/product_review');

            $images = $this->_request->get('images');

            $images = !empty($images) ? (is_array($images) ? $images : [$images]) : [];

            if (!is_array($images)) {
                $images = [];
            }

            $review_content = $this->_request->get('review');

            $black_list_keywords = OSC::helper('core/setting')->get('catalog/product_review/review_black_list');
            $is_sensitive = false;
            if ($black_list_keywords) {
                foreach (explode(',', $black_list_keywords) as $black_list_keyword) {
                    if (strpos($review_content, trim($black_list_keyword)) !== false) {
                        $is_sensitive = true;
                        break;
                    }
                }
            }

            $review->setData([
                'order_id' => $request->data['order_id'],
                'order_item_id' => $request->data['order_item_id'],
                'product_id' => $request->data['product_id'],
                'variant_id' => $request->data['variant_id'],
                'product_type_variant_id' => $request->data['product_type_variant_id'],
                'product_type' => $request->data['product_type'],
                'country_code' => $request->data['country_code'],
                'customer_id' => $request->data['customer_id'],
                'customer_name' => $request->data['customer_name'],
                'customer_email' => $request->data['customer_email'],
                'vote_value' => $this->_request->get('vote'),
                'parent_id' => 0,
                'review' => $review_content,
                'has_photo' => !empty($images) ? 1 : 0,
                'state' => $is_sensitive ? Model_Catalog_Product_Review::STATE_PENDING : Model_Catalog_Product_Review::STATE_APPROVED
            ])->save();

            if ($is_sensitive) {
                $list_email = trim(OSC::helper('core/setting')->get('catalog/product_review/list_email_warning_black_list'));
                $list_email = preg_split('/\n|\||,/is', $list_email);

                if (!empty($list_email) && is_array($list_email)) {
                    $list_email = array_filter($list_email);
                    $list_email = array_map(function ($item) {
                        try {
                            $item = trim($item);
                            OSC::core('validate')->validEmail($item);
                            return $item;
                        } catch (Exception $exception) {
                            return null;
                        }
                    }, $list_email);
                }

                $list_email = !empty($list_email) ? array_filter($list_email) : [];

                if (!empty($list_email)) {
                    $text_content = <<<EOF
Review contains sensitive keywords, please go to backend review list to approve
EOF;
                    foreach ($list_email as $item) {
                        try {
                            $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

                            if ($klaviyo_api_key != '') {
                                OSC::helper('klaviyo/common')->create([
                                    'token' => $klaviyo_api_key,
                                    'event' => 'System office',
                                    'customer_properties' => [
                                        '$email' => $item
                                    ],
                                    'properties' => [
                                        'receiver_name' => $item,
                                        'receiver_email' => $item,
                                        'title' => 'Review contains sensitive keywords',
                                        'message' => $text_content
                                    ]
                                ]);

                            }

                            $skip_amazon = intval(OSC::helper('core/setting')->get('tracking/klaviyo/skip_amazon'));
                            if ($skip_amazon != 1) {
                                OSC::helper('postOffice/email')->create([
                                    'priority' => 100,
                                    'subject' => 'Review contains sensitive keywords',
                                    'receiver_email' => $item,
                                    'receiver_name' => $item,
                                    'text_content' => $text_content,
                                ]);
                            }
                        } catch (Exception $exception) {

                        }
                    }
                }
            }

            //Process save image to osc_catalog_product_review_image
            $this->_processPostImages($review, $images);

            $discount_percent = min(100, abs(intval(OSC::helper('core/setting')->get('catalog/product_review/review_code_percentage'))));
            //Check if review not violate sensitive keyword, has photo & content will return a discount_code
            if (Model_Catalog_Product_Review_Request::SETT_DISCOUNT_TYPE != 'none' && $discount_percent > 0 && !$is_sensitive && !empty($images) && strlen($review_content) > 0) {
                $discount_code = OSC::model('catalog/discount_code')->setData([
                    'auto_generated' => 1,
                    'discount_code' => OSC::helper('catalog/common')->genCodeUkey(),
                    'discount_type' => 'percent',
                    'discount_value' => $discount_percent,
                    'usage_limit' => 1,
                    'deactive_timestamp' => time() + (60 * 60 * 24 * 7 * 3),
                    'note' => 'Mail Review'
                ])->save();

                try {
                    OSC::helper('catalog/product_review')->sendMailCustomerReviewed($request, $discount_code);
                } catch (Exception $ex) {

                }

                $discount_code = [
                    'code' => $discount_code->data['discount_code'],
                    'value' => $discount_code->data['discount_type'] == 'percent' ? ($discount_code->data['discount_value'] . '%') : OSC::helper('catalog/common')->formatPriceByInteger(intval($discount_code->data['discount_value'])),
                    'expire_date' => date('F d, Y, h:i A', $discount_code->data['deactive_timestamp']),
                    'expire_timestamp' => $discount_code->data['deactive_timestamp']
                ];
            } else {
                $discount_code = null;
            }

            try {
                $request->delete();
            } catch (Exception $ex) {

            }

            $this->sendSuccess(['discount_code' => $discount_code]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionProductDetailReviewSave()
    {
        try {
            $review = OSC::model('catalog/product_review');

            $images = $this->_request->get('images');
            if (!is_array($images)) {
                $images = [];
            }

            $customer_name = implode(' ', [trim($this->_request->get('first_name')), trim($this->_request->get('last_name'))]);

            //Save review to DB
            $review->setData([
                'product_id' => $this->_request->get('product_id'),
                'vote_value' => $this->_request->get('vote'),
                'review' => $this->_request->get('review'),
                'has_photo' => !empty($images) ? 1 : 0,
                'customer_name' => $customer_name,
                'customer_email' => $this->_request->get('email'),
                'state' => Model_Catalog_Product_Review::STATE_PENDING
            ])->save();

            //Process save image to osc_catalog_product_review_image
            $this->_processPostImages($review, $images);

            $this->sendSuccess([]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    public function actionLoadReviewImageDetail()
    {
        try {
            $image_id = $this->_request->get('image_id');
            $filter_option = $this->_request->get('filter_option', 'all');
            $filter_option = in_array($filter_option, ['star', 'has-comment', 'has-photo', 'all']) ? $filter_option : 'all';
            $filter_value = intval($this->_request->get('filter_value', 0));
            $filter_value = in_array($filter_value, [0, 1, 2, 3, 4, 5]) ? $filter_value : 0;

            $image_model = OSC::model('catalog/product_review_image');
            $current_image = $image_model->load($image_id);

            $this->apiOutputCaching([
                'image_id' => $image_id,
                'filter_option' => $filter_option,
                'filter_value' => $filter_value,
            ], 0);

            try {
                $review = OSC::model('catalog/product_review')->load($current_image->data['review_id']);
                $review_data = $review->getRootReview()->toArray();
                $review_data['image_id'] = $image_id;
                $review_data['review_id'] = $review->getId();
                $review_data['product_title'] = $review->getProductTitle();
                $review_data['review_summary'] = OSC::helper('catalog/product')->getSomeWords($review->data['review'], 50);
                $review_data['product_detail_url'] = $review->getProductDetailUrl();
                $review_data['product_avatar'] = $review->getProductAvatar();

            } catch (Exception $exception) {
                throw new Exception($exception->getMessage());
            }

            $review_model = OSC::model('catalog/product_review');
            $next_image = $review_model->getReviewImage([
                'get_next' => 1,
                'current_image' => $image_id,
                'filter_option' => $filter_option,
                'filter_value' => $filter_value
            ], 1, 1);

            $prev_image = $review_model->getReviewImage([
                'get_prev' => 1,
                'current_image' => $image_id,
                'filter_option' => $filter_option,
                'filter_value' => $filter_value
            ], 1, 1);

            $this->sendSuccess([
                'review' => $review_data,
                'image_url' => OSC::wrapCDN($current_image->getUrl()),
                'next_image_id' => isset($next_image[0]['image_id']) && !empty($next_image[0]['image_id']) ? $next_image[0]['image_id'] : 0,
                'prev_image_id' => isset($prev_image[0]['image_id']) && !empty($prev_image[0]['image_id']) ? $prev_image[0]['image_id'] : 0
            ]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    public function actionReplyReviewSave()
    {
        try {
            $request = $this->_getReviewRequest();

            $review = $request->getReview();

            if (!($review instanceof Model_Catalog_Product_Review)) {
                throw new Exception('The review is not exist or deleted');
            }

            $review_model = OSC::model('catalog/product_review');

            $images = $this->_request->get('images');
            if (!is_array($images)) {
                $images = [];
            }

            //Save review to DB
            $review_model->setData([
                'order_id' => $review->data['order_id'],
                'product_id' => $review->data['product_id'],
                'customer_id' => $review->data['customer_id'],
                'customer_name' => $review->data['customer_name'],
                'customer_email' => $review->data['customer_email'],
                'vote_value' => $review->data['vote_value'],
                'parent_id' => $review->getId(),
                'review' => $this->_request->get('review'),
                'has_photo' => !empty($images) ? 1 : 0,
                'state' => OSC::helper('core/setting')->get('catalog/product_review/approve_review_email') == 1 ? Model_Catalog_Product_Review::STATE_APPROVED : Model_Catalog_Product_Review::STATE_PENDING
            ])->save();

            //Process save image to osc_catalog_product_review_image
            $this->_processPostImages($review_model, $images);

            try {
                $request->delete();
            } catch (Exception $ex) {
            }

            $this->sendSuccess([]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    public function actionRequirePhoto()
    {
        try {
            $require_photo = OSC::helper('core/setting')->get('catalog/product_review/require_photo');

            $result = intval($require_photo) === 1 ? 1 : 0;

            $this->sendSuccess(['require_photo' => $result]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage());
        }
    }

    public function actionHelpful()
    {
        /* @var $DB OSC_Database */
        try {
            $review_id = $this->_request->get('review_id');
            $helpful_value = OSC::helper('catalog/product_review')->updateHelpfull($review_id);

            $this->sendSuccess([
                'helpful' => intval($helpful_value)
            ]);

        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}