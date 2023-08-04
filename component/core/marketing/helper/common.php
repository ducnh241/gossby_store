<?php

class Helper_Marketing_Common extends OSC_Object
{
    protected $_settings_point = null;

    /**
     * If $get_detail_info will return array of detail information, such as day_after_product_created, marketing_point..
     * else return marketing_point only
     * @param Model_Catalog_Order_Item $lineItem
     * @param bool $get_detail_info
     * @return float|int|int[]
     * @throws OSC_Database_Model_Exception
     */
    public function calculatePoint(Model_Catalog_Order_Item $lineItem, $get_detail_info = false)
    {
        $point_rate = [
            'sref' => 0,
            'vendor' => 0
        ];

        $result = [
            'marketing_point' => $point_rate,
            'day_after_product_created' => 0
        ];

        $productType = $lineItem->data['product_type'];
        $quantity = $lineItem->data['quantity'];

        $quantity_of_pack = $lineItem->data['other_quantity'];
        $marketing_point_pack_rate = $lineItem->getMarketingPointPackRate();

        try {
            $product = OSC::model('catalog/product')->load($lineItem->data['product_id']);
        } catch (Exception $e) {
            return $result;
        }

        $product_point = $this->getSettingPointByType($product->getId());

        $point_values = $product_point ?? [];

        if ($product->isCampaignMode()) {
            //Priority: Add Manual -> product_type -> collection
            if (empty($point_values)) {
                $point_values = $this->getSettingPointByType($productType, 'product_type');
                if (empty($point_values)) {
                    // Get by setting for virtual product_type: 'ALL_REAL_PRODUCTS'
                    $point_values = $this->getSettingPointByType('ALL_REAL_PRODUCTS', 'product_type');
                }
            }

            if (empty($point_values)) {
                $collection_ids = [];
                foreach ($product->getCollections() as $collection) {
                    $collection_ids[] = $collection->getId();
                }

                foreach ($collection_ids as $collection_id) {
                    $point_values = $this->getSettingPointByType($collection_id, 'collection');
                    if (!empty($point_values)) break; // get first result
                }
            }

        } elseif ($product->isSemitestMode()) {
            //Priority: Add Manual -> collection -> product_type = all_beta_product
            if (empty($point_values)) {
                $collection_ids = [];
                foreach ($product->getCollections() as $collection) {
                    $collection_ids[] = $collection->getId();
                }

                foreach ($collection_ids as $collection_id) {
                    $point_values = $this->getSettingPointByType($collection_id, 'collection');
                    if (!empty($point_values)) break; // get first result
                }
            }

            if (empty($point_values)) {
                // Get by setting for virtual product_type: 'ALL_BETA_PRODUCTS'
                $point_values = $this->getSettingPointByType('ALL_BETA_PRODUCTS', 'product_type');
            }
        }

        if (is_array($point_values['value']) && !empty($point_values['value'])) {
            //Calculate how many days after product created
            $days_after_product_created = ceil(($lineItem->data['added_timestamp'] - $product->data['added_timestamp']) / (60 * 60 * 24));
            $days_after_product_created = $days_after_product_created > 0 ? intval($days_after_product_created) : 0;
            $array_config_key = array_keys($point_values['value']);

            //Sort config by days - as key - asc
            asort($array_config_key);

            $config_key = 0;
            /*
             * $days_after_product_created 0 -> first day: get point of first config
             * $days_after_product_created > last day: get 0 point
             * */
            foreach ($array_config_key as $index => &$value) {
                $value = intval($value);
                if ($index == 0 && $days_after_product_created <= $value) {
                    $config_key = 0;
                    break;
                }

                if ($index == count($array_config_key) - 1) {
                    $config_key = -1;
                    break;
                }

                if (($value <= $days_after_product_created && $days_after_product_created <= $array_config_key[$index + 1])) {
                    $config_key = $index + 1;
                    break;
                }
            }

            /* Start calculate mkt point */
            $marketing_point = $config_key === -1 ? 0 : $quantity * ($point_values['value'][$array_config_key[$config_key]]['point']);

            // Calculate marketing point with pack
            if ($quantity_of_pack > 1) {
                $marketing_point = $marketing_point * $quantity_of_pack * $marketing_point_pack_rate / 100 / 100;
            }

            $marketing_point = $marketing_point > 0 ? $marketing_point : 0;

            if ($marketing_point > 0) {
                $point_rate['sref'] = intval($point_values['value'][$array_config_key[$config_key]]['sref']) * $marketing_point / 100;
                $point_rate['vendor'] = intval($point_values['value'][$array_config_key[$config_key]]['vendor']) * $marketing_point / 100;
            }
            /* End calculate mkt point */

            $result['point_setting'] = [
                'ukey' => $point_values['ukey'],
                'value' => $point_values['value']
            ];
            $result['marketing_point'] = $point_rate;
            $result['point_config'] = $point_values['value'][$array_config_key[$config_key]];
            $result['quantity'] = $quantity_of_pack > 1 ? $quantity * $quantity_of_pack : $quantity;
            $result['quantity_of_pack'] = $quantity_of_pack;
            $result['day_after_product_created'] = $days_after_product_created;
        }

        return $get_detail_info ? $result : $point_rate;
    }

