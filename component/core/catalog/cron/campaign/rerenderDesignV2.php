<?php

class Cron_Catalog_Campaign_RerenderDesignV2 extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $DB = OSC::core('database')->getAdapter('db_master');

        $limit = 5;
        $counter = 0;

        $error_flag = false;

        while ($counter < $limit) {
            $model = OSC::model('catalog/order_bulkQueue');

            $DB->select('*', $model->getTableName(), "shop_id = ". OSC::getShop()->getId() ." AND queue_flag = 1 AND action in ('campaign_rerender_v2', 'campaign_rerender_order_desk', 'campaign_edit_design_new_product_type', 'campaign_rerender_order_desk_cross_sell')", 'added_timestamp ASC', 1, 'fetch_queue');

            $row = $DB->fetchArray('fetch_queue');

            $DB->free('fetch_queue');

            if (!$row) {
                break;
            }

            $counter++;

            $model->bind($row);

            $model->setData('queue_flag', 0)->save();

            $action = $model->data['action'];

            $queue_data = $model->data['queue_data'];

            try {
	            $line_item_id = intval($model->data['secondary_key']);

            	if ($line_item_id < 1) {
            		throw new Exception('Line item id need more than 0');
	            }

                $line_item = OSC::model('catalog/order_item')->load($line_item_id);

                switch ($action) {
                    case 'campaign_rerender_v2':
                        $user_name = null;

                        if (isset($queue_data['user_name']) && trim($queue_data['user_name']) != '') {
                            $user_name = $queue_data['user_name'];
                        }

                        OSC::helper('catalog/campaign')->rerenderDesign($line_item, $user_name);
                        break;
                    case 'campaign_edit_design_new_product_type':
                        $variant_id_new = intval($queue_data['variant_id_new']);

                        $print_template_id_new = intval($queue_data['print_template_id_new']);

                        $config_option = $queue_data['config_option'];

                        $id_edit_design_confirm = $queue_data['id_edit_design_confirm'];

                        $data_design_new = OSC::helper('catalog/campaign')->createNewDesignDataEditDesign($line_item, $variant_id_new, $print_template_id_new, $config_option);

                        OSC::helper('catalog/campaign')->updateDesign($line_item, $data_design_new['campaign_data'], ['skip_log' => 1, 'print_template_id_new' => $print_template_id_new]);

                        $model_confirm = OSC::model('catalog/order_editDesignChangeProductType')->load($id_edit_design_confirm);

                        $additional_data_confirm = $model_confirm->data['additional_data'];

                        $additional_data_confirm['sku'] = $data_design_new['sku'];
                        $additional_data_confirm['options'] = $data_design_new['options'];
                        $additional_data_confirm['product_type'] = $data_design_new['product_type'];

                        $model_confirm->setData([
                            'additional_data' => $additional_data_confirm,
                            'status_flag' => Model_Catalog_Order_EditDesignChangeProductType::RENDER_SUCCESS
                        ])->save();

                        OSC::helper('master/common')->callApi('/catalog/api_order/addQueueConfirmDesign', ['id_edit_design_confirm' => $model_confirm->getId()]);

                        break;
                    case 'campaign_rerender_order_desk':
                        $print_template_id_old = intval($queue_data['print_template_id_old']);
                        $print_template_id_new = intval($queue_data['print_template_id_new']);

                        if ($print_template_id_old < 1 || $print_template_id_new < 1) {
                            throw new Exception("print_template_id_old or print_template_id_new not found");
                        }

                        OSC::helper('catalog/campaign')->campaignRerenderDesignByOrderDesk($line_item, $print_template_id_new, $print_template_id_old);

                        break;
                    case 'campaign_rerender_order_desk_cross_sell':
                        $print_template_id_new = intval($queue_data['print_template_id_new']);
                        $segments = $queue_data['segments'];

                        OSC::helper('catalog/campaign')->campaignRerenderDesignCrossSellByOrderDesk($line_item, $print_template_id_new, $segments);
                        break;
                }

                $model->delete();
            } catch (Exception $ex) {

                $model->setData(['error' => $ex->getMessage(), 'queue_flag' => 2, 'added_timestamp' => time()])->save();

                if (in_array($action, ['campaign_rerender_order_desk', 'campaign_rerender_order_desk_cross_sell'])) {
                    try {
                        $model_order_desk_queue = OSC::model('orderdesk/queueV2')->loadByUKey( $queue_data['ukey']);
                        $model_order_desk_queue->setData(['queue_flag' => 5, 'error_message' => 'queue_flag:0/ ' . $ex->getMessage()])->save();
                    } catch (Exception $ex) {

                    }
                }

                $error_flag = true;
            }

        }

        if ($counter == $limit || $error_flag) {
            return false;
        }
    }

}
