<?php

class Cron_Catalog_Collection_Bulk_ImportDataSEO extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database');

        $limit = 300;
        $counter = 0;

        while ($counter < $limit) {
            $model = OSC::model('catalog/collection_bulkQueue');

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
                if ($model->data['queue_data']['collection']['collection_id'] > 0) {
                    $collection = OSC::model('catalog/collection')->load($model->data['queue_data']['collection']['collection_id']);
                }

                $this->_processImport($model->data['queue_data']);

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage()])->save();
            }
        }

        if ($counter == $limit) {
            OSC::core('cron')->addQueue('catalog/collection_bulk_importDataSEO', null, ['requeue_limit' => -1, 'estimate_time' => 60*5]);
        }
    }

    protected function _processImport($import_data) {
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            /* @var $collection Model_Catalog_Collection*/
            $collection = OSC::model('catalog/collection');

            if ($import_data['collection']['collection_id'] > 0) {
                $collection->load($import_data['collection']['collection_id']);
            } else {
                unset($import_data['collection']['collection_id']);
            }

            foreach (['title', 'custom_title', 'description'] as $key) {
                if ($import_data['collection'][$key] == '' || !isset($import_data['collection'][$key])) {
                    $import_data['collection'][$key] = $collection->data[$key];
                }
            }

            foreach (['meta_title', 'meta_keywords', 'meta_description'] as $key) {
                if ($import_data['collection']['meta_tags'][substr($key, 5)] == '' || !isset($import_data['collection']['meta_tags'][substr($key, 5)])) {
                    $import_data['collection']['meta_tags'][substr($key, 5)] = $collection->data['meta_tags'][substr($key, 5)];
                }
            }

            if (isset($import_data['collection']['meta_tags']['slug']) && trim($import_data['collection']['meta_tags']['slug'])) {
                $import_data['collection']['slug'] = OSC::helper('alias/common')->renameSlugDuplicate($import_data['collection']['meta_tags']['title'], trim($import_data['collection']['meta_tags']['slug']), $collection->getId(), 'collection');
                unset($import_data['collection']['meta_tags']['slug']);

                try {
                    OSC::helper('alias/common')->save($import_data['collection']['slug'], 'catalog_collection', $collection->getId());
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            }

            $old_seo_tag_title =  $collection->data['custom_title'] ?: $collection->data['title'];

            $collection->setData($import_data['collection'])->save();

            try {
                OSC::helper('catalog/product')->updateSeoTags($import_data['collection']['collection_id'], $old_seo_tag_title, $collection->data['custom_title'] ?: $collection->data['title']);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $DB->commit();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }
    }

}