    public function getSettingsPoint()
    {
        if (!$this->_settings_point) {
            $this->_settings_point = OSC::helper('core/setting')->get('marketing_point');
        }

        $point = [];
        if ($this->_settings_point) {
            foreach ($this->_settings_point as $key => $val) {
                $point[] = [
                    'ukey' => $key,
                    'name' => $val['name'],
                    'product_type' => $val['product_type'],
                    'collection' => $val['collection'],
                    'product_ids' => $val['product_ids'], // manual product ids (Highest priority for a product)
                    'value' => $val['value']
                ];
            }
        }

        return $point;
    }

    protected function getSettingPointByType($item_id, $type = 'product')
    {
        $settings = $this->getSettingsPoint();
        $point_value = [];
        $arr_ids = [];

        if (!empty($settings)) {
            foreach ($settings as $setting) {
                switch ($type) {
                    case "product":
                        $arr_ids = $setting['product_ids'];
                        break;
                    case "product_type":
                    case "collection":
                        $arr_ids = $setting[$type];
                        break;
                    default:
                        break;
                }
                if (in_array($item_id, $arr_ids)) {
                    $point_value = [
                        'ukey' => $setting['ukey'],
                        'value' => $setting['value']
                    ];
                    break;
                }
            }
        }

        return $point_value;
    }

