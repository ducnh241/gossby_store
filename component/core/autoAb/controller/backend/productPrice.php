<?php

class Controller_AutoAb_Backend_ProductPrice extends Abstract_AutoAb_Controller_Backend {

    public function __construct() {
        parent::__construct();

        if (OSC::isPrimaryStore()) {
            $this->checkPermission('autoAb/super|autoAb/productPrice|autoAb/productPrice/ab_semi_product');
        } else {
            static::notFound();
        }

        $this->getTemplate()
            ->setPageTitle('Manage AutoAB Product Price')
            ->setCurrentMenuItemKey('autoAb/productPrice')
            ->addBreadcrumb('Product Price', $this->getUrl('autoAb/backend_productPrice/list'));
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function actionList() {
        $config_collection = OSC::model('autoAb/productPrice_config')
            ->getCollection()
            ->sort('id', OSC_Database::ORDER_DESC)
            ->setPageSize(25)
            ->setCurrentPage($this->_request->get('page'))
            ->load();

        $this->output($this->getTemplate()->build('autoAb/productPrice/list', [
            'config_collection' => $config_collection
        ]));
    }

    public function actionViewTracking() {
        $config_id = $this->_request->get('id');

        $this->checkPermission('autoAb/super|autoAb/productPrice/view_tracking|autoAb/productPrice/ab_semi_product');

        $timestamp_range = OSC::helper('autoAb/common')->fetchTrackingListData($this->_request->get('range'));

        $data = [];

        if ($config_id > 0) {
            $condition = [
                'condition' => ['config_id = ' . $config_id],
                'params' => []
            ];

            if ($timestamp_range['time'][0] > 0) {
                $condition['condition'][] = 'added_timestamp >= ' . $timestamp_range['time'][0];
            }

            if ($timestamp_range['time'][1] > 0) {
                $condition['condition'][] = 'added_timestamp <= ' . $timestamp_range['time'][1];
            }

            $condition['condition'] = implode(' AND ', $condition['condition']);

            /* @var $DB OSC_Database */
            $DB = OSC::core('database');

            try {
                $model = OSC::model('autoAb/productPrice_config')->load($config_id);

                $product_type_variant_ids = OSC::helper('core/common')->parseProductTypeVariantIds($model->data['variant_data']);
                $title = $model->data['title'];

                $DB->query("SELECT * FROM `osc_auto_ab_product_price_tracking` WHERE {$condition['condition']}", null, 'fetch_ab_tracking');

                $trackings = $DB->fetchArrayAll('fetch_ab_tracking');

                foreach ($trackings as $tracking) {
                    $data[$tracking['price_ab_test']]['order_id'][] = $tracking['order_id'];
                    $data[$tracking['price_ab_test']]['item_id'][] = $tracking['order_item_id'];
                    if (in_array(intval($tracking['product_type_variant_id']), $product_type_variant_ids)) {
                        $data[$tracking['price_ab_test']]['total_sale'] += $tracking['quantity'];
                    }

                    if ($model->isSemitestConfig() &&
                        in_array(intval($tracking['product_id']), $model->data['fixed_product_ids'])
                    ) {
                        $data[$tracking['price_ab_test']]['total_sale'] += $tracking['quantity'];
                    }
                    $data[$tracking['price_ab_test']]['revenue'] += $tracking['revenue'];
                    $data[$tracking['price_ab_test']]['quantity'] += $tracking['quantity'];
                    $data[$tracking['price_ab_test']]['base_cost'] += $tracking['base_cost'];
                    $data[$tracking['price_ab_test']]['product_id'][$tracking['product_id']] += $tracking['quantity'];
                }
            } catch (Exception $ex) {
                $this->addMessage($ex->getCode() == 404 ? 'AB Test Product Price is not exist' : $ex->getMessage());
                static::redirect($this->getUrl('*/*/list'));
            }
        }

        $this->output($this->getTemplate()->build('autoAb/productPrice/viewTracking', [
            'data' => $data,
            'range' => $timestamp_range['range'],
            'name' => $title
        ]));
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function actionGetListProducts() {
        $keyword = intval($this->_request->get('keyword'));
        $config_type = intval($this->_request->get('config_type'));

        if ($config_type === Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST) {
            $condition = OSC_Database::OPERATOR_NOT_LIKE;
        } else {
            $condition = OSC_Database::OPERATOR_LIKE;
        }

        $products = OSC::model('catalog/product')
            ->getCollection()
            ->addField('product_id', 'title')
            ->addCondition('product_id', $keyword, OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_AND)
            ->addCondition('meta_data', 'campaign_config', $condition, OSC_Database::RELATION_AND)
            ->setLimit(20)
            ->load()
            ->toArray();

        $this->_ajaxResponse([
            'result' => $products
        ]);
    }

    /**
     * @throws OSC_Exception_Runtime
     */
    public function actionGetPostForm() {
        $config_id = $this->_request->get('id');
        $config_type = intval($this->_request->get('config_type'));

        $this->checkPermission('autoAb/super|autoAb/productPrice/' . ($config_id > 0 ? 'edit' : 'add') . '|autoAb/productPrice/ab_semi_product');

        $model = OSC::model('autoAb/productPrice_config');

        $selected_products = [];
        if ($config_id > 0) {
            try {
                $model = $model->load($config_id);

                if (!empty($model->data['fixed_product_ids'])) {
                    $selected_products = OSC::model('catalog/product')
                        ->getCollection()
                        ->addField('product_id', 'title')
                        ->addCondition('product_id', $model->data['fixed_product_ids'], OSC_Database::OPERATOR_IN)
                        ->load()
                        ->toArray();
                }

            } catch (Exception $ex) {
                $this->addMessage($ex->getMessage());
                static::redirect($this->getUrl('*/*/list'));
            }
        }

        if ($config_type === Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST) {
            $tpl_path = 'autoAb/productPrice/semitestPostForm';
        } else {
            $tpl_path = 'autoAb/productPrice/postForm';
        }

        $countries = ['*' => 'All Countries'] + OSC::helper('core/country')->getCountries();

        $this->output($this->getTemplate()->build($tpl_path, [
            'model' => $model,
            'countries' => $countries,
            'selected_products' => $selected_products,
        ]));
    }

    public function actionPost() {
        $config_id = $this->_request->get('id');
        $this->checkPermission('autoAb/super|autoAb/productPrice/' . ($config_id > 0 ? 'edit' : 'add') . '|autoAb/productPrice/ab_semi_product');

        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $model = OSC::model('autoAb/productPrice_config');

            try {
                $model = OSC::model('autoAb/productPrice_config')->load($config_id);
            } catch (Exception $ex) {
                //
            }

            $price_range = [];
            foreach ($this->_request->get('price_range') as $value) {
                $price_range[] = intval($value);
            }

            $fixed_product_ids = !empty($this->_request->get('fixed_product_ids')) ?
                array_map('intval', $this->_request->get('fixed_product_ids')) :
                [];
            $is_semitest_config = intval($this->_request->get('is_semitest_config'));

            $post_data = [
                'title' => trim($this->_request->get('title')),
                'config_type' => $is_semitest_config ?
                    Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_SEMITEST :
                    Model_AutoAb_ProductPrice_Config::CONFIG_TYPE_CAMPAIGN,
                'location_data' => $this->_request->get('location_data') ?? [],
                'variant_data' => $this->_request->get('variant_data') ?? [],
                'fixed_product_ids' => $fixed_product_ids ?? [],
                'fee' => trim($this->_request->get('fee')),
                'condition_type' => intval($this->_request->get('condition_type')),
                'begin_at' => trim($this->_request->get('begin_at')),
                'finish_at' => trim($this->_request->get('finish_at')),
                'price_range' => $price_range
            ];

            $model->setData($post_data)->save();
            if (!$is_semitest_config) {
                $model->saveFlattenData();
            }

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $error_message = $ex->getMessage();

            if (strpos($error_message, 'osc_auto_ab_product_price') !== false &&
                strpos($error_message, 'Integrity constraint violation: 1062 Duplicate entry') !== false
            ) {
                $error_message = 'Cannot ab test same product type variant in one location!';
            }

            $this->_ajaxError($error_message);
        }

        OSC::core('cache')->delete('countryABTestPrice');

        $this->_ajaxResponse([
            'result' => 200
        ]);
    }

    public function actionDelete() {
        $this->checkPermission('autoAb/super|autoAb/productPrice/delete|autoAb/productPrice/ab_semi_product');

        $id = intval($this->_request->get('id'));

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        $locked_key = OSC::makeUniqid();

        OSC_Database_Model::lockPreLoadedModel($locked_key);

        try {
            $model = OSC::model('autoAb/productPrice_config')->load($id);
            $title = $model->data['title'];
            $model->delete();

            $this->addMessage('Deleted the config auto ab test price #' . $title);

            $DB->commit();
        } catch (Exception $ex) {
            $DB->rollback();

            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            $this->addErrorMessage($ex->getMessage());
        }

        OSC_Database_Model::unlockPreLoadedModel($locked_key);

        static::redirect($this->getUrl('*/*/list'));
    }

    public function actionGetBaseCostConfig() {
        $variant_data = $this->_request->get('variant_data') ?? [];
        $country_codes = $this->_request->get('location_data') ?? [];

        $data = OSC::helper('catalog/product')->getBaseCostConfig($variant_data, $country_codes);

        $this->_ajaxResponse([
            'result' => 200,
            'data' => array_values($data)
        ]);
    }

    public function actionStopAbTestPrice() {
        $this->checkPermission('autoAb/super|autoAb/productPrice/edit|autoAb/productPrice/add|autoAb/productPrice/ab_semi_product');

        $config_id = intval($this->_request->get('config_id'));

        $stop_ab_options = intval($this->_request->get('stop_ab_options'));

        $DB = OSC::core('database')->getWriteAdapter();

        $DB->begin();

        try {
            $model_config = OSC::model('autoAb/productPrice_config')->load($config_id);

            OSC::model('autoAb/productPrice')->getCollection()
                ->addCondition('config_id', $config_id)
                ->load()
                ->delete();

            if ($model_config->data['status'] == Model_AutoAb_ProductPrice_Config::STATUS_OFF) {
                throw new Exception('This AB Test has already been stopped.');
            }

            $condition_type = $model_config->data['condition_type'];

            if ($condition_type === Model_AutoAb_ProductPrice_Config::CONDITION_CAMPAIGN) {
                if ($stop_ab_options != Model_AutoAb_ProductPrice_Config::STOP_OPTIONS_DO_SOME_THING) {
                    throw new Exception('No action is allowed when stopping AB tests for campaigns');
                }

                $model_config->setData(['status' => Model_AutoAb_ProductPrice_Config::STATUS_OFF])->save();
            } else {
                switch ($stop_ab_options) {
                    case Model_AutoAb_ProductPrice_Config::STOP_OPTIONS_DO_SOME_THING:
                        break;
                    case Model_AutoAb_ProductPrice_Config::STOP_OPTIONS_APPLY_THE_BEST_RESULT:
                        OSC::helper('autoAb/productPrice')->setBestPriceInCountry($model_config, 0);
                        break;
                    case Model_AutoAb_ProductPrice_Config::STOP_OPTIONS_CHOOSE_MANUALLY:
                        $option_manually = floatval($this->_request->get('manually_options'));

                        $price_range = $model_config->data['price_range'];

                        if (array_search($option_manually, $price_range) === false) {
                            throw new Exception('Price range ' . $option_manually . ' is not listed in the AB test.');
                        }

                        $ukey = 'setPriceAutoAb_' . $model_config->getId() . '_ALL';

                        try {
                            $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
                            $model_bulk_queue->delete();
                        } catch (Exception $ex) {
                            if ($ex->getCode() != 404) {
                                throw new Exception($ex->getMessage());
                            }
                        }

                        $option_manually_int = OSC::helper('catalog/common')->floatToInteger($option_manually);

                        OSC::model('catalog/product_bulkQueue')->setData([
                            'member_id' => 1,
                            'ukey' => $ukey,
                            'action' => 'setPriceAutoAb',
                            'queue_data' => [
                                'product_id' => 0,
                                'config_id' => $model_config->getId(),
                                'best_ab_test_price' => $option_manually_int,
                                'setting_mode' => $condition_type,
                                'revenue' => ['CHOOSE_MANUALLY' => $option_manually_int],
                                'country_codes' => $model_config->data['location_data'],
                                'log_price_ab_test' => ['CHOOSE_MANUALLY' => $option_manually_int]
                            ]
                        ])->save();

                        OSC::core('cron')->addQueue('catalog/campaign_setPriceAutoAbTest', null, ['ukey' => 'catalog/campaign_setPriceAutoAbTest', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
                        break;
                    default:
                        throw new Exception('No action is allowed when stopping AB tests for store');
                }

                $model_config->setData(['status' => Model_AutoAb_ProductPrice_Config::STATUS_OFF])->save();
            }

            $DB->commit();

            OSC::core('cache')->delete('countryABTestPrice');

            $this->_ajaxResponse();
        } catch (Exception $ex) {
            $DB->rollback();
            $this->_ajaxError($ex->getMessage());
        }
    }
}
