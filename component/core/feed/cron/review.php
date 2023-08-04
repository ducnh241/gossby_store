<?php

class Cron_Feed_Review extends OSC_Cron_Abstract
{
    const CRON_TIMER = '0 23 * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($params, $queue_added_timestamp)
    {
        /* @var $review Model_Catalog_Product_Review */

        $limit = 500;
        $offset = 0;

        $review_data = [];
        $using_mpn_product = intval(OSC::helper('core/setting')->get('catalog/google_feed/using_mpn_product')) == 1;

        while (true) {
            $reviews = OSC::model('catalog/product_review')
                ->getCollection()
                ->addCondition('state', Model_Catalog_Product_Review::STATE_APPROVED, OSC_Database::OPERATOR_EQUAL)
                ->sort('record_id', OSC_Database::ORDER_ASC)
                ->setOffset($offset)
                ->setLimit($limit)
                ->load();
            if ($reviews->length() < 1) {
                break;
            }

            foreach ($reviews as $review) {
                $product = $review->getProduct();
                if (!$product || !($product instanceof Model_Catalog_Product)) {
                    continue;
                }

                $mpn = $product->data['sku'];

                if (!$using_mpn_product) {
                    $variant = $product->getSelectedOrFirstAvailableVariant(true);
                    if ($variant instanceof Model_Catalog_Product_Variant) {
                        $mpn = $variant->data['sku'];
                    }
                }

                $content_review = htmlspecialchars(OSC::core('string')->removeInvalidCharacter(strip_tags($review->data['review'])));
                if (!$content_review) {
                    continue;
                }

                $review_data[$review->getId()] = [
                    'review_id' => $review->getId(),
                    'reviewer' => [
                        'name' => $review->data['customer_name'],
                        'reviewer_id' => $review->data['customer_id'],
                    ],
                    'review_timestamp' => date('c', $review->data['added_timestamp']),
                    //'title' => '',
                    'content' => $content_review,
                    //'pros' => ['Uu diem'],
                    //'cons' => ['Nhuoc diem'],
                    'review_url' => $review->getDetailUrl(),
                    'reviewer_images' => array_column($review->getListImage(), 'url'),
                    'ratings' => $review->data['vote_value'],
                    'products' => [
                        'product' => [
                            'product_ids' => [
                                //'gtins' => 'Contains GTINs (global trade item numbers) associated with a product',
                                'mpn' => $mpn,
                                'brand' => OSC::helper('core/setting')->get('theme/contact/name'),
                                //'asin' => 'Contains ASINs (Amazon Standard Identification Numbers) associated with a product.',
                            ],
                            'product_name' => $product->data['title'],
                            'product_url' => $product->getDetailUrl()
                        ]
                    ],
                    'is_spam' => 'false',
                    'collection_method' => 'post_fulfillment',
                    'transaction_id' => $review->data['order_id']
                ];
            }
            $offset += $limit;
        }

        try {
            $review_feed = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><feed xmlns:vc="http://www.w3.org/2007/XMLSchema-versioning" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="http://www.google.com/shopping/reviews/schema/product/2.3/product_reviews.xsd"></feed>');

            $review_feed->addChild('version', '2.3');

            $aggregator = $review_feed->addChild('aggregator');
            $aggregator->addChild('name', OSC::helper('core/setting')->get('theme/contact/name'));

            $publisher = $review_feed->addChild('publisher');
            $publisher->addChild('name', OSC::helper('core/setting')->get('theme/contact/name'));
            $publisher->addChild('favicon', OSC::helper('frontend/template')->getFavicon()->url);

            $reviews = $review_feed->addChild('reviews');
            foreach ($review_data as $item_review) {
                $review = $reviews->addChild('review');
                $review->addChild('review_id', $item_review['review_id']);

                $reviewer = $review->addChild('reviewer');
                $reviewer_name = $reviewer->addChild('name', $this->_setAnonymousName($item_review['reviewer']['name']));
                $reviewer_name->addAttribute('is_anonymous', 'true');
                if (intval($item_review['reviewer']['reviewer_id']) > 0) {
                    $reviewer->addChild('reviewer_id', intval($item_review['reviewer']['reviewer_id']));
                }

                $review->addChild('review_timestamp', $item_review['review_timestamp']);
                $review->addChild('content', $item_review['content']);
                $review_url = $review->addChild('review_url', $item_review['review_url']);
                $review_url->addAttribute('type', 'singleton');

                $reviewer_images = $review->addChild('reviewer_images');
                foreach ($item_review['reviewer_images'] as $url_image_review) {
                    $reviewer_image = $reviewer_images->addChild('reviewer_image');
                    $reviewer_image->addChild('url', $url_image_review);
                }

                $ratings = $review->addChild('ratings');
                $overall = $ratings->addChild('overall', $item_review['ratings']);
                $overall->addAttribute('min', 1);
                $overall->addAttribute('max', 5);

                $products = $review->addChild('products');
                $product = $products->addChild('product');
                $product_ids = $product->addChild('product_ids');

                $mpns = $product_ids->addChild('mpns');
                $mpns->addChild('mpn', $item_review['products']['product']['product_ids']['mpn']);

                $brands = $product_ids->addChild('brands');
                $brands->addChild('brand', $item_review['products']['product']['product_ids']['brand']);

                $product->addChild('product_name', $item_review['products']['product']['product_name']);
                $product->addChild('product_url', $item_review['products']['product']['product_url']);

                $review->addChild('is_spam', $item_review['is_spam']);
                $review->addChild('collection_method', $item_review['collection_method']);
                if ($item_review['transaction_id']) {
                    $review->addChild('transaction_id', $item_review['transaction_id']);
                }
            }

            $dom = new DOMDocument;
            $dom->preserveWhiteSpace = false;
            $dom->formatOutput = true;
            $dom->loadXML($review_feed->asXML());

            OSC::writeToFile(OSC_VAR_PATH . '/review/feed.xml', $dom->saveXML());
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _setAnonymousName($name = '')
    {
        $explode_name = explode(' ', $name);
        $name = $explode_name[0];
        if (isset($explode_name[1])) {
            $name = $name . ' ' . mb_substr($explode_name[1], 0, 1);
        }
        return $name;
    }

}