<?php

class Controller_Catalog_Backend_Analytic extends Abstract_Catalog_Controller_Backend {
    public function actionPost() {
        $this->checkPermission('catalog/super|catalog/product');

        $action = $this->_request->get('action');
        if (!empty($action) && $action == 'send') {
            $product_ids = $this->_request->get('product_id');
            $product_ids = explode(' ', $product_ids);
            $list_product_id = [];
            foreach ($product_ids as $product_id) {
                $product_id = preg_replace('/[^0-9]/is', '', $product_id);
                if (!empty($product_id)) {
                    $list_product_id[] = $product_id;
                }
            }

            $date = $this->_request->get('date_range');
            if (preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $date, $matches)) {
                $start_date = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);
                if ($matches[5]) {
                    $end_date = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                } else {
                    $end_date = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                }
            }

            $shop_id = OSC::getShop()->getId();
            $user_id = $this->getAccount()->getId();
            $export_key = md5($user_id . ':' . $shop_id . ':' . OSC::encode($this->_request->getAll()) . ':' . time());

            if (!empty($list_product_id) && !empty($start_date) && !empty($end_date)) {
                OSC::core('cron')->addQueue('catalog/order_analytic',
                    [
                        'export_key' => $export_key,
                        'shop_id' => $shop_id,
                        'shop_domain' => OSC_FRONTEND_BASE_URL,
                        'user' => $user_id,
                        'list_product_id' => implode(',', $list_product_id),
                        'start_date' => $start_date,
                        'end_date' => $end_date,
                        'range_text' => $start_date . ' - ' . $end_date,
                        'receiver' => [
                            'email' => $this->getAccount()->data['email'],
                            'name' => $this->getAccount()->data['username']
                        ]
                    ],
                    [
                        'ukey' => 'catalog/order_analytic:' . $export_key,
                        'requeue_limit' => -1,
                        'skip_realtime',
                        'estimate_time' => 60 * 60,
                        'running_time' => 120
                    ]);

                $this->addMessage('Task has been appended to queue');
            }
        }

        $output_html = $this->getTemplate()->build('catalog/product/analytic', []);

        $this->output($output_html);
    }
}