<?php

class Helper_Feed_Common extends OSC_Object {

    public function triggerDeleteBlock($option = []) {
        try {
            $conditions = [];
            if (isset($option['product_id'])) {
                $conditions[] = 'product_id = ' . $option['product_id'];
            }

            if (isset($option['collection_id'])) {
                $conditions[] = 'collection_id = ' . $option['collection_id'];
            }

            if (isset($option['country_code'])) {
                $conditions[] = 'country_code = "' . $option['country_code'] . '"';
            }
            if (!empty($conditions)) {
                /* @var $DB OSC_Database_Adapter */
                $DB = OSC::core('database')->getWriteAdapter();
                $DB->delete(OSC::model('feed/block')->getTableName(), implode('AND', $conditions) , null, 'delete_block');
                $DB->free('delete_block');
            }
        } catch (Exception $ex) {
        }
    }

    /**
     * triggerDeleteCustomTitle
     *
     * @param  mixed $option
     * @return void
     */
    public function triggerDeleteCustomTitle($product_id) {
        try {
            if ($product_id) {
                /* @var $DB OSC_Database_Adapter */
                $DB = OSC::core('database')->getWriteAdapter();
                $DB->delete('feed_custom_title', 'product_id = '. $product_id, null, 'delete_custom_title');
                $DB->free('delete_custom_title');
            }
        } catch (Exception $ex) { }
    }

    /**
     * @param string $req_url
     * @param string $ref_url
     * @return void
     */
    public function recordTrafficGoogle($req_url, $ref_url) {

        if (empty($req_url) || empty($ref_url)) {
            return;
        }

        $sref_id_google = OSC::helper('core/setting')->get('catalog/google_feed/sref_id');
        $sref_id_google = explode(',', $sref_id_google);

        $parse_url = parse_url($req_url);
        $is_ads = false;

        if (isset($parse_url['query']) && $parse_url['query']) {
            parse_str($parse_url['query'], $parameter);
            $sref = $parameter['sref'] ?? '';
            $is_ads = in_array($sref, $sref_id_google);
        }
        if ($is_ads && preg_match('/product\/[A-Z0-9]{15}/', $req_url, $matchs) && preg_match('/\.google\./', $ref_url)) { // + 1 when refer from google to detail product and sref is google
            $product_sku = explode('/', $matchs[0])[1];
            $this->_incrementTraffic($product_sku);
        }
    }

    protected function _incrementTraffic($product_sku) {
        try {
            $date_current = date('Ymd');
            $traffic_date_exist = OSC::model('feed/trafficProduct');
            try {
                $traffic_date_exist->setCondition("product_sku = '{$product_sku}' AND date = {$date_current}")->load();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    return;
                }
            }
            if ($traffic_date_exist->getId()) {
                $total = $traffic_date_exist->data['total'];
                $traffic_date_exist->setData([
                    'total' => $total + 1
                ])->save();
            } else {
                OSC::model('feed/trafficProduct')->setData([
                    'product_sku' => $product_sku,
                    'date' => $date_current
                ])->save();
            }
        } catch (Exception $ex) {}
    }

    /**
     * @param $category
     * @return array[]
     */
    public function getTabMenu($category) {

        $tab_menu = [
            [
                'url' => OSC::getUrl('feed/backend_block/google'),
                'activated' => $category == 'google',
                'title' => 'Google'
            ],
            [
                'url' => OSC::getUrl('feed/backend_block/bing'),
                'activated' => $category == 'bing',
                'title' => 'Bing'
            ]
        ];

        if (OSC::controller()->checkPermission('feed/block/add', false)) {
            $tab_menu[] = [
                'url' => OSC::getUrl('feed/backend_block/bulkBlockLog'),
                'activated' => $category == 'bulk_block',
                'title' => 'Bulk Block',
            ];
        }

        return $tab_menu;
    }
}
