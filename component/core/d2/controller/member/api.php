<?php

class Controller_D2_Member_Api extends OSC_Controller {

    public function actionGetOrderMember() {
        $email = $this->_request->get('email', null);
        $status_biz = $this->_request->get('status', null);
        $offset = $this->_request->get('offset', null);
        $order_line_id = $this->_request->get('order_line_id', null);
        $limit = $this->_request->get('limit', 25);

        if (!$email) {
            $this->_ajaxError('Email is not empty!', 400);
        }

        if (empty($status_biz)) {
            $this->_ajaxError('Status is not empty!', 400);
        }

        if (!is_array($status_biz)) {
            $status_biz = [$status_biz];
        }

        foreach ($status_biz as $status) {
            if ($status && !isset(Helper_D2_Common::STATUS_BIZ[$status])) {
                $this->_ajaxError('Status is invalid!', 400);
            }
        }

        $results = [];
        try {
            $profile = OSC::helper('d2/common')->getUserProfile($email);
            if (empty($profile)) {
                throw new Exception('Member is not exits');
            }
            try {
                $pic_name = $profile['fields']['Name'];
                $data = OSC::helper('d2/common')->getOrderByPicName($pic_name, $status_biz, $offset, $limit, $order_line_id);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $records = $data['records'];

            foreach ($records as $record) {
                $fields = $record['fields'];
                $ps_photos = [];
                $photos_9 = [];

                $resource_url = OSC::helper('d2/common')->getResourceDesign($fields);

                if ($fields['ps_photo_01_name'] || $fields['ps_photo_01_value'] || $fields['ps_photo_01_opt']) {
                    $ps_photos[] = [
                        'name' => $fields['ps_photo_01_name'],
                        'value' => $fields['ps_photo_01_value'],
                        'opt' => $fields['ps_photo_01_opt']
                    ];
                }

                if ($fields['ps_photo_02_name'] || $fields['ps_photo_02_value'] || $fields['ps_photo_02_opt']) {
                    $ps_photos[] = [
                        'name' => $fields['ps_photo_02_name'],
                        'value' => $fields['ps_photo_02_value'],
                        'opt' => $fields['ps_photo_02_opt']
                    ];
                }

                if ($fields['ps_photo_03_name'] || $fields['ps_photo_03_value'] || $fields['ps_photo_03_opt']) {
                    $ps_photos[] = [
                        'name' => $fields['ps_photo_03_name'],
                        'value' => $fields['ps_photo_03_value'],
                        'opt' => $fields['ps_photo_03_opt']
                    ];
                }

                if ($fields['ps_photo_04_name'] || $fields['ps_photo_04_value'] || $fields['ps_photo_04_opt']) {
                    $ps_photos[] = [
                        'name' => $fields['ps_photo_04_name'],
                        'value' => $fields['ps_photo_04_value'],
                        'opt' => $fields['ps_photo_04_opt']
                    ];
                }

                if ($fields['ps_photo_05_name'] || $fields['ps_photo_05_value'] || $fields['ps_photo_05_opt']) {
                    $ps_photos[] = [
                        'name' => $fields['ps_photo_05_name'],
                        'value' => $fields['ps_photo_05_value'],
                        'opt' => $fields['ps_photo_05_opt']
                    ];
                }

                if ($fields['9p_photo#1']) {
                    $photos_9[] = $fields['9p_photo#1'];
                }

                if ($fields['9p_photo#2']) {
                    $photos_9[] = $fields['9p_photo#2'];
                }

                if ($fields['9p_photo#3']) {
                    $photos_9[] = $fields['9p_photo#3'];
                }

                if ($fields['9p_photo#4']) {
                    $photos_9[] = $fields['9p_photo#4'];
                }

                if ($fields['9p_photo#5']) {
                    $photos_9[] = $fields['9p_photo#5'];
                }

                $sub_result = [
                    'airtable_id' => $record['id'],
                    'status' => intval(explode('-', $fields['Status Biz'])[0] ?? 0),
                    'order_id' => $fields['Order ID'],
                    'order_line_id' => $fields['Order Line ID'],
                    'order_code' => $fields['Order Code'],
                    'order_date' => $fields['Order Date'],
                    'product_id' => $fields['Product ID'],
                    'product_name' => $fields['Product Name'],
                    'product_link' => $fields['Product Link'],
                    'variant_title' => $fields['Variant Title'],
                    'design_id' => $fields['design_id'],
                    'ps_photos' => $ps_photos,
                    'ps_photo_others' => explode("\n", $fields['ps_photo_others']),
                    'ps_product_opt' => $fields['ps_product_opt'],
                    'ps_opt_others' => $fields['ps_opt_others'],
                    'ps_clipart' => explode("\n", $fields['ps_clipart']),
                    '9_photos' => $photos_9,
                    '9_photo_others' => $fields['9p_photo#others'],
                    '9_photo_flows' => $fields['9p_photo_flows'],
                    'note_ctv' => $fields['Note.CTV'],
                    'note_d2' => $fields['Note.D2'],
                    'note_cs' => $fields['Note.CS'],
                    'note_sc' => $fields['Note.SC'],
                    'design_psd' => $fields['design.psd'],
                    'template_url' => $resource_url
                ];

                $results['orders'][] = $sub_result;
            }

            $results['offset'] = $data['offset'] ?? null;

        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), 500);
        }

