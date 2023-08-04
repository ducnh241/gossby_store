<?php
class Helper_SrefReport_Common {
    public function getTimestampRange($begin_date = null, $end_date = null) {
        $begin_date = explode('/', strval($begin_date));
        $end_date = explode('/', strval($end_date));

        if (count($begin_date) == 3 && checkdate($begin_date[1], $begin_date[0], $begin_date[2])) {
            $begin_date = intval(intval($begin_date[2]) . str_pad(intval($begin_date[1]), 2, '0', STR_PAD_LEFT) . str_pad(intval($begin_date[0]), 2, '0', STR_PAD_LEFT));
        } else {
            $begin_date = 0;
        }

        if (count($end_date) == 3 && checkdate($end_date[1], $end_date[0], $end_date[2])) {
            $end_date = intval(intval($end_date[2]) . str_pad(intval($end_date[1]), 2, '0', STR_PAD_LEFT) . str_pad(intval($end_date[0]), 2, '0', STR_PAD_LEFT));
        } else {
            $end_date = 0;
        }

        if ($begin_date > 0 && $end_date > 0 && $begin_date > $end_date) {
            $buff = $begin_date;
            $begin_date = $end_date;
            $end_date = $buff;
        }

        return [
            $begin_date < 1 ? $begin_date : mktime(0, 0, 0, substr($begin_date, 4, 2), substr($begin_date, -2), substr($begin_date, 0, 4)),
            $end_date < 1 ? $end_date : mktime(23, 59, 59, substr($end_date, 4, 2), substr($end_date, -2), substr($end_date, 0, 4))
        ];
    }

    public function getReportDataByHour($keys, $begin_date = null, $end_date = null, $sref_ids) {
        $data = $this->_getReportDataBySrefId('%Y/%m/%d/%H', $keys, $begin_date, $end_date , $sref_ids);

        foreach ($data['rows'] as $row) {
            $row['date_key'] = explode('/', $row['date_key']);

            $date = $row['date_key'][2] . '/' . $row['date_key'][1] . '/' . $row['date_key'][0];
            $hour = $row['date_key'][3] . ':00';
            $key = $date . ' ' . $hour;

            if (!isset($data['group'][$keys[$row['report_key']]][$key])) {
                $data['group'][$keys[$row['report_key']]][$key] = [
                    'label' => $key,
                    'short_label' => $hour,
                    'value' => 0
                ];
            }

            $data['group'][$keys[$row['report_key']]][$key]['value'] += floatval($row['value']);
        }

        return $data['group'];
    }

    public function getReportDataByDate($keys,$begin_date = null, $end_date = null ,$sref_ids) {
        $data = $this->_getReportDataBySrefId('%Y/%m/%d', $keys, $begin_date, $end_date,$sref_ids);

        foreach ($data['rows'] as $row) {
            $row['date_key'] = explode('/', $row['date_key']);

            $date = $row['date_key'][2] . '/' . $row['date_key'][1] . '/' . $row['date_key'][0];

            if (!isset($data['group'][$keys[$row['report_key']]][$date])) {
                $data['group'][$keys[$row['report_key']]][$date] = [
                    'label' => $date,
                    'short_label' => $date,
                    'value' => 0
                ];
            }

            $data['group'][$keys[$row['report_key']]][$date]['value'] += floatval($row['value']);
        }

        return $data['group'];
    }

    public function getReportDataByWeek($keys, $begin_date = null, $end_date = null,$sref_ids) {
        $data = $this->_getReportDataBySrefId('%Y/%U', $keys, $begin_date, $end_date,$sref_ids);

        $dto = new DateTime();

        foreach ($data['rows'] as $row) {
            $row['date_key'] = explode('/', $row['date_key']);

            $dto->setISODate($row['date_key'][0], $row['date_key'][1]);

            $range = $dto->format('d/m/Y') . '-';
            $dto->modify('+6 days');
            $range .= $dto->format('d/m/Y');

            if (!isset($data['group'][$keys[$row['report_key']]][implode('/', $row['date_key'])])) {
                $data['group'][$keys[$row['report_key']]][implode('/', $row['date_key'])] = [
                    'label' => $range,
                    'short_label' => $range, //'Week ' . $row['date_key'][1] . ' of ' . $row['date_key'][0],
                    'value' => 0
                ];
            }

            $data['group'][$keys[$row['report_key']]][implode('/', $row['date_key'])]['value'] += floatval($row['value']);
        }

        return $data['group'];
    }

