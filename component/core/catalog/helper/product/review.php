<?php

class Helper_Catalog_Product_Review extends OSC_Object
{

    public function requestReviewAfterOrderCreated(Model_Catalog_Order $order)
    {
        if (OSC::helper('core/setting')->get('catalog/product_review/order_request_review') != 'purchase') {
            return;
        }
        $requests = [];
        foreach ($order->getLineItems() as $line_item) {
            try {
                if ($line_item->isCrossSellMode()) {
                    continue;
                }
                if (!($line_item->getProduct() instanceof Model_Catalog_Product) || $line_item->getProduct()->getId() < 1) {
                    continue;
                }

                $request = $this->createRequestReview($order, $line_item);

                $request->setProduct($line_item->getProduct())
                    ->setOrder($order);

                $requests[] = $request;
            } catch (Exception $ex) {
                if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        if (count($requests) < 1) {
            return;
        }

        try {
            $this->sendMailRequestReview($order, $requests);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function requestReviewAfterFulfilled(Model_Catalog_Order $order, $line_items = [])
    {
        if (OSC::helper('core/setting')->get('catalog/product_review/order_request_review') != 'fulfillment') {
            return;
        }

        /* @var $order Model_Catalog_Order */

        $requests = [];

        foreach ($line_items as $line_item_id => $info) {
            try {
                $line_item = $order->getLineItemByItemId($line_item_id);
                if ($line_item->isCrossSellMode()) {
                    continue;
                }
                $request = $this->createRequestReview($order, $line_item);

                $request->setProduct($line_item->getProduct())
                    ->setOrder($order);

                $requests[] = $request;
            } catch (Exception $ex) {

            }
        }

        if (count($requests) < 1) {
            return;
        }

        try {
            $this->sendMailRequestReview($order, $requests, 'fulfillment');
        } catch (Exception $ex) {

        }
    }

    public function requestReviewAfterPlaceOrder(Model_Catalog_Order $order)
    {
        if (OSC::helper('core/setting')->get('catalog/product_review/order_request_review') != 'purchase') {
            return;
        }

        /* @var $order Model_Catalog_Order */

        $requests = [];

        foreach ($order->getLineItems() as $line_item) {
            if ($line_item->isCrossSellMode()) {
                continue;
            }
            try {
                $request = $this->createRequestReview($order, $line_item);

                $request->setProduct($line_item->getProduct())
                    ->setOrder($order);

                $requests[] = $request;
            } catch (Exception $ex) {

            }
        }

        if (count($requests) < 1) {
            return;
        }

        try {
            $this->sendMailRequestReview($order, $requests);
        } catch (Exception $ex) {

        }
    }

    /**
     * @throws OSC_Database_Model_Exception
     */
    public function createRequestReview(Model_Catalog_Order $order, Model_Catalog_Order_Item $order_item)
    {
        return OSC::model('catalog/product_review_request')->setData([
            'order_id' => $order->getId(),
            'order_item_id' => $order_item->getId(),
            'product_id' => $order_item->data['product_id'],
            'variant_id' => $order_item->data['variant_id'],
            'product_type_variant_id' => $order_item->data['product_type_variant_id'],
            'product_type' => $order_item->data['product_type'],
            'country_code' => $order->data['shipping_country_code'],
            'customer_id' => $order->data['crm_customer_id'],
            'customer_name' => $order->getFullName(),
            'customer_email' => $order->data['email']
        ])->save();
    }

    public function sendMailRequestReview(Model_Catalog_Order $order, $requests, $type = 'purchase')
    {
        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();
        $klaviyo_enable_request_review = intval(OSC::helper('core/setting')->get('klaviyo/enable_request_review')) === 1;

        if ($klaviyo_api_key) {
            OSC::helper('klaviyo/common')->requestReview([
                'requests' => $requests,
                'order' => $order
            ]);
        }

        if (!$klaviyo_enable_request_review) {
            $time_to_send = $type == 'fulfillment' ? intval(OSC::helper('core/setting')->get('catalog/product_review/days_after_fulfill')) : 0;

            OSC::helper('postOffice/email')->create([
                'priority' => 100,
                'subject' => 'Please let us know what you think!',
                'sender_email' => OSC::helper('core/setting')->get('theme/contact/noreply_email') ?? OSC::helper('core/setting')->get('theme/contact/email'),
                'receiver_email' => $order->data['email'],
                'receiver_name' => $order->getFullName(),
                'html_content' => OSC::core('template')->build(
                    'catalog/email_template/html/main',
                    [
                        'template' => 'catalog/email_template/html/review/request',
                        'is_marketing_email' => true,
                        'requests' => $requests,
                        'order' => $order,
                        'customer_first_name' => $order->getFirstName(),
                        'customer_last_name' => $order->getLastName()
                    ]
                ),
                'running_timestamp' => OSC::helper('catalog/common')->fetchEstimateTimeExceptWeekendDays(max($time_to_send, 0))
            ]);
        }
    }

    public function sendMailCustomerReviewed(Model_Catalog_Product_Review_Request $request, $discount_code)
    {
        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();
        $klaviyo_enable_customer_reviewed = intval(OSC::helper('core/setting')->get('klaviyo/enable_customer_reviewed')) === 1;

        $full_name_segments = explode(' ', $request->data['customer_name'], 2);

        $recommended_items = OSC::model('catalog/product')->getCollection()
            ->addCondition('discarded', 0)
            ->sort('solds', 'DESC')
            ->setLimit(4)
            ->load();

        if ($klaviyo_api_key != '') {
            $items = [];
            foreach ($recommended_items as $recommended_item) {
                $items[] = [
                    'detail_url' => OSC::helper('klaviyo/common')->addParamUrl($recommended_item->getDetailUrl()),
                    'title' => $recommended_item->getProductTitle(),
                    'image_url' => OSC::helper('klaviyo/common')->addParamUrl(
                        OSC::helper('core/image')->imageOptimize($recommended_item->getFeaturedImageUrl(), 300, 300, true)
                    )
                ];
            }

            $items = array_chunk($items, 2);

            if ($discount_code->data['discount_type'] == 'percent') {
                $discount_code_value = $discount_code->data['discount_value'] . '%';
            } else {
                $discount_code_value = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency');
            }

            $data_discount = ['value' => $discount_code_value,
                'code' => preg_replace('/^(.{4})(.{4})(.{4})$/', '\\1-\\2-\\3', $discount_code->data['discount_code']),
                'expire_time' => date('F d, Y, h:i A', $discount_code->data['deactive_timestamp'])
            ];

            $data = [
                'token' => $klaviyo_api_key,
                'event' => 'Customer reviewed',
                'customer_properties' => [
                    '$email' => $request->data['customer_email'],
                ],
                'properties' => [
                    'title' => 'Your discount code at ' . $this->setting('theme/site_name'),
                    'customer_name' => $request->data['customer_name'],
                    'discount_code' => $data_discount,
                    'recommended_items' => $items,
                    'redirect_url' => OSC::helper('klaviyo/common')->addParamUrl(OSC_FRONTEND_BASE_URL),
                    'customer_first_name' => $full_name_segments[0],
                    'customer_last_name' => $full_name_segments[1]
                ],
                'time' => time(),
            ];

            OSC::helper('klaviyo/common')->create($data);

        }

        if (!$klaviyo_enable_customer_reviewed) {
            OSC::helper('postOffice/email')->create([
                'priority' => 100,
                'subject' => 'Your discount code at ' . $this->setting('theme/site_name'),
                'receiver_email' => $request->data['customer_email'],
                'receiver_name' => $request->data['customer_name'],
                'html_content' => OSC::core('template')->build(
                    'catalog/email_template/html/main',
                    [
                        'template' => 'catalog/email_template/html/review/discount_code',
                        'is_marketing_email' => true,
                        'discount_code' => $discount_code,
                        'recommended_items' => $recommended_items,
                        'customer_first_name' => $full_name_segments[0],
                        'customer_last_name' => $full_name_segments[1]
                    ]
                ),
            ]);
        }
    }

    public function renderProductReviewApi(Model_Catalog_Product_Review_Collection $product_review_collection)
    {
        $result = [];

        if ($product_review_collection->length() > 0) {
            /* @var Model_Catalog_Product_Review $review */

            foreach ($product_review_collection as $review) {
                $list_image = $review->getListImage();
                if (!empty($list_image)) {
                    foreach ($list_image as &$image) {
                        $image['url'] = OSC::wrapCDN($image['url']);
                    }
                }
                $item = [
                    'parent_id' => $review->data['parent_id'],
                    'review_id' => $review->data['record_id'],
                    'role' => $review->data['role'],
                    'order_id' => $review->data['order_id'],
                    'product_id' => $review->data['product_id'],
                    'product_title' => $review->getProductTitle(),
                    'product_avatar' => $review->getProductAvatar(),
                    'product_detail_url' => $review->getProductDetailUrl(),
                    'customer_id' => $review->data['customer_id'],
                    'customer_name' => $review->data['customer_name'],
                    'customer_email' => $review->data['customer_email'],
                    'vote_value' => $review->data['vote_value'],
                    'review' => $review->data['review'],
                    'country_code' => $review->data['country_code'],
                    'verified_buyer' => $review->data['order_id'] ? 1 : 0,
                    'review_summary' => OSC::helper('catalog/product')->getSomeWords($review->data['review'], 50),
                    'is_pin' => $review->data['is_pin'],
                    'added_timestamp' => $review->data['added_timestamp'],
                    'list_image' => $list_image,
                ];

                $list_child_review = $review->getChildReview();
                if ($list_child_review->length() > 0) {
                    $item['list_child_review'] = $this->renderProductReviewApi($list_child_review);
                }

                $result[] = $item;
            }
        }

        return $result;
    }

    public function getReviewMetaImage()
    {
        $meta_image = OSC::helper('core/setting')->get('review/meta_image');
        return $meta_image ? OSC::core('aws_s3')->getStorageUrl($meta_image['file']) : OSC::helper('frontend/template')->getMetaImage()->url;
    }

    public function getListProductTypeByProductId($product_id = 0)
    {
        $product_types = [];
        if (!$product_id) {
            return $product_types;
        }
        $product = OSC::helper('catalog/product')->getProduct(['id' => $product_id], true);
        if ($product->isCampaignMode()) {
            $product_types = preg_split('/(\s?)+,(\s?)+/', trim($product->data['product_type'], ', '));
        }
        return $product_types;
    }
}
