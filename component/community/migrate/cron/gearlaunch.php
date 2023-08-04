<?php

class Cron_Migrate_Gearlaunch extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 100;
        $counter = 0;

        while ($counter < $limit) {
            /* @var $queue Model_Migrate_GearLaunch */
            $queue = OSC::model('migrate/gearlaunch')->getCollection()
                    ->addCondition('queue_key', $data['queue_key'])
                    ->addCondition('queue_flag', 1)
                    ->sort('added_timestamp', 'ASC')
                    ->setLimit(1)
                    ->load()
                    ->getItem();

            if (!($queue instanceof Model_Migrate_Gearlaunch)) {
                break;
            }

            $queue->setData('queue_flag', 0)->save();

            $counter ++;

            $DB->begin();

            $locked_key = OSC::makeUniqid();

            OSC_Database_Model::lockPreLoadedModel($locked_key);

            try {
                switch ($queue->data['action_key']) {
                    case 'fetch_collection':
                        $this->_fetchCollection($queue);
                        break;
                    case 'fetch_campaign_list':
                        $this->_fetchCampaignList($queue);
                        break;
                    case 'fetch_campaign':
                        $this->_fetchCampaign($queue);
                        break;
                    default:
                        throw new Exception('Action key [' . $queue->data['action_key'] . '] is not available');
                }

                $queue->delete();

                $DB->commit();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);
            } catch (Exception $ex) {
                $DB->rollback();

                OSC_Database_Model::unlockPreLoadedModel($locked_key);

                if ($queue && ($queue instanceof Model_Migrate_GearLaunch)) {
                    $queue->setData(['error_flag' => 1, 'error_message' => $ex->getMessage()])->save();
                }
            }
        }

        if ($counter > 0 && $counter == $limit) {
            OSC::core('cron')->addQueue('migrate/gearlaunch', ['queue_key' => $data['queue_key']], ['requeue_limit' => -1, 'skip_realtime']);
        } else {
            OSC::core('cron')->addQueue('catalog/product_bulk_import', null, ['requeue_limit' => -1, 'skip_realtime', 'ukey' => 'gearlaunch_import_product']);
        }
    }

    protected function _fetchCollection(Model_Migrate_GearLaunch $queue) {
        $response = OSC::core('network')->curl($queue->data['action_data']['url'], ['timeout' => 20]);

        if (!preg_match('/^.+var\s+globalStore\s*=\s*(.+)var\s+globalStorefrontJson\s*=.+$/s', $response['content'], $matches)) {
            throw new Exception('Cannot detect navigation data');
        }

        $JSON = OSC::decode(preg_replace('/\;$/', '', trim($matches[1])));

        if (!is_array($JSON)) {
            throw new Exception('Cannot detect navigation data');
        }

        if (!isset($JSON['parentMenuItems']) && !is_array($JSON['parentMenuItems'])) {
            throw new Exception('Collection data is incorrect');
        }

        $collections = [];

        foreach ($JSON['parentMenuItems'] as $item) {
            if (!isset($item['storefront']) || !is_array($item['storefront']) || (isset($queue->data['action_data']['collection']) && $queue->data['action_data']['collection'] != $item['storefront']['path'])) {
                continue;
            }

            $collections[] = ['name' => $item['storefront']['name'], 'url' => 'https://dls-store.com/api/storefrontpage/' . $item['storefront']['key'] . '/campaigns'];
        }

        if (isset($JSON['menuDescription']) && is_array($JSON['menuDescription'])) {
            foreach ($JSON['menuDescription'] as $items) {
                foreach ($items as $item) {
                    if (!isset($item['storefront']) || !is_array($item['storefront']) || (isset($queue->data['action_data']['collection']) && $queue->data['action_data']['collection'] != $item['storefront']['path'])) {
                        continue;
                    }

                    $collections[] = ['name' => $item['storefront']['name'], 'url' => 'https://dls-store.com/api/storefrontpage/' . $item['storefront']['key'] . '/campaigns'];
                }
            }
        }

        $collection_urls = [];

        foreach ($collections as $collection) {
            if (in_array($collection['url'], $collection_urls, true)) {
                continue;
            }

            $collection_urls[] = $collection['url'];

            $queue->getNullModel()->setData([
                'member_id' => $queue->data['member_id'],
                'queue_key' => $queue->data['queue_key'],
                'queue_flag' => 1,
                'error_flag' => 0,
                'error_message' => null,
                'action_key' => 'fetch_campaign_list',
                'action_data' => ['url' => $collection['url'], 'name' => $collection['name'], 'filter' => $queue->data['action_data']['filter']],
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ])->save();
        }
    }

    protected function _fetchCampaignList(Model_Migrate_GearLaunch $queue) {
        $campaign_urls = [];

        $page = 0;

        while (true) {
            $response = OSC::core('network')->curl($queue->data['action_data']['url'] . '?cursor=' . $page . '&limit=20', ['timeout' => 20]);

            $JSON = is_array($response['content']) ? $response['content'] : OSC::decode($response['content']);

            if (!is_array($JSON) || !isset($JSON['results']) || !is_array($JSON['results'])) {
                throw new Exception('Response data of page ' . $page . ' is incorrect');
            }

            if (count($JSON['results']) < 1) {
                break;
            }

            foreach ($JSON['results'] as $item) {
                $campaign_urls[] = 'https://dls-store.com/' . $item['path'];
            }

            $page ++;
        }

        $campaign_urls = array_unique($campaign_urls);

        foreach ($campaign_urls as $campaign_url) {
            $queue->getNullModel()->setData([
                'member_id' => $queue->data['member_id'],
                'queue_key' => $queue->data['queue_key'],
                'queue_flag' => 1,
                'error_flag' => 0,
                'error_message' => null,
                'action_key' => 'fetch_campaign',
                'action_data' => ['url' => $campaign_url, 'filter' => $queue->data['action_data']['filter']],
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ])->save();
        }
    }

    protected function _fetchCampaign(Model_Migrate_GearLaunch $queue) {
        $response = OSC::core('network')->curl($queue->data['action_data']['url'], ['timeout' => 20]);

        if (!preg_match('/^.+var\s+globalCampaign\s*=\s*(.+)var\s+globalProductDetails\s*=.+$/s', $response['content'], $matches)) {
            throw new Exception('Cannot detect campagain data');
        }

        $JSON = OSC::decode(preg_replace('/\;$/', '', trim($matches[1])));

        if (!is_array($JSON) || !isset($JSON['name'])) {
            throw new Exception('Response data is incorrect');
        }

        $product = [
            'product' => [
                'product_id' => 0,
                'member_id' => $queue->data['member_id'],
                'title' => $JSON['name'],
                'product_type' => 'GearLaunch',
                'vendor' => 'GearLaunch',
                'description' => $JSON['description'],
                'content' => '',
                'tags' => [],
                'meta_tags' => [
                    'title' => '',
                    'description' => '',
                    'keywords' => ''
                ],
                'options' => [
                    'option1' => [
                        'title' => 'Product type',
                        'type' => 'product_type',
                        'position' => 1,
                        'values' => []
                    ],
                    'option2' => [
                        'title' => 'Color',
                        'type' => 'product_color',
                        'position' => 2,
                        'values' => []
                    ],
                    'option3' => [
                        'title' => 'Size',
                        'type' => 'clothing_size',
                        'position' => 3,
                        'values' => []
                    ]
                ],
                'discarded' => 1
            ],
            'variants' => [],
            'image_map' => [],
            'images' => []
        ];

        foreach ($JSON['variants'] as $variant) {
            if (count($queue->data['action_data']['filter']['product_type']) > 0 && !in_array(static::cleanFilterValue($variant['displayName']), $queue->data['action_data']['filter']['product_type'])) {
                continue;
            }

            $variant['displayName'] = trim($variant['displayName']);
            $product['product']['options']['option1']['values'][] = $variant['displayName'];

            $product_type_sku = explode(' ', $variant['displayName']);
            $product_type_sku = array_map(function($segment) {
                $segment = trim($segment);

                if ($segment != '') {
                    $segment = strtoupper(substr($segment, 0, 1));
                }

                return $segment;
            }, $product_type_sku);
            $product_type_sku = array_filter($product_type_sku, function($segment) {
                return $segment !== '';
            });
            $product_type_sku = implode('', $product_type_sku);

            $sizes = [];

            foreach ($variant['colors'] as $color) {
                if (count($queue->data['action_data']['filter']['color']) > 0 && !in_array(static::cleanFilterValue($color['name']), $queue->data['action_data']['filter']['color'])) {
                    continue;
                }

                $color['name'] = trim($color['name']);
                $product['product']['options']['option2']['values'][] = $color['name'];

                $color_sku = $this->_parseSku($color['name']);

                foreach ($color['sizes'] as $size) {
                    if (isset($size['@ref'])) {
                        if (!isset($sizes[$size['@ref']])) {
                            continue;
                        }

                        $size = $sizes[$size['@ref']];
                    } else {
                        if (count($queue->data['action_data']['filter']['size']) > 0 && !in_array(static::cleanFilterValue($size['name']), $queue->data['action_data']['filter']['size'])) {
                            continue;
                        }

                        $sizes[$size['@id']] = $size;
                        $size['name'] = trim($size['name']);
                    }

                    $product['product']['options']['option3']['values'][] = $size['name'];

                    $size_sku = $this->_parseSku($size['name']);

                    $featured_key = parse_url($color['imageFeatured'], PHP_URL_QUERY);
                    $featured_key = parse_str($featured_key, $query);
                    $featured_key = strtoupper($query['p']);

                    $images = [
                        $color['images'][$featured_key],
                        $color['images'][$featured_key == 'BACK' ? 'FRONT' : 'BACK']
                    ];

                    foreach ($images as $idx => $image_url) {
                        if (!isset($product['image_map'][$image_url])) {
                            $image_id = OSC::makeUniqid();

                            $product['images'][$image_id] = $image_url;
                            $product['image_map'][$image_url] = $image_id;
                        }

                        $images[$idx] = $image_url;
                    }

                    $product['variants'][] = [
                        'variant_id' => 0,
                        'product_id' => 0,
                        'sku' => $JSON['crock'] . '__' . $product_type_sku . '_' . $color_sku . '_' . $size_sku,
                        'option1' => $variant['displayName'],
                        'option2' => $color['name'],
                        'option3' => $size['name'],
                        'image' => $images,
                        'price' => OSC::helper('catalog/common')->integerToFloat($variant['price']['amount'] + ((isset($size['surcharge']) && is_array($size['surcharge']) && isset($size['surcharge']['amount'])) ? $size['surcharge']['amount'] : 0)),
                        'compare_at_price' => 0,
                        'cost' => 0,
                        'require_shipping' => 1,
                        'require_packing' => 1,
                        'keep_flat' => 0,
                        'weight' => $size['weight'],
                        'weight_unit' => 'kg',
                        'dimension_width' => $size['width'],
                        'dimension_height' => 1,
                        'dimension_length' => $size['length'],
                        'track_quantity' => 0,
                        'quantity' => 0,
                        'overselling' => 1
                    ];
                }
            }
        }

        $product['product']['options']['option1']['values'] = array_unique($product['product']['options']['option1']['values']);
        $product['product']['options']['option1']['values'] = array_values($product['product']['options']['option1']['values']);

        $product['product']['options']['option2']['values'] = array_unique($product['product']['options']['option2']['values']);
        $product['product']['options']['option2']['values'] = array_values($product['product']['options']['option2']['values']);

        $product['product']['options']['option3']['values'] = array_unique($product['product']['options']['option3']['values']);
        $product['product']['options']['option3']['values'] = array_values($product['product']['options']['option3']['values']);

        usort($product['variants'], function($a, $b) use($product) {
            for ($i = 1; $i <= 3; $i ++) {
                $a_idx = array_search($a['option' . $i], $product['product']['options']['option' . $i]['values']);
                $b_idx = array_search($b['option' . $i], $product['product']['options']['option' . $i]['values']);

                if ($a_idx !== $b_idx) {
                    return $a_idx > $b_idx ? 1 : -1;
                }
            }

            return 0;
        });

        $product['variants'] = array_values($product['variants']);

        $featured_image = reset($product['variants'][0]['image']);
        $featured_image_id = $product['image_map'][$featured_image];

        unset($product['images'][$featured_image_id]);
        $product['images'] = [$featured_image_id => $featured_image] + $product['images'];

        OSC::model('catalog/product_bulkQueue')->setData([
            'ukey' => 'import/' . md5(OSC::encode($product)),
            'member_id' => 1,
            'action' => 'import',
            'queue_data' => $product
        ])->save();
    }

    protected function _parseSku($segments) {
        $segments = explode(' ', $segments);
        $segments = array_map(function($segment) {
            $segment = trim($segment);

            if ($segment != '') {
                $segment = strtoupper(substr($segment, 0, 1)) . strtolower(substr($segment, 1));
            }

            return $segment;
        }, $segments);
        $segments = array_filter($segments, function($segment) {
            return $segment !== '';
        });
        return implode('', $segments);
    }

    public static function cleanFilterValue($value) {
        return strtolower(preg_replace('/(^\_+|\_+$)/', '', preg_replace('/\_{2,}/', '_', preg_replace('/[^a-zA-Z0-9]/', '_', $value))));
    }

}
