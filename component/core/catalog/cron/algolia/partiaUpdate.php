<?php

class Cron_Catalog_Algolia_PartiaUpdate extends OSC_Cron_Abstract {

    const CRON_TIMER = '0 1 * * *';
    const CRON_SCHEDULER_FLAG = 1;

    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp) {

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $short_term_day = ALGOLIA_DEFAULT_SHORT_TERM ?? 14;

        try {
            $unique_visit_products = OSC::helper('catalog/algolia_product')->countUniqueVisitProductsByDay($short_term_day);
            $order_products = OSC::helper('catalog/algolia_product')->countOrderProductsByDay($short_term_day);

            $products = OSC::model('catalog/product')
                ->getCollection()
                ->addCondition('discarded', 0) // Product is not  discard
                ->addCondition('listing', 1) // Product is listing
                ->addField('product_id, solds')
                ->load();

            $partia_updates = [];

            /* @var Model_Catalog_Product $product */
            foreach ($products as $product) {

                $product_id = (int) $product->getId();

                // Default Short Term
                $object = [
                    'objectID' => $product_id,
                    'sold_short_term' => 0,
                    'sold_short_term_group' => 1,
                    'unique_visit_short_term' => 0,
                    'cr_short_term' => 0,
                    'cr_short_term_group' => 1
                ];

                // Detect sold group
                $sold = (int)$product->data['solds'];
                $sold_group = OSC::helper('catalog/algolia_product')->getSoldGroup($sold);
                $object['sold_group'] = $sold_group;
                $object['solds'] = $sold;

                if (isset($order_products[$product_id])) {
                    $object['sold_short_term'] = (int) $order_products[$product_id];
                    $object['sold_short_term_group'] = OSC::helper('catalog/algolia_product')->getSoldShortTermGroup($order_products[$product_id]);
                }

                if (isset($unique_visit_products[$product_id])) {
                    $object['unique_visit_short_term'] = (int) $unique_visit_products[$product_id];
                }

                if (isset($order_products[$product_id]) && isset($unique_visit_products[$product_id])) {
                    $cr = round(($object['sold_short_term'] / $object['unique_visit_short_term']) * 100);
                    $object['cr_short_term'] = $cr . '%' ;
                    $object['cr_short_term_group'] = OSC::helper('catalog/algolia_product')->getCRShortTermGroup($cr);
                }

                $partia_updates[] = $object;

                if (count($partia_updates) >= 1000) {
                    OSC::core('algolia')->updateRecords(ALGOLIA_PRODUCT_INDEX, $partia_updates);
                    $partia_updates = [];
                }

            }

            if (count($partia_updates)) {
                OSC::core('algolia')->updateRecords(ALGOLIA_PRODUCT_INDEX, $partia_updates);
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }
}
