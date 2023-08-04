<?php

class Controller_Catalog_React_Common extends Abstract_Frontend_ReactApiController {
    public function actionDetectRoute() {
        $slug = trim($this->_request->get('slug'));

        $slug = OSC::helper('alias/common')->trimSlug($slug);

        try {
            $alias = OSC::helper('catalog/react_common')->getAliasBySlug($slug);

            if ($alias && $alias->isExists()) {
                $result = [
                    'ukey' => $alias->data['ukey'],
                    'module_key' => $alias->data['module_key'],
                    'slug' => $alias->data['slug'],
                    'lang_key' => $alias->data['lang_key'],
                    'destination' => $alias->data['destination']
                ];
                $this->sendSuccess($result);
            } else {
                $this->sendError('Module not found', $this::CODE_NOT_FOUND);
            }
        } catch (Exception $ex) {
            $this->sendError('Module not found', $this::CODE_NOT_FOUND);
        }
    }

    public function actionLogOptionsDesign()
    {
        try {
            $enable_abtest_tab = intval(OSC::helper('core/setting')->get('catalog/abtest/enable_tab')) === 1;

            if (!$enable_abtest_tab) {
                throw new Exception('Not abtest');
            }

            $list_options = $this->_request->get('list_options');
            if (!$list_options) {
                throw new Exception('Data options is incorrect');
            }

            $product_id = intval($this->_request->get('product_id'));
            if ($product_id < 1) {
                throw new Exception('Data product id is incorrect');
            }

            // get data to save
            $tracking_key = Abstract_Frontend_Controller::getTrackingKey();
            if (!$tracking_key) {
                throw new Exception('Data tracking key is incorrect');
            }

            $abtest_key = OSC::AB_VER4_TAB_PRODUCT['key'];

            $abtest_value = OSC::helper('frontend/frontend')->getValueAbtestTab();

            if (!$abtest_value) {
                throw new Exception('Data abtest value is incorrect');
            }

            $mongodb = OSC::core('mongodb');

            $mongo_collection = 'log_design_options';

            $data_filter = [
                'tracking_key' => $tracking_key,
                'product_id' => $product_id,
                'abtest' => $abtest_key . ':' . $abtest_value['value']
            ];

            $is_finished_form = $this->_request->get('is_finished_form');

            $data_request = [
                'list_options' => OSC::encode($list_options),
                'count_options' => (is_array($list_options) && !empty($list_options)) ? count($list_options) : 1,
                'modified_timestamp' => time(),
                'is_finished_form' => !$is_finished_form ? 0 : 1
            ];

            try {
                $mongodb->selectCollection($mongo_collection, 'product')->updateOne(
                    $data_filter,
                    [
                        '$set' => $data_request,
                        '$setOnInsert' => array_merge(
                            $data_filter,
                            ['added_timestamp' => time()]
                        )
                    ],
                    ['upsert' => true]
                );
            } catch (Exception $exception) {
                throw new Exception($exception->getMessage());
            }

            $this->sendSuccess('Success');
        } catch (Exception $exception) {
            $this->sendError('Error::' . $exception->getMessage());
        }
    }
}

