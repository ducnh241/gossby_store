<?php

class Cron_Filter_ImportTag extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');
        $DB = OSC::core('database')->getWriteAdapter();

        $model = OSC::model('catalog/product_bulkQueue');

        $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'importTags'", '`added_timestamp` ASC, `queue_id` ASC', 100, 'fetch_queue');

        $rows = $DB->fetchArrayAll('fetch_queue');

        $DB->free('fetch_queue');

        if (count($rows) < 1) {
            return;
        }

        $queue_id_list = array_column($rows, 'queue_id');

        $DB->update($model->getTableName(), ['queue_flag' => 0], 'queue_id IN (' . implode(',', $queue_id_list) . ')');

        $product_ids = [];
        $data = [];

        foreach ($rows as $row) {
            $queue_data = OSC::decode($row['queue_data'], true);

            $tags = [];

            foreach ($queue_data['tags'] as $tag) {
                if (trim($tag) != '') {
                    $tags[] = strtolower(trim($tag));
                }
            }

            if (count($tags) < 1) {
                continue;
            }

            $product_ids[] = $queue_data['product_id'];

            $data[$row['queue_id']] = ['product_id' => $queue_data['product_id'], 'tags' => $tags];
        }

        if (count($product_ids) < 1) {
            $DB->update($model->getTableName(), ['queue_flag' => 0, 'error' => 'product_ids not found'], 'queue_id IN (' . implode(',', $queue_id_list) . ')');
            return false;
        }

        $collection_product = OSC::model('catalog/product')->getCollection()->addField('product_id')->load($product_ids);

        if ($collection_product->length() < 1) {
            $DB->update($model->getTableName(), ['queue_flag' => 0, 'error' => 'product collection not found'], 'queue_id IN (' . implode(',', $queue_id_list) . ')');
            return false;
        }

        $leaves = OSC::helper('filter/common')->getAllLeaves();

        $tag_mapping = [];

        foreach ($leaves as $leave) {
            $tag_mapping[strtolower($leave['title'])] = $leave['id'];
        }

        $queue_ids_remove = [];

        $tag_id_lock = [];

        foreach ($data as $queue_id => $product_info) {
            $product_model = $collection_product->getItemByPK($product_info['product_id']);

            if (!($product_model instanceof Model_Catalog_Product)) {
                continue;
            }

            $tags = $product_info['tags'];

            foreach ($tags as $tag) {
                $tag_id = $tag_mapping[$tag];

                if (!isset($tag_id)) {
                    continue;
                }

                try {
                    OSC::model('filter/tagProductRel')->setData([
                        'product_id' => $product_info['product_id'],
                        'tag_id' => $tag_id,
                        'added_timestamp' => time()
                    ])->save();

                    $tag_id_lock[] = $tag_id;

                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        $tag_id_lock[] = $tag_id;
                    } else {
                        $DB->update($model->getTableName(), ['queue_flag' => 2, 'error' => $ex->getMessage()], 'queue_id=' . $queue_id, 1, 'update_queue');
                    }
                }
            }

            $queue_ids_remove[] = $queue_id;
        }

        if (count($tag_id_lock) > 0) {
            $DB->update(OSC::model('filter/tag')->getTableName(), ['lock_flag' => Model_Filter_Tag::STATE_TAG_LOCK], 'id IN (' . implode(',', $tag_id_lock) . ')');
        }

        if (count($queue_ids_remove) > 0) {
            $DB->delete($model->getTableName(), 'queue_id IN (' . implode(',', $queue_ids_remove) . ')', null, 'delete_queue');
        }

        return false;
    }
}