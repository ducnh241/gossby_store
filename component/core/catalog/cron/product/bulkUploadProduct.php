<?php

class Cron_Catalog_Product_BulkUploadProduct extends OSC_Cron_Abstract {
    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '2000M');
        //render mockup
        $DB = OSC::core('database')->getWriteAdapter();

        $limit = 5;

        $counter = 0;

        $count_error = 0;

        while ($counter < $limit && $count_error < 4) {
            $model = OSC::model('catalog/product_bulkQueue');

            $DB->select('*', $model->getTableName(), "`queue_flag` = 1 AND `action` = 'bulkUploadProduct'", '`added_timestamp` ASC, `queue_id` ASC', 1, 'fetch_queue');

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

                if ($data['type'] == 'beta') {
                    OSC::helper('catalog/product')->bulkUploadProductBeta($data['product_data'], $data['variants'], $data['images'], $data['member_id']);
                } elseif ($data['type'] == 'campaign') {
                    OSC::helper('catalog/campaign')->bulkUploadProductCampaign($data['product_type_variant'], $data['product_type'], $data['design_ids'], $data['product_data'], $data['images'], $data['member_id']);
                }

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();
                $count_error++;
            }
        }

        if ($counter >= $limit) {
            return false;
        }
    }
}




