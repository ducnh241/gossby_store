<?php

class Cron_Catalog_Product_Bulk_ImportDataSEO extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database');

        $limit = 300;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'importDataSEO'", '`added_timestamp` ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter ++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
                if ($model->data['queue_data']['product']['product_id'] > 0) {
                    $product = OSC::model('catalog/product')->load($model->data['queue_data']['product']['product_id']);

                    if ($product->checkMasterLock()) {
                        throw new Exception('You do not have the right to perform this function');
                    }
                }

                $this->_processImport($model->data['queue_data']);

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage()])->save();
            }
        }

        if ($counter == $limit) {
            OSC::core('cron')->addQueue('catalog/product_bulk_importDataSEO', null, ['requeue_limit' => -1, 'estimate_time' => 60 * 5]);
        }
    }

    protected function _processImport($import_data) {
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            /* @var $product Model_Catalog_Product */
            $product = OSC::model('catalog/product');

            if ($import_data['product']['product_id'] > 0) {
                $product->load($import_data['product']['product_id']);
            } else {
                unset($import_data['product']['product_id'], $import_data['product']['sku']);
            }

            $import_data['product']['meta_tags']['keywords'] = $product->data['meta_tags']['keywords'];

            foreach (['title', 'topic', 'description', 'seo_tags'] as $key) {
                if ($import_data['product'][$key] == '' || !isset($import_data['product'][$key])) {
                    $import_data['product'][$key] = $product->data[$key];
                }
            }

            foreach (['meta_title', 'meta_keywords', 'meta_description'] as $key) {
                if ($import_data['product']['meta_tags'][substr($key, 5)] == '' || !isset($import_data['product']['meta_tags'][substr($key, 5)])) {
                    $import_data['product']['meta_tags'][substr($key, 5)] = $product->data['meta_tags'][substr($key, 5)];
                }
            }

            if (count($import_data['product']['seo_tags']) >= 1 || isset($import_data['product']['seo_tags'])) {
                foreach ($import_data['product']['seo_tags'] as $key => $seo_tag) {
                    if ($seo_tag['collection_id'] == 0 && $seo_tag['collection_title'] == '') {
                        unset($import_data['product']['seo_tags'][$key]);
                    }
                }
            }

            if (count($import_data['product']['seo_tags']) < 1) {
                $import_data['product']['seo_tags'] = $product->data['seo_tags'];
            }

            if (!is_numeric($import_data['product']['seo_status']) || intval($import_data['product']['seo_status']) < 0 || intval($import_data['product']['seo_status']) > 1) {
                $import_data['product']['seo_status'] = intval($product->data['seo_status']);
            } else {
                $import_data['product']['seo_status'] = intval($import_data['product']['seo_status']);
            }

            if (isset($import_data['product']['meta_tags']['slug']) && trim($import_data['product']['meta_tags']['slug'])) {
                $import_data['product']['slug'] = trim($import_data['product']['meta_tags']['slug']);
                unset($import_data['product']['meta_tags']['slug']);
            }else{
                $import_data['product']['slug'] = $product->data['slug'];
            }

            if (isset($product->data['meta_tags']['is_clone']) && intval($product->data['meta_tags']['is_clone']) > 0) {
                if (($import_data['product']['topic'] != $product->data['topic']) || ($import_data['product']['title'] != $product->data['title'])) {
                    $import_data['product']['slug'] = OSC::core('string')->cleanAliasKey($import_data['product']['topic'] . '-' . $import_data['product']['title']);
                }
            }

            $product->setData($import_data['product'])->save();

            $DB->commit();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }
    }

}
