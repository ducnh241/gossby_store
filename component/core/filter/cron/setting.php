<?php

class Cron_Filter_Setting extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');
        $DB = OSC::core('database')->getWriteAdapter();

        $model = OSC::model('catalog/product_bulkQueue');

        $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'buildFilter'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

        $row = $DB->fetchArray('fetch_queue');

        $DB->free('fetch_queue');

        if (!$row) {
            return;
        }

        try {
            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            $data = $model->data['queue_data'];

            if ($data['collection_id'] > 0) {
                $collection = OSC::model('catalog/collection')->load($data['collection_id']);

                $list_product = $collection->loadProducts(['flag_feed' => true, 'page_size' => 'all'])->toArray();

                $product_ids = array_column($list_product, 'product_id');

                if (count($product_ids) < 1) {
                    $filter_setting = [];
                } else {
                    $product_tag = OSC::model('filter/tagProductRel')
                        ->getCollection()
                        ->addField('tag_id')
                        ->addCondition('product_id', array_unique($product_ids), OSC_Database::OPERATOR_IN)
                        ->load();

                    if ($product_tag->length() < 1) {
                        $filter_setting = [];
                    } else {
                        //tag in collection
                        $tag_ids = array_unique(array_column($product_tag->toArray(), 'tag_id'));

                        $list_tag_in_collection = [];

                        foreach ($tag_ids as $id) {
                            $this->_getTagInCollection($id, $list_tag_in_collection);
                        }

                        $filter_setting = OSC::helper('filter/common')->buildFilter(true, array_unique($list_tag_in_collection));
                    }
                }
            } else {
                $filter_setting = OSC::helper('filter/common')->buildFilter();
            }

            $model_filter = OSC::model('filter/collection')->getCollection()->getFilterByCollectionId($data['collection_id']);

            if ($model_filter == null) {
                OSC::model('filter/collection')->setData([
                    'collection_id' => $data['collection_id'],
                    'filter_setting' => $filter_setting
                ])->save();
            } else {
                $model_filter->setData([
                    'filter_setting' => $filter_setting
                ])->save();
            }

            $model->delete();

        } catch (Exception $ex) {
            $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'modified_timestamp' => time()])->save();
        }

        return false;
    }

    public function _getTagInCollection($tag_id, &$list_tag_in_collection) {
        $parent_array = OSC::helper('filter/common')->getParentTags();

        if (isset($parent_array[$tag_id])) {
            $list_tag_in_collection[] = $tag_id;

            if ($parent_array[$tag_id] != 0) {
                $this->_getTagInCollection($parent_array[$tag_id], $list_tag_in_collection);
            }
        }
    }
}