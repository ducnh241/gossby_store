<?php

class Controller_Developer_Kafka extends Abstract_Core_Controller
{
    public function actionHealthy() {
        $loop = $this->_request->get('loop', 1);
        for ($i = 0; $i <= $loop; $i++) {
            try {
                OSC::core('kafka_client')->sendData([
                    'data' => 'test'
                ], 'test');

            } catch (Exception $ex) {
                echo $ex->getMessage() . ': loop at ' . $i;
                return;
            }
        }
        echo 'OK';
    }

    public function actionChecklogD2Flow() {
        $order_id = $this->_request->get('order_id', 0);
        $order_item_id = $this->_request->get('order_item_id', 0);

        $query = [];
        if ($order_id) {
            $query['order_id'] = intval($order_id);
        }

        if ($order_item_id) {
            $query['order_item_id'] = intval($order_item_id);
        }

        if (!empty($query)) {
            $mongodb = OSC::core('mongodb');
            $result = $mongodb->selectCollection('d2_flow', 'product')->find($query, ['typeMap' => ['root' => 'array', 'document' => 'array']])->toArray();

            dd($result);
        }
    }

    public function actionResendOrderItem() {
        $order_item_id = $this->_request->get('order_item_id', 0);

        if ($order_item_id) {
            $url_personalized = OSC_ENV == 'production' ? 'https://personalizeddesign.9prints.com/storage' : 'https://personalizeddesign-v2.9prints.com/storage';

            try {
                $line_item = OSC::model('catalog/order_item')->load($order_item_id);
                $order = $line_item->getOrder();
                $order_item_meta = $line_item->getOrderItemMeta();

                if (!empty($order_item_meta->data['custom_data'])) {
                    foreach ($order_item_meta->data['custom_data'] as $custom_data) {
                        if ($custom_data['key'] == 'personalized_design' && $custom_data['type'] == 'semitest') {
                            foreach ($custom_data['data'] as $data) {
                                if (isset($data['config_preview'])) {
                                    $layers = [];
                                    foreach ($data['config_preview'] as $key => $item) {
                                        if (($item['type'] == 'imageUploader') && !empty($item['flow_id'])) {
                                            $value = OSC::decode($item['value']);
                                            $layers[] = [
                                                'key' => $key,
                                                'name' => $item['layer'],
                                                'value' => $url_personalized . '/' . $value['file'],
                                                'flowId' => $item['flow_id']
                                            ];
                                        }
                                    }

                                    if (!empty($layers)) {
                                        $key = md5(OSC::encode($layers) . ':' . time());
                                        dump('data', [
                                            'layers' => $layers,
                                            'order_id' => $order->getId(),
                                            'order_code' => $order->data['code'],
                                            'order_item_id' => $line_item->getId()
                                        ]);

                                        OSC::core('cron')->addQueue('d2/flows',
                                            [
                                                'layers' => $layers,
                                                'order_id' => $order->getId(),
                                                'order_code' => $order->data['code'],
                                                'order_item_id' => $line_item->getId()
                                            ],
                                            [
                                                'ukey' => 'catalog/order_analytic:' . $key,
                                                'requeue_limit' => -1,
                                                'skip_realtime',
                                                'estimate_time' => 60 * 60,
                                                'running_time' => 120
                                            ]);
                                        dump('Send message successfully!!!');
                                    } else {
                                        dump('Empty layers');
                                    }
                                }
                            }
                        } else {
                            dump('Not a semitest');
                        }
                    }
                }
            } catch (Exception $exception) {
                dd($exception);
            }
        }
    }
}