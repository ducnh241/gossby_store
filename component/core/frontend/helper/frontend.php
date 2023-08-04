<?php

class Helper_Frontend_Frontend extends Abstract_Frontend_Controller
{
    public function renderBlockMain($params)
    {
        return $this->getTemplate()->build('frontend/block/main', array(
            'data' => $params['data']
        ));
    }

    public function getDefaultSettingCollection()
    {
        $catalog_collections = [];
        try {
            $collection = OSC::model('catalog/collection')->getCollection()->load();
            foreach ($collection as $catalog_collection) {
                $catalog_collections[$catalog_collection->getId()] = $catalog_collection->data['title'];
            }
        } catch (Exception $ex) {

        }
        return [
            'collection_name' => [
                'label' => 'Collection name',
                'type' => 'text',
                'name' => 'collection_name',
                'value' => 'Collection',
            ],
            'collection_id' => [
                'label' => 'Select collection',
                'type' => 'select',
                'name' => 'collection_id',
                'options' => $catalog_collections,
                'value' => ''
            ],
            'collection_image' => [
                'label' => 'Collection image',
                'type' => 'file',
                'name' => 'collection_image',
                'value' => '',
                'description' => 'Upload collection image'
            ],
            'collection_mobile_image' => [
                'label' => 'Collection mobile image',
                'type' => 'file',
                'name' => 'collection_mobile_image',
                'value' => '',
                'description' => 'Upload banner for mobile'
            ]
        ];
    }

    public function checkIsPageDisplayLiveChat() {
        return OSC::helper('core/setting')->get('theme/live_chat/enable') == 1;
    }

    public function getValueAbTestTab()
    {
        return OSC::getABTestValue('ab_test_ver_4_tab_product');
    }

    public function flagProductActiveAbtestTab($product_id) {
        $result = 0;
        try {
            $enable_abtest_tab = intval(OSC::helper('core/setting')->get('catalog/abtest/enable_tab')) === 1;

            if (!$enable_abtest_tab) {
                throw new Exception('Abtest tab inactive');
            }
            $list_product_abtest_tab = trim(OSC::helper('core/setting')->get('catalog/abtest/list_product_tab'));

            if (!$list_product_abtest_tab) {
                throw new Exception('Product not have abtest tab');
            }

            $list_arr_product = explode(',', $list_product_abtest_tab);

            if (is_array($list_arr_product) && !empty($list_arr_product) && in_array($product_id, $list_arr_product)) {
                $result = 1;
            }
        } catch (Exception $ex) { }

        return $result;
    }

    public function handleAbtestTab(Model_Catalog_Product $product) {
        $enable_abtest = $this->flagProductActiveAbtestTab($product->getId());

        if (!$enable_abtest || !$product->checkHasTabDesign()) {
            return null;
        }

        return $this->getValueAbTestTab();
    }
}
