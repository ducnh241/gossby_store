<?php

class Cron_Catalog_Order_Report extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        $file_url = static::renderExcel($params['export_key'], $params['range_timestamp']);

        $expired_time = date('d/m/Y H:i:s', time() + (60 * 60 * 24));

        $email_content = <<<EOF
Your report has been generated,
Click the URL below to download:
<a href="{$file_url}">{$file_url}</a>

The URL will be expired at {$expired_time}
EOF;

        $klaviyo_api_key = OSC::helper('klaviyo/common')->getApiKey();

        if ($klaviyo_api_key != '') {
            OSC::helper('klaviyo/common')->create([
                'token' => $klaviyo_api_key,
                'event' => 'System office',
                'customer_properties' => [
                    '$email' => $params['receiver']['email'],
                ],
                'properties' => [
                    'receiver_name' => $params['receiver']['email'],
                    'receiver_email' => $params['receiver']['name'],
                    'title' =>  'Report orders from ' . OSC::helper('core/setting')->get('theme/site_name') . ' [' . $params['range_text'] . ']' . ': ' . date('d/m/Y H:i:s'),
                    'message' => implode('<br />', explode("\n", $email_content))
                ]
            ]);

        }

        $skip_amazon = intval(OSC::helper('core/setting')->get('tracking/klaviyo/skip_amazon'));
        if ($skip_amazon != 1) {
            OSC::helper('postOffice/email')->create([
                'priority' => 1000,
                'subject' => 'Report orders from ' . OSC::helper('core/setting')->get('theme/site_name') . ' [' . $params['range_text'] . ']' . ': ' . date('d/m/Y H:i:s'),
                'receiver_email' => $params['receiver']['email'],
                'receiver_name' => $params['receiver']['name'],
                'html_content' => implode('<br />', explode("\n", $email_content)),
                'text_content' => strip_tags($email_content, '<br>')
            ]);
        }

    }

    public static function renderExcel($report_key, $range_timestamp) {
        $draft_file = OSC_VAR_PATH . '/orderReportDraft/' . $report_key . '.txt';

        if (!file_exists($draft_file)) {
            $draft_data = [
                'state' => 'fetch_order'
            ];

            OSC::makeDir(dirname($draft_file));

            if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                throw new Exception('Unable to write to draft file');
            }
        } else {
            $draft_data = file_get_contents($draft_file);

            if ($draft_data === false) {
                throw new Exception('Unable to read draft file');
            }

            $draft_data = OSC::decode($draft_data, true);

            if (!is_array($draft_data)) {
                throw new Exception('Draft file data is incorrect');
            }
        }

        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        /* @var $DB_MASTER OSC_Database_Adapter */
        $DB_MASTER = OSC::core('database')->getAdapter('db_master');

        if ($draft_data['state'] == 'fetch_order') {
            $offset = max(0, intval($draft_data['offset']));
            $limit = 10000;

            while (true) {
                $DB_MASTER->query("SELECT o.order_id,o.code,i.item_id,o.payment_method,i.quantity,i.title,i.options,i.vendor,o.shipping_country_code,o.shipping_country,o.shipping_province_code,o.shipping_province,o.total_price,o.subtotal_price,o.shipping_price,o.discount_codes,o.added_timestamp,o.client_info FROM {$DB_MASTER->getTableName('catalog_order')} o, {$DB_MASTER->getTableName('catalog_order_item')} i WHERE o.order_id = i.order_id AND o.added_timestamp >= {$range_timestamp['begin']} AND o.added_timestamp <= {$range_timestamp['end']} ORDER BY o.added_timestamp ASC, o.order_id ASC, i.item_id ASC LIMIT {$offset},{$limit};", null, 'fetch_order_info');

                while ($row = $DB_MASTER->fetchArray('fetch_order_info')) {
                    $row['options'] = OSC::decode($row['options'], true);
                    $row['client_info'] = OSC::decode($row['client_info'], true);
                    $row['discount_codes'] = OSC::decode($row['discount_codes'], true);
                    $row['payment_method'] = OSC::decode($row['payment_method'], true);

                    $option_text = implode(' / ', array_map(function($option) {
                                return $option['value'];
                            }, $row['options']));

                    $sref = '';
                    $discount_price = 0;

                    if (isset($row['client_info']['DLS_SALE_REF']) && is_array($row['client_info']['DLS_SALE_REF']) && isset($row['client_info']['DLS_SALE_REF']['username'])) {
                        $sref = $row['client_info']['DLS_SALE_REF']['username'];
                    }

                    foreach ($row['discount_codes'] as $discount) {
                        $discount_price += $discount['discount_price'];
                    }

                    unset($row['discount_codes']);
                    unset($row['client_info']);

                    $row['discount_price'] = $discount_price;
                    $row['sref'] = $sref;

                    try {
                        $DB->insert('catalog_order_export_draft', [
                            'export_key' => $report_key,
                            'secondary_key' => 'order',
                            'order_id' => $row['order_id'],
                            'line_item_id' => $row['item_id'],
                            'export_data' => OSC::encode([
                                'code' => $row['code'],
                                'date' => date('Y/m/d', $row['added_timestamp']),
                                'payment_method' => $row['payment_method']['key'],
                                'payment_account' => $row['payment_method']['account']['title'],
                                'product_name' => $row['title'],
                                'variant_title' => $option_text,
                                'quantity' => $row['quantity'],
                                'revenue' => OSC::helper('catalog/common')->integerToFloat($row['total_price']),
                                'discount' => OSC::helper('catalog/common')->integerToFloat($row['discount_price']),
                                'shipping_fee' => OSC::helper('catalog/common')->integerToFloat($row['shipping_price']),
                                'gross_sale' => OSC::helper('catalog/common')->integerToFloat($row['subtotal_price']),
                                'vendor' => $row['vendor'],
                                'sref' => $row['sref'],
                                'province_code' => $row['shipping_province_code'] ? $row['shipping_province_code'] : $row['shipping_province'],
                                'country' => $row['shipping_country'],
                                'country_code' => $row['shipping_country_code']
                            ]),
                            'added_timestamp' => time()
                        ]);
                    } catch (Exception $ex) {
                        if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                            throw new Exception($ex->getMessage());
                        }
                    }
                }

                $total_rows = $DB_MASTER->rowCount('fetch_order_info');

                $offset += $total_rows;

                $DB_MASTER->free('fetch_order_info');

                if ($total_rows < $limit) {
                    $draft_data['state'] = 'fetch_refund';
                    unset($draft_data['offset']);

                    if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                        throw new Exception('Unable to write to draft file');
                    }

                    break;
                } else {
                    $draft_data['offset'] = $offset;

                    if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                        throw new Exception('Unable to write to draft file');
                    }
                }
            }
        }

        if ($draft_data['state'] == 'fetch_refund') {
            $offset = max(0, intval($draft_data['offset']));
            $limit = 5000;

            while (true) {
                $orders = [];
                $line_items = [];
                $transactions = [];
                $line_item_ids = [];
                $order_ids = [];

                $DB_MASTER->query("SELECT record_id,order_id,transaction_type,amount,transaction_data,note,added_timestamp FROM {$DB_MASTER->getTableName('catalog_order_transaction')} WHERE transaction_type IN ('refund','cancel') AND added_timestamp >= {$range_timestamp['begin']} AND added_timestamp <= {$range_timestamp['end']} ORDER BY added_timestamp ASC, record_id ASC LIMIT {$offset},{$limit};", null, 'fetch_transaction_info');

                while ($row = $DB_MASTER->fetchArray('fetch_transaction_info')) {
                    $row['transaction_data'] = OSC::decode($row['transaction_data'], true);
                    $row['amount'] = intval($row['amount']);

                    $transactions[] = $row;

                    $order_ids[] = intval($row['order_id']);

                    if (isset($row['transaction_data']['line_items'])) {
                        foreach ($row['transaction_data']['line_items'] as $line_item_id => $refund_info) {
                            $line_item_ids[] = intval($line_item_id);
                        }
                    }
                }

                $total_rows = $DB_MASTER->rowCount('fetch_transaction_info');
                $DB_MASTER->free('fetch_transaction_info');

                $order_ids = array_unique($order_ids);
                $line_item_ids = array_unique($line_item_ids);

                if (count($order_ids) > 0) {
                    $DB_MASTER->query("SELECT order_id,code,payment_method,shipping_country_code,shipping_country,shipping_province_code,shipping_province,client_info FROM {$DB_MASTER->getTableName('catalog_order')} WHERE order_id IN (" . implode(',', $order_ids) . ") LIMIT " . count($order_ids), null, 'fetch_order_info');

                    while ($row = $DB_MASTER->fetchArray('fetch_order_info')) {
                        $row['client_info'] = OSC::decode($row['client_info'], true);
                        $row['payment_method'] = OSC::decode($row['payment_method'], true);

                        $sref = '';

                        if (isset($row['client_info']['DLS_SALE_REF']) && is_array($row['client_info']['DLS_SALE_REF']) && isset($row['client_info']['DLS_SALE_REF']['username'])) {
                            $sref = $row['client_info']['DLS_SALE_REF']['username'];
                        }

                        unset($row['client_info']);

                        $row['sref'] = $sref;
                        $row['payment_account'] = $row['payment_method']['account']['title'];
                        $row['payment_method'] = $row['payment_method']['key'];

                        $orders[$row['order_id']] = $row;
                    }

                    $DB_MASTER->free('fetch_order_info');
                }

                $order_ids = null;

                if (count($line_item_ids) > 0) {
                    $DB_MASTER->query("SELECT item_id,title,options,vendor FROM {$DB_MASTER->getTableName('catalog_order_item')} WHERE item_id IN (" . implode(',', $line_item_ids) . ") LIMIT " . count($line_item_ids), null, 'fetch_order_line_item_info');

                    while ($row = $DB_MASTER->fetchArray('fetch_order_line_item_info')) {
                        $row['options'] = OSC::decode($row['options'], true);
                        $row['options'] = implode(' / ', array_map(function($option) {
                                    return $option['value'];
                                }, $row['options']));

                        $line_items[$row['item_id']] = $row;
                    }

                    $DB_MASTER->free('fetch_order_line_item_info');
                }

                $line_item_ids = null;

                foreach ($transactions as $row) {
                    if (!isset($orders[$row['order_id']])) {
                        throw new Exception('Unable to find order #' . $row['order_id']);
                    }

                    $order = $orders[$row['order_id']];

                    if (isset($row['transaction_data']['line_items']) && count($row['transaction_data']['line_items']) > 0) {
                        foreach ($row['transaction_data']['line_items'] as $line_item_id => $refund_info) {
                            if (!isset($line_items[$line_item_id])) {
                                throw new Exception('Unable to find line item #' . $line_item_id);
                            }

                            $line_item = $line_items[$line_item_id];

                            try {
                                $DB->insert('catalog_order_export_draft', [
                                    'export_key' => $report_key,
                                    'secondary_key' => 'refund:' . $row['record_id'],
                                    'order_id' => $row['order_id'],
                                    'line_item_id' => $line_item_id,
                                    'export_data' => OSC::encode([
                                        'code' => $order['code'],
                                        'payment_method' => $order['payment_method'],
                                        'payment_account' => $order['payment_account'],
                                        'date' => date('Y/m/d', $row['added_timestamp']),
                                        'product_name' => $line_item['title'],
                                        'variant_title' => $line_item['options'],
                                        'quantity' => $refund_info['refund_quantity'],
                                        'refunded' => OSC::helper('catalog/common')->integerToFloat($row['amount']),
                                        'vendor' => $line_item['vendor'],
                                        'sref' => $order['sref'],
                                        'province_code' => $order['shipping_province_code'] ? $order['shipping_province_code'] : $order['shipping_province'],
                                        'country' => $order['shipping_country'],
                                        'country_code' => $order['shipping_country_code'],
                                        'reason' => $row['note']
                                    ]),
                                    'added_timestamp' => time()
                                ]);
                            } catch (Exception $ex) {
                                if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                                    throw new Exception($ex->getMessage());
                                }
                            }
                        }
                    } else {
                        try {
                            $DB->insert('catalog_order_export_draft', [
                                'export_key' => $report_key,
                                'secondary_key' => 'refund:' . $row['record_id'],
                                'order_id' => $row['order_id'],
                                'line_item_id' => 0,
                                'export_data' => OSC::encode([
                                    'code' => $order['code'],
                                    'payment_method' => $order['payment_method'],
                                    'payment_account' => $order['payment_account'],
                                    'date' => date('Y/m/d', $row['added_timestamp']),
                                    'product_name' => '',
                                    'variant_title' => '',
                                    'quantity' => 0,
                                    'refunded' => OSC::helper('catalog/common')->integerToFloat($row['amount']),
                                    'vendor' => '',
                                    'sref' => $order['sref'],
                                    'province_code' => $order['shipping_province_code'] ? $order['shipping_province_code'] : $order['shipping_province'],
                                    'country' => $order['shipping_country'],
                                    'country_code' => $order['shipping_country_code'],
                                    'reason' => $row['note']
                                ]),
                                'added_timestamp' => time()
                            ]);
                        } catch (Exception $ex) {
                            if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                                throw new Exception($ex->getMessage());
                            }
                        }
                    }
                }

                $offset += $total_rows;

                if ($total_rows < $limit) {
                    $draft_data['state'] = 'fetch_fulfillment';
                    unset($draft_data['offset']);

                    if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                        throw new Exception('Unable to write to draft file');
                    }

                    break;
                } else {
                    $draft_data['offset'] = $offset;

                    if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                        throw new Exception('Unable to write to draft file');
                    }
                }
            }
        }

        if ($draft_data['state'] == 'fetch_fulfillment') {
            $offset = max(0, intval($draft_data['offset']));
            $limit = 5000;

            while (true) {
                $orders = [];
                $line_items = [];
                $fulfillments = [];
                $line_item_ids = [];
                $order_ids = [];

                $DB_MASTER->query("SELECT record_id,order_id,tracking_number,shipping_carrier,tracking_url,line_items,service,added_timestamp FROM {$DB_MASTER->getTableName('catalog_order_fulfillment')} WHERE added_timestamp >= {$range_timestamp['begin']} AND added_timestamp <= {$range_timestamp['end']} ORDER BY added_timestamp ASC, record_id ASC LIMIT {$offset},{$limit};", null, 'fetch_fulfillment_info');

                while ($row = $DB_MASTER->fetchArray('fetch_fulfillment_info')) {
                    $row['line_items'] = OSC::decode($row['line_items'], true);

                    $fulfillments[] = $row;

                    $order_ids[] = intval($row['order_id']);

                    foreach ($row['line_items'] as $line_item_id => $fulfill_info) {
                        $line_item_ids[] = intval($line_item_id);
                    }
                }

                $total_rows = $DB_MASTER->rowCount('fetch_fulfillment_info');
                $DB_MASTER->free('fetch_fulfillment_info');

                $order_ids = array_unique($order_ids);
                $line_item_ids = array_unique($line_item_ids);

                if (count($order_ids) > 0) {
                    $DB_MASTER->query("SELECT order_id,code,shipping_country_code,shipping_country,shipping_province_code,shipping_province,client_info FROM {$DB_MASTER->getTableName('catalog_order')} WHERE order_id IN (" . implode(',', $order_ids) . ") LIMIT " . count($order_ids), null, 'fetch_order_info');

                    while ($row = $DB_MASTER->fetchArray('fetch_order_info')) {
                        $row['client_info'] = OSC::decode($row['client_info'], true);

                        $sref = '';

                        if (isset($row['client_info']['DLS_SALE_REF']) && is_array($row['client_info']['DLS_SALE_REF']) && isset($row['client_info']['DLS_SALE_REF']['username'])) {
                            $sref = $row['client_info']['DLS_SALE_REF']['username'];
                        }

                        unset($row['client_info']);

                        $row['sref'] = $sref;

                        $orders[$row['order_id']] = $row;
                    }

                    $DB_MASTER->free('fetch_order_info');
                }

                $order_ids = null;

                if (count($line_item_ids) > 0) {
                    $DB_MASTER->query("SELECT item_id,title,options,vendor FROM {$DB_MASTER->getTableName('catalog_order_item')} WHERE item_id IN (" . implode(',', $line_item_ids) . ") LIMIT " . count($line_item_ids), null, 'fetch_order_line_item_info');

                    while ($row = $DB_MASTER->fetchArray('fetch_order_line_item_info')) {
                        $row['options'] = OSC::decode($row['options'], true);
                        $row['options'] = implode(' / ', array_map(function($option) {
                                    return $option['value'];
                                }, $row['options']));

                        $line_items[$row['item_id']] = $row;
                    }

                    $DB_MASTER->free('fetch_order_line_item_info');
                }

                $line_item_ids = null;

                foreach ($fulfillments as $row) {
                    if (!isset($orders[$row['order_id']])) {
                        throw new Exception('Unable to find order #' . $row['order_id']);
                    }

                    $order = $orders[$row['order_id']];

                    foreach ($row['line_items'] as $line_item_id => $fulfill_info) {
                        if (!isset($line_items[$line_item_id])) {
                            throw new Exception('Unable to find line item #' . $line_item_id);
                        }

                        $line_item = $line_items[$line_item_id];

                        try {
                            $DB->insert('catalog_order_export_draft', [
                                'export_key' => $report_key,
                                'secondary_key' => 'fulfill:' . $row['record_id'],
                                'order_id' => $row['order_id'],
                                'line_item_id' => $line_item_id,
                                'export_data' => OSC::encode([
                                    'code' => $order['code'],
                                    'date' => date('Y/m/d', $row['added_timestamp']),
                                    'product_name' => $line_item['title'],
                                    'variant_title' => $line_item['options'],
                                    'quantity' => $fulfill_info['fulfill_quantity'],
                                    'vendor' => $line_item['vendor'],
                                    'sref' => $order['sref'],
                                    'supplier' => $row['service'],
                                    'province_code' => $order['shipping_province_code'] ? $order['shipping_province_code'] : $order['shipping_province'],
                                    'country' => $order['shipping_country'],
                                    'country_code' => $order['shipping_country_code'],
                                    'tracking_number' => $row['tracking_number'],
                                    'shipping_carrier' => $row['shipping_carrier'],
                                    'tracking_url' => $row['tracking_url']
                                ]),
                                'added_timestamp' => time()
                            ]);
                        } catch (Exception $ex) {
                            if (strpos($ex->getMessage(), '1062 Duplicate entry') === false) {
                                throw new Exception($ex->getMessage());
                            }
                        }
                    }
                }

                $offset += $total_rows;

                if ($total_rows < $limit) {
                    $draft_data['state'] = 'render';
                    unset($draft_data['offset']);

                    if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                        throw new Exception('Unable to write to draft file');
                    }

                    break;
                } else {
                    $draft_data['offset'] = $offset;

                    if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                        throw new Exception('Unable to write to draft file');
                    }
                }
            }
        }

        if ($draft_data['state'] == 'render') {
            if (!isset($draft_data['xlsx_file'])) {
                $draft_data['xlsx_file'] = 'catalog/order/report/' . $report_key . '.' . date('d-m-Y') . '.xlsx';
                $file_path = OSC_Storage::preDirForSaveFile($draft_data['xlsx_file']);

                if (file_put_contents($draft_file, OSC::encode($draft_data)) === false) {
                    throw new Exception('Unable to write to draft file');
                }

                exec("python " . dirname(__FILE__) . "/reportRender.py -i {$report_key} -o {$file_path} -r " . OSC_SITE_PATH . " > {$file_path}.output");
            } else {
                $file_path = OSC_Storage::preDirForSaveFile($draft_data['xlsx_file']);
            }

            while (true) {
                if (file_exists($file_path)) {
                    unlink($draft_file);

                    return OSC_Storage::tmpGetFileUrl($draft_data['xlsx_file']);
                } else if (file_exists($file_path . '.error')) {
                    throw new Exception(file_get_contents($file_path . '.error'));
                }

                sleep(1);
            }
        }
    }

}
