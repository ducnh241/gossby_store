<?php

class Helper_Catalog_MasterSync extends OSC_Object {
    public function syncCommonData($sync_data, $key, $table, $pk = 'id') {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        $supplier_location_rel_ids = [];
        try {
            if (isset($sync_data[$key]['create'])) {
                $supplier_location_rel_ids = array_keys($sync_data[$key]['create']);
                $this->createOrUpdateData($DB, $sync_data[$key]['create'], $table);
            }

            if (isset($sync_data[$key]['delete'])) {
                $item_ids = implode(',', $sync_data[$key]['delete']);
                $supplier_location_rel_ids = array_unique(array_merge(
                    $supplier_location_rel_ids,
                    array_keys($sync_data[$key]['delete'])
                ));

                foreach (explode(',', $item_ids) as $item_id) {
                    try {
                        switch ($table) {
                            case 'product_type':
                                $model = OSC::model('catalog/productType')->load($item_id);
                                OSC::core('observer')->dispatchEvent('model__product_type__row_deleted', [
                                    'model' => $model
                                ]);
                                break;
                            case 'product_type_variant':
                                $model = OSC::model('catalog/productType_variant')->load($item_id);
                                OSC::core('observer')->dispatchEvent('model__product_type_variant__row_deleted', [
                                    'model' => $model
                                ]);
                                break;
                            case 'product_type_description':
                                $model = OSC::model('catalog/productTypeDescription')->load($item_id);
                                OSC::core('observer')->dispatchEvent('model__product_type_description__row_deleted', [
                                    'model' => $model
                                ]);
                                break;
                            case 'catalog_product_pack':
                                $model = OSC::model('catalog/product_pack')->load($item_id);
                                OSC::core('observer')->dispatchEvent('model__catalog_product_pack__row_deleted', [
                                    'model' => $model
                                ]);
                                break;
                            default:
                                break;
                        }
                    } catch (Exception $exception) { }
                }

                $DB->query("DELETE FROM {$DB->getTableName($table)} WHERE {$pk} IN ({$item_ids})", null, 'clean_data_' . $key);
            }

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        if ($key === 'supplier_location') {
            OSC::core('cron')->addQueue('supplier/renderSupplyVariant', ['supplier_location_rel_ids' => $supplier_location_rel_ids], [
                'overwrite',
                'running_time' => 10,
                'requeue_limit' => -1,
                'estimate_time' => 60 * 60
            ]);
        }
    }

    protected function createOrUpdateData($DB, $data_create, $table) {
        foreach ($data_create as $data) {
            $insert = ['columns' => [], 'values' => []];
            $update = [];

            foreach (array_keys($data) as $data_column) {
                $insert['columns'][] = $data_column;
                $insert['values'][] = ':' . $data_column;

                $update[] = $data_column . '=:' . $data_column;
            }

            $insert['columns'] = implode(',', $insert['columns']);
            $insert['values'] = implode(',', $insert['values']);
            $update = implode(',', $update);

            $DB->query("INSERT INTO {$DB->getTableName($table)} ({$insert['columns']}) VALUES ({$insert['values']}) ON DUPLICATE KEY UPDATE " . $update, $data, 'insert_new_data');

            if (isset($data['id']) && !empty($data['id'])) {
                try {
                    //Reset cache
                    switch ($table) {
                        case 'product_type':
                            $model = OSC::model('catalog/productType')->load($data['id']);
                            OSC::core('observer')->dispatchEvent('model__product_type__after_save', [
                                'model' => $model,
                                'columns' => $insert['columns']
                            ]);
                            break;
                        case 'product_type_variant':
                            $model = OSC::model('catalog/productType_variant')->load($data['id']);
                            OSC::core('observer')->dispatchEvent('model__product_type_variant__after_save', [
                                'model' => $model,
                                'columns' => $insert['columns']
                            ]);
                            break;
                        case 'product_type_description':
                            $model = OSC::model('catalog/productTypeDescription')->load($data['id']);
                            OSC::core('observer')->dispatchEvent('model__product_type_description__after_save', [
                                'model' => $model,
                                'columns' => $insert['columns']
                            ]);
                            break;
                        case 'catalog_product_pack':
                            $model = OSC::model('catalog/product_pack')->load($data['id']);
                            OSC::core('observer')->dispatchEvent('model__catalog_product_pack__after_save', [
                                'model' => $model,
                                'columns' => $insert['columns']
                            ]);
                            break;
                        default:
                            break;

                    }
                } catch (Exception $exception) {}
            }
        }
    }

    public function syncStoreSetting($sync_data) {
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        if (!is_array($sync_data)) {
            $sync_data = [];
        }

        try {
            foreach ($sync_data as $key => $value) {
                OSC::helper('core/setting')->set($key, $value);
            }

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);
    }

}
