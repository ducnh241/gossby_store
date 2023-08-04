<?php

class Cron_Filter_AutoTag extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database')->getWriteAdapter();

        $model = OSC::model('catalog/product_bulkQueue');

        $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'autoTag'", '`added_timestamp` ASC, `queue_id` ASC', 100, 'fetch_queue');

        $rows = $DB->fetchArrayAll('fetch_queue');

        $DB->free('fetch_queue');

        if (count($rows) < 1) {
            return;
        }

        $queue_id_list = array_column($rows, 'queue_id');

        $DB->update($model->getTableName(), ['queue_flag' => 0], 'queue_id IN (' . implode(',', $queue_id_list) . ')');

        foreach ($rows as $row) {

            $queue = OSC::model('catalog/product_bulkQueue');
            $queue->bind($row);

            try {
                $queue_data = $queue->data['queue_data'];
                $product_id = $queue_data['product_id'];
                $product = OSC_Database_Model::getPreLoadedModel('catalog/product', $product_id);
                if (!$product) {
                    throw new Exception('Product Id is invalid');
                }

                $product_tag_ids = OSC::helper('filter/autoTag')->generate($product);
                $tag_product_rel = OSC::model('filter/TagProductRel')
                    ->getCollection()
                    ->addCondition('product_id', $product_id)
                    ->load()->first();

                if (!empty($product_tag_ids) && !$tag_product_rel) {
                    OSC::model('filter/autoTag')->setData([
                        'product_id' => $product->getId(),
                        'auto_tag' => $product_tag_ids,
                        'added_by' => $queue_data['member_id'],
                        'modified_by' => $queue_data['member_id'],
                    ])->save();

                    foreach ($product_tag_ids as $tag_id) {
                        $tag_id = intval($tag_id);

                        OSC::model('filter/TagProductRel')->setData([
                            'product_id' => $product_id,
                            'tag_id' => $tag_id
                        ])->save();

                        $tag_model = OSC::model('filter/tag')->load($tag_id);
                        if ($tag_model->data['lock_flag'] == 0) {
                            $tag_model->setData(['lock_flag' => 1])->save();
                        }
                    }
                }

                $queue->delete();
            } catch (Exception $ex) {
                $queue->setData([
                    'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'],
                    'error' => $ex->getMessage()
                ])->save();
            }
        }

        return false;
    }
}
