<?php

require_once(dirname(__FILE__) . '/../lib/Node.php');
require_once(dirname(__FILE__) . '/../lib/Edge.php');
require_once(dirname(__FILE__) . '/../lib/Graph.php');
require_once(dirname(__FILE__) . '/../lib/Result.php');

use Redis\Graph;
use Redis\Graph\Node;
use Redis\Graph\Edge;

class Helper_RedisGraph_Query {
    protected $_redis = null;
    protected $_redis_graph = null;
    protected $_store_id = null;

    protected function _getStoreId() {
        if ($this->_store_id == null) {
            try {
                $store_info = OSC::getStoreInfo();
                $this->_store_id = $store_info['id'] ?? 0;
            } catch (Exception $exception) {
                $this->_store_id = 0;
            }
        }
        return $this->_store_id;
    }

    protected function _getRedis() {
        if ($this->_redis === null) {
            try {
                $redis_config = OSC::systemRegistry('catalog/redisgraph');

                if (!is_array($redis_config)) {
                    throw new Exception('Cannot get redis config');
                }

                //$this->_redis = new Predis\Client('redis://' . $redis_config['host'] . ':' . $redis_config['port'] . '/');
                $this->_redis = new Redis();
                $this->_redis->connect($redis_config['host'], $redis_config['port']);
            } catch (Exception $exception) {
                throw $exception;
            }
        }

        return $this->_redis;
    }

    protected function _getRedisGraph() {
        if ($this->_redis_graph === null) {
            try {
                $redis = $this->_getRedis();
                $this->_redis_graph = new Graph($this->_getStoreId(), $redis);
            } catch (Exception $exception) {
                throw $exception;
            }
        }

        return $this->_redis_graph;
    }

    public function deleteRedisGraphData() {
        $this->_getRedisGraph()->delete();
        $key = $this->_getStoreId() . '.partitionSync_orderId';
        $redis = $this->_getRedis();
        $redis->set($key, 0);
    }

    public function partitionSync($limit = 1000) {
        set_time_limit(120);
        ini_set('memory_limit', '-1');

        $redis = $this->_getRedis();
        $key = $this->_getStoreId() . '.partitionSync_orderId';

        $orderId = $redis->get($key);
        $orderId = $orderId ?? 0;

        $orders = OSC::model('catalog/order')->getCollection()
            ->addCondition('order_id', $orderId, OSC_Database::OPERATOR_GREATER_THAN)
            ->setLimit($limit)
            ->sort('order_id', 'ASC')
            ->load();

        if (!empty($orders)) {
            foreach ($orders as $order) {
                try {
                    $this->addOrder($order);
                } catch (Exception $exception) {

                } finally {
                    $orderId = $order->getId() ?? 0;
                }
            }
        }

        if ($orderId > 0) {
            $redis->set($key, $orderId);
        }
    }

    public function resync() {
        $this->_getRedisGraph()->delete();

        $orders = OSC::model('catalog/order')->getCollection()->load();

        foreach ($orders as $order) {
            $this->addOrder($order);
        }
    }

    public function addOrder(Model_Catalog_Order $order) {
        try {
            $line_items = $order->getLineItems();
        } catch (Exception $ex) {
            return $this;
        }

        if ($line_items->length() < 1) {
            return $this;
        }

        $app_key = OSC_SITE_KEY;

        $product_keys = [];

        foreach ($line_items as $line_item) {
            $product_keys[$line_item->data['product_id']] = $app_key . '/' . $line_item->data['product_id'];
        }

        foreach ($product_keys as $product_id => $product_key) {
            try {
                $this->_getRedisGraph()->query("MERGE (p:Product {key: \"{$product_key}\", app: \"{$app_key}\", product_id: \"{$product_id}\"})");
            } catch (Exception $ex) { }
        }

        $order_key = $app_key . '/' . $order->getId();

        try {
            $this->_getRedisGraph()->query("MERGE (o:Order {key: \"{$order_key}\", app: \"{$app_key}\", order_id: \"{$order->getId()}\"})");
        } catch (Exception $ex) {
            return $this;
        }

        try {
            $this->_getRedisGraph()->query("MATCH (o:Order {key: \"{$order_key}\"}) MATCH(p:Product) WHERE p.key IN [\"" . implode('","', $product_keys) . "\"] MERGE (p)-[rel:IN]->(o)");
        } catch (Exception $ex) { }

        return $this;
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @param int $limit
     * @param int $minimum_orders
     * @return array
     */
    public function fetch(Model_Catalog_Product $product, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $product_key = OSC_SITE_KEY . '/' . $product->getId();

            $limit = max(min($limit, 10), 1);
            $minimum_orders = max($minimum_orders, 1);
            $result = $this->_getRedisGraph()->query("MATCH (o:Order)<-[:IN]-(Product {key: \"{$product_key}\"}) MATCH (p:Product)-[:IN]->(o) WITH p,count(p) AS ct WHERE ct >= {$minimum_orders} AND p.key <> \"{$product_key}\" RETURN p,ct ORDER BY ct DESC LIMIT " . $limit);
        } catch (Exception $ex) {
            return [];
        }

        $rows = [];

        foreach ($result->fetchAll() as $record) {
            $p = $record['p'];
            if (is_array($p) && !empty($p)) {
                foreach ($p as $item) {
                    if ($item[0] === 'properties' && is_array($item[1]) && !empty($item[1])) {
                        foreach ($item[1] as $subItem) {
                            if ($subItem[0] === 'product_id' && isset($subItem[1]) && !empty($subItem[1]) && !in_array($subItem[1], $rows)) {
                                $rows[] = $subItem[1];
                            }
                        }
                    }
                }
            }
        }

        return $rows;
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @param int $limit
     * @param int $minimum_orders
     * @return array
     */
    public function fetchByMultiProducts(array $product_ids, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        $product_ids = array_map(function($product_id) {
            return intval($product_id);
        }, $product_ids);
        $product_ids = array_filter($product_ids, function($product_id) {
            return $product_id > 0;
        });

        if (count($product_ids) < 1) {
            return [];
        }

        $product_ids = array_unique($product_ids);
        $product_ids = array_map(function($product_id) {
            return OSC_SITE_KEY . '/' . $product_id;
        }, $product_ids);

        try {
            $limit = max(min($limit, 10), 1);
            $minimum_orders = max($minimum_orders, 1);

            $product_ids = '"' . implode('", "', $product_ids) . '"';

            $query = <<<EOF
MATCH(o:Order)<-[:IN]-(_p:Product) WHERE _p.key IN [{$product_ids}]
MATCH(p:Product)-[:IN]->(o) WITH p,count(p) AS ct
WHERE ct >= {$minimum_orders} AND NOT p.key IN [{$product_ids}]
RETURN p,ct ORDER BY ct DESC LIMIT {$limit}             
EOF;
            $result = $this->_getRedisGraph()->query($query);
        } catch (Exception $ex) {
            return [];
        }

        $rows = [];

        foreach ($result->fetchAll() as $record) {
            $p = $record['p'];
            if (is_array($p) && !empty($p)) {
                foreach ($p as $item) {
                    if ($item[0] === 'properties' && is_array($item[1]) && !empty($item[1])) {
                        foreach ($item[1] as $subItem) {
                            if ($subItem[0] === 'product_id' && isset($subItem[1]) && !empty($subItem[1]) && !in_array($subItem[1], $rows)) {
                                $rows[] = $subItem[1];
                            }
                        }
                    }
                }
            }
        }

        return $rows;
    }
}