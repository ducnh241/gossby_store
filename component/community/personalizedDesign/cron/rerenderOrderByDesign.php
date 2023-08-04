<?php

class Cron_PersonalizedDesign_RerenderOrderByDesign extends OSC_Cron_Abstract {

    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 10;

        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "queue_flag = 1 AND action = 'rerender_order_by_design'", 'added_timestamp ASC', 1, 'fetch_queue');
            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {

                $queue_data = $model->data['queue_data'];
                $design_id = $queue_data['design_id'];
                $shop_id = OSC::getShop()->getId();
                $user_name = $queue_data['user_name'];

                $product_campaign = OSC::model('catalog/product')->getCollection()
                    ->addField('selling_type,meta_data')
                    ->addCondition('selling_type', Model_Catalog_Product::TYPE_CAMPAIGN)
                    ->addCondition('meta_data', '%"design_id":'. $design_id .'%', OSC_Database::OPERATOR_LIKE)
                    ->load()->toArray();

                $variant_semitest = OSC::model('catalog/product_variant')->getCollection()
                    ->addField('product_id,design_id')
                    ->addCondition('design_id', $design_id, OSC_Database::OPERATOR_EQUAL)
                    ->load()->toArray();

                $product_campaign_ids = array_column($product_campaign, 'product_id');
                $product_semitest_ids = array_column($variant_semitest, 'product_id');

                $product_ids = array_merge($product_campaign_ids, $product_semitest_ids);

                if (empty($product_ids)) {
                    throw new Exception('Design is not attached to the product');
                }

                $line_item_collections = OSC::model('catalog/order_item')->getCollection()
                    ->addCondition('shop_id', $shop_id, OSC_Database::OPERATOR_EQUAL)
                    ->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN)
                    ->load();

                if ($line_item_collections->length() <=0) {
                    throw new Exception('Design is not attached to the order');
                }

                $queue_items = [];
                $log_items = [];

                foreach ($line_item_collections as $line_item) {

                    if ($line_item->data['refunded_quantity'] == 0 && $line_item->data['fulfilled_quantity'] < $line_item->data['quantity']) {
                        $additional_data = $line_item->data['additional_data'];

                        $queue_data = [
                            'order_master_record_id' => $line_item->data['order_id'],
                            'item_master_record_id' => $line_item->getId(),
                            'user_name' => $user_name,
                            'member_id' => $model->data['member_id'],
                            'design_id' => $design_id,
                            'service' => $additional_data['supplier_design_beta'] ?? ''
                        ];

                        $queue_items[] = [
                            'order_master_record_id' => $line_item->data['order_id'],
                            'secondary_key' => $line_item->getId(),
                            'member_id' => $model->data['member_id'],
                            'shop_id' => $shop_id,
                            'action' => in_array($line_item->data['product_id'], $product_campaign_ids) ? 'campaign_rerender_v2' : 'render_design_order_beta',
                            'queue_data' => $queue_data,
                            'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['queue']
                        ];

                        $log_items[] = $queue_data;
                    }

                }
                if (!empty($queue_items)) {
                    OSC::model('catalog/order_bulkQueue')->insertMultiUpdateDuplicateKey($queue_items);
                    OSC::model('personalizedDesign/rerenderLog')->insertMultiUpdateDuplicateKey($log_items);
                    for ($i = 1; $i <= 7; $i ++) {
                        OSC::core('cron')->addQueue('catalog/campaign_rerenderDesignV2', null, ['ukey' => 'catalog/campaign_rerenderDesignV2:' . $i, 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60, 'running_time' => 30*$i]);
                        OSC::core('cron')->addQueue('catalog/campaign_renderDesignOrderBeta', null, ['ukey' => 'catalog/campaign_rerenderDesignOrderBeta:' . $i, 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60, 'running_time' => 30*$i]);
                    }
                }

                $model->delete();

            } catch (Exception $ex) {
                $model->setData([
                    'queue_flag' => Model_Catalog_Order_BulkQueue::QUEUE_FLAG['error'],
                    'error' => $ex->getMessage()
                ])->save();
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}