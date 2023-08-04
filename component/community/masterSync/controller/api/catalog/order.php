<?php

class Controller_MasterSync_Api_Catalog_Order extends Abstract_MasterSync_Controller_Api {

    public function actionFulfill() {
        try {
            $order = OSC::model('catalog/order')->load($this->_request->get('order_id'));

            $fulfill_by_process = $this->_request->get('fulfill_by_process') !== false ? true : false ;

            if ($fulfill_by_process) {
                $fulfillable_items = OSC::helper('catalog/order')->lineItemsGetByProcess($order);
            } else {
                $fulfillable_items = OSC::helper('catalog/order')->lineItemsGetByFulfillable($order);

            }


            if (count($fulfillable_items) < 1) {
                throw new Exception('The order is already fulfilled');
            }

            $line_items = $this->_request->get('line_items');

            if (!is_array($line_items)) {
                $line_items = [];
            }

            $service = $this->_request->get('service');

            $fulfillment = OSC::helper('catalog/order')->fulfill(
                $order->getId(), $line_items, $this->_request->get('tracking_number'), $this->_request->get('shipping_carrier'), $this->_request->get('tracking_url'), true , $fulfill_by_process ,$service
            );

            $this->_ajaxResponse([
                'fulfillment' => $fulfillment->data
            ]);

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionLock()
    {
        try {
            $order = OSC::model('catalog/order')->load($this->_request->get('order_id'));

            $order->setData(['master_lock_flag' => 1])->save();

            $this->_ajaxResponse([
                'order' => $order
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());

        }
    }
    public function actionUnlock()
    {
        try {
            $order = OSC::model('catalog/order')->load($this->_request->get('order_id'));

            $order->setData(['master_lock_flag' => 0])->save();

            $this->_ajaxResponse([
                'order' => $order
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());

        }
    }

    public function actionResend(){
        $order_id = intval($this->_request->get('order_id'));
        $item_ids = $this->_request->get('item_ids');

        if (!isset($item_ids) || !is_array($item_ids)) {
            $item_ids = [];
        }

        if ($order_id < 1) {
            throw new Exception('order id not found');
        }

        try {
            $DB = OSC::core('database')->getWriteAdapter();
            $DB_MASTER = OSC::core('database')->getAdapter('db_master');

            $DB_MASTER->select('*', 'catalog_order_item', "item_id IN (" . implode(',', $item_ids) . ")", null, count($item_ids), 'fetch_order_item');

            $rows = $DB_MASTER->fetchArrayAll('fetch_order_item');

            $DB_MASTER->free('fetch_order_item');

            if (!$rows) {
                throw new Exception('Line item ids is not exists');
            }

            $item_ids_compare = [];
            $line_items = [];

            foreach ($rows as $row) {
                $item_ids_compare[] = $row['item_id'];
                $line_items[$row['item_id']] = $row;
            }

            $result_compare = array_diff($item_ids, $item_ids_compare);

            if (count($result_compare) > 0) {
                throw new Exception('Line item ids: ' . implode(",", array_values($result_compare)) . ' is not exists');
            }

            $now = time();

            if (count($line_items) > 0) {
                $DB_MASTER->begin();

                $locked_key = OSC::makeUniqid();

                OSC_Database_Model::lockPreLoadedModel($locked_key);

                $line_item_response = [];

                try {
                    foreach ($line_items as $item_id => $line_item) {
                        $DB_MASTER->select('*', 'catalog_order_item_meta', "meta_id = '{$line_item['order_item_meta_id']}'", null, 1, 'fetch_order_item_meta');

                        $meta = $DB_MASTER->fetchArray('fetch_order_item_meta');

                        $DB_MASTER->free('fetch_order_item_meta');

                        if (!$meta) {
                            throw new Exception('Line item meta id ' . $line_item['order_item_meta_id'] . ' is not exists');
                        }

                        $DB_MASTER->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "catalog_order_item_meta (custom_data,added_timestamp,modified_timestamp) VALUES(:custom_data, :added_timestamp,:modified_timestamp)", ['custom_data' => $meta['custom_data'], 'added_timestamp' => $now, 'modified_timestamp' => $now], 'insert_catalog_order_item_meta');

                        $new_meta_id = $DB_MASTER->getInsertedId();

                        $ukey = $order_id . ':' . 1 . ':' . md5(OSC::encode(OSC::makeUniqid()));

                        $data = [
                            'order_id' => $order_id,
                            'ukey' => $ukey,
                            'product_id' => $row['product_id'],
                            'variant_id' => $row['variant_id'],
                            'price' => 0,
                            'quantity' => 1,
                            'require_shipping' => $row['require_shipping'],
                            'require_packing' => $row['require_packing'],
                            'additional_data' => OSC::encode(['hide' => 1]),
                            'added_timestamp' => $now,
                            'modified_timestamp' => $now,
                            'design_url' => [],
                            'title' => $row['title'],
                            'order_item_meta_id' => $new_meta_id,
                            'image_url' => $row['image_url'],
                            'product_type' => $row['product_type'],
                            'options' => $row['options']
                        ];

                        $DB_MASTER->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "catalog_order_item (order_id,ukey,product_id,variant_id,price,quantity,require_shipping,require_packing,additional_data,added_timestamp,modified_timestamp,design_url,title,order_item_meta_id,image_url,product_type,options) VALUES(:order_id,:ukey,:product_id,:variant_id,:price,:quantity,:require_shipping,:require_packing,:additional_data,:added_timestamp,:modified_timestamp,:design_url,:title,:order_item_meta_id,:image_url,:product_type,:options)", $data, 'update_catalog_order_item');

                        $new_item_id = $DB_MASTER->getInsertedId();

                        $line_item_response[$item_id] = ['new_item_id' => $new_item_id, 'new_meta_id' => $new_meta_id, 'added_timestamp' => $now];
                    }

                    $DB_MASTER->commit();
                    OSC_Database_Model::unlockPreLoadedModel($locked_key);

                    $this->_ajaxResponse($line_item_response);
                } catch (Exception $ex) {
                    $DB_MASTER->rollback();
                    OSC_Database_Model::unlockPreLoadedModel($locked_key);
                    throw new Exception($ex->getMessage());
                }
            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetDataCampaignByTemplate(){
        $product_id = intval($this->_request->get('product_id'));
        $print_template_id_new = intval($this->_request->get('print_template_id_new'));
        $print_template_id_old = intval($this->_request->get('print_template_id_old'));
        $campaign_data = $this->_request->get('campaign_data');
        try{
            if ($product_id < 1 || $print_template_id_old < 1 || $print_template_id_new < 1 || !is_array($campaign_data)) {
                throw new Exception('Data is incorrect');
            }
            $product = OSC::model('catalog/product')->load($product_id);
            $print_template_new = OSC::model('catalog/printTemplate')->load($print_template_id_new);
            $print_template_old = OSC::model('catalog/printTemplate')->load($print_template_id_old);
            if (count(array_diff_key($print_template_old->data['config']['segments'], $print_template_new->data['config']['segments'])) > 0 || count(array_diff_key($print_template_new->data['config']['segments'], $print_template_old->data['config']['segments'])) > 0) {
                throw new Exception('Not matched key segments between #' . $print_template_id_old . ' and #' . $print_template_id_new);
            }

            $segments = null;
            $segmentsOld = null;
            foreach ($product->data['meta_data']['campaign_config']['print_template_config'] as $print_template_config) {
                if ($print_template_config['print_template_id'] == $print_template_id_old){
                    $segmentsOld = $print_template_config['segments'];
                }
                if ($print_template_config['print_template_id'] == $print_template_id_new) {
                    $segments = $print_template_config['segments'];
                }
            }

            if ($segmentsOld == null) {
                throw new Exception('Not found print template id '.$print_template_id_old. ' in product id '.$product_id);
            }
            if ($segments == null) {
                throw new Exception('Not found print template id '.$print_template_id_new. ' in product id '.$product_id);
            }
            $type_segment_old = [];

            foreach ($segmentsOld as $segment_key => $segment) {
                $type_segment_old[$segment_key] = $segment['source'];
            }

            $map_segment = true;

            foreach ($segments as $segment_key => $segment) {
                if (!array_key_exists($segment_key, $type_segment_old) || $type_segment_old[$segment_key]['design_id'] != $segment['source']['design_id']){
                    $map_segment = false;
                    break;
                }
            }

            if ($map_segment == false) {
                throw new Exception('Not map segment '.$print_template_id_old. ' and '.$print_template_id_new.' in product id '.$product_id);
            }

            $campaign_config = [];
            $personalized_design_ids = [];
            $image_ids = [];
            foreach ($campaign_data['data']['print_template']['segment_source'] as $segment_key => $segment_source) {
                if($segment_source['source']['type'] == 'personalizedDesign') {
                    $campaign_config[$segment_key] = $segment_source['source']['config'];
                    $personalized_design_ids[] = $segment_source['source']['design_id'];
                } elseif ($segment_source['source']['type'] == 'image') {
                    $image_ids[] = $segment_source['source']['image_id'];
                }
            }
            if (count($personalized_design_ids) > 0) {
                $personalized_design_collection = OSC::model('personalizedDesign/design')->getCollection()->load($personalized_design_ids);
            }
            if (count($image_ids) > 0) {
                $image_collection = OSC::model('catalog/campaign_imageLib_item')->getCollection()->load($image_ids);
            }
            foreach ($segments as $segment_key => $segment_source) {
                $campaign_data['data']['print_template']['segment_source'][$segment_key] = $segment_source;
                if ($segment_source['source']['type'] == 'personalizedDesign') {

                    $personalized_design = $personalized_design_collection->getItemByPK($segment_source['source']['design_id']);
                    if (!($personalized_design instanceof Model_PersonalizedDesign_Design)) {
                        throw new Exception('Cannot load personalized design [' . $segment_source['source']['design_id'] . ']');
                    }
                    $personalizedDesign = Observer_Catalog_Campaign::validatePersonalizedDesign($personalized_design, $campaign_config[$segment_key]);
                    foreach($personalizedDesign as $k => $v) {
                        $campaign_data['data']['print_template']['segment_source'][$segment_key]['source'][$k] = $v;
                    }
                } elseif ($segment_source['source']['type'] == 'image') {
                    $image = $image_collection->getItemByPK($segment_source['source']['image_id']);

                    if (!($image instanceof Model_Catalog_Campaign_ImageLib_Item)) {
                        throw new Exception('Cannot load image item [' . $segment_source['source']['image_id'] . ']');
                    }
                    $campaign_data['data']['print_template']['segment_source'][$segment_key]['source']['file_name'] = $image->data['filename'];
                }
            }

            $campaign_data['data']['print_template']['print_template_id'] = $print_template_id_new;
            $campaign_data['data']['print_template']['preview_config'] = $print_template_new->data['config']['preview_config'];
            $campaign_data['data']['print_template']['segments'] = $print_template_new->data['config']['segments'];
            $campaign_data['data']['print_template']['print_file'] = $print_template_new->data['config']['print_file'];

            $this->_ajaxResponse($campaign_data);
        }catch (Exception $ex){
            $this->_ajaxError($ex->getMessage(), 404);
        }
    }
}
