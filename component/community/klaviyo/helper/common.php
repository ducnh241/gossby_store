<?php

class Helper_Klaviyo_Common {
    const TBL_QUEUE_NAME = 'catalog_klaviyo_queue';
    const KLAVIYO_HOST = 'https://a.klaviyo.com';
    const TYPE_ABANDON = 'abandon';

    public function getApiKey($force_enable = false)
    {
        $klaviyo_enable = intval(OSC::helper('core/setting')->get('tracking/klaviyo/enable'));
        $klaviyo_api_key = trim(OSC::helper('core/setting')->get('tracking/klaviyo/code'));

        if ($klaviyo_enable != 1 || !$klaviyo_api_key || $klaviyo_api_key == '') {
            return '';
        }

        return $klaviyo_api_key;
    }

    public function create($data, $type = null) {
        $url_facebook = OSC::helper('core/setting')->get('theme/social/facebook');
        $url_twitter = OSC::helper('core/setting')->get('theme/social/twitter');
        $url_youtube = OSC::helper('core/setting')->get('theme/social/youtube');
        $url_instagram = OSC::helper('core/setting')->get('theme/social/instagram');
        $url_pinterest = OSC::helper('core/setting')->get('theme/social/pinterest');
        $data['properties']['store'] = [
            'faq_url' => $this->addParamUrl(OSC_FRONTEND_BASE_URL . '/faqs', $type),
            'contact_url' => $this->addParamUrl(OSC::helper('frontend/template')->getContactUrl() . '?open_chat=1', $type),
            'chat_us' => $this->addParamUrl(OSC::helper('frontend/template')->getContactUrl() . '?open_chat=1', $type),
            'support_email' => OSC::helper('core/setting')->get('theme/contact/email'),
            'sender_email' => OSC::helper('core/setting')->get('theme/contact/noreply_email') ?? OSC::helper('core/setting')->get('theme/contact/email'),
            'logo_url' => $this->addParamUrl(OSC::helper('frontend/template')->getLogo(true)->url, $type),
            'name' => OSC::helper('core/setting')->get('theme/site_name'),
            'url' => $this->addParamUrl(OSC_FRONTEND_BASE_URL, $type),
            'url_facebook' => $this->addParamUrl($url_facebook, $type),
            'url_twitter' => $this->addParamUrl($url_twitter, $type),
            'url_youtube' => $this->addParamUrl($url_youtube, $type),
            'url_instagram' => $this->addParamUrl($url_instagram, $type),
            'url_pinterest' => $this->addParamUrl($url_pinterest, $type),
        ];
        try {
            $DB = OSC::core('database')->getWriteAdapter();

            $DB->insert('catalog_klaviyo_queue', [
                'queue_flag' => Model_Klaviyo_Item::FLAG_QUEUE_DEFAULT,
                'error_message' => '',
                'data' => OSC::encode($data),
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ], 'insert_queue_klaviyo');

            $queue_id = $DB->getInsertedId();

            if ($queue_id > 0) {
                OSC::core('cron')->addQueue('klaviyo/push', null, ['ukey' => 'klaviyo/push', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60*60]);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
    public function requestReview($datas) {
        $order = $datas['order'];
        $review_code_percentage = min(100, abs(intval(OSC::helper('core/setting')->get('catalog/product_review/review_code_percentage'))));
        $discount_percent = 0;
        if (Model_Catalog_Product_Review_Request::SETT_DISCOUNT_TYPE != 'none' && $review_code_percentage > 0) {
            $discount_percent = $review_code_percentage;
        }

        $data_request = [];
        foreach ($datas['requests'] as $request){
            $product = $request->getProduct();

            if (!$product instanceof Model_Catalog_Product) {
                continue;
            }

            $variant = $product->getSelectedOrFirstAvailableVariant();

            $data_request[] = [
                'image_url' => $this->addParamUrl($variant->getImageUrl()),
                'product_title' => $product->getProductTitle(),
                'variant_title' => $variant->getVariantTitle() ? $variant->getVariantTitle() : '',
                'variant_sku' => $variant->data['sku'] ? $variant->data['sku'] : '',
                'review_url' => $this->addParamUrl($request->getUrl())
            ];
        }

        $data = [
            'token' => OSC::helper('klaviyo/common')->getApiKey(),
            'event' => 'Request review',
            'customer_properties' => [
                '$email' =>  $order->data['email'],
            ],
            'properties' => [
                'title' => 'Please let us know what you think!',
                'discount_percent' => $discount_percent,
                'order_code' => $order->getCode(),
                'customer_name' => $order->getFullName(),
                'data_request' =>  $data_request,
                'customer_first_name' => $order->getFirstName(),
                'customer_last_name' => $order->getLastName()
            ],
            'time' => time(),
        ];
        OSC::helper('klaviyo/common')->create($data);
    }

    public function addParamUrl($url, $type = null) {
        if (is_string($url) && OSC::isUrl($url)) {
            $klaviyo_sref_id = trim(OSC::helper('core/setting')->get('tracking/klaviyo/sref_id'));

            if ($type == self::TYPE_ABANDON) {
                $klaviyo_sref_id = trim(OSC::helper('core/setting')->get('tracking/klaviyo/abandon_sref_id'));
            }

            if ($klaviyo_sref_id) {
                $url .= (strpos($url, '?') !== false ? '&' : '?').'sref='.$klaviyo_sref_id;
            }
        }
        return $url;

    }

    /**
     * @param $post_office_subscriber
     * @return void
     */
    public function subscribe($post_office_subscriber) {
        $helper_core_setting = OSC::helper('core/setting');

        $klaviyo_api_key = $this->getApiKey();
        $klaviyo_enable_subscriber = intval($helper_core_setting->get('klaviyo/enable_subscribe')) === 1;

        $title = 'Your subscribe at ' .  $helper_core_setting->get('theme/site_name');

        $token = $post_office_subscriber->data['token'];

        try {
            if ($klaviyo_api_key) {
                $catalog_klaviyo_data = [
                    'token' => $klaviyo_api_key,
                    'event' => 'Subscribe',
                    'customer_properties' => [
                        '$email' => $post_office_subscriber->data['email'],
                    ],
                    'properties' => [
                        'full_name' => $post_office_subscriber->data['full_name'],
                        'title' => $title,
                        'token' => $token,
                        'confirm_url' => $this->addParamUrl(OSC_FRONTEND_BASE_URL . '/postOffice/subscriber/confirm?token=' . $token),
                    ],
                    'time' => $post_office_subscriber->data['added_timestamp'],
                ];

                // create record in catalog_klaviyo_queue table
                $this->create($catalog_klaviyo_data);
            }

            if (!$klaviyo_enable_subscriber) {
                // create record in post_office_email_queue table
                OSC::helper('postOffice/email')->create([
                    'priority' => 100,
                    'subject' => $title,
                    'receiver_email' => $post_office_subscriber->data['email'],
                    'receiver_name' => $post_office_subscriber->data['full_name'],
                    'html_content' => OSC::core('template')->build(
                        'catalog/email_template/html/main',
                        [
                            'template' => 'catalog/email_template/html/confirmsubscriber',
                            'title' => $title,
                            'subscriber_name' => $post_office_subscriber->data['full_name'],
                            'token' => $token,
                            'is_marketing_email' => true,
                            'big_logo' => true,
                        ]
                    ),
                ]);
            }
        } catch (Exception $exception) {
            return;
        }
    }
}