    public function getReportGroupByUser($member_ids = [], $date_range = 'today', $page = 1, $options = [])
    {
        $timestamp_range = $this->parseDateRange($date_range);
        $member_map = [];

        $member_collection = OSC::model('user/member')->getCollection()
            ->addCondition('member_id', $member_ids, OSC_Database::OPERATOR_IN)
            ->load();

        foreach ($member_collection as $member) {
            $member_map[$member->data['member_id']] = $member->data['username'];
        }

        $DB = OSC::core('database');
        $query_total = "SELECT count(record_id) as total_record FROM {$DB->getTableName('marketing_point')} ";
        $query_rows = "SELECT * FROM {$DB->getTableName('marketing_point')} ";
        $query = "WHERE (member_id IN ('" . implode("','", $member_ids ) . "') OR vendor IN ('" . implode("','", array_values($member_map) ) . "')) ";

        if ($timestamp_range['time'][0] > 0) {
            $query .= "AND added_timestamp >= '" . $timestamp_range['time'][0] . "' ";
        }

        if ($timestamp_range['time'][1] > 0) {
            $query .= "AND added_timestamp <= '" . $timestamp_range['time'][1] . "' ";
        }

        $DB->query($query_total . $query, null, 'fetch_report_total');
        $total_rows = $DB->fetch('fetch_report_total')->total_record;

        if ($total_rows < 1) {
            return [
                'total_rows' => 0,
                'data' => [],
                'time_range' => $timestamp_range,
                'title' => $timestamp_range['title']
            ];
        }

        $DB->query($query_rows . $query, null, 'fetch_report_marketing_point');

        $data = [];
        foreach ($member_ids as $member_id) {
            $data[$member_id] = [
                'member_id' => $member_id,
                'name' => $member_map[$member_id],
                'point' => 0,
                'vendor_point' => 0,
                'total_point' => 0
            ];
        }

        $data_keys = array_keys($data);

        $rows = $DB->fetchArrayAll('fetch_report_marketing_point');

        if (count($rows) < 1) {
            return [
                'total_rows' => 0,
                'data' => [],
                'time_range' => $timestamp_range,
                'title' => $timestamp_range['title']
            ];
        }

        foreach ($rows as $row) {
            if (in_array($row['member_id'], $data_keys)) {
                $data[$row['member_id']]['point'] = $data[$row['member_id']]['point'] + $row['point'];
            }

            if ($row['vendor'] == $member_map[$row['member_id']]) { // Sref member is a vendor in this item, too
                $data[$row['member_id']]['vendor_point'] = $data[$row['member_id']]['vendor_point'] + $row['vendor_point'];
                $data[$row['member_id']]['total_point'] = $data[$row['member_id']]['total_point'] + $row['point'] + $row['vendor_point'];
            } else {
                if (in_array($row['member_id'], $data_keys)) {
                    $data[$row['member_id']]['total_point'] = $data[$row['member_id']]['total_point'] + $row['point'];
                }

                // Get point for vendor
                $names = array_column($data, 'name');
                $found_key = array_search($row['vendor'], $names);
                if ($found_key !== false) {
                    $member_id_of_vendor = $data_keys[$found_key];
                    $data[$member_id_of_vendor]['vendor_point'] = $data[$member_id_of_vendor]['vendor_point'] + $row['vendor_point'];
                    $data[$member_id_of_vendor]['total_point'] = $data[$member_id_of_vendor]['total_point'] + $row['vendor_point'];

                }
            }
        }

        $DB->free('fetch_report_marketing_point');

        if (count($data) < 1) {
            return [
                'total_rows' => 0,
                'data' => [],
                'time_range' => $timestamp_range,
                'title' => $timestamp_range['title']
            ];
        }

        $result_data = [];
        foreach ($data as $member_data) {
            $result_data[] = [
                'member_id' => $member_data['member_id'],
                'name' => $member_data['name'],
                'point' => OSC::helper('catalog/common')->integerToFloat($member_data['point']),
                'vendor_point' => OSC::helper('catalog/common')->integerToFloat($member_data['vendor_point']),
                'total_point' => OSC::helper('catalog/common')->integerToFloat($member_data['total_point'])
            ];
        }

        // Sort data:
        if ($options['order'] === 'desc') {
            $result_data = OSC::helper('core/array')->sortByFieldDesc($result_data, $options['sort']);
        } elseif ($options['order'] === 'asc') {
            $result_data = OSC::helper('core/array')->sortByFieldAsc($result_data, $options['sort']);
        }

        $total_rows = count($result_data);

        $result_data = array_slice($result_data,$options['page_size'] * ($page - 1), $options['page_size']);

        $result = [
            'total_rows' => $total_rows,
            'data' => $result_data,
            'time_range' => $timestamp_range,
            'title' => $timestamp_range['title']
        ];

        return $result;
    }

