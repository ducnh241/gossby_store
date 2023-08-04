<?php

class Helper_Report_Common {

    protected $_record_event = [];

    const SECRET_KEY = 'dfg&^sdfsdffDF';
    const REPORT_PER_PAGE = 15;

    public static function getRoundTimestamp() {
        static $timestamp = 0;

        if ($timestamp === 0) {
            $timestamp = time();
            $timestamp -= $timestamp % (60 * 15);
        }

        return $timestamp;
    }

    protected function _getRefererCookieKey() {
        return '_referer';
    }

    public function eventEncode($data) {
        return base64_encode(OSC::core('encode')->encode(OSC::encode($data), static::SECRET_KEY));
    }

    public function eventDecode($data) {
        return OSC::decode(OSC::core('encode')->decode(base64_decode($data), static::SECRET_KEY), true);
    }

    public function addRecordEvent($event, $event_data = 0) {
        $this->_record_event[$event] = $event_data;
    }

    public function getRecordEvent() {
        return $this->_record_event;
    }

    public function loadExternalTrackingCode() {
        $data = OSC::core('observer')->dispatchEvent('frontend/tracking', $this->getRecordEvent());

        $html_content = '';

        foreach ($data as $value) {
            if ($value) {
                $html_content .= $value;
            }
        }

        return $html_content;
    }

    public function fetchRefererList($report_keys = null, $begin_date = null, $end_date = null) {
        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $condition = [
            'condition' => [],
            'params' => []
        ];

        if (!is_array($report_keys)) {
            $report_keys = [];
        }

        if (count($report_keys) > 0) {
            if (count($report_keys) > 1) {
                $condition['condition'][] = 'report_key IN (\'' . implode("','", $report_keys) . '\')';
            } else {
                $condition['condition'][] = 'report_key = :report_keys';
                $condition['params']['report_keys'] = reset($report_keys);
            }
        }

        $timestamp_range = $this->getTimestampRange($begin_date, $end_date);

        if ($timestamp_range[0] > 0) {
            $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
        }

        if ($timestamp_range[1] > 0) {
            $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
        }

        $condition['condition'] = implode(' AND ', $condition['condition']);

        $DB->query("SELECT DISTINCT referer_host FROM `osc_report_record` WHERE {$condition['condition']}", $condition['params'], 'fetch_report');

        $referers = [];

        while ($row = $DB->fetchArray('fetch_report')) {
            $referers[] = $row['referer_host'];
        }

        return $referers;
    }

    public function setReferer($referer_url) {
        $referer_key = $this->_getRefererCookieKey();

        OSC::cookieRemoveCrossSite($referer_key);

        $referer_url = trim(strval($referer_url));

        if (!$referer_url) {
            return;
        }

        $referer_info = parse_url($referer_url);

        if ($referer_info['host'] && strtolower($referer_info['host']) == OSC::$domain) {
            return;
        }

        OSC::cookieSetCrossSite($referer_key, OSC::encode([
            'url' => $referer_url,
            'host' => $referer_info['host'] ?: $referer_info['path']
        ]));
    }

    public function getReferer() {
        $referer = OSC::cookieGet($this->_getRefererCookieKey());

        return $referer ? OSC::decode($referer) : '';
    }

