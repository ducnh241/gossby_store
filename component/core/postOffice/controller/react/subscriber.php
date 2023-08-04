<?php

class Controller_PostOffice_React_Subscriber extends Abstract_Frontend_ReactApiController {

    public function actionPost(){
        try {
            $email = $this->_request->get('email');
            $request = $this->_request->get('request');
            $subscriber = OSC::model('postOffice/subscriber');

            try {
                $subscriber->loadByEmail($email);
            } catch (Exception $ex) {
                if ($ex->getCode() !== 404) {
                    throw new Exception('System error. Please try again!!!');
                }
            }

            if ($subscriber->getId() > 0) {
                throw new Exception('Thank you. This email already exists !!!');
            }
            $username = explode("@", $email);

            $subscriber->setData([
                'email' => $email,
                'request' => $request,
                'flag_action' => '1',
                'newsletter' => 1,
                'full_name' => $username[0],
                'ab_key' => '',
                'ab_value' => ''
            ])->save();

            // subscribe email to klaviyo and send subscriber email
            OSC::helper('klaviyo/common')->subscribe($subscriber);

            $product_ids = Helper_Catalog_Common::recentlyViewedProductGet();
            $products = OSC::model('catalog/product')->getCollection()->addField('product_id', 'product_type')->load($product_ids);

            $categories = [];
            foreach ($products as $product) {
                $product_type = explode(', ', $product->data['product_type']);
                $collection_title = $product->getListCollectionTitle();
                $tags = $product->getListProductTagsWithoutRootTag(false, true);
                $categories = array_unique(array_merge($categories, $product_type, $collection_title, $tags));
            } 

            $this->sendSuccess([
                'categories' => $categories
            ]);

        } catch(Exception $ex){
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionConfirm() {
        try {
            $token = $this->_request->get('token');

            if (!isset($token) || $token == '') {
                throw new  Exception('Unable to confirm your email address. Please re-enter your email and subscribe again.');
            }
            try {
                $model = OSC::model('postOffice/subscriber')->loadByUKey($token);
            } catch (Exception $ex) {
                throw new Exception('Your confirmation email is no longer valid. Please re-enter your email and subscribe again.');
            }
            if ($model->data['confirm'] == 1) {
                throw new Exception('Your email address has been added to our subscription list already!');
            }

            $username = explode("@", $model->data['email']);

            $recommended_items = OSC::model('catalog/product')->getCollection()->addCondition('discarded', 0)->sort('solds', 'DESC')->setLimit(4)->load();

            $discount = intval(OSC::helper('core/setting')->get('catalog/setting_type/subscriber_discount'));

            $discount_value = $discount > 0 ? $discount : 0;

            $discount_code = null;
            if ($discount > 0) {
                $discount_code = OSC::model('catalog/discount_code')->setData([
                    'auto_generated' => 1,
                    'discount_code' => OSC::helper('catalog/common')->genCodeUkey(),
                    'discount_type' => 'percent',
                    'discount_value' => $discount_value,
                    'usage_limit' => 1,
                    'deactive_timestamp' => time() + (60 * 60 * 24 * 15),
                    'note' => 'Mail Subscribe'
                ])->save();
            }
            $title = 'Thanks for subscribe at ' . $this->setting('theme/site_name');

            $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();
            $klaviyo_enable_subscriber = intval(OSC::helper('core/setting')->get('klaviyo/enable_subscribe')) === 1;

            if ($klaviyo_api_key != '') {
                $items = [];
                foreach ($recommended_items as $key => $recommended_item) {
                    $items[] = [
                        'detail_url' => OSC::helper('klaviyo/common')->addParamUrl($recommended_item->getDetailUrl()),
                        'title' => $recommended_item->getProductTitle(),
                        'image_url' => OSC::helper('klaviyo/common')->addParamUrl(OSC::helper('core/image')->imageOptimize($recommended_item->getFeaturedImageUrl(), 300, 300, true))
                    ];
                }
                $items = array_chunk($items, 2);

                $data_discount = '';
                if ($discount_code != null) {
                    if ($discount_code->data['discount_type'] == 'percent') {
                        $discount_code_value = $discount_code->data['discount_value'] . '%';
                    } else {
                        $discount_code_value = OSC::helper('catalog/common')->formatPriceByInteger($discount_code->data['discount_value'], 'email_with_currency');
                    }
                    $data_discount = ['value' => $discount_code_value, 'code' => preg_replace('/^(.{4})(.{4})(.{4})$/', '\\1-\\2-\\3', $discount_code->data['discount_code']), 'expire_time' => date('F d, Y, h:i A', $discount_code->data['deactive_timestamp'])];
                }

                $data = [
                    'token' => $klaviyo_api_key,
                    'event' => 'Subscribe Confirmed',
                    'customer_properties' => [
                        '$email' => $model->data['email'],
                    ],
                    'properties' => [
                        'discount_code' => $data_discount,
                        'recommended_items' => $items,
                        'full_name' => $model->data['full_name'],
                        'title' => $title
                    ],
                    'time' => $model->data['added_timestamp'],
                ];

                OSC::helper('klaviyo/common')->create($data);
            }

            if (!$klaviyo_enable_subscriber) {
                OSC::helper('postOffice/email')->create([
                    'priority' => 100,
                    'subject' => $title,
                    'receiver_email' => $model->data['email'],
                    'receiver_name' => $username[0],
                    'html_content' => OSC::core('template')->build(
                        'catalog/email_template/html/main',
                        [
                            'template' => 'catalog/email_template/html/thankyousubscriber',
                            'customer_name' => $username[0],
                            'token' => $token,
                            'discount_code' => $discount_code,
                            'recommended_items' => $recommended_items,
                            'is_marketing_email' => true
                        ]
                    )
                ]);
            }
            $model->setData(['confirm' => 1, 'token' => ''])->save();

            $this->sendSuccess('Your email address has been successfully added to our subscription list!');
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}
