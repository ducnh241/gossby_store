<?php

class Controller_Developer_RerenderDesignSvgBeta extends Abstract_Core_Controller {
    public function actionIndex() {
        $order_ids = $this->_request->get('order_ids', []);
        $success = [];
        $errors = [];

        if (!empty($order_ids)) {
            $order_ids = array_unique($order_ids);
            foreach ($order_ids as $order_id) {
                try {
                    $order = OSC::model('catalog/order')->load($order_id);
                    OSC::helper('catalog/orderItem')->renderDesignSvgBeta($order, true);
                    $success[] = $order_id;
                } catch (Exception $ex) {
                    $errors[] = $order_id;
                }
            }
        } else {
            echo 'Empty order' . '<br>';
        }

        echo 'Success: ' . implode(',', $success) . '<br>';
        echo 'Error: ' . implode(',', $errors) . '<br>';
    }

    public function actionTestD2() {
        $kafka_config = OSC::systemRegistry('kafka_d2');

        $data = [
            'orderId' => 6349733,
            'code' => '9P_6349733',
            'orderItemId' => 6926720,
            'layers' => [
                [
                    'key' => 'avXBZN0865886008',
                    'name' => 'Ps_photo_Name #2',
                    'value' => 'https://personalizeddesign-v2.9prints.com/storage/personalizedDesign/customer_uploaded/20221011/F95JZ6344d4da4acea.jpg',
                    'flowId' => '633ef12c6fd5d19260925838'
                ],
                [
                    'key' => 'Ds32U99919440279',
                    'name' => 'Ps_photo_Name #1',
                    'value' => 'https://personalizeddesign-v2.9prints.com/storage/personalizedDesign/customer_uploaded/20221011/W7FBT6344d4e3d4728.jpg',
                    'flowId' => '633e82676fd5d192609254ac'
                ]
            ]
        ];

        OSC::core('kafka_client')->sendData($data, $kafka_config['topic']);
    }
}