    public function increment($key, $value) {
        $value = round($value, 2);

        if ($value == 0) {
            return;
        }

        $referer = $this->getReferer();
        $referer_host = $referer ? $referer['host'] : 'direct';

        $ab_test_keys = OSC::getABTestKey();

        $DB = OSC::core('database')->getWriteAdapter();

        $added_timestamp = static::getRoundTimestamp();

        try {
            $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_new (report_key, report_value, added_timestamp) VALUES(:report_key, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
        } catch (Exception $ex) {

        }

        try {
            $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_new_referer (report_key, referer, report_value, added_timestamp) VALUES(:report_key, :referer, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
        } catch (Exception $ex) {

        }

        if (count($ab_test_keys) > 0) {
            foreach ($ab_test_keys as $ab_key => $ab_value) {
                if (strpos($ab_key, Helper_AutoAb_ProductPrice::PREFIX_COOKIE_KEY_ABTEST) !== false) {
                    continue;
                }

                try {
                    $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_new_ab (report_key, ab_key, ab_value, report_value, added_timestamp) VALUES(:report_key, :ab_key, :ab_value, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
                } catch (Exception $ex) {

                }

                try {
                    $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_new_referer_ab (report_key, ab_key, ab_value, referer, report_value, added_timestamp) VALUES(:report_key, :ab_key, :ab_value, :referer, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
                } catch (Exception $ex) {

                }
            }
        }
    }

    public function incrementProductRecord($key, $product_id, $value) {
        $value = round($value, 2);

        if ($value == 0) {
            return;
        }

        $product_id = intval($product_id);

        if ($product_id < 0) {
            $product_id = 0;
        }

        $referer = $this->getReferer();
        $referer_host = $referer ? $referer['host'] : 'direct';

        $ab_test_keys = OSC::getABTestKey();

        $added_timestamp = static::getRoundTimestamp();

        $today = gmdate('Ymd', time());

        $DB = OSC::core('database')->getWriteAdapter();

        try {
            $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_product (report_key, product_id, report_value, added_timestamp) VALUES(:report_key, :product_id, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'product_id' => $product_id, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
        } catch (Exception $ex) {

        }

        try {
            $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_product_referer (report_key, product_id, referer, report_value, added_timestamp) VALUES(:report_key, :product_id, :referer, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'product_id' => $product_id, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
        } catch (Exception $ex) {

        }

        if ($key == 'catalog/item/unique_visitor') { // Tracking unique_visit for product
            try {
                $tracking_report_key = 'view_unique';
                $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_product_tracking (report_key, product_id, report_value, date, added_timestamp, modified_timestamp) VALUES(:report_key, :product_id, :report_value, :date, :added_timestamp, :modified_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value), modified_timestamp = :modified_timestamp", ['report_key' => $tracking_report_key, 'product_id' => $product_id, 'report_value' => $value, 'date' => $today, 'added_timestamp' => time(), 'modified_timestamp' => time()], 'update_report_record');
            } catch (Exception $ex) {

            }
        }

        if (count($ab_test_keys) > 0) {
            foreach ($ab_test_keys as $ab_key => $ab_value) {
                try {
                    $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_product_ab (report_key, product_id, ab_key, ab_value, report_value, added_timestamp) VALUES(:report_key, :product_id, :ab_key, :ab_value, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'product_id' => $product_id, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
                } catch (Exception $ex) {

                }

                try {
                    $DB->query("INSERT INTO " . OSC::systemRegistry('db_prefix') . "report_record_product_referer_ab (report_key, product_id, referer, ab_key, ab_value, report_value, added_timestamp) VALUES(:report_key, :product_id, :referer, :ab_key, :ab_value, :report_value, :added_timestamp) ON DUPLICATE KEY UPDATE report_value=(report_value + :report_value)", ['report_key' => $key, 'product_id' => $product_id, 'ab_key' => $ab_key, 'ab_value' => $ab_value, 'referer' => $referer_host, 'report_value' => $value, 'added_timestamp' => $added_timestamp], 'update_report_record');
                } catch (Exception $ex) {

                }
            }
        }
    }

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

        return [$begin_date < 1 ? $begin_date : mktime(0, 0, 0, substr($begin_date, 4, 2), substr($begin_date, -2), substr($begin_date, 0, 4)), $end_date < 1 ? $end_date : mktime(23, 59, 59, substr($end_date, 4, 2), substr($end_date, -2), substr($end_date, 0, 4))];
    }

    public function getReportDataByHour($keys, $auto_fill = false, $begin_date = null, $end_date = null, $extra_group_by = null, $extra_condition = null, $ab_test = null) {
        $data = $this->_getReportData('%Y/%m/%d/%H', $keys, $auto_fill, $begin_date, $end_date, $extra_group_by, $extra_condition, $ab_test);

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

    public function getReportDataByDate($keys, $auto_fill = false, $begin_date = null, $end_date = null, $extra_group_by = null, $extra_condition = null, $ab_test = null) {
        $data = $this->_getReportData('%Y/%m/%d', $keys, $auto_fill, $begin_date, $end_date, $extra_group_by, $extra_condition, $ab_test);

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

    public function getReportDataByWeek($keys, $auto_fill = false, $begin_date = null, $end_date = null, $extra_group_by = null, $extra_condition = null, $ab_test = null) {
        $data = $this->_getReportData('%Y/%U', $keys, $auto_fill, $begin_date, $end_date, $extra_group_by, $extra_condition, $ab_test);

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

    public function getReportDataByMonth($keys, $auto_fill = false, $begin_date = null, $end_date = null, $extra_group_by = null, $extra_condition = null, $ab_test = null) {
        $data = $this->_getReportData('%Y/%m', $keys, $auto_fill, $begin_date, $end_date, $extra_group_by, $extra_condition, $ab_test);

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

    public function getReportDataByYear($keys, $auto_fill = false, $begin_date = null, $end_date = null, $extra_group_by = null, $extra_condition = null, $ab_test = null) {
        $data = $this->_getReportData('%Y', $keys, $auto_fill, $begin_date, $end_date, $extra_group_by, $extra_condition, $ab_test);

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

    protected function _getReportProductData(&$report_keys, $date_format, $timestamp_range, $product_ids = null, $ab_test = null, $fetch_referer = false) {
        /* @var $DB OSC_Database */

        $DB_MASTER = OSC::core('database')->getAdapter('db_master_read');

        $product_report_keys = [];
        $product_order_report_keys = [];

        foreach ($report_keys as $idx => $report_key) {
            if (in_array($report_key, ['catalog/item/unique_visitor', 'catalog/item/visit', 'catalog/item/view', 'catalog/add_to_cart', 'catalog/checkout_initialize'], true)) {
                unset($report_keys[$idx]);
                $product_report_keys[] = $report_key;
            }

            if (in_array($report_key, ['catalog/order', 'catalog/order_tax', 'catalog/item_solds', 'catalog/revenue', 'catalog/refunded_price', 'catalog/refunded_tax_price', 'catalog/tip_price'])) {
                unset($report_keys[$idx]);
                $product_order_report_keys[] = $report_key;
            }
        }

        $report_keys = array_values($report_keys);

        if (!is_array($product_ids)) {
            $product_ids = [$product_ids];
        }

        $product_ids = array_map(function ($id) {
            return intval($id);
        }, $product_ids);
        $product_ids = array_filter($product_ids, function ($id) {
            return $id > 0;
        });
        $product_ids = array_unique($product_ids);

        $rows = [];

        if (count($product_order_report_keys) > 0) {
            $condition = [
                'condition' => [
                    'order_status != "cancelled"',
                    'shop_id = ' . OSC::getShop()->getId()
                ],
                'params' => []
            ];

            if ($timestamp_range[0] > 0) {
                $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
            }

            if ($timestamp_range[1] > 0) {
                $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
            }

            if (is_array($ab_test)) {
                $condition['condition'][] = "ab_test REGEXP :ab_test";
                $condition['params']['ab_test'] = '(^|\|)' . $ab_test['key'] . ':' . $ab_test['value'] . '(\||$)';
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
                $select[] = '(SUM(tax_price)/100) AS tax_price';
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
                        "transaction_type = 'refund'",
                        "shop_id = ". OSC::getShop()->getId()
                    ],
                    'params' => []
                ];

                if ($timestamp_range[0] > 0) {
                    $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
                }

                if ($timestamp_range[1] > 0) {
                    $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
                }

                if (is_array($ab_test)) {
                    $condition['condition'][] = "order_id IN (SELECT order_id FROM osc_catalog_order WHERE ab_test REGEXP :ab_test)";
                    $condition['params']['ab_test'] = '(^|\|)' . $ab_test['key'] . ':' . $ab_test['value'] . '(\||$)';
                }

                $condition['condition'] = implode(' AND ', $condition['condition']);

                $DB_MASTER->query("SELECT (SUM(amount)/100) as refunded, (SUM(SUBSTRING_INDEX(SUBSTRING_INDEX(transaction_data,'\"tax_price\":',-1), ',', 1))/100) as refunded_tax_price, FROM_UNIXTIME(added_timestamp, '{$date_format}') as `date_key` FROM osc_catalog_order_transaction WHERE {$condition['condition']} GROUP BY `date_key` ORDER BY `date_key` ASC", $condition['params'], 'fetch_report_record');

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
                        'i.additional_data NOT LIKE \'%"resend":{"resend":"%\'',
                        'i.shop_id = '. OSC::getShop()->getId()
                    ],
                    'params' => []
                ];

                if ($timestamp_range[0] > 0) {
                    $condition['condition'][] = 'i.added_timestamp >= ' . $timestamp_range[0];
                }

                if ($timestamp_range[1] > 0) {
                    $condition['condition'][] = 'i.added_timestamp <= ' . $timestamp_range[1];
                }

                if (count($product_ids) > 1) {
                    $condition['condition'][] = 'i.product_id IN (' . implode(',', $product_ids) . ')';
                } else if (count($product_ids) == 1) {
                    $condition['condition'][] = 'i.product_id = ' . $product_ids[0];
                }

                if (is_array($ab_test)) {
                    $condition['condition'][] = "o.ab_test REGEXP :ab_test";
                    $condition['params']['ab_test'] = '(^|\|)' . $ab_test['key'] . ':' . $ab_test['value'] . '(\||$)';
                }

                if (count($condition['condition']) > 0) {
                    $condition['condition'] = ' WHERE ' . implode(' AND ', $condition['condition']);
                } else {
                    $condition['condition'] = '';
                }

                $DB_MASTER->query("SELECT SUM(CAST(i.quantity AS SIGNED) * if(i.other_quantity > 0, CAST(i.other_quantity AS SIGNED), 1)) as total_items, FROM_UNIXTIME(i.added_timestamp, '{$date_format}') as `date_key`" . (count($product_ids) < 1 ? '' : ',product_id') . " FROM osc_catalog_order_item i, osc_catalog_order o {$condition['condition']} GROUP BY `date_key`" . (count($product_ids) < 1 ? '' : ', product_id') . " ORDER BY `date_key` ASC", $condition['params'], 'fetch_report_record');

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
        }

        if (count($product_report_keys) > 0) {
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

            if (is_array($ab_test)) {
                $condition['condition'][] = 'ab_key=:ab_key AND ab_value=:ab_value';

                $condition['params']['ab_key'] = $ab_test['key'];
                $condition['params']['ab_value'] = $ab_test['value'];
            }

            if (count($product_ids) > 1) {
                $condition['condition'][] = 'product_id IN (' . implode(',', $product_ids) . ')';
            } else if (count($product_ids) == 1) {
                $condition['condition'][] = 'product_id = ' . $product_ids[0];
            }

            $condition['condition'] = implode(' AND ', $condition['condition']);

            // todo chua khai bao bien $extra_group_by
            if (!is_array($extra_group_by)) {
                $extra_group_by = explode(',', $extra_group_by);
            }

            $extra_group_by = array_map(function ($key) {
                $key = trim($key);
                return (!$key || preg_match('/[^a-zA-Z0-9\_\-]/', $key)) ? false : $key;
            }, $extra_group_by);
            $extra_group_by = array_filter($extra_group_by);

            if (count($extra_group_by) > 0) {
                $extra_group_by = array_unique($extra_group_by);
                $extra_group_by = ',' . implode(',', $extra_group_by);
            } else {
                $extra_group_by = '';
            }

            $DB = OSC::core('database');
            $DB->query("SELECT SUM(report_value) as `value`, report_key, FROM_UNIXTIME(added_timestamp, '{$date_format}') as `date_key`{$extra_group_by}" . (count($product_ids) < 1 ? '' : ', product_id') . ($fetch_referer ? ', referer' : '') . " FROM `osc_report_record_product" . ($fetch_referer ? '_referer' : '') . (is_array($ab_test) ? '_ab' : '') . "` WHERE {$condition['condition']} GROUP BY report_key, `date_key`" . (count($product_ids) < 1 ? '' : ', product_id') . ($fetch_referer ? ', referer' : '') . " ORDER BY `date_key` ASC", $condition['params'], 'fetch_report');

            $rows = array_merge($rows, $DB->fetchArrayAll('fetch_report'));
        }

        return $rows;
    }

    protected function _getReportData($date_format, $keys, $auto_fill = false, $begin_date = null, $end_date = null, $extra_group_by = null, $extra_condition = null, $ab_test = null) {
        /* @var $DB OSC_Database */
        $td = new DateTime('now', new DateTimeZone(OSC::helper('core/setting')->get('core/timezone')));

        $DB_MASTER = OSC::core('database')->getAdapter('db_master_read');
        $DB_MASTER->query("SET time_zone=:timezone", ['timezone' => $td->format('P')], 'set_timezone');

        $DB = OSC::core('database');
        $DB->query("SET time_zone=:timezone", ['timezone' => $td->format('P')], 'set_timezone');

        $report_keys = array_keys($keys);

        $timestamp_range = $this->getTimestampRange($begin_date, $end_date);

        $rows = $this->_getReportProductData($report_keys, $date_format, $timestamp_range, null, $ab_test, null);

        $condition = [
            'condition' => [],
            'params' => []
        ];

        if (count($report_keys) > 1) {
            $condition['condition'][] = 'report_key IN (\'' . implode("','", $report_keys) . '\')';
        } else {
            $condition['condition'][] = 'report_key = :report_keys';
            $condition['params']['report_keys'] = reset($report_keys);
        }

        if ($timestamp_range[0] > 0) {
            $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range[0];
        }

        if ($timestamp_range[1] > 0) {
            $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range[1];
        }

        if (is_array($ab_test)) {
            $condition['condition'][] = 'ab_key=:ab_key AND ab_value=:ab_value';

            $condition['params']['ab_key'] = $ab_test['key'];
            $condition['params']['ab_value'] = $ab_test['value'];
        }

        $condition['condition'] = implode(' AND ', $condition['condition']);

        if (!is_array($extra_group_by)) {
            $extra_group_by = explode(',', $extra_group_by);
        }

        $extra_group_by = array_map(function ($key) {
            $key = trim($key);
            return (!$key || preg_match('/[^a-zA-Z0-9\_\-]/', $key)) ? false : $key;
        }, $extra_group_by);
        $extra_group_by = array_filter($extra_group_by);

        if (count($extra_group_by) > 0) {
            $extra_group_by = array_unique($extra_group_by);
            $extra_group_by = ',' . implode(',', $extra_group_by);
        } else {
            $extra_group_by = '';
        }

        $DB->query("SELECT SUM(report_value) as `value`, report_key, FROM_UNIXTIME(added_timestamp, '{$date_format}') as `date_key`{$extra_group_by} FROM `osc_report_record_new" . (is_array($ab_test) ? '_ab' : '') . "` WHERE {$condition['condition']} GROUP BY report_key, `date_key`{$extra_group_by} ORDER BY `date_key` ASC", $condition['params'], 'fetch_report');

        $group = [];

        foreach ($keys as $data_key) {
            $group[$data_key] = [];
        }

        $rows = array_merge($rows, $DB->fetchArrayAll('fetch_report'));

        return ['group' => $group, 'rows' => $rows];
    }

    public function getProductListData($begin_date, $end_date, $current_page) {
        $timestamp_range = OSC::helper('report/common')->getTimestampRange($begin_date, $end_date);

        $date_condition = [
            'o.order_status != "cancelled"',
            'i.order_id = o.order_id',
            'i.additional_data NOT LIKE \'%"resend":{"resend":"%\'',
            'i.shop_id = '. OSC::getShop()->getId()
        ];

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

        $DB_MASTER->query("SELECT COUNT(DISTINCT product_id) as total FROM osc_catalog_order_item i, osc_catalog_order o {$date_condition}", null, 'fetch_report_record');

        $total_rows = $DB_MASTER->fetch('fetch_report_record')->total;

        if ($total_rows < 1) {
            throw new Exception('No data');
        }

        $page_size = 15;
        $current_page = min(max(1, intval($current_page)), ceil($total_rows / $page_size));

        $offset = ($current_page - 1) * $page_size;

        $DB_MASTER->query("SELECT product_id, SUM(price * quantity)/100 AS revenue, SUM(quantity * IF(other_quantity > 0, other_quantity, 1)) AS sales, COUNT(DISTINCT (o.order_id)) AS orders FROM osc_catalog_order_item i, osc_catalog_order o {$date_condition} GROUP BY product_id ORDER BY revenue DESC LIMIT {$offset}, {$page_size}", null, 'fetch_report_record');

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

        $keys = [
            'catalog/item/visit' => 'visits',
            'catalog/item/unique_visitor' => 'unique_visitors'
        ];

        $report_keys = array_keys($keys);

        $_data = $this->_getReportProductData($report_keys, '%Y', $timestamp_range, array_keys($data));

        foreach ($_data as $row) {
            if (!isset($data[$row['product_id']])) {
                continue;
            }

            if (!isset($data[$row['product_id']][$keys[$row['report_key']]])) {
                $data[$row['product_id']][$keys[$row['report_key']]] = 0;
            }

            $data[$row['product_id']][$keys[$row['report_key']]] += $row['value'];
        }

        return [
            'rows' => $data,
            'current_page' => $current_page,
            'page_size' => $page_size,
            'total_rows' => $total_rows
        ];
    }

    public function getProductDetailData($product_id, $begin_date, $end_date) {
        $timestamp_range = OSC::helper('report/common')->getTimestampRange($begin_date, $end_date);

        $condition = [
            'o.order_status != "cancelled"',
            'i.product_id=' . $product_id,
            'i.order_id = o.order_id',
            'i.additional_data NOT LIKE \'%"resend":{"resend":"%\'',
            'i.shop_id = '. OSC::getShop()->getId()
        ];

        if ($timestamp_range[0] > 0) {
            $condition[] = 'i.added_timestamp >= ' . $timestamp_range[0];
        }

        if ($timestamp_range[1] > 0) {
            $condition[] = 'i.added_timestamp <= ' . $timestamp_range[1];
        }

        $condition = implode(' AND ', $condition);

        /* @var $DB OSC_Database */

        $DB_MASTER = OSC::core('database')->getAdapter('db_master_read');

        $DB_MASTER->query("SELECT variant_id, sku, title, options, SUM(price * quantity)/100 AS revenue,SUM(quantity * IF(other_quantity > 0, other_quantity, 1)) AS sales, COUNT(DISTINCT (i.order_id)) AS orders FROM osc_catalog_order_item i, osc_catalog_order o WHERE {$condition} GROUP BY variant_id ORDER BY revenue DESC", null, 'fetch_report_record');

        $variants = [];

        $orders = 0;
        $sales = 0;
        $visits = 0;
        $unique_visitors = 0;
        $revenue = 0;
        $product_title = 0;

        while ($row = $DB_MASTER->fetchArray('fetch_report_record')) {
            $orders += intval($row['orders']);
            $sales += intval($row['sales']);
            $revenue += floatval($row['revenue']);

            if (!$product_title) {
                $product_title = $row['title'];
            }

            if (!isset($variants[$row['variant_id']])) {
                $row['options'] = OSC::decode($row['options']);

                $title = $product_title;

                if (count($row['options']) > 0) {
                    foreach ($row['options'] as $idx => $option) {
                        $row['options'][$idx] = $option['value'];
                    }

                    $title .= ' - ' . implode(' / ', $row['options']);
                }

                $variants[$row['variant_id']] = [
                    'title' => $title,
                    'revenue' => 0,
                    'sales' => 0,
                    'sku' => $row['sku']
                ];
            }

            $variants[$row['variant_id']]['revenue'] += floatval($row['revenue']);
            $variants[$row['variant_id']]['sales'] += intval($row['sales']);
        }

        if (count($variants) < 1) {
            throw new Exception('No data');
        }

        $referers = [];

        $condition = [
            'o.order_id = i.order_id',
            'o.order_status != "cancelled"',
            'i.additional_data NOT LIKE \'%"resend":{"resend":"%\'',
            'i.product_id=' . $product_id,
            'i.shop_id = '. OSC::getShop()->getId()
        ];

        if ($timestamp_range[0] > 0) {
            $condition[] = 'o.added_timestamp >= ' . $timestamp_range[0];
        }

        if ($timestamp_range[1] > 0) {
            $condition[] = 'o.added_timestamp <= ' . $timestamp_range[1];
        }

        $condition = implode(' AND ', $condition);

        $DB_MASTER->query("SELECT SUM(i.price * i.quantity)/100 AS revenue, SUM(i.quantity * IF(i.other_quantity > 0, i.other_quantity, 1)) AS sales, COUNT(DISTINCT (o.order_id)) AS orders, o.client_referer AS referer FROM osc_catalog_order_item i, osc_catalog_order o WHERE {$condition} GROUP BY referer ORDER BY revenue DESC", null, 'fetch_report_record');

        while ($row = $DB_MASTER->fetchArray('fetch_report_record')) {
            if (!$row['referer']) {
                $row['referer'] = 'direct';
            }

            if (!isset($referers[$row['referer']])) {
                $referers[$row['referer']] = [
                    'referer' => $row['referer'],
                    'orders' => 0,
                    'sales' => 0,
                    'revenue' => 0,
                    'visits' => 0,
                    'unique_visitors' => 0
                ];
            }

            $referers[$row['referer']]['orders'] += $row['orders'];
            $referers[$row['referer']]['sales'] += $row['sales'];
            $referers[$row['referer']]['revenue'] += $row['revenue'];
        }

        $keys = [
            'catalog/item/visit' => 'visits',
            'catalog/item/unique_visitor' => 'unique_visitors'
        ];

        $report_keys = array_keys($keys);

        $data = $this->_getReportProductData($report_keys, '%Y', $timestamp_range, $product_id, null, true);

        foreach ($data as $row) {
            if ($row['report_key'] == 'catalog/item/visit') {
                $visits += intval($row['value']);
            } else if ($row['report_key'] == 'catalog/item/unique_visitor') {
                $unique_visitors += intval($row['value']);
            }

            if (!isset($referers[$row['referer']])) {
                continue;
            }

            $referers[$row['referer']][$keys[$row['report_key']]] += $row['value'];
        }

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

    protected $_list_mrk_members = null;

    public function getListMemberMkt()
    {
        if ($this->_list_mrk_members === null) {
            $member_collection = OSC::model('user/member')->getCollection()
                ->setCondition('has_order = ' . Model_User_Member::SREF_HAS_ORDER)
                ->load();
            if ($member_collection->length() > 0) {
                $member_parse_arr = [];

                foreach ($member_collection as $member) {
                    $member_parse_arr[$member->getId()] = $member;
                }

                $this->_list_mrk_members = $member_parse_arr;
            }
        }
        return $this->_list_mrk_members;
    }

    public function getListMemberActiveAnalytic($data = []) {
        $member = OSC::helper('user/authentication')->getMember();
        $list_members = [];

        if ($member->isAdmin() || (isset($data['product_page']) && $data['product_page'] == 1)) {
            $list_members = $this->getListMemberMkt();
        } else {
            if ($this->getListMemberMkt()[$member->data['member_id']] != null) {
                $list_members[$member->data['member_id']] = $member;
            }

            $perm_analytic_table_name = OSC::model('user/permissionAnalytic')->getTableName(true);
            $DB = OSC::core('database');

            $DB->query("SELECT member_mkt_ids FROM {$perm_analytic_table_name} WHERE member_id=:member_id", ['member_id' => $member->getId()], 'fetch_member_mkt_ids');

            $arr_member_ids = [];
            while ($row = $DB->fetchArray('fetch_member_mkt_ids')) {
                $arr_member_ids[] = $row['member_mkt_ids'];
            }

            $member_ids = array_unique(explode(',', implode(',', $arr_member_ids)));

            OSC_Database_Model::preLoadModelData('user/member', array_values($member_ids));

            foreach ($member_ids as $member_id) {
                $member_mkt = OSC_Database_Model::getPreLoadedModel('user/member', $member_id);
                if ($member_mkt instanceof Model_User_Member) {
                    $list_members[$member_mkt->data['member_id']] = $member_mkt;
                }
            }

            $members_group = OSC::helper('adminGroup/common')->getMembersGroup($member->getId());
            if (is_array($members_group) && count($members_group) > 0) {
                foreach ($members_group as $member_group) {
                    $list_members[$member_group->getId()] = $member_group;
                }
            }
        }

        return $list_members;
    }

    public function getMarketingPermId()
    {
        $marketing_permission_masks = OSC::model('user/permissionMask')->getCollection()->addField('perm_mask_id')->addCondition('title', Model_User_PermissionMask::PERMISSION_MARKETING_TITLE)->setLimit(1)->load();
        return $marketing_permission_masks->getItem() ? $marketing_permission_masks->getItem()->getId() : 0;
    }

    protected function _getPinelinePagination($current_page, $page_size) {
        return [
            [
                '$group' =>  [
                    '_id' => null,
                    'total' => [ '$sum' => 1 ],
                    'total_view' => ['$sum' => '$product_view_count'],
                    'total_add_to_cart' => ['$sum' => '$add_to_cart_count'],
                    'total_checkout_initialize_count' => ['$sum' => '$checkout_initialize_count'],
                    'total_purchase_count' => ['$sum' => '$purchase_count'],
                    'total_sale_count' => ['$sum' => '$sale_count'],
                    'total_revenue' => ['$sum' => '$revenue'],
                    'total_subtotal_revenue' => ['$sum' => '$subtotal_revenue'],
                    'results' => [
                        '$push' => '$$ROOT'
                    ]
                ]
            ],
            [
                '$project' => [
                    'total' => [
                        'total_record' => '$total',
                        'total_view' => '$total_view',
                        'total_add_to_cart' => '$total_add_to_cart',
                        'total_checkout_initialize_count' => '$total_checkout_initialize_count',
                        'total_purchase_count' => '$total_purchase_count',
                        'total_sale_count' => '$total_sale_count',
                        'total_revenue' => '$total_revenue',
                        'total_subtotal_revenue' => '$total_subtotal_revenue',
                    ],
                    'results' => [
                        '$slice' => [
                            '$results',
                            ($current_page - 1) * $page_size,
                            $page_size
                        ]
                    ]
                ]
            ]
        ];
    }

    protected function _parseDateRangeFilter(string $date_range) {
        $title = 'Today';
        $begin_date = $end_date = date('d/m/Y');
        if ($date_range == 'yesterday') {
            $title = 'Yesterday';
            $begin_date = $end_date = date('d/m/Y', strtotime('-1 day'));
        } else if ($date_range == 'thisweek') {
            $title = 'This week';
            $begin_date = date('d/m/Y', strtotime('-' . date('w') . ' days'));
            $end_date = null;
        } else if ($date_range == 'lastweek') {
            $title = 'Last week';
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('w')) + 7) . ' days'));
            $end_date = date('d/m/Y', strtotime('-' . (intval(date('w')) + 1) . ' days'));
        } else if ($date_range == 'thismonth') {
            $title = 'This month';
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('j')) - 1) . ' day'));
            $end_date = null;
        } else if ($date_range == 'lastmonth') {
            $title = 'Last month';
            $end_date = strtotime('-' . date('j') . ' day');
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('j', $end_date)) - 1) . ' day', $end_date));
            $end_date = date('d/m/Y', $end_date);
        } else if ($date_range == 'alltime') {
            $title = 'All time';
            $begin_date = $end_date = null;
        } else if (preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $date_range, $matches)) {
            for ($i = 1; $i <= 7; $i ++) {
                if ($i == 4) {
                    continue;
                }

                $matches[$i] = intval($matches[$i]);
            }

            if (!checkdate($matches[2], $matches[1], $matches[3]) || ($matches[5] && !checkdate($matches[6], $matches[5], $matches[7]))) {
                $date_range = null;
            } else {
                $compare_start = intval(str_pad($matches[3], 4, 0, STR_PAD_LEFT) . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . str_pad($matches[1], 2, 0, STR_PAD_LEFT));

                if ($matches[5]) {
                    $compare_end = intval(str_pad($matches[7], 4, 0, STR_PAD_LEFT) . str_pad($matches[6], 2, 0, STR_PAD_LEFT) . str_pad($matches[5], 2, 0, STR_PAD_LEFT));

                    if ($compare_start > $compare_end) {
                        $buff = $compare_end;
                        $compare_end = $compare_start;
                        $compare_start = $buff;
                    }

                    $begin_date = str_pad($matches[1], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[3], 4, 0, STR_PAD_LEFT);
                    $end_date = str_pad($matches[5], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[6], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[7], 4, 0, STR_PAD_LEFT);
                } else {
                    $begin_date = $end_date = str_pad($matches[1], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . '/' . str_pad($matches[3], 4, 0, STR_PAD_LEFT);
                }

                if ($begin_date == $end_date) {
                    $title = $begin_date;
                } else {
                    $title = 'From ' . $begin_date . ' to ' . $end_date;
                }

                $date_range = [$begin_date, $end_date];
            }
        } else {
            $date_range = null;
        }

        if (!$date_range || $date_range == 'today') {
            $date_range = 'today';
        }

        return [
            'date_range' => $date_range,
            'title' => $title,
            'begin_date' => $begin_date,
            'end_date' => $end_date
        ];
    }

    public function fetchAdTrackingData(string $data_type, array $params, int $page, array $sref_ids, array $sort) {
        $page_size = 15;
        $data_range = $this->_parseDateRangeFilter($params['date_range']);

        $timestamp_range = OSC::helper('report/common')->getTimestampRange($data_range['begin_date'], $data_range['end_date']);

        $match_added_timestamp = ['$gte' => $timestamp_range[0]];

        if ($timestamp_range[1] > 0) {
            $match_added_timestamp['$lte'] = $timestamp_range[1];
        }

        $pineline = [];
        $match = [
            'added_timestamp' => $match_added_timestamp
        ];

        if (count($sref_ids) > 0) {
            $match['sref_id'] = [ '$in' => $sref_ids];
        }

        $group = [
            'sref_id'=> ['$first'=> '$sref_id'],
            'product_view_count'=> ['$sum'=> '$product_view'],
            'add_to_cart_count'=> ['$sum'=> '$add_to_cart'],
            'checkout_initialize_count'=> ['$sum'=> '$checkout_initialize'],
            'purchase_count'=> ['$sum'=> '$purchase'],
            'sale_count'=> ['$sum'=> '$quantity'],
            'revenue'=> ['$sum'=> '$revenue'],
            'subtotal_revenue'=> ['$sum'=> '$subtotal_revenue'],
            'added_timestamp' => ['$max'=> '$added_timestamp']
        ];

        $pineline_sort = ['$sort' => ['added_timestamp' => -1]];
        if (count($sort) > 0) {
            $pineline_sort = ['$sort' => [$sort['key'] => $sort['order']]];
        }

        switch ($data_type) {
            case 'campaign':
                $group['_id'] = '$campaign_id';
                $group['campaign_id'] = ['$first'=> '$campaign_id'];
                $group['utm_campaign'] = ['$max'=> '$utm_campaign'];
                break;
            case 'adsets':
                $group['_id'] = '$adset_id';
                $group['adset_id'] = ['$first'=> '$adset_id'];
                $group['adset_name'] = ['$max'=> '$adset_name'];
                break;
            case 'ads':
                $adsets_filter = isset($params['adset_ids']) ? $params['adset_ids'] : [];
                if (count($adsets_filter) > 0) {
                    $match['adset_id'] = [ '$in' => $adsets_filter];
                }

                $group['_id'] = '$ad_id';
                $group['ad_id'] = ['$first'=> '$ad_id'];
                $group['ad_name'] = [ '$max'=> '$ad_name' ];
                break;
        }

        $match_filter = [];
        $campaign_filter = isset($params['campaign_ids']) ? $params['campaign_ids'] : [];
        if (count($campaign_filter) > 0) {
            $match['campaign_id'] = [ '$in' => $campaign_filter];
        } else {
            $apply_search = isset($params['apply_search']) ? $params['apply_search'] : [];
            if (count($apply_search) > 0) {
                if (isset($apply_search['keyword']['$match'])) {
                    foreach ($apply_search['keyword']['$match'] as $key => $value) {
                        $match[$key] = $value;
                    }
                }
                if (isset($apply_search['filter'])) {
                    foreach ($apply_search['filter']['$match'] as $key => $value) {
                        $match_filter[$key] = $value;
                    }
                }
            }
        }

        $mongodb = OSC::core('mongodb');
        $pineline[] = ['$match'  =>  $match];
        $pineline[] = ['$group' =>  $group];

        if (count($match_filter) > 0) {
            $pineline[] = ['$match'  =>  $match_filter];
        }

        if (count($pineline_sort) > 0) {
            $pineline[] = $pineline_sort;
        }

        $pineline = array_merge($pineline, $this->_getPinelinePagination($page, $page_size));

        $result = $mongodb->selectCollection('ads_tracking_analytic', 'report')->aggregate($pineline, ['typeMap' => ['root' => 'array', 'document' => 'array']])->toArray()[0];

        $data = [];
        $total = [];
        if (count($result['results']) > 0) {
            foreach ($result['results'] as $key => $value) {
                $data[$key] = $value;
                $data[$key]['revenue_format'] = OSC::helper('catalog/common')->formatPriceByInteger($value['revenue']);
                $data[$key]['subtotal_revenue_format'] = OSC::helper('catalog/common')->formatPriceByInteger($value['subtotal_revenue']);
            }

            $result['total']['total_subtotal_revenue_format'] = OSC::helper('catalog/common')->formatPriceByInteger($result['total']['total_subtotal_revenue']);
            $result['total']['total_revenue_format'] = OSC::helper('catalog/common')->formatPriceByInteger($result['total']['total_revenue']);
            $total = $result['total'];
        }

        return [
            'range' => $data_range['date_range'],
            'title' => $data_range['title'],
            'data' => $data,
            'total' => $total,
            'current_page' => $page,
            'page_size' => $page_size
        ];
    }
}