    public function getReportDataByMonth($keys, $begin_date = null, $end_date = null, $sref_ids) {
        $data = $this->_getReportDataBySrefId('%Y/%m', $keys, $begin_date, $end_date,$sref_ids);

        foreach ($data['rows'] as $row) {
            $row['date_key'] = explode('/', $row['date_key']);

            $date = $row['date_key'][1] . '/' . $row['date_key'][0];

            if (!isset($data['group'][$keys[$row['report_key']]][$date])) {
                $data['group'][$keys[$row['report_key']]][$date] = [
                    'label' => $date,
                    'short_label' => $date,
                    'value' => 0
                ];
            }

            $data['group'][$keys[$row['report_key']]][$date]['value'] += floatval($row['value']);
        }

        return $data['group'];
    }

    public function getReportDataByYear($keys, $begin_date = null, $end_date = null ,$sref_ids) {
        $data = $this->_getReportDataBySrefId('%Y', $keys, $begin_date, $end_date ,$sref_ids);

        foreach ($data['rows'] as $row) {
            $row['date_key'] = explode('/', $row['date_key']);

            $date = $row['date_key'][0];

            if (!isset($data['group'][$keys[$row['report_key']]][$date])) {
                $data['group'][$keys[$row['report_key']]][$date] = [
                    'label' => $date,
                    'short_label' => $date,
                    'value' => 0
                ];
            }

            $data['group'][$keys[$row['report_key']]][$date]['value'] += floatval($row['value']);
        }

        return $data['group'];
    }

