<?php

class Helper_Catalog_CollectionProductRel extends OSC_Object
{

    private function _buildCacheKey($options) {
        if (is_array($options)) {
            unset($options['before_load_callback']);
            ksort($options);
            $options = implode('.', array_map(
                function ($v, $k) {
                    if (is_array($v)) {
                        return $k . '[]=' . implode('&' . $k . '[]=', $v);
                    } else {
                        return $k . '=' . $v;
                    }
                },
                $options,
                array_keys($options)
            ));
        }
        $key = trim(strval($options));
        $cache_key = 'CollectionProductRel|' . implode('|', [
            debug_backtrace()[1]['class'] . '.' . debug_backtrace()[1]['function'],
             $key
        ]);

        return $cache_key;
    }
    /**
     * @param $collection
     * @param array $options
     * $options['page_size']: All, integer
     * $options['page']: pagination (integer)
     * @return OSC_Database_Model_Collection
     * @throws OSC_Exception_Runtime
     */

    public function getProductsByCollection($collection, $options = [], $get_only_id = false, $use_cache = true) {
        $options['collection_id'] = $collection->getId();
        $cache_key = $this->_buildCacheKey($options);
        $adapter = OSC::core('cache')->getAdapter();
        $products = $adapter->get($cache_key);

        if ($products && $get_only_id && $use_cache) {
            return OSC::decode($products);
        }

        $product_model = OSC::model('catalog/product')->getCollection();

        $product_model->addField('product_id')
        ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL)
        ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL);

        if (!$products || !$use_cache) {
            if ($collection->data['collect_method'] == Model_Catalog_Collection::COLLECT_MANUAL) {
                $product_model->addCondition('collection_ids', ",{$collection->getId()},", OSC_Database::OPERATOR_LIKE);
            } else {
                $clause_idx = OSC::makeUniqid();
                $product_model->addClause($clause_idx);
                $relation = $collection->data['auto_conditions']['matched_by'] == 'any' ? 'OR' : 'AND';

                foreach ($collection->data['auto_conditions']['conditions'] as $condition) {
                    if ($collection->getConditionFields()[$condition['field']]['field'] == 'tags') {
                        $condition_value = $condition['value'];

                        // escape regular expression
                        $brackets = ['(', ')', '{', '}', '[', '^', '$', '.', '*', '+', '?', '|'];
                        $escape_brackets = ['\\(', '\\)', '\\{', '\\}', '\\[', '\\^', '\\$', '\\.', '\\*', '\\+', '\\?', '\\|'];
                        $condition_value = str_replace($brackets, $escape_brackets, $condition_value);
                        $product_model->addCondition($collection->getConditionFields()[$condition['field']]['field'], '(^|,)\s*' . preg_quote($condition_value, "'") . '\s*(,|$)', $condition['operator'] == 'equals' ? OSC_Database::OPERATOR_REGEXP : OSC_Database::OPERATOR_NOT_REGEXP, $relation, $clause_idx);

                        continue;
                    }

                    $product_model->addCondition($collection->getConditionFields()[$condition['field']]['field'], $condition['value'], $collection->getConditionOperators()[$condition['operator']]['query_operator'], $relation, $clause_idx);

                }
            }

            if ($options['page_size'] !== "all") {
                $product_model->setPageSize($options['page_size'])->setCurrentPage($options['page']);
            }

            if ($options['order_by'] && in_array($options['order_by'], ['product_id', 'title', 'solds', 'price', 'product_id'])) {
                $order_by = $options['order']?$options['order'] : 'DESC';
                $product_model->sort($options['order_by'], $order_by);
            } else {
                switch ($collection->data['sort_option']) {
                    case 'solds':
                        $product_model->sort('solds', 'DESC');
                        break;
                    case 'title_az':
                        $product_model->sort('title', 'ASC');
                        break;
                    case 'title_za':
                        $product_model->sort('title', 'DESC');
                        break;
                    case 'highest_price':
                        $product_model->sort('price', 'DESC');
                        break;
                    case 'lowest_price':
                        $product_model->sort('price', 'ASC');
                        break;
                    case 'newest':
                        $product_model->sort('product_id', 'DESC');
                        break;
                    case 'oldest':
                        $product_model->sort('product_id', 'ASC');
                        break;
                    case 'manual':
                        $product_model->sort('position_index', 'DESC')->sort('title', 'ASC');
                        break;
                }
            }
        }
        else {
            $product_model->addCondition('product_id', OSC::decode($products), OSC_Database::OPERATOR_IN);
        }
        $product_model->load();
        $cache_data = [];
        foreach ($product_model as $product) {
            $cache_data[] = $product->getId();
            $product->setCatalogCollection($collection);
        }
        if (!$products && count($cache_data) > 0) {
            $adapter->set($cache_key, OSC::encode($cache_data), 3600);
            if ($get_only_id) {
                return $cache_data;
            }
        }

        return $product_model;
    }

    public function getCollectionsByProduct($product, $use_cache = true) {
        $catalog_collections = OSC::model('catalog/collection')->getCollection();
        $catalog_collections->addCondition('collect_method', Model_Catalog_Collection::COLLECT_AUTO)->load();

        $collection_ids = $product->data['collection_ids'];

        foreach ($catalog_collections as $catalog_collection) {
            if ($catalog_collection->productIsInCollection($product)) {
                $collection_ids[] = $catalog_collection->getId();
            }
        }

        return array_unique($collection_ids);
    }

    /**
     * @param $field: product_id, collection_id
     * @param $field_value
     */
    public function deleteByField($field, $field_value) {
        if (!in_array($field, ['collection_id', 'product_id'])) {
            return false;
        }

        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database');
            $DB->query("DELETE FROM {$DB->getTableName('collection_product_rel')} WHERE `{$field}` =:value", ['value' => $field_value], 'delete_items');

        } catch (Exception $e) {
        }
    }
}
