<?php

class Helper_Supplier_Location extends OSC_Object
{
    private $__product_hidden_cached_key = 'product_hidden';

    /**
     * @param array $product_type_variant_ids
     * @param string $country_code
     * @param string $province_code
     * @param boolean $flag_feed
     * @return array
     */
    public function getPrintTemplateForCustomer(
        array $product_type_variant_ids,
        $country_code = '',
        $province_code = '',
        $flag_feed = false,
        $skip_auth = false
    ) {
        $result = [];

        $product_type_variant_ids = implode(',', $product_type_variant_ids);

        if ($product_type_variant_ids == '') {
            return $result;
        }

        if ((intval(OSC::cookieGet(OSC::helper('core/common')->getMemberCookieIDKey())) > 0 || ($flag_feed && !$country_code)) && $skip_auth == false) {
            return $this->_getPrintTemplateForMember($product_type_variant_ids);
        }

        $group_locations = OSC::helper('catalog/common')->getGroupLocationCustomer($country_code, $province_code, $flag_feed);
        $location_datas = count($group_locations) ? "'" . implode("','", $group_locations) . "'" : "'*'";
        $location_code = ',' . $country_code . '_' . $province_code . ',';
        $condition = "AND location_parsed LIKE '%{$location_code}%'";
        $location_data_query = ($flag_feed && $location_datas === "'*'") ? "" : $condition;

        $DB = OSC::core('database');

        $data_variant_rel = [];
        $query_variant_rel = <<<EOF
SELECT product_type_variant_id, print_template_id, supplier_id
FROM osc_supplier_variant_rel 
WHERE product_type_variant_id IN ({$product_type_variant_ids})
AND print_template_id > 0;
EOF;
        $DB->query($query_variant_rel, null, 'fetch_supplier_variant_rel');
        while ($row = $DB->fetchArray('fetch_supplier_variant_rel')) {
            $data_variant_rel[$row['supplier_id']][$row['product_type_variant_id']] = intval($row['print_template_id']);
        }
        $DB->free('fetch_supplier_variant_rel');

        $data_location_rel = [];
        $query_location_rel = <<<EOF
SELECT supplier_id, product_type_variant_id
FROM osc_supply_variant
WHERE product_type_variant_id
IN ({$product_type_variant_ids}) {$location_data_query};
EOF;
        $DB->query($query_location_rel, null, 'fetch_supplier_location_rel');

        while ($row = $DB->fetchArray('fetch_supplier_location_rel')) {
            $data_location_rel[$row['supplier_id']][] = intval($row['product_type_variant_id']);
        }
        $DB->free('fetch_supplier_location_rel');

        // Handle expect result, array product type variant with print template available
        foreach ($data_location_rel as $supplier_id => $product_type_variant_ids) {
            foreach ($product_type_variant_ids as $product_type_variant_id) {
                $print_template_id = intval($data_variant_rel[$supplier_id][$product_type_variant_id]);

                if ($print_template_id > 0) {
                    $result[$product_type_variant_id]['product_type_variant_id'] = intval($product_type_variant_id);
                    $result[$product_type_variant_id]['print_template_id'][] = intval($data_variant_rel[$supplier_id][$product_type_variant_id]);
                    $result[$product_type_variant_id]['print_template_id'] = array_unique($result[$product_type_variant_id]['print_template_id']);
                }
            }
        }

        $result = array_values($result);

        return $result;
    }

    protected function _getPrintTemplateForMember($product_type_variant_ids) {
        $result = [];

        if ($product_type_variant_ids == '') {
            return $result;
        }

        $DB = OSC::core('database');

        $query = <<<EOF
SELECT product_type_variant_id, print_template_id
FROM osc_supplier_variant_rel 
WHERE product_type_variant_id IN ({$product_type_variant_ids})
AND print_template_id > 0;
EOF;
        $DB->query($query, null, 'fetch_supplier_variant_rel');
        while ($row = $DB->fetchArray('fetch_supplier_variant_rel')) {
            $result[$row['product_type_variant_id']]['product_type_variant_id'] = intval($row['product_type_variant_id']);
            $result[$row['product_type_variant_id']]['print_template_id'][] = intval($row['print_template_id']);
            $result[$row['product_type_variant_id']]['print_template_id'] = array_unique($result[$row['product_type_variant_id']]['print_template_id']);
        }
        $DB->free('fetch_supplier_variant_rel');
        $result = array_values($result);

        return $result;
    }