        $this->_ajaxResponse($results);
    }

    public function actionUpdateOrderAirtable() {
        $email = $this->_request->get('email', null);
        $order_line_id = $this->_request->get('order_line_id', null);
        $fields = $this->_request->get('fields', []);

        if (!$email) {
            $this->_ajaxError('Email is not empty!', 400);
        }
        if (!$order_line_id) {
            $this->_ajaxError('Order Line ID is not empty!', 400);
        }

        if (empty($fields)) {
            $this->_ajaxError('Fields is not empty!', 400);
        }

        try {
            $order_item = OSC::model('catalog/order_item')->load($order_line_id);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), 400);
        }
        $addition_data = $order_item->data['additional_data'];
        if (!isset($addition_data['sync_airtable_id'])) {
            $this->_ajaxError('Order item is not synced on airtable', 400);
        }

        $airtable_id = $addition_data['sync_airtable_id'];

        $records[$airtable_id] = [
            'id' => $airtable_id
        ];

        foreach ($fields as $key => $value) {
            if (!isset(Helper_D2_Common::FIELD_UPDATE_BY_MEMBERS[$key])) {
                $this->_ajaxError("Member is not updated fields.{$key} on Airtable!", 400);
            }

            if ($key == 'status') {
                if (!isset(Helper_D2_Common::STATUS_BIZ[$value])) {
                    $this->_ajaxError('Status is invalid!', 400);
                }
                $value = Helper_D2_Common::STATUS_BIZ[$value];
            }
            $records[$airtable_id]['fields'][Helper_D2_Common::FIELD_UPDATE_BY_MEMBERS[$key]] = $value;
        }

        try {

            $profile = OSC::helper('d2/common')->getUserProfile($email);
            if (empty($profile)) {
                throw new Exception('Member is not exits');
            }

            $res_upd = OSC::core('airtable')->updateData(array_values($records), OSC_AIRTABLE_ORDER_LINE_TABLE);
            $message_error = $res_upd['content']['error']['message'] ?? '';
            if ( $message_error && strpos($message_error, 'does not exist in this table') !== false) {
                $airtable_id_filter = OSC::helper('d2/common')->filterOrderItemAirtableId("{Order Line ID} = {$order_line_id}");
                if ($airtable_id_filter) {
                    $addition_data['sync_airtable_id'] = $airtable_id_filter;
                    $order_item->setData([
                        'additional_data' => $addition_data
                    ])->save();

                    $records[$airtable_id]['id'] = $airtable_id_filter;
                    $res_upd = OSC::core('airtable')->updateData(array_values($records), OSC_AIRTABLE_ORDER_LINE_TABLE);
                    $message_error = $res_upd['content']['error']['message'] ?? '';
                }
            }

            if ($message_error) {
                throw new Exception('Error update airtable: ' . $message_error);
            }

        } catch (Exception $ex) {

            $this->_ajaxError($ex->getMessage(), 500);
        }

        $this->_ajaxResponse();

    }

}
