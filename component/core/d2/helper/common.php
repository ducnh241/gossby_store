<?php

class Helper_D2_Common extends OSC_Object {

    const STATUS_BIZ = [
        11 => '11 - Waiting',
        12 => '12 - Doing',
        18 => '18 - Check Design',
        19 => '19 - Done Preview',
        24 => '24 - Check Updated Design',
        25 => '25 - Edit Request'
    ];

    const FIELD_UPDATE_BY_MEMBERS = [
        'status' => 'Status Biz',
        'mockup' => 'design.mockup',
        'full_front' => 'design.full_front',
        'full_back' => 'design.full_back',
        'psd' => 'design.psd',
        'note' => 'Note .CTV',
    ];

    const OSC_AIRTABLE_RETRY_SYNC_FIELD = 3;
    const RUNNING_TIME_RETRY = 60*10; // 10 minutes

    /**
     * @param $model_item Model_Catalog_Order_Item
     * @return array
     * @throws Exception
     */
    public function exportOptionPersonalized(Model_Catalog_Order_Item $model_item): array
    {

        if ( $model_item->getId() < 1 ) {
            throw new Exception('Order Item not exist!');
        }

        $additional_data = $model_item->data['additional_data'];

        $design_ids = [];
        $ps_photo_name = [];
        $ps_photo_values = [];
        $ps_photo_others = [];
        $ps_photo_opt = [];
        $ps_clipart = [];
        $custom_data = [];

        $url_personalized = OSC_ENV == 'production' ? 'https://personalizeddesign.9prints.com/storage' : 'https://personalizeddesign-v2.9prints.com/storage';

        if ($model_item->isSemitestMode()) {
            $custom_data = $model_item->getSemitestData();
        } else if ($model_item->getCampaignDataIdx()) {
            $custom_data = $model_item->getCampaignData();
        }

        foreach ($custom_data as $design_id => $data) {
            $design_ids[] = $design_id;

            $design_url_beta = $additional_data['design_url_beta'] ?? [];
            $design_url_clip_art_beta = $additional_data['design_url_clip_art_beta'] ?? [];
            $design_url_preview_beta = $additional_data['design_url_preview_beta'] ?? [];

            if (isset($design_url_clip_art_beta[$design_id]) && count($design_url_clip_art_beta[$design_id]) > 0) {
                foreach ($design_url_clip_art_beta[$design_id] as $clip_art_name => $clip_art_url) {
                    $ps_clipart[] = $clip_art_name . ': ' . $clip_art_url;
                }
            }

            if (isset($design_url_beta[$design_id])) {
                $ps_clipart[] = 'all_clipart: ' . $design_url_beta[$design_id];
            }

            if (isset($design_url_preview_beta[$design_id])) {
                $ps_clipart[] = 'all_preview: ' . $design_url_preview_beta[$design_id];
            }

            foreach ($data['config_preview'] as $key => $value) {
                $layer = trim($value['layer']);

                if (is_numeric(stripos($layer, 'ps_opt_')) && stripos($layer, 'ps_opt_') == 0) {
                    $value_opt = str_replace('ps_opt_', '', $layer);

                    $_value = preg_replace('/<[^>]+>/', '', $value['value']);

                    if ($value['type'] == 'checker') {
                        $_value = $value['value'] == 1 ? 'Yes' : 'No';
                    }

                    if ($value['type'] == 'imageUploader') {
                        $value['value'] = OSC::decode($value['value'], true);
                        $_value = $url_personalized . '/' . $value['value']['file'];
                    }

                    switch ($value_opt) {
                        case is_numeric(stripos($value_opt, '#1')):
                            $ps_photo_opt['ps_photo_01_opt'][] = $value_opt . ' : ' . $_value;
                            break;
                        case is_numeric(stripos($value_opt, '#2')):
                            $ps_photo_opt['ps_photo_02_opt'][] =  $value_opt . ' : ' . $_value;
                            break;
                        case is_numeric(stripos($value_opt, '#3')):
                            $ps_photo_opt['ps_photo_03_opt'][] =  $value_opt . ' : ' . $_value;
                            break;
                        case is_numeric(stripos($value_opt, '#4')):
                            $ps_photo_opt['ps_photo_04_opt'][] =  $value_opt . ' : ' . $_value;
                            break;
                        case is_numeric(stripos($value_opt, '#5')):
                            $ps_photo_opt['ps_photo_05_opt'][] =  $value_opt . ' : ' . $_value;
                            break;
                        case is_numeric(stripos($value_opt, '*')) && stripos($value_opt, '*') == 0:
                            $ps_photo_opt['ps_product_opt'][] =  $value_opt . ' : ' . $_value;
                            break;
                        default:
                            $ps_photo_opt['ps_opt_others'][] =  $value_opt . ' : ' . $_value;
                            break;
                    }
                }

                if (is_numeric(stripos($layer, 'ps_photo_')) && stripos($layer, 'ps_photo_') == 0) {
                    $value_photo = str_replace('ps_photo_', '', strtolower($layer));

                    $image_upload_value = OSC::decode($value['value'], true);

                    switch ($value_photo) {
                        case is_numeric(stripos($value_photo, '#1')):
                            $ps_photo_name['ps_photo_01_name'][] = $value_photo;

                            if ($value['type'] == 'imageUploader') {
                                $ps_photo_values['ps_photo_01_value'][] = $url_personalized . '/' . $image_upload_value['file'];
                            }

                            break;
                        case is_numeric(stripos($value_photo, '#2')):
                            $ps_photo_name['ps_photo_02_name'][] = $value_photo;

                            if ($value['type'] == 'imageUploader') {
                                $ps_photo_values['ps_photo_02_value'][] = $url_personalized . '/' . $image_upload_value['file'];
                            }

                            break;
                        case is_numeric(stripos($value_photo, '#3')):
                            $ps_photo_name['ps_photo_03_name'][] = $value_photo;

                            if ($value['type'] == 'imageUploader') {
                                $ps_photo_values['ps_photo_03_value'][] = $url_personalized . '/' . $image_upload_value['file'];
                            }

                            break;
                        case is_numeric(stripos($value_photo, '#4')):
                            $ps_photo_name['ps_photo_04_name'][] = $value_photo;

                            if ($value['type'] == 'imageUploader') {
                                $ps_photo_values['ps_photo_04_value'][] = $url_personalized . '/' . $image_upload_value['file'];
                            }

                            break;
                        case is_numeric(stripos($value_photo, '#5')):
                            $ps_photo_name['ps_photo_05_name'][] = $value_photo;

                            if ($value['type'] == 'imageUploader') {
                                $ps_photo_values['ps_photo_05_value'][] = $url_personalized . '/' . $image_upload_value['file'];
                            }

                            break;
                        default:
                            if ($value['type'] == 'imageUploader') {
                                $image_upload_value = OSC::decode($value['value'], true);
                                $ps_photo_others[] = $value_photo . ' : ' . $url_personalized . '/' . $image_upload_value['file'];
                            } else {
                                $ps_photo_others[] = $value_photo . ':' . $value['value'];
                            }

                            break;
                    }
                }
            }
        }

        return [
            'design_id' => $design_ids,
            'ps_photo_opt' => $ps_photo_opt,
            'ps_photo_name' => $ps_photo_name,
            'ps_photo_values' => $ps_photo_values,
            'ps_photo_others' => $ps_photo_others,
            'ps_clipart' => $ps_clipart
        ];
    }

