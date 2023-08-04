<?php

class Controller_PersonalizedDesign_React_Common extends Abstract_Frontend_ReactApiController {
    public function actionMultiSvg() {
        /* @var $collection Model_PersonalizedDesign_Design_Collection */
        /* @var $model Model_PersonalizedDesign_Design */

        $configs = $this->_request->get('design');

        if (!is_array($configs) || count($configs) < 1) {
            $this->sendError('Data is incorrect', $this::CODE_BAD_REQUEST);
        }

        $options = [];

        if ($this->_request->get('trigger_flag')) {
            $options[] = 'render_personalized_trigger';
        }

        if ($this->_request->get('skip_validate_config')) {
            $options[] = 'skip_validate_config';
        }

        if (intval($this->_request->get('layer_data')) == 1) {
            $options[] = 'layer_data';
        }

        try {
            $design_ids = array_map(function($key) {
                return preg_replace('/^_+/', '', $key);
            }, array_keys($configs));
            $collection = OSC::helper('personalizedDesign/common')->getPersonalizedDesign($design_ids);

            $designs = [];

            foreach ($collection as $model) {
                $designs['_' . $model->getId()] = [
                    'svg' => OSC::helper('personalizedDesign/common')->renderSvg($model, is_array($configs['_' . $model->getId()]) ? $configs['_' . $model->getId()] : [], $options),
                    'document' => $model->data['design_data']['document']
                ];
            }

            $this->sendSuccess($this->_replaceCDNImage($designs));
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * @param $content
     * @return mixed
     * @throws Exception
     */
    protected function _replaceCDNImage($content) {
        if (!OSC::enableCDN()) {
            return $content;
        }

        if (is_array($content)) {
            $content = json_encode($content, JSON_UNESCAPED_SLASHES);
        }

        $s3_url = OSC::core('aws_s3')->getS3BucketUrl();
        $imagekit_url = OSC::systemRegistry('CDN_CONFIG')['imagekit_url'];

        $font_path = '/' . OSC::getStoreInfo()['store_id'] . '/storage/personalizedDesign/fonts';
        $s3_fonts_url = $s3_url . $font_path;
        $cdn_fonts_url = OSC::systemRegistry('CDN_CONFIG')['base_url'] . $font_path;

        /* Replace image s3 url to cdn url */
        $content = str_replace($s3_fonts_url, $cdn_fonts_url, $content);
        $content = str_replace($s3_url, $imagekit_url, $content);

        return OSC::decode($content);
    }

    public function actionSvg() {
        /* @var $model Model_PersonalizedDesign_Design */

        $id = intval($this->_request->get('id'));
        $order_line_item_id = intval($this->_request->get('order_line_item'));

        if ($id < 1) {
            $this->error('Design ID is incorrect', $this::CODE_BAD_REQUEST, ['log_code' => $this::CODE_PERSONALIZED_MISSING_PARAM]);
        }

        if ($order_line_item_id > 0) {
            try {
                $line_item = OSC::model('catalog/order_item')->load($order_line_item_id);
                $personalized_data_idx = OSC::helper('personalizedDesign/common')->fetchCustomDataIndex($line_item->data['custom_data']);

                if ($personalized_data_idx === null) {
                    throw new Exception('Line Item is not personalized');
                }

                if ($this->_request->get('output')) {
                    $this->_sendCrossDomainHeader();

                    $this->_sendHeaders(['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'no-cache, no-store, must-revalidate', 'Pragma' => 'no-cache', 'Expires' => '0']);

                    echo $line_item->data['custom_data'][$personalized_data_idx]['data']['design_svg'];
                    die;
                }

                $design = OSC::model('personalizedDesign/design')->load($line_item->data['custom_data'][$personalized_data_idx]['data']['design_id']);

                $document_type = $line_item->data['custom_data'][$personalized_data_idx]['data']['config_preview']['document_type']['value'];

                $ornament_type = OSC::helper('personalizedDesign/common')->fetchOrnamentType($design, $line_item->data['custom_data'][$personalized_data_idx]['data']['config']);

                if ($ornament_type) {
                    $document_type = 'ornament_' . $ornament_type;
                }

                $this->sendSuccess(['svg' => $line_item->data['custom_data'][$personalized_data_idx]['data']['design_svg'], 'document_type' => $document_type]);
            } catch (Exception $ex) {
                $this->sendError($ex->getMessage(), $ex->getCode());
            }
        }

        $options = [];

        if ($this->_request->get('trigger_flag')) {
            $options[] = 'render_personalized_trigger';
        }

        if ($this->_request->get('skip_validate_config')) {
            $options[] = 'skip_validate_config';
        }

        try {
            $model = OSC::model('personalizedDesign/design')->load($id);
            $custom_config = $this->_request->get('config');

            $svg = OSC::helper('personalizedDesign/common')->renderSvg($model, $custom_config, $options);

            if ($this->_request->get('output')) {
                $this->_sendCrossDomainHeader();

                $this->_sendHeaders(['Content-Type' => 'image/svg+xml', 'Cache-Control' => 'no-cache, no-store, must-revalidate', 'Pragma' => 'no-cache', 'Expires' => '0']);
                echo $svg;
                die;
            }

            $document_type = $model->data['design_data']['document']['type'];

            $ornament_type = OSC::helper('personalizedDesign/common')->fetchOrnamentType($model, $custom_config);

            if ($ornament_type) {
                $document_type = 'ornament_' . $ornament_type;
            }

            $size = $this->_request->get('size');

            if (strpos($document_type, 'canvas') != false) {
                $variant_id = intval($this->_request->get('variant_id'));

                if ($variant_id > 0) {
                    $variant = OSC::model('catalog/product_variant')->load($variant_id);
                    foreach ($variant->getOptions() as $value) {
                        if (strtolower($value['title']) == 'size') {
                            $size = $value['value'];
                            break;
                        }
                    }
                }
            }

            $this->sendSuccess(['svg' => $svg, 'document' => $model->data['design_data']['document'], 'document_type' => $document_type, 'size' => $size]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionSvgSemiTest() {
        /* @var $model Model_PersonalizedDesign_Design */
        $design_ids = $this->_request->get('design_ids');

        if (!is_array($design_ids) && count($design_ids) < 1) {
            $this->sendError('Design ID is incorrect', $this::CODE_BAD_REQUEST);
        }

        $options = [];

        if ($this->_request->get('trigger_flag')) {
            $options[] = 'render_personalized_trigger';
        }
        $custom_configs = $this->_request->get('config');
        $data_svg = [];

        try {
            foreach ($design_ids as $key => $id) {
                $model = OSC::model('personalizedDesign/design')->load($id);
                $custom_config = $custom_configs[$id];
                $data_svg[] = [
                    'svg' => OSC::helper('personalizedDesign/common')->renderSvg($model, $custom_config, $options),
                    'document' => $model->data['design_data']['document'],
                    'document_type' => $model->data['design_data']['document']['type'],
                    'title' => count($design_ids) > 1 ? ($key == 0 ? 'Front' : 'Back') : ''
                ];

            }

            $this->sendSuccess(['data_svg' => $data_svg]);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetSpotifyToken() {
        try {
            $refresh_token = $this->_request->get('refresh_token');
            $use_cache = $refresh_token == 1 ? false : true;
            $access_token = OSC::helper('personalizedDesign/spotify')->generateAccessToken($use_cache);

            $this->sendSuccess($access_token);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionMultiSvgByDefault() {
        /* @var $collection Model_PersonalizedDesign_Design_Collection */
        /* @var $model Model_PersonalizedDesign_Design */

        $configs = $this->_request->get('design');

        if (!is_array($configs) || count($configs) < 1) {
            $this->sendError('Data is incorrect', $this::CODE_BAD_REQUEST);
        }

        $options = ['skip_validate_config', 'is_live_preview'];

        if (intval($this->_request->get('layer_data')) == 1) {
            $options[] = 'layer_data';
        }

        try {
            $design_ids = array_map(function($key) {
                return preg_replace('/^_+/', '', $key);
            }, array_keys($configs));

            $collection = OSC::helper('personalizedDesign/common')->getPersonalizedDesign($design_ids);

            $designs = [];

            foreach ($collection as $model) {
                $model_id = $model->getId();
                $designs['_' . $model_id] = [
                    'svg' => OSC::helper('personalizedDesign/common')->renderSvg($model, isset($configs['_' . $model_id]) && is_array($configs['_' . $model_id]) ? $configs['_' . $model_id] : [], $options),
                    'document' => $model->data['design_data']['document']
                ];
            }

            $this->sendSuccess($this->_replaceCDNImage($designs));
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

}