<?php

class Helper_Catalog_FrequentlyBoughtTogether extends OSC_Object {

    /**
     *
     * @var GraphAware\Neo4j\Client\ClientBuilder
     */
    protected $_neo4j = null;

    /**
     * 
     * @return $this->_neo4j
     */
    protected function _getNeo4j() {
        if ($this->_neo4j === null) {
            $neo4j_config = OSC::systemRegistry('catalog/neo4j');

            if (!is_array($neo4j_config)) {
                throw new Exception('Cannot get neo4j config');
            }

            $this->_neo4j = GraphAware\Neo4j\Client\ClientBuilder::create()->addConnection('default', 'bolt://' . $neo4j_config['user'] . ':' . $neo4j_config['pass'] . '@' . $neo4j_config['host'] . ':' . $neo4j_config['port'])->build();
        }

        return $this->_neo4j;
    }

    public function resync() {
        $this->_getNeo4j()->run("MATCH p=()-->() DELETE p", []);
        $this->_getNeo4j()->run("MATCH (n) DELETE n", []);

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
                $this->_getNeo4j()->run("MERGE (p:Product {key: \"{$product_key}\", app: \"{$app_key}\", product_id: \"{$product_id}\"})", []);
            } catch (Exception $ex) {
                
            }
        }

        $order_key = $app_key . '/' . $order->getId();

        try {
            $this->_getNeo4j()->run("MERGE (o:Order {key: \"{$order_key}\", app: \"{$app_key}\", order_id: \"{$order->getId()}\"})");
        } catch (Exception $ex) {
            return $this;
        }

        try {
            $this->_getNeo4j()->run("MATCH(o:Order {key: \"{$order_key}\"}) MATCH(p:Product) WHERE p.key IN [\"" . implode('","', $product_keys) . "\"] MERGE (p)-[rel:IN]->(o)");
        } catch (Exception $ex) {
            
        }

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

//            $result = $this->_getNeo4j()->run("MATCH(o:Order)<-[:IN]-(Product {key: \"{$product_key}\"}) MATCH(p:Product)-[:IN]->(o) WHERE p.key <> \"{$product_key}\" RETURN p,count(p) ORDER BY count(p) DESC LIMIT " . $limit);
            $result = $this->_getNeo4j()->run("MATCH(o:Order)<-[:IN]-(Product {key: \"{$product_key}\"}) MATCH(p:Product)-[:IN]->(o) WITH p,count(p) AS ct WHERE ct >= {$minimum_orders} AND p.key <> \"{$product_key}\" RETURN p,ct ORDER BY ct DESC LIMIT " . $limit);
        } catch (Exception $ex) {
            return [];
        }

        $rows = [];

        /* @var $record GraphAware\Bolt\Record\RecordView */
        foreach ($result->records() as $record) {
            //$rows[] = ['product_id' => $record->value('p')->value('product_id'), 'counter' => $record->value('ct')];
            $rows[] = $record->value('p')->value('product_id');
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

            $result = $this->_getNeo4j()->run($query);
        } catch (Exception $ex) {
            return [];
        }

        $rows = [];

        /* @var $record GraphAware\Bolt\Record\RecordView */
        foreach ($result->records() as $record) {
            $rows[] = $record->value('p')->value('product_id');
        }

        return $rows;
    }

}
