<?php

class Cron_Catalog_Campaign_RenderDesignOrderBeta extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database')->getAdapter('db_master');

        $limit = 10;
        $counter = 0;

        $error_flag = false;

        $design_ids = [];

        while ($counter < $limit) {
            $model = OSC::model('catalog/order_bulkQueue');

            $DB->select('*', $model->getTableName(), "shop_id = ". OSC::getShop()->getId() ." AND queue_flag = 1 AND action = 'render_design_order_beta'", 'added_timestamp ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            $queue_data = $model->data['queue_data'];

            try {

                $line_item_id = intval($model->data['secondary_key']);

                if ($line_item_id < 1) {
                    throw new Exception('Line item id need more than 0');
                }

                $line_item = OSC::model('catalog/order_item')->load($line_item_id);

                $service = '';

                if (isset($queue_data['service'])) {
                    $service = $queue_data['service'];
                }

                $response_queue_render = OSC::helper('catalog/campaign')->addQueueRenderDesignBeta($line_item, $service);

                $_design_ids = $response_queue_render['design_ids'];

                if (!is_array($_design_ids) || !$response_queue_render['status']) {

                    $model->setData(['error' => $response_queue_render['message'], 'queue_flag' => 2, 'added_timestamp' => time()])->save();
                    continue;
                }

                $design_ids = array_merge($design_ids, $_design_ids);

                $model->delete();
            } catch (Exception $ex) {

                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();
                $error_flag = true;
            }
        }

        try {
            OSC::helper('personalizedDesign/common')->lockDesignByIds(array_unique($design_ids));
        } catch (Exception $ex) {

        }

        if ($counter == $limit || $error_flag) {
            return false;
        }
    }

}
