<?php

class Helper_Filter_TagProductRel extends OSC_Object {

    public function saveTagProductRel($product_tag_ids, $product, $member_id) {
        if (!$product instanceof Model_Catalog_Product) {
            return;
        }

        $product_id = $product->getId();

        if ($product_id < 1) {
            return;
        }

        $is_empty_tag = empty($product_tag_ids);

        $product_tag_selected = $product->getProductTagSelected($product_id);

        if ($is_empty_tag) {
            $product_tag_ids = [];
            $ukey = 'filter/autoTag:' . $product_id;
            $auto_tag_queue = OSC::model('catalog/product_bulkQueue')->getCollection()
                ->addCondition('ukey', $ukey)
                ->addCondition('queue_flag', Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'])
                ->load();

            if ($auto_tag_queue->length()) {
                $auto_tag_queue->delete();
            }
            OSC::model('catalog/product_bulkQueue')->insertMulti([
                [
                    'ukey' => $ukey,
                    'member_id' => 1,
                    'action' => 'autoTag',
                    'queue_data' => [
                        'product_id' => $product_id,
                        'member_id' => $member_id
                    ]
                ]
            ]);

            OSC::core('cron')->addQueue('filter/autoTag', null, ['ukey' => 'filter/autoTag', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
        }

        if ($is_empty_tag && count($product_tag_selected) < 1) {
            return;
        }

        $auto_tag = OSC::model('filter/autoTag')
            ->getCollection()
            ->addCondition('product_id', $product_id)
            ->sort('added_timestamp', OSC_Database::ORDER_DESC)
            ->load()->first();

        if ($auto_tag && !$is_empty_tag) {
            $auto_tag->analyticAutoTag($product_tag_ids);
        }

        $tag_new = array_diff($product_tag_ids, $product_tag_selected);
        $tag_delete = array_diff($product_tag_selected, $product_tag_ids);

        try {
            if (count($tag_delete) > 0) {
                OSC::model('filter/TagProductRel')
                    ->getCollection()
                    ->addCondition('product_id', $product_id)
                    ->addCondition('tag_id', $tag_delete, OSC_Database::OPERATOR_IN)
                    ->delete();

                $product_tag_rel = OSC::model('filter/TagProductRel')
                    ->getCollection()
                    ->addField('product_id', 'tag_id')
                    ->addCondition('tag_id', $tag_delete, OSC_Database::OPERATOR_IN)
                    ->load();

                $tag_rel_data = [];

                if ($product_tag_rel->length() > 0) {
                    foreach ($product_tag_rel as $key => $item) {
                        $tag_rel_data[] = $item->data['tag_id'];
                    }
                }

                foreach ($tag_delete as $tag) {
                    if (!in_array($tag, $tag_rel_data)) {
                        $tag_collection_delete = OSC::model('filter/tag')->load($tag);
                        if ($tag_collection_delete->data['lock_flag'] == 1) {
                            $tag_collection_delete->setData(['lock_flag' => 0])->save();
                        }
                    }
                }
            }

            if (count($tag_new) > 0) {
                foreach ($tag_new as $key => $tag_id) {
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
        } catch (Exception $e) {
        }

        if (count($tag_delete) || count($tag_new)) {
            OSC::core('observer')->dispatchEvent('catalog/algoliaSyncProduct',
                [
                    'product_id' => $product->getId(),
                    'sync_type' => Helper_Catalog_Algolia_Product::SYNC_TYPE_UPDATE_PRODUCT
                ]
            );
        }
    }

    public function deleteTagProductRel($product_id) {
        if(intval($product_id) < 1) {
            return;
        }

        try {
            OSC::model('filter/TagProductRel')
                ->getCollection()
                ->addCondition('product_id', $product_id)
                ->delete();
        } catch (Exception $ex) {}
    }
}