    /**
     * @param $product Model_Catalog_Product
     * @return void
     */
    public function afterProductD2Delete(Model_Catalog_Product $product) {
        try {
            /* @var Model_D2_Product $d2_product */
            $d2_product = OSC::model('d2/product')->getCollection()->addCondition('product_id', $product->getId())->load()->first();
            if ($d2_product) {
                $d2_product->setData([
                    'title' => $product->data['title']
                ])->save();
            }
        } catch (Exception $ex) {

        }
    }

    /**
     * @param array $data
     * @param $order_id
     * @param Model_Catalog_Order_Item $order_line_item
     * @return array
     * @throws Exception
     */
    public function getRecordUpdateOrderStatus($data, $order_id, $order_line_item){
        $order_status = $data['order_status'];

        try {

            $addition_data = $order_line_item->data['additional_data'];
            if (isset($addition_data['sync_airtable_flag'])) {

                if (isset($addition_data['sync_airtable_id'])) {

                    $record_upd_airtable = [
                        'id' => $addition_data['sync_airtable_id'],
                        'fields' => [
                            'Order Status' => $order_status
                        ]
                    ];

                } else {

                    try {
                        $airtable_id = $this->filterOrderItemAirtableId("AND({Order Line ID} = {$order_line_item->getId()}, {Order ID} = {$order_id})");
                        $addition_data['sync_airtable_id'] = $airtable_id;
                        $order_line_item->setData([
                            'additional_data' => $addition_data
                        ])->save();
                    } catch (Exception $ex) {
                        throw new Exception($ex->getMessage());
                    }

                    $record_upd_airtable = [
                        'id' => $airtable_id,
                        'fields' => [
                            'Order Status' => $order_status
                        ]
                    ];
                }

            } else {
                throw new Exception('Order Item not sync airtable');
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        return $record_upd_airtable;
    }

    /**
     * @param $filter
     * @return mixed
     * @throws Exception
     */
    public function filterOrderItemAirtableId($filter){
        $fields = [
            'Order Line ID',
            'Order Code',
            'Order ID'
        ];

        $sorts = [[
            'field' => 'Added to AirTable',
            'direction' => 'asc'
        ]];

        try {
            $response = OSC::core('airtable')->filterData($fields, $filter, $sorts, OSC_AIRTABLE_ORDER_LINE_TABLE);

            if (isset($response['content']['error'])) {
                throw new Exception($response['content']['error']['message']);
            }

            return $response['content']['records'][0]['id'] ?? null;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

    }

    /**
     * @param $email
     * @return mixed
     * @throws Exception
     */
    public function getUserProfile($email) {
        try {
            $response = OSC::core('airtable')->filterData([], "{Email} = '{$email}'", [], OSC_AIRTABLE_USER_PROFILE_TABLE);

            if (isset($response['content']['error'])) {
                throw new Exception($response['content']['error']['message']);
            }

            return $response['content']['records'][0];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param $pic_name
     * @param $status_biz
     * @param $offset
     * @param int $limit
     * @param null $order_line_id
     * @return mixed
     * @throws Exception
     */
    public function getOrderByPicName($pic_name, $status_biz, $offset, $limit = 25, $order_line_id = null) {
        $fields = [];

        $sorts = [[
            'field' => 'Added to AirTable',
            'direction' => 'asc'
        ]];

        try {
            $status_biz_query= [] ;
            foreach ($status_biz as $status) {
                $status_biz_query[]= "{Status Biz} = '". self::STATUS_BIZ[$status] ."'" ;
            }
            $filter = "AND({PIC CTV} = '{$pic_name}', OR(". implode(',', $status_biz_query) . ')' . ($order_line_id ? ", {Order Line ID} = '{$order_line_id}'" : '') .')';
            $response = OSC::core('airtable')->filterData($fields, $filter, $sorts, OSC_AIRTABLE_ORDER_LINE_TABLE, $limit, $offset);

            if (isset($response['content']['error'])) {
                OSC::logFile('getOrderByPicName: ' . OSC::encode($response['content']['error']), 'get_order_member_log');
                throw new Exception($response['content']['error']['message'] ?? 'Internet server error');
            }

            return $response['content'];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param array $fields
     * @return void
     */
    public function getResourceDesign($fields) {
        $design_id = $fields['design_id'];

        /* @var $DB OSC_Database */
        $DB = OSC::core('database')->getWriteAdapter();

        $query = "select 
resource.*, 
(select count(id) from osc_d2_condition as con where con.resource_id = resource.id) as quantity_con
FROM osc_d2_resource as resource
where resource.design_id = {$design_id}
order by quantity_con desc";

        $DB->query($query, null, 'fetch_resource');

        $resources = $DB->fetchArrayAll('fetch_resource');

        $DB->free('fetch_resource');

        if (empty($resources)) {
            return null;
        }

        $resource_url = null;

        /* @var $resource Model_D2_Resource */
        foreach ($resources as $resource) {
            $model = OSC::model('d2/resource')->bind($resource);
            /* @var $condition Model_D2_Condition */
            $conditions = $model->getConditions();
            $is_matches = true;
            foreach ($conditions as $condition) {
                if (strtolower(trim($fields[$condition->data['condition_key']])) != strtolower(trim($condition->data['condition_value']))) {
                    $is_matches = false;
                    break;
                }
            }

            if ($is_matches) {
                $resource_url = $model->data['resource_url'];
                break;
            }
        }
        return $resource_url;
    }

    /*
     * @param $rows
     * @param $action
     * @return void
     * @throws OSC_Database_Model_Exception
     */
    public function processFlowReply($rows, $action) {

        $data = [];

        foreach ($rows as $row) {

            $bulk_queue = OSC::model('catalog/product_bulkQueue');

            $bulk_queue->bind($row);
            $bulk_queue->setData('queue_flag', Model_Catalog_Product_BulkQueue::QUEUE_FLAG['running'])->save();

            try {
                $queue_data = $bulk_queue->data['queue_data'];
                $order_item = OSC::model('catalog/order_item')->load($queue_data['orderItemId']);
                $additional_data = $order_item->data['additional_data'];

                OSC::helper('d2/common')->writeLog($queue_data['orderId'], $queue_data['orderItemId'], OSC::encode($queue_data), $bulk_queue->data['action'] . ' sync Airtable process');

                // Save to order item for resync airtable
                $field = '9p_photo#others';

                if (preg_match('/#[1-9]{1,2}/', $queue_data['layerName'], $matches)) {
                    $field = '9p_photo' . $matches[0];
                }

                $flow_name = $additional_data['flow_name'] ?? [];
                $flow_name[] = "{$field} : " . ($queue_data['flowName'] ?? '');

                $additional_data[$field] = $queue_data['resultImageUrl'];
                $additional_data['flow_name'] = $flow_name;
                $order_item->setData([
                    'additional_data' => $additional_data
                ])->save();

                if (!isset($additional_data['sync_airtable_id'])) { // check order duoc dong bo len airtable chua
                    throw new Exception('Order Item not sync airtable');
                }

                $airtable_id = $additional_data['sync_airtable_id'];

                $record = [
                    'id' => $airtable_id,
                    'fields' => [
                        $field => $queue_data['resultImageUrl'],
                        '9p_photo_flows' => implode("\n", $flow_name)
                    ]
                ];

                $data[] = [
                    'ukey' => "updateAirtable_{$queue_data['orderItemId']}_:" . OSC::makeUniqid(),
                    'member_id' => 1,
                    'action' => 'update_raw_airtable',
                    'queue_data' => $record
                ];

                $bulk_queue->delete();

                OSC::helper('d2/common')->writeLog($queue_data['orderId'], $queue_data['orderItemId'], OSC::encode($queue_data), $bulk_queue->data['action'] . ' sync Airtable add request update successfully');

            } catch (Exception $ex) {
                $bulk_queue->setData([
                    'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'],
                    'error' => $ex->getMessage()
                ])->save();

                OSC::helper('d2/common')->writeLog($queue_data['orderId'], $queue_data['orderItemId'], OSC::encode($queue_data), $bulk_queue->data['action'] . ' sync Airtable error: ' . $ex->getMessage());
            }
        }

        if (!empty($data)) {
            OSC::model('catalog/product_bulkQueue')->insertMulti($data);
            OSC::core('cron')->addQueue('d2/updateRawAirtable', null, ['ukey'=> 'd2/updateRawAirtable','requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60]);
        }
    }

    public function writeLog($order_id = 0, $order_item_id = 0, $queue_data = '', $message = '') {
        $mongodb = OSC::core('mongodb');
        $mongodb->insert('d2_flow', [
            'order_id' => $order_id,
            'order_item_id' => $order_item_id,
            'queue_data' => is_string($queue_data) ? $queue_data : OSC::encode($queue_data),
            'message' => $message,
            'added_timestamp' => time(),
            'created_at' => date('d M Y H:i:s')
        ], 'product');
    }
}
