<?php

class Cron_Catalog_Product_CorrectCollectionIds extends OSC_Cron_Abstract {

    public static function addQueue() {
        OSC::core('cron')->addQueue('catalog/product_correctCollectionIds', null, array('requeue_limit' => -1, 'ukey' => 'catalog/product_correctCollectionIds', 'estimate_time' => 60*10));
    }

    public function process($params, $queue_added_timestamp) {
        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $product_collection = OSC::model('catalog/product')->getCollection();

        $product_table = $product_collection->getTableName(true);
        $collection_table = OSC::model('catalog/collection')->getTableName(true);

        $collect_manual_method = Model_Catalog_Collection::COLLECT_MANUAL;

        $queries = <<<EOF
SELECT	p.product_id AS `product_id`
FROM {$product_table} p
WHERE
    p.collection_ids != '' AND
    (
        LENGTH(p.collection_ids) - 1 - LENGTH(REPLACE(p.collection_ids, ',', ''))
    ) !=
    (
        SELECT COUNT(c.collection_id) FROM {$collection_table} c WHERE FIND_IN_SET(c.collection_id, p.collection_ids) AND c.collect_method = '{$collect_manual_method}'
    );	
UPDATE {$product_table} p
SET
    p.collection_ids = IF(
        (
            @tmp:=(
                SELECT GROUP_CONCAT(collection_id SEPARATOR ',') FROM {$collection_table} c WHERE FIND_IN_SET(c.collection_id, p.collection_ids) AND c.collect_method = '{$collect_manual_method}'
            )
        ) != '',
        CONCAT(',',@tmp,','),
        ''
    )
WHERE p.collection_ids != '';                
EOF;

        $query_id = OSC::makeUniqid();

        $DB->query($queries, null, $query_id);

        $product_ids = array();

        while ($row = $DB->fetchArray($query_id)) {
            $product_ids[] = $row['product_id'];
        }

        if (count($product_ids) > 0) {
            try {
                $product_collection->load($product_ids)->resetCache();
            } catch (Exception $ex) {
                
            }
        }
    }

}
