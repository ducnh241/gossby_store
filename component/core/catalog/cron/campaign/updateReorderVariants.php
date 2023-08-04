<?php

class Cron_Catalog_Campaign_UpdateReorderVariants extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 1000;
        $counter = 0;
        $count_error = 0;

        while ($counter < $limit && $count_error < 5) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'updateReorderVariants'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');
            $row = $DB->fetchArray('fetch_queue');
            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            try {
                $data = $model->data['queue_data'];

                $product_id = $data['product_id'];
                $product_type_id= $data['product_type_id'];
                $product_type_variant_id = $data['product_type_variant_id'];
                $position = intval($data['position']);


                $collection = OSC::model('catalog/product_variant')->getCollection()
                    ->addField('id','product_id')
                    ->addCondition('product_type_variant_id', $product_type_variant_id, OSC_Database::OPERATOR_EQUAL)
                    ->load()
                    ->toArray();
                if ( count($collection) > 0) {
                    $variantIds = [];
                    $DB = OSC::core('database')->getWriteAdapter();
                    $DB->begin();

                    foreach ($collection as $item) {
                        $variantIds[] = $item['id'];

                        // Update product meta
                        try {
                            $product_model = OSC::model('catalog/product')->load($item['product_id']);
                            $meta_data = $product_model->data['meta_data'];
                            $is_reorder = intval($meta_data['campaign_config']['is_reorder']);
                            if ($is_reorder === 0) {
                                $meta_data['campaign_config']['is_reorder'] = 1;
                                $product_model->setData(['meta_data' => $meta_data])->save();
                            }
                        } catch (Exception $ex) {
                            // Do nothing
                        }
                    }

                    $locked_key = OSC::makeUniqid();

                    OSC_Database_Model::lockPreLoadedModel($locked_key);
                    try {
                        $updateId = implode(',', $variantIds);

                        $DB->query("UPDATE {$DB->getTableName('product_variant')} SET `position`= {$position} WHERE `id` IN({$updateId})", null, 'update_variant_position');

                        $DB->commit();
                    } catch (Exception $ex) {
                        $DB->rollback();

                        OSC_Database_Model::unlockPreLoadedModel($locked_key);

                        throw new Exception($ex->getMessage());
                    }

                    OSC_Database_Model::unlockPreLoadedModel($locked_key);
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 1, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}
