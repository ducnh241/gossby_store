<?php

class Cron_Catalog_Campaign_RerenderDesignUrl extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $DB = OSC::core('database')->getAdapter('db_master');

        $limit = 5;
        $counter = 0;

        $error_flag = false;

        while ($counter < $limit) {
            $model = OSC::model('catalog/order_bulkQueue');

            $DB->select('*', $model->getTableName(), "shop_id = ". OSC::getShop()->getId() ." AND queue_flag = 1 AND action = 'campaign_rerender_design_url'", 'added_timestamp ASC', 1, 'fetch_queue');

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

                $print_template_id_old = intval($queue_data['print_template_id_old']);
                $print_template_id_new = intval($queue_data['print_template_id_new']);

                if ($print_template_id_old < 1 || $print_template_id_new < 1) {
                    throw new Exception("print_template_id_old or print_template_id_new not found");
                }

                OSC::helper('catalog/campaign')->campaignRerenderDesignByOrderDesk($line_item, $print_template_id_new, $print_template_id_old);

                $model->delete();
            } catch (Exception $ex) {
                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();

                $error_flag = true;
            }

        }

        if ($counter == $limit || $error_flag) {
            return false;
        }
    }

}
