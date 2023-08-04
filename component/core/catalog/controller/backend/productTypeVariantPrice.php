<?php

class Controller_Catalog_Backend_ProductTypeVariantPrice extends Abstract_Catalog_Controller_Backend {

    protected $_product_type_variants = null;

    public function __construct() {
        parent::__construct();
        $this->checkPermission();

        $this->getTemplate()->push('catalog/product_type.js', 'js');
        $this->getTemplate()->push('vendor/bootstrap/bootstrap-grid.min.css', 'css');

        $this->getTemplate()
            ->setCurrentMenuItemKey('product_config/product_type_variant_price')
            ->resetBreadcrumb()
            ->addBreadcrumb('Manage Pricing for Variants', $this->getUrl('catalog/backend_productTypeVariantPrice/index'))
            ->setPageTitle('Manage Pricing for Product Type Variants');
    }

    public function actionIndex() {
        $this->forward('*/*/listProductType');
    }

    public function actionListProductType() {
        $product_types = OSC::model('catalog/productType')->getCollection()->addField('id', 'title')->load();

        $this->output($this->getTemplate()->build('catalog/product/productTypeVariantPrice/listProductType', [
            'product_types' => $product_types
        ]));
    }

    public function actionGetListLocationData() {
        $data['default'] = 'Default Price';

        $location_price_collection = OSC::model('catalog/productType_variantLocationPrice')
            ->getCollection()
            ->addField('location_data')
            ->load();

        $location_datas = array_unique(array_column($location_price_collection->toArray(), 'location_data'));

        foreach ($location_datas as $location_data) {
            $data[$location_data] = OSC::helper('core/country')->getNameByLocation($location_data);
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }

    public function actionGetListPrice() {
        $location = $this->_request->get('location_data');

        if (!$location || $location === 'default') {
            $data = $this->_getDefaultPrice();
        } else {
            $data = $this->_getLocationPrice($location);
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }

    /**
     * @return array
     * @throws OSC_Exception_Runtime
     */
    protected function _getDefaultPrice(): array {
        $data = [];
        $product_types = [];
        $product_type_collection = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('id', 'title')
            ->load();
        foreach ($product_type_collection as $product_type) {
            $product_types[$product_type->getId()] = $product_type->data['title'];
        }

        $product_type_variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id', 'product_type_id', 'title', 'best_price', 'price', 'compare_at_price', 'base_cost_configs')
            ->load();

        foreach ($product_type_variants as $product_type_variant) {
            $product_type_id = $product_type_variant->data['product_type_id'];
            $data[$product_type_id]['product_type_name'] = $product_types[$product_type_id];
            $data[$product_type_id]['variants'][] = [
                'id' => $product_type_variant->getId(),
                'title' => $product_type_variant->data['title'],
                'best_price' => $this->_showBestPriceForStore($product_type_variant->data['best_price']),
                'price' => OSC::helper('catalog/common')->integerToFloat($product_type_variant->data['price']),
                'comparePrice' => OSC::helper('catalog/common')->integerToFloat($product_type_variant->data['compare_at_price']),
                'baseCostConfigs' => $product_type_variant->data['base_cost_configs'],
                'hasInfo' => $product_type_variant->hasLocationPrices()
            ];
            $data[$product_type_id]['getMoreAble'] = false;
        }

        return [
            'data' => $data,
            'getMoreAble' => false
        ];
    }

    /**
     * @param $location
     * @return array
     * @throws OSC_Exception_Runtime
     */
    protected function _getLocationPrice($location): array {
        $data = [];
        $product_types = [];
        $product_type_collection = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('id', 'title')
            ->load();
        foreach ($product_type_collection as $product_type) {
            $product_types[$product_type->getId()] = $product_type->data['title'];
        }

        $product_type_variants = [];
        $product_type_variant_collection = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id', 'product_type_id', 'title')
            ->load();

        foreach ($product_type_variant_collection as $product_type_variant) {
            $product_type_variants[$product_type_variant->getId()] = [
                'product_type_id' => $product_type_variant->data['product_type_id'],
                'product_type_name' => $product_types[$product_type_variant->data['product_type_id']],
                'title' => $product_type_variant->data['title'],
                'hasInfo' => $product_type_variant->hasLocationPrices()
            ];

            $total_product_type_variants[$product_type_variant->data['product_type_id']]++;
        }

        $location_prices = OSC::model('catalog/productType_variantLocationPrice')
            ->getCollection()
            ->addCondition('location_data', $location, OSC_Database::OPERATOR_EQUAL)
            ->load();

        foreach ($location_prices as $location_price) {
            $product_type_variant_id = $location_price->data['product_type_variant_id'];
            $product_type_variant = $product_type_variants[$product_type_variant_id];
            $product_type_id = $product_type_variant['product_type_id'];

            $data[$product_type_id]['product_type_name'] = $product_type_variant['product_type_name'];
            $data[$product_type_id]['variants'][] = [
                'id' => $product_type_variant_id,
                'title' => $product_type_variant['title'],
                'price' => OSC::helper('catalog/common')->integerToFloat($location_price->data['price']),
                'comparePrice' => OSC::helper('catalog/common')->integerToFloat($location_price->data['compare_at_price']),
                'baseCostConfigs' => $location_price->data['base_cost_configs'],
                'hasInfo' => $product_type_variant['hasInfo']
            ];
        }

        foreach ($data as $key => $value) {
            $location_variant_price_counter = count($value['variants']);

            $data[$key]['getMoreAble'] = $total_product_type_variants[$key] > $location_variant_price_counter;
        }

        $ableToGetMoreProductType = count($product_types) > count($data);

        return [
            'data' => $data,
            'getMoreAble' => $ableToGetMoreProductType
        ];
    }

    public function actionGetMoreProductOfLocation() {
        $exits_product_type_ids = $this->_request->get('product_type_ids') ?? [];
        
        $data = [];
        $product_types = [];
        $product_type_collection = OSC::model('catalog/productType')
            ->getCollection()
            ->addField('id', 'title')
            ->addCondition('id', $exits_product_type_ids, OSC_Database::OPERATOR_NOT_IN)
            ->load();
        foreach ($product_type_collection as $product_type) {
            $product_types[$product_type->getId()] = $product_type->data['title'];
        }

        $product_type_variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id', 'product_type_id', 'title')
            ->load();

        foreach ($product_type_variants as $product_type_variant) {
            $product_type_id = $product_type_variant->data['product_type_id'];
            if (!isset($product_types[$product_type_id])) {
                continue;
            }

            $data[$product_type_id]['product_type_name'] = $product_types[$product_type_id];
            $data[$product_type_id]['variants'][] = [
                'id' => $product_type_variant->getId(),
                'title' => $product_type_variant->data['title']
            ];
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }

    public function actionGetMoreVariantOfLocation() {
        $exits_product_type_id = intval($this->_request->get('product_type_id'));
        $exits_product_type_variant_ids = $this->_request->get('product_type_variant_ids');

        $data = [];
        $product_type = OSC::model('catalog/productType')->load($exits_product_type_id);

        $product_type_variants = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id', 'product_type_id', 'title')
            ->addCondition('product_type_id', $exits_product_type_id, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
            ->addCondition('id', $exits_product_type_variant_ids, OSC_Database::OPERATOR_NOT_IN, OSC_Database::RELATION_AND)
            ->load();

        foreach ($product_type_variants as $product_type_variant) {
            $product_type_id = $product_type->getId();

            $data[$product_type_id]['product_type_name'] = $product_type->data['title'];
            $data[$product_type_id]['variants'][] = [
                'id' => $product_type_variant->getId(),
                'title' => $product_type_variant->data['title']
            ];
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }

    public function actionSetDefaultPrice() {
        $product_type_variant_ids = $this->_request->get('product_type_variant_ids');
        $price = floatval($this->_request->get('price'));
        $compare_at_price = floatval($this->_request->get('compare_at_price'));
        $base_cost_configs = $this->_request->get('base_cost_configs');

        try {
            $params = [
                'price' => OSC::helper('catalog/common')->floatToInteger($price),
                'compare_at_price' => OSC::helper('catalog/common')->floatToInteger($compare_at_price),
                'base_cost_configs' => $base_cost_configs
            ];

            $this->_updateDefaultPrice($product_type_variant_ids, $params);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Default price saved successfully!']);
    }

    protected function _updateDefaultPrice($product_type_variant_ids, $params) {
        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();

        try {
            $this->_validateBaseCost($product_type_variant_ids, $params);

            foreach (['price', 'compare_at_price'] as $value) {
                if ($params[$value] != 0) {
                    $data_update[$value] = $params[$value];
                }
            }

            $data_update['base_cost_configs'] = is_array($params['base_cost_configs']) ? $params['base_cost_configs'] : [];
            $data_update['modified_timestamp'] = time();

            $product_type_variants = OSC::model('catalog/productType_variant')->getCollection()
                ->addCondition('id', $product_type_variant_ids, OSC_Database::OPERATOR_IN)
                ->load();

            foreach ($product_type_variants as $product_type_variant) {

                $product_type_variant->setData($data_update)->save();

            }
            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            throw new Exception($ex->getMessage());
        }
    }

    public function actionSetLocationPrice() {
        $location = $this->_request->get('location');
        $product_type_variant_ids = $this->_request->get('product_type_variant_ids');
        $price = floatval($this->_request->get('price'));
        $compare_at_price = floatval($this->_request->get('compare_at_price'));
        $base_cost_configs = $this->_request->get('base_cost_configs');

        try {
            $params = [
                'location' => $location,
                'price' => OSC::helper('catalog/common')->floatToInteger($price),
                'compare_at_price' => OSC::helper('catalog/common')->floatToInteger($compare_at_price),
                'base_cost_configs' => $base_cost_configs
            ];

            $this->_insertOrUpdateLocationPrice($product_type_variant_ids, $params);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse(['message' => 'Default price saved successfully!']);
    }

    protected function _insertOrUpdateLocationPrice($product_type_variant_ids, $params) {
        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();

        try {
            if (empty($params['location'])) {
                throw new Exception('Location is empty');
            }

            $this->_validateBaseCost($product_type_variant_ids, $params);

            foreach (['price', 'compare_at_price'] as $value) {
                if ($params[$value] != 0) {
                    $data_update[$value] = $params[$value];
                }
            }

            $data_update['location_data'] = $params['location'];
            $data_update['base_cost_configs'] = is_array($params['base_cost_configs']) ? $params['base_cost_configs'] : [];
            $data_update['modified_timestamp'] = time();

            foreach ($product_type_variant_ids as $product_type_variant_id) {
                $variant_location_price = OSC::model('catalog/productType_variantLocationPrice')->getCollection()
                    ->addCondition('location_data', $params['location'], OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->addCondition('product_type_variant_id', $product_type_variant_id, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->load()
                    ->first();
                if (!$variant_location_price) {
                    $variant_location_price = OSC::model('catalog/productType_variantLocationPrice');
                }

                $update_flag = $variant_location_price->data['added_timestamp'] > 0;
                $data_update['product_type_variant_id'] = intval($product_type_variant_id);
                $data_update['added_timestamp'] = $update_flag ?
                    $variant_location_price->data['added_timestamp'] :
                    time();

                $variant_location_price->setData($data_update)->save();

            }

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            throw new Exception($ex->getMessage());
        }
    }

    protected function _validateBaseCost($product_type_variant_ids, $params) {
        if (count($product_type_variant_ids) < 1) {
            throw new Exception('Please select the product type or product type variant before set price');
        }

        if ($params['price'] == 0 && $params['compare_at_price'] == 0) {
            throw new Exception('Price and compare at price must be bigger than 0');
        }

        $flag_check_require_base_cost_min = true;
        $quantity_check = [];

        foreach ($params['base_cost_configs'] as $value) {
            if (intval($value['quantity']) === 0) {
                throw new Exception('Quantity of base cost must bigger than 0');
            }

            if (intval($value['base_cost']) === 0) {
                throw new Exception('Base cost must be bigger than 0');
            }

            if ($quantity_check[$value['quantity']]) {
                throw new Exception('Duplicate quantity value = ' . $value['quantity']);
            } else {
                $quantity_check[$value['quantity']] = 1;
            }

            if ((OSC::helper('catalog/common')->floatToInteger(floatval($value['base_cost'])) / intval($value['quantity'])) >= $params['price']) {
                throw new Exception('Base cost must be lower than price');
            }

            if (intval($value['quantity']) === 1) {
                $flag_check_require_base_cost_min = false;
            }
        }

        if ($flag_check_require_base_cost_min && count($params['base_cost_configs']) > 0) {
            throw new Exception('Base cost must have setting of quantity 1');
        }
    }

    public function actionGetLocationPriceInfo() {
        $product_type_variant_id = $this->_request->get('product_type_variant_id');

        $location_price_collection = OSC::model('catalog/productType_variantLocationPrice')
            ->getCollection()
            ->addCondition('product_type_variant_id', $product_type_variant_id, OSC_Database::OPERATOR_EQUAL)
            ->load();

        $data = [];

        foreach ($location_price_collection as $location_price) {
            $data[] = [
                'location' => OSC::helper('core/country')->getNameByLocation($location_price->data['location_data']),
                'location_data' => $location_price->data['location_data'],
                'price' => OSC::helper('catalog/common')->integerToFloat($location_price->data['price']),
                'compare_at_price' => OSC::helper('catalog/common')->integerToFloat($location_price->data['compare_at_price']),
            ];
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }

    public function actionDeleteManyPriceOfLocation() {
        $location = $this->_request->get('location');
        $product_type_variant_ids = $this->_request->get('product_type_variant_ids') ?? [];

        try {
            $this->_deleteLocationPrice($location, $product_type_variant_ids);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'result' => 200,
            'message' => 'Delete many location price product type variant successfully'
        ]);
    }

    public function actionDeletePriceOfLocation() {
        $location = $this->_request->get('location');
        $product_type_id = intval($this->_request->get('product_type_id'));
        $product_type_variant_id = intval($this->_request->get('product_type_variant_id'));

        try {
            $product_type_variant_ids = $this->_getProductTypeVariantIds($product_type_id, [$product_type_variant_id]);
            $this->_deleteLocationPrice($location, $product_type_variant_ids);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'result' => 200,
            'message' => 'Delete location price product type variant #' . $product_type_variant_id . ' successfully'
        ]);
    }

    public function actionDeleteAllPriceOfLocation() {
        $location = $this->_request->get('location');

        try {
            $this->_deleteLocationPrice($location);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'result' => 200,
            'message' => 'Delete all price of location successfully'
        ]);
    }

    protected function _deleteLocationPrice($location_data, $product_type_variant_ids = []) {
        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();

        try {
            if (empty($location_data)) {
                throw new Exception('Location is empty');
            }

            if (count($product_type_variant_ids) === 0) {
                $location_prices = OSC::model('catalog/productType_variantLocationPrice')
                    ->getCollection()
                    ->addCondition('location_data', $location_data, OSC_Database::OPERATOR_EQUAL)
                    ->load();

            } else {
                $location_prices = OSC::model('catalog/productType_variantLocationPrice')
                    ->getCollection()
                    ->addCondition('location_data', $location_data, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->addCondition('product_type_variant_id', $product_type_variant_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                    ->load();

            }

            foreach ($location_prices as $location_price) {
                $location_price->delete();
            }

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            throw new Exception($ex->getMessage());
        }
    }

    protected function _getProductTypeVariantIds($product_type_id = 0, $product_type_variant_ids = []): array {
        $collection = OSC::model('catalog/productType_variant')
            ->getCollection()
            ->addField('id')
            ->addCondition('product_type_id', $product_type_id, OSC_Database::OPERATOR_EQUAL)
            ->load();
        foreach ($collection as $model) {
            $product_type_variant_ids[] = $model->getId();
        }

        return array_unique($product_type_variant_ids);
    }

    public function actionGetLocationName() {
        $location = $this->_request->get('location');

        try {
            $data = $location === '*' ? 'All Location' : OSC::helper('core/country')->getNameByLocation($location);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $this->_ajaxResponse([
            'result' => 200,
            'data' => $data
        ]);
    }

    protected function _showBestPriceForStore($best_price) {
        $best_price_data = '';

        if (!is_array($best_price) || count($best_price) < 1) {
            return $best_price_data;
        }

        foreach ($best_price as $location => $data) {
            $best_price_data .= ' ' . $location . ':' . OSC::helper('catalog/common')->integerToFloat(array_key_first($data)) . "\n";
        }

        return $best_price_data;
    }

}
