<?php

class Helper_Report_Process {

    protected function _fetchABTestKeyList($begin_date, $end_date) {
        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $condition = [];

        $timestamp_range = OSC::helper('report/common')->getTimestampRange($begin_date, $end_date);

        if ($timestamp_range[0] > 0) {
            $condition[] = 'added_timestamp >= ' . $timestamp_range[0];
        }

        if ($timestamp_range[1] > 0) {
            $condition[] = 'added_timestamp <= ' . $timestamp_range[1];
        }

        $condition = implode(' AND ', $condition);

        $DB->query("SELECT DISTINCT ab_key, ab_value FROM `osc_report_record_new_ab`" . ($condition ? (' WHERE ' . $condition) : ''), null, 'fetch_report_ab_test');

        $ab_test_key = [];

        while ($row = $DB->fetchArray('fetch_report_ab_test')) {
            if (!isset($ab_test_key[$row['ab_key']])) {
                $ab_test_key[$row['ab_key']] = [];
            }

            $ab_test_key[$row['ab_key']][] = $row['ab_value'];
        }

        return $ab_test_key;
    }

    public function fetchDashboardData($date_range, $ab_test = null) {
        $online = 0;

        if ($date_range == 'yesterday') {
            $title = 'Yesterday';
            $begin_date = $end_date = date('d/m/Y', strtotime('-1 day'));
            $caller = 'getReportDataByHour';
        } else if ($date_range == 'thisweek') {
            $title = 'This week';
            $begin_date = date('d/m/Y', strtotime('-' . date('w') . ' days'));
            $end_date = null;
            $caller = 'getReportDataByDate';
        } else if ($date_range == 'lastweek') {
            $title = 'Last week';
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('w')) + 7) . ' days'));
            $end_date = date('d/m/Y', strtotime('-' . (intval(date('w')) + 1) . ' days'));
            $caller = 'getReportDataByDate';
        } else if ($date_range == 'thismonth') {
            $title = 'This month';
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('j')) - 1) . ' day'));
            $end_date = null;
            $caller = 'getReportDataByDate';
        } else if ($date_range == 'lastmonth') {
            $title = 'Last month';
            $end_date = strtotime('-' . date('j') . ' day');
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('j', $end_date)) - 1) . ' day', $end_date));
            $end_date = date('d/m/Y', $end_date);

            $caller = 'getReportDataByDate';
        } else if ($date_range == 'alltime') {
            $title = 'All time';
            $begin_date = $end_date = null;
            $caller = 'getReportDataByMonth';
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

                    $caller = 'getReportDataByHour';
                } else {
                    $title = 'From ' . $begin_date . ' to ' . $end_date;

                    $bd = new DateTime(substr($begin_date, 3, 2) . '/' . substr($begin_date, 0, 2) . '/' . substr($begin_date, 6, 4));
                    $ed = new DateTime(substr($end_date, 3, 2) . '/' . substr($end_date, 0, 2) . '/' . substr($end_date, 6, 4));

                    if ($bd->diff($ed)->days > 365 * 2) {
                        $caller = 'getReportDataByYear';
                    } else if ($bd->diff($ed)->days > 31) {
                        $caller = 'getReportDataByMonth';
                    } else {
                        $caller = 'getReportDataByDate';
                    }
                }

                $date_range = [$begin_date, $end_date];
            }
        } else {
            $date_range = null;
        }

        if (!$date_range || $date_range == 'today') {
            $date_range = 'today';
            $title = 'Today';
            $begin_date = $end_date = date('d/m/Y');
            $online = 0;//OSC::model('frontend/tracking')->getCollection()->addCondition('modified_timestamp', time() - (60 * 2), OSC_Database::OPERATOR_GREATER_THAN)->collectionLength();
            $caller = 'getReportDataByHour';
        }

        $ab_test_keys = $this->_fetchABTestKeyList($begin_date, $end_date);

        if (is_array($ab_test)) {
            if (!isset($ab_test_keys[$ab_test['key']])) {
                $ab_test = null;
            } else if (!in_array($ab_test['value'], $ab_test_keys[$ab_test['key']])) {
                $ab_test = null;
            }
        }

        $data = OSC::helper('report/common')->$caller([
            'catalog/order' => 'orders',
            'catalog/order_tax' => 'tax_price',
            'catalog/item_solds' => 'sales',
            'pageview' => 'pageviews',
            'catalog/revenue' => 'revenue',
            'catalog/refunded_price' => 'refunded_price',
            'catalog/refunded_tax_price' => 'refunded_tax_price',
            'catalog/tip_price' => 'tip_price',
            'visit' => 'visits',
            'unique_visitor' => 'unique_visitors',
            'new_visitor' => 'new_visitors',
            'catalog/item/unique_visitor' => 'product_unique_visitors',
            'catalog/add_to_cart' => 'add_to_cart',
            'catalog/checkout_initialize' => 'checkout_initialize'
                ], true, $begin_date, $end_date, null, null, $ab_test);

        $data['conversion_rate'] = [];

        foreach ($data['unique_visitors'] as $idx => $record) {
            $data['conversion_rate'][$idx] = [
                'label' => $record['label'],
                'short_label' => $record['short_label'],
                'value' => number_format((isset($data['orders'][$idx]) ? $data['orders'][$idx]['value'] : 0) / $record['value'] * 100, 2)
            ];
        }

        return [
            'ab_test_keys' => ['keys' => $ab_test_keys, 'current' => $ab_test],
            'range' => $date_range,
            'title' => $title,
            'online' => $online,
            'data' => $data
        ];
    }

    public function fetchProductListData($date_range, $page) {
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
            $title = 'Today';
            $begin_date = $end_date = date('d/m/Y');
        }

        try {
            $data = OSC::helper('report/common')->getProductListData($begin_date, $end_date, $page);

            /* @var $DB OSC_Database */
            $DB = OSC::core('database');

            $DB->select('product_id,sku,slug,title,topic', 'catalog/product', 'product_id IN (' . implode(',', array_keys($data['rows'])) . ')', null, count($data['rows']), 'fetch_product_title');

            while ($row = $DB->fetchArray('fetch_product_title')) {
                $data['rows'][$row['product_id']]['title'] = $row['topic'] ? $row['topic'] . ' - ' . $row['title'] : $row['title'];
                $data['rows'][$row['product_id']]['url'] = OSC_FRONTEND_BASE_URL . '/' . $row['sku'] . '/' . $row['slug'];
            }
        } catch (Exception $ex) {}

        return [
            'ab_test_keys' => $this->_fetchABTestKeyList($begin_date, $end_date),
            'range' => $date_range,
            'title' => $title,
            'data' => $data['rows'],
            'current_page' => $data['current_page'],
            'page_size' => $data['page_size'],
            'total_rows' => $data['total_rows']
        ];
    }

    public function fetchProductDetailData($product_id, $date_range) {
        $product_id = intval($product_id);

        if ($product_id < 1) {
            return null;
        }

        if ($date_range == 'yesterday') {
            $title = 'Yesterday';
            $begin_date = $end_date = date('d/m/Y', strtotime('-1 day'));
            $caller = 'getReportDataByHour';
        } else if ($date_range == 'thisweek') {
            $title = 'This week';
            $begin_date = date('d/m/Y', strtotime('-' . date('w') . ' days'));
            $end_date = null;
            $caller = 'getReportDataByDate';
        } else if ($date_range == 'lastweek') {
            $title = 'Last week';
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('w')) + 7) . ' days'));
            $end_date = date('d/m/Y', strtotime('-' . (intval(date('w')) + 1) . ' days'));
            $caller = 'getReportDataByDate';
        } else if ($date_range == 'thismonth') {
            $title = 'This month';
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('j')) - 1) . ' day'));
            $end_date = null;
            $caller = 'getReportDataByDate';
        } else if ($date_range == 'lastmonth') {
            $title = 'Last month';
            $end_date = strtotime('-' . date('j') . ' day');
            $begin_date = date('d/m/Y', strtotime('-' . (intval(date('j', $end_date)) - 1) . ' day', $end_date));
            $end_date = date('d/m/Y', $end_date);

            $caller = 'getReportDataByDate';
        } else if ($date_range == 'alltime') {
            $title = 'All time';
            $begin_date = $end_date = null;
            $caller = 'getReportDataByMonth';
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

                    $caller = 'getReportDataByHour';
                } else {
                    $title = 'From ' . $begin_date . ' to ' . $end_date;

                    $bd = new DateTime(substr($begin_date, 3, 2) . '/' . substr($begin_date, 0, 2) . '/' . substr($begin_date, 6, 4));
                    $ed = new DateTime(substr($end_date, 3, 2) . '/' . substr($end_date, 0, 2) . '/' . substr($end_date, 6, 4));

                    if ($bd->diff($ed)->days > 365 * 2) {
                        $caller = 'getReportDataByYear';
                    } else if ($bd->diff($ed)->days > 31) {
                        $caller = 'getReportDataByMonth';
                    } else {
                        $caller = 'getReportDataByDate';
                    }
                }

                $date_range = [$begin_date, $end_date];
            }
        } else {
            $date_range = null;
        }

        if (!$date_range || $date_range == 'today') {
            $date_range = 'today';
            $title = 'Today';
            $begin_date = $end_date = date('d/m/Y');
            $caller = 'getReportDataByHour';
        }

        try {
            $data = OSC::helper('report/common')->getProductDetailData($product_id, $begin_date, $end_date);
        } catch (Exception $ex) {}

        return [
            'ab_test_keys' => $this->_fetchABTestKeyList($begin_date, $end_date),
            'product_id' => $product_id,
            'product_title' => $data['product_title'],
            'variants' => $data['variants'],
            'referers' => $data['referers'],
            'range' => $date_range,
            'title' => $title,
            'data' => [
                'revenue' => $data['revenue'],
                'orders' => $data['orders'],
                'sales' => $data['sales'],
                'visits' => $data['visits'],
                'unique_visitors' => $data['unique_visitors']
            ]
        ];
    }
}