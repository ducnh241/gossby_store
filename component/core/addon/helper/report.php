<?php

class Helper_Addon_Report extends OSC_Object
{
    public function fetchTrackingListData($date_range)
    {
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
            for ($i = 1; $i <= 7; $i++) {
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

        return [
            'time' => OSC::helper('report/common')->getTimestampRange($begin_date, $end_date),
            'range' => $date_range
        ];
    }

    /**
     * @throws Exception
     */
    public function getAddonReportData($addon_id, $date_range)
    {
        try {
            $timestamp_range = $this->fetchTrackingListData($date_range);
            $condition = ['addon_id = ' . $addon_id];

            if ($timestamp_range['time'][0] > 0) {
                $condition[] = 'added_timestamp >= ' . $timestamp_range['time'][0];
            }

            if ($timestamp_range['time'][1] > 0) {
                $condition[] = 'added_timestamp <= ' . $timestamp_range['time'][1];
            }

            $condition = implode(' AND ', $condition);

            $addon_service = OSC::model('addon/service')->load($addon_id);
            $addon_service_versions = OSC::model('addon/version')->getCollection()->addCondition('addon_id', $addon_id)->load();
            $addon_version_data = [];
            foreach ($addon_service_versions as $addon_service_version) {
                $addon_version_data[$addon_service_version->getId()]['title'] = $addon_service_version->data['title'];
                $addon_version_data[$addon_service_version->getId()]['addon_id'] = $addon_service_version->data['addon_id'];
            }

            /* @var $DB OSC_Database */
            $DB = OSC::core('database');
            $addon_report_model = OSC::model('addon/report');
            $query = <<<EOF
SELECT addon_version_id, version_name, addon_id,
       MAX(distributed) as distributed, 
       SUM(approached_unique) AS 'approached_unique', 
       SUM(approached) AS 'approached', 
       SUM(total_order) AS 'total_order', 
       SUM(total_sale) AS 'total_sale', 
       SUM(revenue) AS 'revenue' 
FROM `{$addon_report_model->getTableName(true)}` 
WHERE {$condition} GROUP BY `addon_version_id`
ORDER BY revenue DESC, addon_version_id
EOF;
            $DB->query($query, null, 'fetch_addon_report');
            $addon_report_rows = $DB->fetchArrayAll('fetch_addon_report');

            $addon_report_data = [];
            foreach ($addon_report_rows as $addon_report) {
                $addon_report_data[$addon_report['addon_version_id']] = $addon_report;
                if (!isset($addon_version_data[$addon_report['addon_version_id']]['title'])) {
                    $addon_version_data[$addon_report['addon_version_id']]['title'] = $addon_report['version_name'] . ' (deleted)';
                    $addon_version_data[$addon_report['addon_version_id']]['addon_id'] = $addon_report['addon_id'];
                }
            }

            $addon_report_order_model = OSC::model('addon/report_order');
            $query_report_order = <<<EOF
SELECT addon_version_id, COUNT(DISTINCT order_id) AS 'total_order', 
SUM(sale) AS 'total_sale', 
SUM(revenue) AS 'revenue' 
FROM `{$addon_report_order_model->getTableName(true)}` 
WHERE {$condition} GROUP BY `addon_version_id`
ORDER BY revenue DESC, addon_version_id
EOF;

            $DB->query($query_report_order, null, 'fetch_addon_report_order');
            $addon_report_order_fetch = $DB->fetchArrayAll('fetch_addon_report_order');

            $addon_report_order_data = [];
            foreach ($addon_report_order_fetch as $addon_report_order) {
                $addon_report_order_data[$addon_report_order['addon_version_id']] = $addon_report_order;
            }

            return [
                'addon_service' => $addon_service,
                'addon_report_data' => $addon_report_data,
                'addon_version_data' => $addon_version_data,
                'addon_report_order_data' => $addon_report_order_data
            ];
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function updateReportAddon($page_type = 'cart', $product_id = 0, $product_type_id = 0, $product_type_variant_id = 0)
    {
        /* @var $product Model_Catalog_Product */
        /* @var $line_item Model_Catalog_Cart_Item */

        $addon_service_id = 0;
        $addon_version_id = 0;

        $cookie_key_ab_test_addon = OSC::helper('addon/service')->getABTestAddonKey();
        $ab_test_addon = OSC::cookieGet($cookie_key_ab_test_addon) ? OSC::decode(OSC::cookieGet($cookie_key_ab_test_addon)) : [];

        if (empty($ab_test_addon)) {
            return;
        }

        try {
            if ($page_type == 'product_detail') {
                if (!$product_id) {
                    return;
                }
                $product = OSC::model('catalog/product')->load($product_id);
                $product_type_ids = [$product_type_id];
                $product_type_variant_ids = [$product_type_variant_id];
                $addon_services = OSC::helper('addon/service')->getAddonServices($product, $product_type_ids, $product_type_variant_ids);

                $approached = 1;
                $approached_unique = 1;
                foreach ($addon_services['data_addon_service'] as $addon_service) {

                    if (!$addon_service['is_running_ab_test']) {
                        continue;
                    }

                    if (!$addon_service['show_in_detail'] || $addon_service['is_hide']) {
                        $approached = 0;
                        $approached_unique = 0;
                    }

                    $apply_for_product_type_variants = $addon_service['apply_for_product_type_variants'] ?? null;

                    if (!$this->verifyAddonBelongToProduct($apply_for_product_type_variants, $product_type_id, $product_type_variant_id)) {
                        continue;
                    }

                    $addon_service_id = $addon_service['id'] ?? 0;
                    $addon_version_id = $addon_service['version_id'] ?? 0;
                }

                if ($addon_service_id && $addon_version_id) {
                    $this->saveData($ab_test_addon, $addon_service_id, $addon_version_id, $approached_unique, $approached);
                }
            } elseif ($page_type == 'cart') {
                $cart = OSC::helper('catalog/common')->getCart(false);

                if (!$cart instanceof Model_Catalog_Cart) {
                    return;
                }

                $approached = 1;
                $approached_unique = 1;
                foreach ($cart->getLineItems() as $line_item) {
                    $addon_services = $line_item->getAddonServices();
                    if (!empty($addon_services['configs']['data_addon_service'])) {
                        foreach ($addon_services['configs']['data_addon_service'] as $addon_service) {
                            if (!$addon_service['is_running_ab_test']) {
                                continue;
                            }

                            if ($addon_service['is_hide']) {
                                $approached = 0;
                                $approached_unique = 0;
                            }

                            $apply_for_product_type_variants = $addon_service['apply_for_product_type_variants'] ?? null;

                            $product_type_id = 0;
                            $product_type_variant_id = 0;
                            $product_type_variant = $line_item->getProductTypeVariant();

                            if ($product_type_variant instanceof Model_Catalog_ProductType_Variant) {
                                $product_type_id = $product_type_variant->data['product_type_id'];
                                $product_type_variant_id = $product_type_variant->data['id'];
                            }

                            if (!$this->verifyAddonBelongToProduct($apply_for_product_type_variants, $product_type_id, $product_type_variant_id)) {
                                continue;
                            }

                            $addon_service_id = $addon_service['id'] ?? 0;
                            $addon_version_id = $addon_service['version_id'] ?? 0;
                            if ($addon_service_id && $addon_version_id) {
                                $this->saveData($ab_test_addon, $addon_service_id, $addon_version_id, $approached_unique, $approached);
                            }
                        }
                    }
                }
            }
        } catch (Exception $ex) {

        }
    }

    public function verifyAddonBelongToProduct($apply_for_product_type_variants, $product_type_id, $product_type_variant_id)
    {
        if ($apply_for_product_type_variants === null || (!$product_type_id && !$product_type_variant_id)) {
            return true;//Sp ban thu se set manual
        }
        if (in_array('*', $apply_for_product_type_variants)) {
            return true;
        }
        if (in_array($product_type_id, array_keys($apply_for_product_type_variants))) {
            if (in_array('*', $apply_for_product_type_variants[$product_type_id]) ||
                in_array($product_type_variant_id, $apply_for_product_type_variants[$product_type_id])) {
                return true;
            }
        }
        return false;
    }

    public function saveData($ab_test_addon, $addon_service_id, $addon_version_id, $approached_unique = 1, $approached = 1)
    {
        $track_ukey = Abstract_Frontend_Controller::getTrackingKey();
        $date_current = date('Ymd');
        //$addon_version_id_cookie = $ab_test_addon[$addon_service_id]['addon_version_id'] ?? $addon_version_id;
        if (empty($ab_test_addon || !$addon_service_id || !$addon_version_id)) {
            return;
        }

        $addon_version = OSC_Database_Model::getPreLoadedModel('addon/version', $addon_version_id);
        if (!$addon_version || $addon_version->getId() < 1) {
            return;
        }

        $report_data = [
            'distributed' => 0,
            'approached_unique' => 0,
            'approached' => 0,
            'addon_id' => $addon_service_id,
            'addon_version_id' => $addon_version->getId(),
            'version_name' => $addon_version->data['title'],
            'date' => $date_current
        ];

        $addon_service_report = OSC::model('addon/report')->getCollection()
            ->addCondition('addon_id', $addon_service_id)
            ->addCondition('addon_version_id', $addon_version->getId())
            ->addCondition('date', $date_current)
            ->load()
            ->first();

        if ($addon_service_report) {
            $report_data['distributed'] = $addon_service_report->data['distributed'];
            $report_data['approached_unique'] = $addon_service_report->data['approached_unique'];
            $report_data['approached'] = $addon_service_report->data['approached'];
        }

        try {
            OSC::model('addon/report_view')->setData([
                'track_ukey' => $track_ukey,
                'addon_id' => $addon_service_id,
                'addon_version_id' => $addon_version->getId(),
                'date' => $date_current,
                'increment_unique' => $approached_unique
            ])->save();

            // increment 1 unique and page view because agent have view product has addon
            $report_data['distributed'] += 1;
            $report_data['approached_unique'] += $approached_unique;
            $report_data['approached'] += $approached;

            $addon_version->increment('traffic');

        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                // if had viewed product has AB test Addon in date then increment approached not approached_unique
                $report_view = OSC::model('addon/report_view')->getCollection()
                    ->addCondition('track_ukey', $track_ukey)
                    ->addCondition('addon_id', $addon_service_id)
                    ->addCondition('addon_version_id', $addon_version->getId())
                    ->addCondition('date', $date_current)
                    ->load()
                    ->first();

                if ($report_view->data['increment_unique']) {
                    $approached_unique = 0;
                }

                if ($approached_unique && $report_view instanceof Model_Addon_Report_View) {
                    $report_view->setData(['increment_unique' => 1])->save();
                }

                $report_data['approached_unique'] += $approached_unique;
                $report_data['approached'] += $approached;
            }
        }

        $addon_report_model = $addon_service_report ?? OSC::model('addon/report');
        $addon_report_model->setData($report_data)->save();
    }
}