    /**
     * Get array group location from customer location
     * @param string $country_code
     * @param string $province_code
     * @return string $result
     */
    public function getStringLocationDatas(string $country_code = '', string $province_code = '') {
        $group_locations = OSC::helper('catalog/common')->getGroupLocationCustomer($country_code, $province_code);

        return count($group_locations) ? "'" . implode("','", $group_locations) . "'" : '*';
    }

    /**
     * @param int $product_type_variant_id
     * @param string $country_code
     * @param string $province_code
     * @return bool
     */
    public function isSellingVariantInCountry(int $product_type_variant_id, $country_code = '', $province_code = '') {
        if ($product_type_variant_id === 0) {
            return true;
        }

        $location_code = ',' . $country_code . '_' . $province_code . ',';

        $DB = OSC::core('database');

        $query = <<<EOF
SELECT count(id) AS result FROM osc_supplier_variant_rel 
WHERE product_type_variant_id = {$product_type_variant_id}
AND print_template_id > 0
AND supplier_id IN (
SELECT DISTINCT (supplier_id)
FROM osc_supply_variant
WHERE product_type_variant_id = {$product_type_variant_id}
AND location_parsed LIKE '%{$location_code}%'
);
EOF;

        $DB->query($query);

        $result = 0;
        while ($row = $DB->fetchArray()) {
            $result = intval($row['result']);
        }

        return $result > 0;
    }

    protected $_supply_variant_data = null;

    public function getSupplyVariantData() {
        if ($this->_supply_variant_data === null) {
            $DB = OSC::core('database');

            $DB->select('*', 'supply_variant', null, null, null, 'fetch_supply_product');

            $this->_supply_variant_data = $DB->fetchArrayAll('fetch_supply_product');
        }

        return $this->_supply_variant_data;
    }

    /**
     * Set product hidden in countries
     * @param $product_id
     */
    public function setSupplyLocationOfProduct($product_id) {
        try {
            $DB = OSC::core('database');

            $product_type_variant_ids = array_unique(array_column(OSC::model('catalog/product_variant')->getCollection()
                ->addCondition('product_id', $product_id)
                ->addField('product_type_variant_id')
                ->load()
                ->toArray(), 'product_type_variant_id'));

            $rows = $this->getSupplyVariantData();
            $location_codes = $this->_getListLocationCode();

            $supply_locations = [];
            foreach ($location_codes as $location_code) {
                foreach ($rows as $row) {
                    if (!in_array(intval($row['product_type_variant_id']), $product_type_variant_ids)) {
                        continue;
                    }

                    $verify_location_string = ',' . $location_code . ',';
                    if (!isset($supply_locations[$location_code]) &&
                        strpos($row['location_parsed'], $verify_location_string) !== false
                    ) {
                        $supply_locations[$location_code] = $location_code;
                    }
                }
            }

            $product_supply_location = ',' . implode(',', $supply_locations) . ',';

            $DB->update('catalog_product', [
                'supply_location' => $product_supply_location
            ], 'product_id = ' . $product_id, 1, 'update_product');
        } catch (Exception $ex) {
            //
        }
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    protected function _getListLocationCode() {
        $cache_key = '_getListLocationCode';
        $cache = OSC::core('cache')->get($cache_key);

        if ($cache !== false) {
            return $cache;
        }

        $items = [];
        $country_collection = OSC::model('core/country_country')
            ->getCollection()
            ->addField('country_code')
            ->load();
        foreach ($country_collection as $country) {
            $items[] = $country->data['country_code'] . '_';
        }

        $province_collection = OSC::model('core/country_province')
            ->getCollection()
            ->addField('country_code', 'province_code')
            ->load();
        foreach ($province_collection as $province) {
            $items[] = $province->data['country_code'] . '_' . $province->data['province_code'];
        }
        OSC::core('cache')->set($cache_key, $items, OSC_CACHE_TIME);

        return $items;
    }
}
