<?php

class Helper_Catalog_ProductType extends OSC_Object {
    protected $_list_product_type_option = null;
    protected $_list_product_type_option_value = null;

    const PER_PAGE = 10;

    //Convert product_type_variant's key to specified option
    public function parseProductTypeVariantKey($ukey = '', $list_product_type_option = [], $list_product_type_option_value = []) {
        $result = [];

        if (!empty($ukey)) {
            if (empty($list_product_type_option)) {
                $this->_list_product_type_option = $this->_list_product_type_option ?? OSC::model('catalog/productType_option')->getCollection()->load()->toArray();
                $list_product_type_option = $this->_list_product_type_option;
            }

            if (empty($list_product_type_option_value)) {
                $this->_list_product_type_option_value = $this->_list_product_type_option_value ?? OSC::model('catalog/productType_optionValue')->getCollection()->load()->toArray();
                $list_product_type_option_value = $this->_list_product_type_option_value;
            }

            $ukey = explode('_', $ukey);
            if (!empty($ukey)) {
                foreach ($ukey as $item) {
                    $item = explode(':', $item);
                    if (isset($item[1]) && !empty($item[1])) {
                        $option = $list_product_type_option[array_search($item[0], array_column($list_product_type_option, 'id'))];
                        $option_value = $list_product_type_option_value[array_search($item[1], array_column($list_product_type_option_value, 'id'))];

                        if (isset($option['ukey']) && !empty($option['ukey']) && isset($option_value['ukey']) && !empty($option_value['ukey']) && $option_value['product_type_option_id'] === $option['id']) {
                            $result[$option['ukey']] = $option_value['ukey'];
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function array_merge_recursive_distinct()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);
        if (!is_array($base)) {
            $base = empty($base) ? array() : array($base);
        }
        foreach ($arrays as $append) {
            if (!is_array($append)) {
                $append = array($append);
            }
            foreach ($append as $ukey => $value) {
                if (!array_key_exists($ukey, $base) and !is_numeric($ukey)) {
                    $base[$ukey] = $append[$ukey];
                    continue;
                }
                if (is_array($value) or is_array($base[$ukey])) {
                    $base[$ukey] = $this->array_merge_recursive_distinct($base[$ukey], $append[$ukey]);
                } else {
                    if (is_numeric($ukey)) {
                        if (!in_array($value, $base)) {
                            $base[] = $value;
                        }
                    } else {
                        $base[$ukey] = $value;
                    }
                }
            }
        }
        return $base;
    }

    //Get all available options of single product_type
    public function collectProductTypeOption($product_type_id) {
        $list_product_type_variant = OSC::model('catalog/productType_variant')->getCollection()
            ->addCondition('product_type_id', $product_type_id)
            ->load();

        $temp = [];
        $return = [];
        if ($list_product_type_variant->length() > 0) {
            foreach ($list_product_type_variant as $product_type_variant) {
                $product_type_variant_option = $this->parseProductTypeVariantKey($product_type_variant->data['ukey']);

                if (!empty($product_type_variant_option)) {
                    $temp[] = $product_type_variant_option;
                }
            }

            foreach ($temp as $item) {
                if(empty($return)) {
                    $return = $item;
                } else {
                    if($return['id'] == $item['id']) {
                        $return['values'][] = $item['values'];
                    }
                }
            }
        }

        return $return;
    }

    public function collectVariantTree($productTypes = []) {
        if(!is_array($productTypes) || count($productTypes) < 1) return false;
        $collection = OSC::model('catalog/productType')->getCollection()
            ->addCondition('id', $productTypes, OSC_Database::OPERATOR_IN)
            ->load();
        $result = [];

        if($collection->length() > 0) {
            foreach ($collection as $item) {
                $result[] = [
                    'id' => $item->data['id'],
                    'title' => OSC::safeString($item->data['title']),
                    'ukey' => $item->data['ukey'],
                    'image' => $item->data['image'],
                    'options' => $this->collectProductTypeOption($item->data['id'])
                ];
            }
        }

        return $result;
    }

    /**
     * @param array $product_type_ukey
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getCampaignConfigs(array $product_type_ukey) {
        $result = [];

        $option_type_ids = [];
        $list_ukeys = [];
        $listProductTypeVariantId = [];
        $listProductTypeTitle = [];
        $listProductTypeVariantPrice = [];
        $listProductTypeVariantComparePrice = [];
        $unique_option_type_ids = [];

        $product_types = OSC::model('catalog/productType')
            ->getCollection()
            ->loadByUkey($product_type_ukey);

        foreach ($product_types as $product_type) {
            $result[$product_type->data['ukey']]['id'] = $product_type->getId();
            $result[$product_type->data['ukey']]['name'] = $product_type->data['title'];
            $result[$product_type->data['ukey']]['key'] = $product_type->data['ukey'];
            $result[$product_type->data['ukey']]['image'] = $product_type->data['image'];
            $result[$product_type->data['ukey']]['options'] = [];
            $result[$product_type->data['ukey']]['sort_option'] = explode(',', $product_type->data['product_type_option_ids']);

            $option_type_ids[$product_type->data['ukey']] = explode(',', $product_type->data['product_type_option_ids']);
            foreach (explode(',', $product_type->data['product_type_option_ids']) as $option_type_id) {
                $unique_option_type_ids[] = intval($option_type_id);
            }

            $result[$product_type->data['ukey']]['product_type_variant'] = [];
            foreach ($product_type->getProductTypeVariants() as $product_type_variant) {
                $hasPrintTemplate = false;
                $supplierVariantRel = OSC::model('catalog/supplierVariantRel')->getCollection()
                    ->addCondition('product_type_variant_id', $product_type_variant->getId(), OSC_Database::OPERATOR_EQUAL)
                    ->load()
                    ->toArray();
                if(count($supplierVariantRel) > 0) {
                    foreach ($supplierVariantRel as $rel) {
                        if($rel['print_template_id'] > 0) {
                            $hasPrintTemplate = true;
                            break;
                        }
                    }
                } else {
                    continue;
                }

                if($hasPrintTemplate) {
                    $list_ukeys[$product_type->data['ukey']][] = $product_type_variant->data['ukey'];
                    $listProductTypeVariantId[$product_type_variant->data['ukey']] = $product_type_variant->getId();
                    $listProductTypeTitle[$product_type_variant->data['ukey']] = $product_type_variant->data['title'];
                    $listProductTypeVariantPrice[$product_type_variant->data['ukey']] = $product_type_variant->data['price'];
                    $listProductTypeVariantComparePrice[$product_type_variant->data['ukey']] = $product_type_variant->data['compare_at_price'];
                }
            }
        }
        $unique_option_type_ids = array_unique($unique_option_type_ids);

        $option_values = [];
        $option_datas = [];
        $options = OSC::model('catalog/productType_option')
            ->getCollection()
            ->addCondition('id', $unique_option_type_ids, OSC_Database::OPERATOR_IN)
            ->load();

        foreach ($options as $option) {
            $option_datas[$option->getId()] = [
                'id' => $option->getId(),
                'title' => OSC::safeString($option->data['title']),
                'key' => $option->data['ukey'],
                'type' => $option->data['type'],
                'auto_select' => $option->data['auto_select'],
                'is_reorder' => $option->data['is_reorder'],
                'values' => [],
            ];
            foreach ($option->getOptionValues() as $option_value) {
                $option_datas[$option->getId()]['values'][] = [
                    'id' => $option_value->getId(),
                    'title' => OSC::safeString($option_value->data['title']),
                    'key' => $option_value->data['ukey'],
                    'meta_data' => $option_value->data['meta_data'],
                ];
                $option_values[$option_value->getId()] = $option_value->data['ukey'];
            }
        }

        $productTypeOptionValues = Helper_Catalog_ProductType::getInstance()->parseAvailableVariants($list_ukeys);

        foreach ($option_type_ids as $product_type => $option_type_ids_of_product_type) {
            foreach ($option_type_ids_of_product_type as $option_type_id) {
                /**
                 * Only push the available product type variant to option
                 */
                $issetValues = [];
                foreach ($option_datas[$option_type_id]['values'] as $key => $optionsValues) {
                    if(in_array($optionsValues['id'], $productTypeOptionValues[$product_type][$option_type_id])) {
                        $issetValues[] = $option_datas[$option_type_id]['values'][$key];
                    }
                }

                /**
                 * if the option not include any option variant, don't create option
                 */
                if(count($issetValues) > 0) {
                    $result[$product_type]['options'][] = [
                        'id' => $option_datas[$option_type_id]['id'],
                        'title' => $option_datas[$option_type_id]['title'],
                        'key' => $option_datas[$option_type_id]['key'],
                        'type' => $option_datas[$option_type_id]['type'],
                        'auto_select' => $option_datas[$option_type_id]['auto_select'],
                        'is_reorder' => $option_datas[$option_type_id]['is_reorder'],
                        'values' => $issetValues
                    ];
                }
            }
        }

        foreach ($list_ukeys as $product_type => $ukeys_of_product_type) {
            foreach ($ukeys_of_product_type as $ukey) {
                $opt = [];
                foreach (Helper_Catalog_ProductType::getInstance()->parseOptionValueIdsFromUkey($ukey, $result[$product_type]['sort_option']) as $option_value_id) {
                    $opt[] = $option_values[$option_value_id];
                }
                $result[$product_type]['product_type_variant'][] = [
                    'id' => $listProductTypeVariantId[$ukey],
                    'title' => $listProductTypeTitle[$ukey],
                    'price' => $listProductTypeVariantPrice[$ukey],
                    'compare_at_price' => $listProductTypeVariantComparePrice[$ukey],
                    'ukey' => $ukey,
                    'option' => $opt
                ];
            }
        }

        /**
         * Re-order results by post data
         */
        $return = [];
        foreach ($product_type_ukey as $product_type) {
            $return[$product_type] = $result[$product_type];
        }

        return $return;
    }

    /**
     * @param string $ukey
     * @return array
     */
    public function parseOptionValueIdsFromUkey(string $ukey = '', array $sort) {
        $result = [];
        foreach (explode('_', explode('/', $ukey)[1]) as $option_type_value_pair) {

            $option_value = explode(':', $option_type_value_pair);
            $result[$option_value[0]] = $option_value[1];
        }

        $return = [];
        foreach ($sort as $s) {
            $return[] =  $result[$s];
        }

        return $return;
    }

    /**
     * @param array $data: Array of product type (key) and product type option values available (Value)
     * @return array
     */
    public function parseAvailableVariants(array $data) {
        $result = [];
        foreach ($data as $productType => $ukeys) {
            $result[$productType] = [];
            foreach ($ukeys as $ukey) {
                foreach (explode('_', explode('/', $ukey)[1]) as $valueStructure) {
                    $optionValues = explode(':', $valueStructure);
                    if(!in_array($optionValues[1], $result[$productType][$optionValues[0]])) {
                        $result[$productType][$optionValues[0]][] = $optionValues[1];
                    }
                }
            }
        }

        return $result;
    }

    public function getProductTypeTabs($amazon_status = 0) {
        $data = [];
        $DB = OSC::core('database');
        $query = "SELECT tab_name FROM " . OSC::model('catalog/productType')->getTableName(true) . " WHERE status = " . Model_Catalog_ProductType::STATE_ENABLE;
        if ($amazon_status == Model_Catalog_ProductType::STATE_AMAZON_ENABLE) {
            $query .= " AND amazon_status = " . Model_Catalog_ProductType::STATE_AMAZON_ENABLE;
        }
        $query .= " GROUP BY tab_name";
        $DB->query($query, null, 'fetch_product_type');

        while ($row = $DB->fetchArray('fetch_product_type')) {
            $data[] = $row['tab_name'];
        }

        return $data;
    }

    public function getListProductTypeByTab($tabName) {
        $collection = OSC::model('catalog/productType')->getCollection()
            ->addField('id', 'tab_name', 'group_name', 'title', 'ukey', 'image', 'product_type_option_ids')
            ->addCondition('status', Model_Catalog_ProductType::STATE_ENABLE, OSC_Database::OPERATOR_EQUAL);


        $collection->addCondition('tab_name', $tabName, OSC_Database::OPERATOR_EQUAL)->load();

        $result = [];
        if($collection->length() > 0) {
            $groupKeys = [];

            foreach ($collection as $item) {
                if (!in_array($item->data['group_name'], $groupKeys)) {
                    $groupKeys[] = $item->data['group_name'];
                }
            }

            foreach ($groupKeys as $group) {
                foreach ($collection as $item) {
                    if($group == $item->data['group_name']) {
                        $result[$group][] = [
                            'id' => $item->data['id'],
                            'tab_name' => $item->data['tab_name'],
                            'group_name' => $item->data['group_name'],
                            'title' => OSC::safeString($item->data['title']),
                            'ukey' => $item->data['ukey'],
                            'image' => $item->data['image'],
                            'product_type_option_ids' => $item->data['product_type_option_ids']
                        ];
                    }
                }
            }

        }

        return $result;
    }

    public function getProductTypeDeactive($ukeys) {
        try {
            $product_types_deactivate = OSC::model('catalog/productType')->getCollection()
                ->addField('title', 'ukey')
                ->addCondition('ukey', array_unique($ukeys), OSC_Database::OPERATOR_IN)
                ->addCondition('status', 0)->load();

            return  $product_types_deactivate->getItems();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getProductTypeIdDeactive() {
        $product_type_id_deactivate = [];
        try {
            $product_types_deactivate = OSC::model('catalog/productType')
                ->getCollection()
                ->addField('title', 'ukey')
                ->addCondition('status', 0)->load();

            foreach ($product_types_deactivate as $product_type) {
                $product_type_id_deactivate[] = $product_type->data['id'];
            }

            return $product_type_id_deactivate;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getListProductTypeByGroup($groupName, $page) {
        $data = [];
        $collection = OSC::model('catalog/productType')->getCollection()
            ->addField('id', 'group_name', 'title', 'ukey', 'image', 'product_type_option_ids')
            ->addCondition('status', 1, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('group_name', $groupName, OSC_Database::OPERATOR_EQUAL)
            ->setPageSize(static::PER_PAGE)
            ->setCurrentPage($page)
            ->load();

        if($collection->length() > 0) {
            foreach ($collection as $item) {
                $data[] = [
                    'id' => $item->data['id'],
                    'group_name' => $item->data['group_name'],
                    'title' => OSC::safeString($item->data['title']),
                    'ukey' => $item->data['ukey'],
                    'image' => $item->data['image'],
                    'product_type_option_ids' => $item->data['product_type_option_ids']
                ];
            }
        }

        return [
            'items' => $data,
            'total' => $collection->collectionLength(),
            'offset' => (($collection->getCurrentPage() - 1) * $collection->getPageSize()) + $collection->length(),
            'current_page' => $collection->getCurrentPage(),
            'page_size' => $collection->getPageSize(),
        ];
    }

    /**
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function getProductTypeVariantMappingProductType() {
        $result = [];

        $variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('product_type_id')
            ->load();

        foreach ($variants as $variant) {
            $result[$variant->getId()] = $variant->data['product_type_id'];
        }

        return $result;
    }

    /**
     * Get all product type variant ID in list variants of the product
     * @param Model_Catalog_Product $product
     * @return array
     */
    public function parseProductTypeVariantId(Model_Catalog_Product $product) {
        $variants = $product->getVariants();
        $productTypeVariants = [];
        if(count($variants) > 0) {
            foreach ($variants as $variant) {
                $metaData = $variant->data['meta_data'];
                $productTypeVariants[] = $metaData['campaign_config']['product_type_variant_id'];
            }
        }

        return $productTypeVariants;
    }

    /**
     * Parse campaign config from DB
     * set Key for each print template data. This key will use as current print template key
     * insert svg_content for each print_template in this data
     * @param $config
     * @return array
     */
    public function parseCampaignConfigForBuilder($config) {
        $result = [];
        if(isset($config['print_template_config']) && count($config['print_template_config']) > 0) {
            $personal_design_ids = [];
            $personal_designs = [];
            foreach ($config['print_template_config'] as $item) {
                foreach ($item['segments'] as $key => $value) {
                    if ($value['source']['type'] == 'personalizedDesign') {
                        $personal_design_ids[] = $value['source']['design_id'];
                    }
                }
            }

            if (count($personal_design_ids) > 0) {
                $design_collection = OSC::model('personalizedDesign/design')->getCollection()
                    ->addCondition('design_id', $personal_design_ids, OSC_Database::OPERATOR_IN)
                    ->load();
                if ($design_collection->length() > 0) {
                    foreach ($design_collection as $design) {
                        $personal_designs[$design->getId()] = $design;
                    }
                }
            }

            $newConfig = [];
            foreach ($config['print_template_config'] as $item) {
                foreach ($item['segments'] as $key => $value) {
                    if($value['source']['type'] == 'personalizedDesign') {
                        $model = $personal_designs[$value['source']['design_id']];
                        if (!($model instanceof Model_PersonalizedDesign_Design) || $model->getId() < 1) {
                            throw new Exception('Not exist design #'. $value['source']['design_id']);
                        }
                        //$model = OSC::model('personalizedDesign/design')->load($value['source']['design_id']);
                        if($value['source']['design_id'] == $model->getId()) {
                            if(isset($value['source']['option_default_values']['options'])) {
                                $item['segments'][$key]['source']['option_default_values']['svg_content'] = OSC::helper('personalizedDesign/common')->renderSvg($model, $value['source']['option_default_values']['options'], ['original_render','skip_validate_config']);
                            }
                            $item['segments'][$key]['source']['svg_content'] = OSC::helper('personalizedDesign/common')->renderSvg($model, [], ['original_render']);
                            $item['segments'][$key]['source']['orig_size'] = ['width' => $model->data['design_data']['document']['width'], 'height' => $model->data['design_data']['document']['height']];
                        }
                    } elseif ($value['source']['type'] == 'image') {
                        $model = OSC::model('catalog/campaign_imageLib_item')->load($value['source']['image_id']);
                        if($value['source']['image_id'] == $model->getId()) {
                            $item['segments'][$key]['source']['url'] = $model->getFileThumbUrl();
                            $item['segments'][$key]['source']['orig_size'] = ['width' => $model->data['width'], 'height' => $model->data['height']];
                        }
                    }
                }
                $newConfig[] = $item;
            }

            /**
             * Parse configs
             */
            foreach ($newConfig as $item) {
                $result['print_template_config']['printTemplate_' . $item['print_template_id']] = $item;
            }
        }

        if(isset($config['is_reorder'])) {
            $result['is_reorder'] = $config['is_reorder'];
        }

        if(isset($config['apply_reorder'])) {
            $result['apply_reorder'] = $config['apply_reorder'];
        }

        return $result;
    }

    /**
     * @param $array
     * @return boolean
     */
    public function isProductTypeInTwoLocation($array) {
        for ($i = 0; $i < (count($array) - 1); $i++) {
            for ($j = $i + 1; $j < count($array); $j++) {
                if (!isset($array[$i]['product_types']) && isset($array[$i]['product_type_id'])) {
                    $array[$i]['product_types'] = [$array[$i]['product_type_id']];
                }

                if (!isset($array[$j]['product_types']) && isset($array[$j]['product_type_id'])) {
                    $array[$j]['product_types'] = [$array[$j]['product_type_id']];
                }

                if (($array[$i]['location_data'] === '*' && in_array('*', $array[$i]['product_types'])) ||
                    ($array[$j]['location_data'] === '*' && in_array('*', $array[$j]['product_types']))
                ) {
                    continue;
                }

                foreach ($array[$i]['product_types'] as $product_type) {
                    if (in_array($product_type, $array[$j]['product_types']) &&
                        $product_type !== '*' &&
                        OSC::helper('core/country')->compareLocation($array[$i]['location_data'], $array[$j]['location_data'])
                    ) {
                        return true;
                    }
                }
            }

        }

        return false;
    }

    public function groupProductType()
    {
        $list_product_type = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('ukey', 'group_name')
            ->sort('group_name')
            ->load();

        $group_name = [];
        $group_product_type = [];

        foreach ($list_product_type as $product_type) {
            $group_name[] = $product_type->data['group_name'];
        }

        $group_name = array_unique($group_name);

        foreach ($group_name as $group) {
            foreach ($list_product_type as $product_type) {
                if ($product_type->data['group_name'] == $group) {
                    $group_product_type[$group][] = $product_type->data['ukey'];
                }
            }
        }

        return $group_product_type;
    }

    public function getAllVariants() {
        $product_types = [];

        $product_type_data = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('id', 'title')
            ->load()
            ->toArray();

        foreach ($product_type_data as $item) {
            $item['variants'] = [];
            $product_types[$item['id']] = $item;
        }

        $variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('product_type_id', 'title')
            ->sort('title', OSC_Database::ORDER_ASC)
            ->load()
            ->toArray();

        foreach ($variants as $variant) {
            $product_types[$variant['product_type_id']]['variants'][] = $variant;
        }

        return $product_types;
    }
}
