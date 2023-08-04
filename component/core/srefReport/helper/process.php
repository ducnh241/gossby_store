<?php
class Helper_SrefReport_Process {
    public function fetchDashboardDataBySref($date_range, $sref_id = null) {
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

        $data = OSC::helper('srefReport/common')->$caller([
            'catalog/order' => 'orders',
            'catalog/order_tax' => 'tax_price',
            'catalog/item_solds' => 'sales',
            'catalog/revenue' => 'revenue',
            'catalog/refunded_price' => 'refunded_price',
            'catalog/refunded_tax_price' => 'refunded_tax_price',
            'catalog/tip_price' => 'tip_price',
            'marketing/point' => 'point',
            'marketing/vendor_point' => 'vendor_point',
        ], $begin_date, $end_date , $sref_id);


        return [
            'range' => $date_range,
            'title' => $title,
            'online' => $online,
            'data' => $data
        ];
    }

    public function fetchProductListDataBySerf($date_range, $page ,$sref_id = null) {
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
            $data = OSC::helper('srefReport/common')->getProductListDataBySref($begin_date, $end_date, $page , $sref_id);

            /* @var $DB OSC_Database */
            $DB = OSC::core('database');

            $DB->select('product_id, sku, slug, title, topic', 'catalog/product', 'product_id IN (' . implode(',', array_keys($data['rows'])) . ')', null, count($data['rows']), 'fetch_product_title');

            while ($row = $DB->fetchArray('fetch_product_title')) {
                $data['rows'][$row['product_id']]['title'] = $row['title'];
                $data['rows'][$row['product_id']]['topic'] = $row['topic'];
                $data['rows'][$row['product_id']]['url'] = OSC_FRONTEND_BASE_URL . '/' . $row['sku'] . '/' . $row['slug'];
            }
        } catch (Exception $ex) {

        }

        return [
            'range' => $date_range,
            'title' => $title,
            'data' => $data['rows'],
            'current_page' => $data['current_page'],
            'page_size' => $data['page_size'],
            'total_rows' => $data['total_rows']
        ];
    }

    public function fetchProductDetailDataBySref($product_id, $date_range, $member_ids = null) {
        $product_id = intval($product_id);

        if ($product_id < 1) {
            return null;
        }

        $title = null;
        $begin_date = $end_date = null;

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
            $data = OSC::helper('srefReport/common')->getProductDetailDataBySref($product_id, $begin_date, $end_date, $member_ids);
        } catch (Exception $exception) {}

        return [
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