    /**
     * @param $search_type - type = sref or vendor
     * @param array $input - input for sref list or vendor list
     * @param $date_range
     * @return array
     */
    public function getReportDetail($search_type = 'sref', $input = [], $date_range = 'today', $current_page = 1)
    {
        $page_size = 25;
        $timestamp_range = $this->parseDateRange($date_range);
        $collection = OSC::model('marketing/point')->getCollection();

        if ($search_type == 'sref') {
            $collection->addCondition('point', 0, OSC_Database::OPERATOR_GREATER_THAN);
            $collection->addCondition('member_id', $input, OSC_Database::OPERATOR_IN);
        } elseif ($search_type == 'vendor') {
            $collection->addCondition('vendor_point', 0, OSC_Database::OPERATOR_GREATER_THAN);
            $collection->addCondition('vendor', $input, OSC_Database::OPERATOR_IN);
        }

        if ($timestamp_range['time'][0] > 0) {
            $collection->addCondition('added_timestamp', $timestamp_range['time'][0], OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL);
        }

        if ($timestamp_range['time'][1] > 0) {
            $collection->addCondition('added_timestamp', $timestamp_range['time'][1], OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL);
        }

        $collection->sort('record_id', 'DESC')
            ->setPageSize($page_size)
            ->setCurrentPage($current_page)
            ->load();

        $result = [
            'current_page' => $current_page,
            'page_size' => $page_size,
            'total_rows' => $collection->collectionLength(),
            'collection' => $collection,
            'time_range' => $timestamp_range,
            'title' => $timestamp_range['title']
        ];

        return $result;
    }

    public function getSelectorData($current_selectors, $current_member_ids, $action, $range)
    {
        $selector_member_ids = [];
        if (count($current_selectors) > 0) {
            foreach ($current_selectors as $selector) {
                if ($selector['member'] == 'member') {
                    $selector_member_ids[] = $selector['primary_id'];
                }
            }
        }

        $vendors = OSC::model('user/member')->getListMemberIdeaResearch();
        $current_member = OSC::helper('user/authentication')->getMember();

        // Get vendor has record
        $timestamp_range = $this->parseDateRange($range);
        $DB = OSC::core('database');

        $query_vendor = "SELECT DISTINCT vendor FROM {$DB->getTableName('marketing_point')} WHERE vendor !='' ";

        if ($timestamp_range['time'][0] > 0) {
            $query_vendor .= "AND added_timestamp >= '" . $timestamp_range['time'][0] . "' ";
        }

        if ($timestamp_range['time'][1] > 0) {
            $query_vendor .= "AND added_timestamp <= '" . $timestamp_range['time'][1] . "' ";
        }

        $DB->query($query_vendor, null, 'fetch_vendor');
        $rows = $DB->fetchArrayAll('fetch_vendor');
        $vendor_valid = [];

        if (count($rows)) {
            foreach ($rows as $row) {
                $vendor_valid[] = $row['vendor'];
            }
        }

        $list_member_id_vendor = []; // member_id for get marketing point records
        foreach ($vendors as $vendor) {
            if (!in_array($vendor->data['username'], $vendor_valid)) {
                continue;
            }
            // Update $selectors for Admin Root or Leader to select have a vendor but the vendor is not a sref
            if ($current_member->isAdmin() || in_array($vendor->data['member_id'], $selector_member_ids)) {
                $list_member_id_vendor[] = $vendor->data['member_id'];
                $current_selectors["m{$vendor->data['member_id']}"] = [
                    'primary_id' => $vendor->data['member_id'],
                    'title' => $vendor->data['username'],
                    'selected' => false,
                    'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['sref_member_id' => $vendor->data['member_id'], 'range' => $range], [])),
                    'type' => 'member',
                    'child' => [],
                    'group_title' => '',
                ];
            }
        }

        $member_ids = array_unique(array_merge($current_member_ids, $list_member_id_vendor));

        return [
            'selectors' => $current_selectors,
            'member_ids' => $member_ids
        ];
    }

    protected function parseDateRange($date_range)
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
            'title' => $title,
            'time' => OSC::helper('report/common')->getTimestampRange($begin_date, $end_date),
            'range' => $date_range
        ];
    }
}
