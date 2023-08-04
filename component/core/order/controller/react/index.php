<?php

class Controller_Order_React_Index extends Abstract_Frontend_ReactApiController {
    public function actionGetCatalogOrderDelayEmail() {
        $token = $this->_request->get('token');
        $request_time = $this->_request->get('request_time');
        $is_form_validated = 0;

        // validate token
        if (!$token) {
            $this->sendError('Token is not found', $this::CODE_NOT_FOUND);
        }

        // validate request time
        if (!$request_time) {
            $this->sendError('Request time is not found', $this::CODE_NOT_FOUND);
        }

        // convert request time from millisecond to second
        $request_time = intval($request_time / 1000);

        try {
            $response = OSC::helper('master/common')
                ->callApi(
                    '/catalog/api_orderDelayEmail/getOrderDataByToken',
                    [
                        'token' => $token,
                        'request_time' => $request_time
                    ]
                );
        } catch (Exception $exception) {
            if ($exception->getCode() === $this::CODE_UNPROCESSABLE_ENTITY &&
                !empty($exception->response_content) &&
                !empty($exception->response_content['data'])) {
                // get failed validation
                $response = $exception->response_content['data'];
            } else {
                $this->sendError($exception->getMessage(), $this::CODE_NOT_FOUND);
            }
        }

        if (empty($response['token'])) {
            $this->sendError('Response Token is not found', $this::CODE_NOT_FOUND);
        }

        $response_data = OSC::decode(base64_decode($response['token']));

        if (!empty($response_data['catalog_order_delay_email']) && !empty($response_data['order_items'])) {
            $is_form_validated = 1;
        }

        $this->sendSuccess(array_merge($response_data, ['is_form_validated' => $is_form_validated]));
    }

    protected function _validateKeepOrCancelOrderItemForm() {
        // validate catalog_order_delay_email_id
        if (!$this->_request->get('catalog_order_delay_email_id')) {
            $this->sendError('Catalog order delay email id is not found', $this::CODE_NOT_FOUND);
        }

        // validate order_item_ids
        if (!$this->_request->get('order_item_ids')) {
            $this->sendError('Order item ids are not found', $this::CODE_NOT_FOUND);
        }

        // validate order_item_ids
        if (!$this->_request->get('action')) {
            $this->sendError('Action is not found', $this::CODE_NOT_FOUND);
        }
    }

    public function actionKeepOrCancelOrderItem() {
        // validate keep or cancel order item form
        $this->_validateKeepOrCancelOrderItemForm();

        try {
            OSC::helper('master/common')
                ->callApi(
                    '/catalog/api_orderDelayEmail/KeepOrCancelOrderItem',
                    [
                        'token' => base64_encode(OSC::encode([
                            'catalog_order_delay_email_id' => $this->_request->get('catalog_order_delay_email_id'),
                            'order_item_ids' => $this->_request->get('order_item_ids'),
                            'action' => $this->_request->get('action')
                        ]))
                    ]
                );
        } catch (Exception $exception) {
            $this->sendError($exception->getMessage());
        }

        $this->sendSuccess([
            'success' => true
        ]);
    }
}
