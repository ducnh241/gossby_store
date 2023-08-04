<?php

class Cron_Supplier_RenderSupplyVariant extends OSC_Cron_Abstract {

    /**
     * @throws Exception
     */
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $update_product_ids = [];
        $list_product_type_variant_ids = [];
        $total_update_product = 0;
        $total_query_update = 0;

        try {
            $supplier_location_rel_ids = $params['supplier_location_rel_ids'];
            $ids = implode(',', $supplier_location_rel_ids);

            $start_time = microtime(true);

            if (count($supplier_location_rel_ids) > 0) {
                $DB = OSC::core('database')->getWriteAdapter();

                $DB->select('id ,supplier_id, variant_data, location_data', 'supplier_location_rel', "id IN ({$ids})", null, null, 'fetch_location_rel');

                $rows = $DB->fetchArrayAll('fetch_location_rel');

                $DB->free('fetch_location_rel');

                $location_datas = [];
                foreach ($rows as $row) {
                    $location_datas[$row['id']] = $row['location_data'];
                }

                $province_collection = OSC::model('core/country_province')
                    ->getCollection()
                    ->addField('country_code', 'province_code')
                    ->load();

                $provinces = [];
                foreach ($province_collection as $province) {
                    $provinces[$province->data['country_code']][] = $province->data['province_code'];
                }

                $data = [];
                foreach ($location_datas as $supplier_location_rel_id => $location_data) {
                    $data_parse = OSC::helper('core/country')->getParsedDataByLocation(OSC::decode($location_data));

                    $location_parsed = OSC::helper('core/country')->dataPreview($data_parse, $provinces);

                    foreach ($location_parsed as $country_code => $province_parse) {
                        if (count($province_parse) > 0) {
                            $data[$supplier_location_rel_id][$country_code . '_'] = $country_code . '_';
                            foreach ($province_parse as $province_code) {
                                $key = $country_code . '_' . $province_code;
                                $data[$supplier_location_rel_id][$key] = $key;
                            }
                        } else {
                            $key = $country_code . '_';
                            $data[$supplier_location_rel_id][$key] = $key;
                        }

                    }
                }

                $insert_datas = [];

                foreach ($rows as $row) {
                    $supplier_id = intval($row['supplier_id']);
                    $supplier_location_rel_id = intval($row['id']);
                    $location_parsed = ',' . implode(',', $data[$row['id']]) . ',';

                    $product_type_variant_ids = OSC::helper('core/common')->parseProductTypeVariantIds(OSC::decode($row['variant_data']));
                    foreach ($product_type_variant_ids as $product_type_variant_id) {
                        $list_product_type_variant_ids[$product_type_variant_id] = $product_type_variant_id;
                        $insert_datas[] = "('{$product_type_variant_id}', '{$supplier_id}', '{$supplier_location_rel_id}', '{$location_parsed}')";
                    }
                }

                $DB->begin();
                try {
                    $DB->delete('supply_variant', "supplier_location_rel_id IN ({$ids})", null, 'delete_supply_variant');

                    if (count($insert_datas) > 0) {
                        $insert_values = implode(',', $insert_datas);
                        $insert_multi_queries = <<<EOF
INSERT INTO `osc_supply_variant` (product_type_variant_id, supplier_id, supplier_location_rel_id, location_parsed)
VALUES {$insert_values}
EOF;
                        $DB->query($insert_multi_queries, null, 'insert_items');
                    }

                    $DB->commit();
                } catch (Exception $ex) {
                    $DB->rollback();
                    throw new Exception($ex->getMessage());
                }

                try {
                    $update_products_data = $this->_getDataUpdateSupplyLocationProducts($list_product_type_variant_ids);
                    $total_query_update = count($update_products_data);

                    foreach ($update_products_data as $update_product_data) {
                        $product_supply_location = ',' . implode(',', $update_product_data['location_parsed']) . ',';
                        $update_product_ids = implode(',', $update_product_data['product_ids']);
                        $total_update_product += count($update_product_data['product_ids']);

                        $DB->update('catalog_product', [
                            'supply_location' => $product_supply_location
                        ], "product_id IN ({$update_product_ids})", null, 'update_product');
                    }
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }

            }

            $execute_time = microtime(true) - $start_time;
            $str_update_product_type_variant_ids = implode(',', $list_product_type_variant_ids);
            $message = OSC::$base_url .
                "\nCacheSellingVariant run successfully in {$execute_time} seconds." .
                "\nSupplier Location Rel IDs: {$ids}" .
                "\nTotal Product Updated: {$total_update_product}" .
                "\nTotal Query Updated: {$total_query_update}" .
                "\nProduct Type Variant Updated IDs: {$str_update_product_type_variant_ids}";

            OSC::helper('core/telegram')->sendMessage($message, '-409036884');
        } catch (Exception $ex) {
            $message = OSC::$base_url . "\n" . ' RenderSupplyVariant Error: ' . $ex->getMessage();
            OSC::helper('core/telegram')->sendMessage($message, '-409036884');

            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param $list_product_type_variant_ids
     * @return array
     * @throws OSC_Exception_Runtime
     */
    protected function _getDataUpdateSupplyLocationProducts($list_product_type_variant_ids) {
        $supply_variant_data = [];

        $list_ptv_ids = implode(',', $list_product_type_variant_ids);

        $DB = OSC::core('database');
        $DB->select('product_type_variant_id, location_parsed', 'supply_variant', "product_type_variant_id IN ({$list_ptv_ids})", null, null, 'fetch_supply_variant');

        $supply_variant_rows = $DB->fetchArrayAll('fetch_supply_variant');

        foreach ($supply_variant_rows as $supply_variant_row) {
            $locations = explode(',', substr($supply_variant_row['location_parsed'], 1, -1));
            foreach ($locations as $location) {
                $supply_variant_data[$supply_variant_row['product_type_variant_id']][$location] = $location;
            }
        }

        $collection = OSC::model('catalog/product_variant')->getCollection()
            ->addCondition('product_type_variant_id', $list_product_type_variant_ids, OSC_Database::OPERATOR_IN)
            ->addField('product_id', 'product_type_variant_id')
            ->load();

        $update_product_data = [];
        foreach ($collection as $variant) {
            $update_product_data[$variant->data['product_id']][] = $variant->data['product_type_variant_id'];
            sort($update_product_data[$variant->data['product_id']]);
        }

        $result = [];
        foreach ($update_product_data as $product_id => $product_type_variant_ids) {
            $key = implode('_', $product_type_variant_ids);
            $location_parsed = [];
            foreach ($product_type_variant_ids as $ptv_id) {
                $location_parsed = array_unique(array_merge($location_parsed, $supply_variant_data[$ptv_id]));
            }
            $result[$key]['product_ids'][] = $product_id;
            $result[$key]['location_parsed'] = $location_parsed;
        }

        return $result;
    }

}
