<?php

class Helper_Core_Cache extends OSC_Object {
    protected $_redis = null;
    protected $_reset_cache_queue = 'reset_cache_queue';

    public function __construct() {
        if ($this->_redis === null) {
            try {
                $cache_config = OSC::systemRegistry('cache_config');
                $redis_config = isset($cache_config['instance']['redis']) ? $cache_config['instance']['redis'] : [];

                if (!is_array($redis_config) || empty($redis_config)) {
                    throw new Exception('Cannot get redis config');
                }

                $this->_redis = new Redis();
                $this->_redis->connect($redis_config['host'], $redis_config['port']);
            } catch (Exception $exception) {
                throw $exception;
            }
        }
    }

    /*
     * reset_cache_queue: status 0 - not handle, 1 - handling, 2 - handle error
     * */
    public function insertResetCacheQueue($type, $id, $metadata = []) {
        if (isset($type) && !empty($type) && isset($id) && !empty($id)) {
            try {
                $mongodb = OSC::core('mongodb');
                $document = [
                    'type' => strval($type),
                    'id' => (int) $id,
                    'metadata' => $metadata,
                    'status' => 0,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                $mongodb->insert($this->_reset_cache_queue, $document, 'product');
            } catch (Exception $exception) { }
        }
    }

    public function resetCacheQueue() {
        $mongodb = OSC::core('mongodb');
        $result = $mongodb->selectCollection($this->_reset_cache_queue, 'product')
            ->find([
                'status' => 0
            ], ['typeMap' => ['root' => 'array', 'document' => 'array'], 'limit' => 10])
            ->toArray();

        $list_id = array_map(function ($item) {
            return $item['_id'];
        }, $result);

        if (!empty($result)) {
            $pattern_list = [];
            foreach ($result as $item) {
                $pattern_list = array_merge($pattern_list, $this->getPatternList($item['type'], $item['id'], $item['metadata']));
            }

            if (!empty($pattern_list)) {
                $this->_deleteCachePatternList($pattern_list);
            }

            $mongodb->deleteMany($this->_reset_cache_queue, ['_id' => ['$in' => $list_id]], [], 'product');
        }
    }

    public function getPatternList($type, $id, $metadata = []) {
        $pattern_list = [];

        switch ($type) {
            case self::MODEL_PERSONALIZED_DESIGN:
                if ($id) {
                    $pattern_list[] = "getPersonalizedDesign|helper.personalizedDesign.common|design_ids*,{$id},*";
                }

                break;
            case self::MODEL_CATALOG_PRODUCT:
                $pattern_list = $this->getPatternProductDetail([
                    'product_id' => $id,
                    'product_sku' => $metadata['product_sku'] ?? ''
                ]);

                break;
            case self::MODEL_CATALOG_PRODUCT_COLLECTION:
                if (isset($metadata['collection_id']) && !empty($metadata['collection_id'])) {
                    $pattern_list[] = "actionGetReviewFilter|collection_id={$metadata['collection_id']}.*";
                    $pattern_list[] = "actionGetReviewList|collection_id={$metadata['collection_id']}.*";
                    $pattern_list[] = "getProductsByCollection|collection_id={$metadata['collection_id']}.*";
                }

                break;
            case self::MODEL_CONFIG_AUTO_AB_TEST:
                if (isset($metadata['location_data']) && !empty($metadata['location_data'])) {
                    foreach ($metadata['location_data'] as $country_code) {
                        $pattern_list[] =  "getABProductPriceOfCountry|countryCode:{$country_code}";
                    }
                }

                break;
            default:
                break;
        }

        return $pattern_list;
    }

    public function getPatternProductDetail($product) {
        $pattern_list = [];
        if (isset($product['product_id']) && !empty($product['product_id'])) {
            $pattern_list[] = "getProduct|helper.catalog.product|product_id:*,{$product['product_id']},*";
            $pattern_list[] = "Controller_Catalog_React_Product.actionGetProductDetail*id={$product['product_id']}";
        }

        if (isset($product['product_sku']) && !empty($product['product_sku'])) {
            $pattern_list[] = "getProduct|helper.catalog.product|sku:*,{$product['product_sku']},*";
            $pattern_list[] = "Controller_Catalog_React_Product.actionGetProductDetail*ukey={$product['product_sku']}";
        }

        return $pattern_list;
    }

    protected function _deleteCachePatternList($pattern_list, $return_list_keys = false) {
        if (!empty($pattern_list)) {
            $list_keys = [];

            $start_time_scan = microtime(true);
            foreach ($pattern_list as $pattern) {
                try {
                    $iterator = null;
                    do
                    {
                        $arr_keys = $this->_redis->scan($iterator, (OSC_SITE_KEY ? OSC_SITE_KEY . ':' : '') . "*{$pattern}*", 100);
                        $list_keys = array_merge($list_keys, is_array($arr_keys) && !empty($arr_keys) ? $arr_keys : []);
                    } while ($arr_keys !== false);
                } catch (Exception $exception) { }
            }

            $exec_time_scan = microtime(true) - $start_time_scan;

            $start_time_del = microtime(true);
            if (!empty($list_keys)) {
                try {
                    $this->_redis->del($list_keys);
                } catch (Exception $exception) {
                    OSC::helper('core/common')->writeLog($exception->getMessage());
                }
            }

            $exec_time_del = microtime(true) - $start_time_del;
            if ($return_list_keys) {
                return $list_keys;
            }
        }
    }

    public function deleteByPattern($pattern_lists = [])
    {
        return $this->_deleteCachePatternList($pattern_lists, true);
    }

    const MODEL_PERSONALIZED_DESIGN = 'MODEL_PERSONALIZED_DESIGN';
    const MODEL_CATALOG_PRODUCT = 'MODEL_CATALOG_PRODUCT';

    const MODEL_CATALOG_PRODUCT_COLLECTION = 'MODEL_CATALOG_PRODUCT_COLLECTION';

    const MODEL_CONFIG_AUTO_AB_TEST = 'MODEL_CONFIG_AUTO_AB_TEST';

}