    protected function _getReportProductData(&$report_keys, $date_format, $timestamp_range, $sref_ids, $product_ids = []) {
        /* @var $DB OSC_Database */
        $DB = OSC::core('database');
        $DB_MASTER = OSC::core('database')->getAdapter('db_master_read');

        $product_report_keys = [];
        $product_order_report_keys = [];

        foreach ($report_keys as $idx => $report_key) {
            if (in_array($report_key, ['catalog/item/unique_visitor', 'catalog/item/visit', 'catalog/item/view', 'catalog/add_to_cart', 'catalog/checkout_initialize'], true)) {
                unset($report_keys[$idx]);
                $product_report_keys[] = $report_key;
            }

            if (in_array($report_key, ['catalog/order', 'catalog/order_tax', 'catalog/item_solds', 'catalog/revenue', 'catalog/refunded_price', 'catalog/refunded_tax_price', 'catalog/tip_price', 'marketing/point'])) {
                unset($report_keys[$idx]);
                $product_order_report_keys[] = $report_key;
            }
        }

        $report_keys = array_values($report_keys);

        $rows = [];

        if (count($product_order_report_keys) > 0) {
            $condition = [
                'condition' => [
                    'order_status != "cancelled"',
                    'shop_id = '. OSC::getShop()->getId()
                ],
                'params' => []
            ];

            if ($timestamp_range[0] > 0) {
                $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
            }

            if ($timestamp_range[1] > 0) {
                $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
            }

            if (is_array($sref_ids) && count($sref_ids) > 0) {
                $condition['condition'][] = 'sref_id in (' . implode(',', $sref_ids) . ')';
            }

            if (count($condition['condition']) > 0) {
                $condition['condition'] = ' WHERE ' . implode(' AND ', $condition['condition']);
            } else {
                $condition['condition'] = '';
            }

            $select = ["FROM_UNIXTIME(added_timestamp, '{$date_format}') as `date_key`"];

            if (in_array('catalog/order', $product_order_report_keys, true)) {
                $select[] = "COUNT(order_id) as `total_orders`";
            }

            if (in_array('catalog/order_tax', $product_order_report_keys, true)) {
                $select[] = '(SUM(tax_price)/100) as tax_price';
            }

            if (in_array('catalog/revenue', $product_order_report_keys, true)) {
                $select[] = "(SUM(total_price)/100) as revenue";
            }

            if (in_array('catalog/tip_price', $product_order_report_keys, true)) {
                $select[] = "(SUM(tip_price)/100) AS tip_price";
            }

            $select = implode(',', $select);

            $DB_MASTER->query("SELECT {$select} FROM osc_catalog_order {$condition['condition']} GROUP BY `date_key` ORDER BY `date_key` ASC", $condition['params'], 'fetch_report_record');

            while ($row = $DB_MASTER->fetchArray('fetch_report_record')) {
                $rows[] = [
                    'report_key' => 'catalog/order',
                    'value' => $row['total_orders'],
                    'date_key' => $row['date_key']
                ];

                $rows[] = [
                    'report_key' => 'catalog/order_tax',
                    'value' => $row['tax_price'],
                    'date_key' => $row['date_key']
                ];

                $rows[] = [
                    'report_key' => 'catalog/revenue',
                    'value' => $row['revenue'],
                    'date_key' => $row['date_key']
                ];

                $rows[] = [
                    'report_key' => 'catalog/tip_price',
                    'value' => $row['tip_price'],
                    'date_key' => $row['date_key']
                ];
            }

            if (in_array('catalog/refunded_price', $product_order_report_keys, true)) {
                $condition = [
                    'condition' => [
                        "t.transaction_type = 'refund'",
                        't.shop_id = ' . OSC::getShop()->getId(),
                        'o.order_id = t.order_id'
                    ],
                    'params' => []
                ];

                if (is_array($sref_ids) && count($sref_ids) > 0) {
                    $condition['condition'][] = 'o.sref_id in (' . implode(',', $sref_ids) . ')';
                }

                if ($timestamp_range[0] > 0) {
                    $condition['condition'][] = 't.added_timestamp >= ' . $timestamp_range[0];
                }

                if ($timestamp_range[1] > 0) {
                    $condition['condition'][] = 't.added_timestamp <= ' . $timestamp_range[1];
                }

                $condition['condition'] = implode(' AND ', $condition['condition']);

                $DB_MASTER->query("SELECT (SUM(t.amount)/100) as refunded, (SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(t.transaction_data,'\"tax_price\":',-1), ',', 1))/100) as refunded_tax_price, FROM_UNIXTIME(t.added_timestamp, '{$date_format}') as `date_key` FROM osc_catalog_order_transaction t, osc_catalog_order o WHERE {$condition['condition']} GROUP BY `date_key` ORDER BY `date_key` ASC", $condition['params'], 'fetch_report_record');

                while ($row = $DB_MASTER->fetchArray('fetch_report_record')) {
                    $rows[] = [
                        'report_key' => 'catalog/refunded_price',
                        'value' => $row['refunded'],
                        'date_key' => $row['date_key']
                    ];
                    $rows[] = [
                        'report_key' => 'catalog/refunded_tax_price',
                        'value' => $row['refunded_tax_price'],
                        'date_key' => $row['date_key']
                    ];
                }
            }

            if (in_array('catalog/item_solds', $product_order_report_keys, true)) {
                $condition = [
                    'condition' => [
                        'o.order_status != "cancelled"',
                        'i.order_id = o.order_id',
                        'i.shop_id = '. OSC::getShop()->getId(),
                        'i.additional_data NOT LIKE \'%"resend":{"resend":"%\''
                    ],
                    'params' => []
                ];

                if (is_array($sref_ids) && count($sref_ids) > 0) {
                    $condition['condition'][] = 'o.sref_id in (' . implode(',', $sref_ids) . ')';
                }

                if ($timestamp_range[0] > 0) {
                    $condition['condition'][] = 'i.added_timestamp >= ' . $timestamp_range[0];
                }

                if ($timestamp_range[1] > 0) {
                    $condition['condition'][] = 'i.added_timestamp <= ' . $timestamp_range[1];
                }

                if (count($condition['condition']) > 0) {
                    $condition['condition'] = ' WHERE ' . implode(' AND ', $condition['condition']);
                } else {
                    $condition['condition'] = '';
                }

                $DB_MASTER->query("SELECT SUM(CAST(i.quantity AS SIGNED) * IF(i.other_quantity > 0, CAST(i.other_quantity AS SIGNED), 1)) as total_items, FROM_UNIXTIME(i.added_timestamp, '{$date_format}') as `date_key`" . " FROM osc_catalog_order_item i, osc_catalog_order o {$condition['condition']} GROUP BY `date_key` ORDER BY `date_key` ASC", $condition['params'], 'fetch_report_record');

                while ($row = $DB_MASTER->fetchArray('fetch_report_record')) {
                    $_row = [
                        'report_key' => 'catalog/item_solds',
                        'value' => $row['total_items'],
                        'date_key' => $row['date_key']
                    ];

                    if (isset($row['product_id'])) {
                        $_row['product_id'] = $row['product_id'];
                    }

                    $rows[] = $_row;
                }
            }

            if (in_array('marketing/point', $product_order_report_keys, true)) {
                $condition = [
                    'condition' => [],
                    'params' => []
                ];

                $condition_vendor = [
                    'condition' => [],
                    'params' => []
                ];

                if (is_array($sref_ids) && count($sref_ids) > 0){
                    $condition['condition'][] = 'member_id in (' . implode(',', $sref_ids) . ')';

                    // Vendor point
                    $vendor_collection = OSC::model('user/member')->getCollection()
                        ->addField('username')
                        ->addCondition('member_id', $sref_ids, OSC_Database::OPERATOR_IN)
                        ->load()->toArray();
                    $vendors = array_column($vendor_collection, 'username');

                    $condition_vendor['condition'][] = 'vendor in ("' . implode('","', $vendors) . '")';
                }

                if ($timestamp_range[0] > 0) {
                    $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
                    $condition_vendor['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
                }

                if ($timestamp_range[1] > 0) {
                    $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
                    $condition_vendor['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
                }

                if (count($condition['condition']) > 0) {
                    $condition['condition'] = ' WHERE ' . implode(' AND ', $condition['condition']);
                } else {
                    $condition['condition'] = '';
                }

                if (count($condition_vendor['condition']) > 0) {
                    $condition_vendor['condition'] = ' WHERE ' . implode(' AND ', $condition_vendor['condition']);
                } else {
                    $condition_vendor['condition'] = '';
                }

                $DB->query("SELECT SUM(point) as total_point, FROM_UNIXTIME(added_timestamp, '{$date_format}') as `date_key`" . " FROM osc_marketing_point {$condition['condition']} GROUP BY `date_key` ORDER BY `date_key` ASC", $condition['params'], 'fetch_report_record');
                $_row = [];
                while ($row = $DB->fetchArray('fetch_report_record')) {
                    $_row = [
                        'report_key' => 'marketing/point',
                        'value' => $row['total_point'],
                        'date_key' => $row['date_key']
                    ];

                    if (isset($row['product_id'])) {
                        $_row['product_id'] = $row['product_id'];
                    }
                    $rows[] = $_row;
                }

                $DB->free('fetch_report_record');

                $DB->query("SELECT SUM(vendor_point) as total_vendor_point, FROM_UNIXTIME(added_timestamp, '{$date_format}') as `date_key`" . " FROM osc_marketing_point {$condition_vendor['condition']} GROUP BY `date_key` ORDER BY `date_key` ASC", $condition_vendor['params'], 'fetch_report_vendor_record');
                while ($row_v = $DB->fetchArray('fetch_report_vendor_record')) {
                    $_row = [
                        'report_key' => 'marketing/vendor_point',
                        'value' => $row_v['total_vendor_point'],
                        'date_key' => $row_v['date_key']
                    ];
                    $rows[] = $_row;
                }

                $DB->free('fetch_report_vendor_record');


            }
        }

        if (count($product_report_keys) && $product_ids) {
            $condition = [
                'condition' => [],
                'params' => []
            ];

            if (count($product_report_keys) > 1) {
                $condition['condition'][] = 'report_key IN (\'' . implode("','", $product_report_keys) . '\')';
            } else {
                $condition['condition'][] = 'report_key = :report_keys';
                $condition['params']['report_keys'] = reset($product_report_keys);
            }

            if ($timestamp_range[0] > 0) {
                $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
            }

            if ($timestamp_range[1] > 0) {
                $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
            }

            if (count($product_ids) > 1) {
                $condition['condition'][] = 'product_id IN (' . implode(',', $product_ids) . ')';
            } else if (count($product_ids) == 1) {
                $condition['condition'][] = 'product_id = ' . $product_ids[0];
            }

            $condition['condition'] = implode(' AND ', $condition['condition']);

            $DB->query("SELECT SUM(report_value) as `value`, report_key, FROM_UNIXTIME(added_timestamp, '{$date_format}') as `date_key`" . (count($product_ids) < 1 ? '' : ', product_id') . ", referer FROM `osc_report_record_product_referer` WHERE {$condition['condition']} GROUP BY report_key, `date_key`" . (count($product_ids) < 1 ? '' : ', product_id') . ", referer ORDER BY `date_key` ASC", $condition['params'], 'fetch_report');

            $rows = array_merge($rows, $DB->fetchArrayAll('fetch_report'));
        }

        return $rows;
    }

    public function getProductListDataBySref($begin_date, $end_date, $current_page ,$sref_ids) {
        $timestamp_range = $this->getTimestampRange($begin_date, $end_date);

        $date_condition = [
            'o.order_status != "cancelled"',
            'i.order_id = o.order_id',
            'i.shop_id = '. OSC::getShop()->getId(),
            'i.additional_data NOT LIKE \'%"resend":{"resend":"%\''
        ];

        if (is_array($sref_ids) && count($sref_ids) > 0){
            $date_condition[] = 'o.sref_id in (' . implode(',', $sref_ids) . ')';
        }

        if ($timestamp_range[0] > 0) {
            $date_condition[] = 'i.added_timestamp >= ' . $timestamp_range[0];
        }

        if ($timestamp_range[1] > 0) {
            $date_condition[] = 'i.added_timestamp <= ' . $timestamp_range[1];
        }

        if (count($date_condition) > 0) {
            $date_condition = ' WHERE ' . implode(' AND ', $date_condition);
        } else {
            $date_condition = '';
        }

        /* @var $DB OSC_Database */

        $DB_MASTER = OSC::core('database')->getAdapter('db_master_read');

        $DB_MASTER->query("SELECT COUNT(DISTINCT i.product_id) as total FROM osc_catalog_order_item i , osc_catalog_order o{$date_condition}", null, 'fetch_report_record');

        $total_rows = $DB_MASTER->fetch('fetch_report_record')->total;

        if ($total_rows < 1) {
            throw new Exception('No data');
        }

        $page_size = 15;
        $current_page = min(max(1, intval($current_page)), ceil($total_rows / $page_size));

        $offset = ($current_page - 1) * $page_size;

        $DB_MASTER->query("SELECT i.product_id, SUM(i.price * i.quantity)/100 AS revenue, SUM(i.quantity * IF(i.other_quantity > 0, i.other_quantity, 1)) AS sales, COUNT(DISTINCT i.order_master_record_id) AS orders FROM osc_catalog_order_item i, osc_catalog_order o{$date_condition} GROUP BY i.product_id ORDER BY revenue DESC LIMIT {$offset}, {$page_size}", null, 'fetch_report_record');

        $data = [];

        while ($row = $DB_MASTER->fetchArray('fetch_report_record')) {
            $data[$row['product_id']] = [
                'revenue' => floatval($row['revenue']),
                'orders' => intval($row['orders']),
                'sales' => intval($row['sales']),
                'title' => 'Deleted',
                'url' => '#'
            ];
        }

        if (count($data) < 1) {
            throw new Exception('No data');
        }

        return [
            'rows' => $data,
            'current_page' => $current_page,
            'page_size' => $page_size,
            'total_rows' => $total_rows
        ];
    }

    /**
     * @param $product_id
     * @param $sref_ids
     * @param $timestamp_range
     * @return array
     */
    private function __getVariantProductReportDataBySrefIds($product_id, $sref_ids, $timestamp_range) {
        /* @var $master_database OSC_Database */
        $master_database = OSC::core('database')->getAdapter('db_master_read');

        $result = [];

        // define selected fields
        $selected_fields = [
            'product_table.variant_id',
            'product_table.sku',
            'product_table.title',
            'product_table.options',
            'SUM( product_table.price * product_table.quantity ) / 100 AS revenue',
            'SUM( product_table.quantity * IF ( product_table.other_quantity > 0, product_table.other_quantity, 1 )) AS sales',
            'COUNT(DISTINCT product_table.order_master_record_id) AS orders',
            'order_table.billing_country AS billing_country',
            'order_table.sref_id AS member_id'
        ];

        $string_selected_fields = implode(' , ', $selected_fields);

        // define conditions
        $conditions = [
            'order_table.order_status != "cancelled"',
            'product_table.product_id = ' . $product_id,
            'product_table.additional_data NOT LIKE \'%"resend":{"resend":"%\'',
            'product_table.shop_id = ' . OSC::getShop()->getId()
        ];

        if (is_array($sref_ids)) {
            if (count($sref_ids) > 1) {
                $conditions[] = 'order_table.sref_id IN (' . implode(',', $sref_ids) . ')';
            } else {
                $conditions[] = 'order_table.sref_id = ' . $sref_ids[0];
            }
        }

        if (!empty($timestamp_range[0]) && $timestamp_range[0] > 0) {
            $conditions[] = 'product_table.added_timestamp >= ' . $timestamp_range[0];
        }

        if (!empty($timestamp_range[1]) && $timestamp_range[1] > 0) {
            $conditions[] = 'product_table.added_timestamp <= ' . $timestamp_range[1];
        }

        $string_conditions = implode(' AND ', $conditions);

        $query = "SELECT {$string_selected_fields} 
            FROM osc_catalog_order_item AS product_table
            INNER JOIN osc_catalog_order AS order_table
                ON product_table.order_master_record_id = order_table.master_record_id
            WHERE {$string_conditions}
            GROUP BY product_table.variant_id, order_table.billing_country, order_table.sref_id
            ORDER BY revenue DESC";

        try {
            $query_key = 'fetch_report_record';
            $master_database->query($query, null, $query_key);

            while ($row = $master_database->fetchArray($query_key)) {
                $result[] = $row;
            }

            $master_database->free($query_key);
        } catch (Exception $exception) {}

        return $result;
    }

    /**
     * @param $variant_product_report_data_list
     * @param $variants
     * @param $product_title
     * @param $orders
     * @param $sales
     * @param $revenue
     * @return void
     */
    private function __handleResultVariantProductReportDataBySrefIds(
        $variant_product_report_data_list,
        &$variants,
        &$product_title,
        &$orders,
        &$sales,
        &$revenue
    ) {
        $member_id_list = [];

        try {
            foreach ($variant_product_report_data_list as $product_report_data) {
                $orders += intval($product_report_data['orders'] ?? 0);
                $sales += intval($product_report_data['sales'] ?? 0);
                $revenue += floatval($product_report_data['revenue'] ?? 0);
                $member_id_list[] = intval($product_report_data['member_id']);

                if (!$product_title) {
                    $product_title = $product_report_data['title'] ?? null;
                }

                if (empty($product_report_data['variant_id']) || empty($product_report_data['options'])) {
                    continue;
                }

                $existed_index = null;

                foreach ($variants as $index => $variant) {
                    if ($product_report_data['variant_id'] == $variant['variant_id'] &&
                        $product_report_data['member_id'] == $variant['member_id'] &&
                        $product_report_data['billing_country'] === $variant['billing_country']) {
                        $existed_index = $index;
                        break;
                    }
                }

                if (is_null($existed_index)) {
                    $product_report_data['options'] = OSC::decode($product_report_data['options']);
                    $variant_title = $product_title;

                    if (count($product_report_data['options'])) {
                        foreach ($product_report_data['options'] as $index => $option) {
                            $product_report_data['options'][$index] = $option['value'];
                        }

                        $variant_title .= ' - ' . implode(' / ', $product_report_data['options']);
                    }

                    $variants[] = [
                        'variant_id' => intval($product_report_data['variant_id']),
                        'title' => $variant_title,
                        'revenue' => floatval($product_report_data['revenue'] ?? 0),
                        'sales' => intval($product_report_data['sales'] ?? 0),
                        'sku' => $product_report_data['sku'],
                        'billing_country' => $product_report_data['billing_country'],
                        'member_id' => intval($product_report_data['member_id'])
                    ];
                } else {
                    $variants[$existed_index]['sales'] += intval($product_report_data['sales']);
                    $variants[$existed_index]['revenue'] += floatval($product_report_data['revenue']);
                }
            }

            $member_id_list = array_unique($member_id_list);

            if ($member_id_list) {
                // get member list
                $member_list = OSC::model('user/member')
                    ->getCollection()
                    ->addField('member_id', 'username')
                    ->addCondition('member_id', $member_id_list, OSC_Database::OPERATOR_IN)
                    ->load()
                    ->toArray();

                if (count($member_list) && count($variants)) {
                    // update username of variant
                    foreach ($variants as $variantIndex => $variant) {
                        foreach ($member_list as $member) {
                            if ($variant['member_id'] === $member['member_id']) {
                                $variants[$variantIndex]['username'] = $member['username'];
                                break;
                            }
                        }
                    }
                }
            }
        } catch (Exception $exception) {}
    }

    /**
     * @param $product_id
     * @param $sref_ids
     * @param $timestamp_range
     * @return array
     */
    private function __getRefererProductReportDataBySrefIds($product_id, $sref_ids, $timestamp_range) {
        /* @var $master_database OSC_Database */
        $master_database = OSC::core('database')->getAdapter('db_master_read');

        $result = [];

        // define selected fields
        $selected_fields = [
            'order_table.client_referer AS referer',
            'SUM(product_table.quantity * IF ( product_table.other_quantity > 0, product_table.other_quantity, 1 )) AS sales',
            'COUNT(DISTINCT order_table.order_id ) AS orders',
            'SUM( product_table.price * product_table.quantity ) / 100 AS revenue'
        ];

        $string_selected_fields = implode(' , ', $selected_fields);

        // define conditions
        $conditions = [
            'order_table.order_status != "cancelled"',
            'product_table.shop_id = ' . OSC::getShop()->getId(),
            'product_table.product_id=' . $product_id,
            'product_table.additional_data NOT LIKE \'%"resend":{"resend":"%\''
        ];

        if (is_array($sref_ids)) {
            if (count($sref_ids) > 1) {
                $conditions[] = 'order_table.sref_id IN (' . implode(',', $sref_ids) . ')';
            } else {
                $conditions[] = 'order_table.sref_id = ' . $sref_ids[0];
            }
        }

        if (!empty($timestamp_range[0]) && $timestamp_range[0] > 0) {
            $conditions[] = 'product_table.added_timestamp >= ' . $timestamp_range[0];
        }

        if (!empty($timestamp_range[1]) && $timestamp_range[1] > 0) {
            $conditions[] = 'product_table.added_timestamp <= ' . $timestamp_range[1];
        }

        $string_conditions = implode(' AND ', $conditions);

        $query = "SELECT {$string_selected_fields} 
            FROM osc_catalog_order_item AS product_table
            INNER JOIN osc_catalog_order AS order_table
                ON product_table.order_id = order_table.order_id
            WHERE {$string_conditions}
            GROUP BY referer
            ORDER BY revenue DESC";

        try {
            $query_key = 'fetch_report_record';
            $master_database->query($query, null, $query_key);

            while ($row = $master_database->fetchArray($query_key)) {
                $result[] = $row;
            }

            $master_database->free($query_key);
        } catch (Exception $exception) {}

        return $result;
    }

    private function __handleResultRefererProductReportDataBySrefIds($referer_product_report_data_list, &$referers) {
        try {
            foreach ($referer_product_report_data_list as $product_report_data) {
                if (empty($product_report_data['referer'])) {
                    $product_report_data['referer'] = 'direct';
                }

                if (!isset($referers[$row['referer']])) {
                    $referers[$product_report_data['referer']] = [
                        'referer' => $product_report_data['referer'],
                        'orders' => 0,
                        'sales' => 0,
                        'revenue' => 0,
                        'visits' => 0,
                        'unique_visitors' => 0
                    ];
                }

                $referers[$product_report_data['referer']]['orders'] += intval($product_report_data['orders']);
                $referers[$product_report_data['referer']]['sales'] += intval($product_report_data['sales']);
                $referers[$product_report_data['referer']]['revenue'] += floatval($product_report_data['revenue']);
            }
        } catch (Exception $exception) {}
    }

    public function getProductDetailDataBySref($product_id, $begin_date, $end_date, $sref_ids) {
        $timestamp_range = $this->getTimestampRange($begin_date, $end_date);

        if (count($timestamp_range) !== 2) {
            return null;
        }

        $variants = [];
        $referers = [];
        $product_title = null;
        $orders = 0;
        $sales = 0;
        $revenue = 0;
        $visits = 0;
        $unique_visitors = 0;

        $variant_product_report_data_list = $this->__getVariantProductReportDataBySrefIds($product_id, $sref_ids, $timestamp_range);

        $this->__handleResultVariantProductReportDataBySrefIds(
            $variant_product_report_data_list,
            $variants,
            $product_title,
            $orders,
            $sales,
            $revenue
        );

        $referer_product_report_data_list = $this->__getRefererProductReportDataBySrefIds($product_id, $sref_ids, $timestamp_range);

        $this->__handleResultRefererProductReportDataBySrefIds($referer_product_report_data_list, $referers);

        $keys = [
            'catalog/item/visit' => 'visits',
            'catalog/item/unique_visitor' => 'unique_visitors'
        ];

        $report_keys = array_keys($keys);

        $report_data_list = $this->_getReportProductData($report_keys, '%Y', $this->getTimestampRange($begin_date, $end_date), $sref_ids, [$product_id]);

        try {
            foreach ($report_data_list as $report_data) {
                if ($report_data['report_key'] == 'catalog/item/visit') {
                    $visits += intval($report_data['value']);
                } else if ($report_data['report_key'] == 'catalog/item/unique_visitor') {
                    $unique_visitors += intval($report_data['value']);
                }

                if (empty($referers[$report_data['referer']])) {
                    continue;
                }

                $referers[$report_data['referer']][$keys[$report_data['report_key']]] += $report_data['value'];
            }
        } catch (Exception $exception) {}

        return [
            'product_title' => $product_title,
            'variants' => $variants,
            'referers' => $referers,
            'revenue' => $revenue,
            'orders' => $orders,
            'sales' => $sales,
            'visits' => $visits,
            'unique_visitors' => $unique_visitors
        ];
    }

    protected function _getReportDataBySrefId($date_format, $keys, $begin_date = null, $end_date = null, $sref_ids) {
        /* @var $DB OSC_Database */
        $td = new DateTime('now', new DateTimeZone(OSC::helper('core/setting')->get('core/timezone')));
        $DB_MASTER = OSC::core('database')->getAdapter('db_master_read');
        $DB_MASTER->query("SET time_zone=:timezone", ['timezone' => $td->format('P')], 'set_timezone');

        $DB = OSC::core('database');
        $DB->query("SET time_zone=:timezone", ['timezone' => $td->format('P')], 'set_timezone');

        $report_keys = array_keys($keys);

        $timestamp_range = $this->getTimestampRange($begin_date, $end_date);

        $rows = $this->_getReportProductData($report_keys, $date_format, $timestamp_range,$sref_ids);

        $group = [];

        foreach ($keys as $data_key) {
            $group[$data_key] = [];
        }

        return ['group' => $group, 'rows' => $rows];
    }

    public function currentUserHasPowerBiPerm()
    {
        $power_bi_manage = OSC::helper('core/setting')->get('power_bi_manage');

        $has_permission = false;
        foreach ($power_bi_manage as $item) {
            if (
                OSC::helper('user/authentication')->getMember()->isAdmin() ||
                (is_array($item['viewer']) && in_array(OSC::helper('user/authentication')->getMember()->getId(), $item['viewer']))
            ) {
                $has_permission = true;
                break;
            }
        }
        return $has_permission;
    }
}


