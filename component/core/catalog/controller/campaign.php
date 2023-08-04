<?php

class Controller_Catalog_Campaign extends Abstract_Core_Controller {
    public function actionGetMockupData()
    {
        try {
            $id = intval($this->_request->get('id'));

            if ($id < 1) {
                throw new Exception('Data is incorrect');
            }

            switch ($this->_request->get('type')) {
                case 'cart':
                    $line_item = OSC::model('catalog/cart_item')->load($id);

                    if ($line_item->getId() < 1) {
                        throw new Exception('Line item is not exists');
                    }

                    $campaign_data = $line_item->getCampaignData();
                    break;
                case 'order':
                    $line_item = OSC::model('catalog/order_item')->load($id);

                    if ($line_item->getId() < 1) {
                        throw new Exception('Line item is not exists');
                    }

                    $campaign_data = $line_item->getCampaignData();
                    break;
                default:
                    throw new Exception('Data is incorrect');
            }

            if ($campaign_data === null) {
                throw new Exception('Line item is not campaign');
            }

            try {
                $product = OSC::model('catalog/product')->load($line_item->data['product_id']);
                $campaign_config = $product->data['meta_data']['campaign_config']['print_template_config'];
            } catch (Exception $exception) {

            }

            $product_type_variant = OSC::model('catalog/productType_variant')->load($campaign_data['product_type_variant_id']);
            $product_type = OSC::model('catalog/productType')->load($product_type_variant->data['product_type_id']);

            $mockup_configs = [];

            $print_template = $campaign_data['print_template'];
            try {
                $source_print_template = OSC::model('catalog/printTemplate')->load($campaign_data['print_template']['print_template_id']);

                if (isset($source_print_template->data['config']['preview_config']) && !empty($source_print_template->data['config']['preview_config'])) {
                    $print_template['preview_config'] = $source_print_template->data['config']['preview_config'];
                }

                if (isset($source_print_template->data['config']['segments']) && !empty($source_print_template->data['config']['segments'])) {
                    $print_template['segments'] = $source_print_template->data['config']['segments'];
                }

                if (isset($source_print_template->data['config']['print_file']) && !empty($source_print_template->data['config']['print_file'])) {
                    $print_template['print_file'] = $source_print_template->data['config']['print_file'];
                }
            } catch (Exception $exception) {
                $print_template = $campaign_data['print_template'];
            }

            OSC::helper('catalog/campaign')->replaceLayerUrl($print_template['preview_config'], $campaign_data['product_type']['options']['keys']);

            foreach ($campaign_data['print_template']['segment_source'] as $segment_key => $segment_source) {
                $preview_config = [];
                foreach ($print_template['preview_config'] as $config_item) {
                    if (isset($config_item['config'][$segment_key]) && !empty($config_item['config'][$segment_key])) {
                        $preview_config = $config_item;
                        break;
                    }
                }

                $data = [
                    'title' => $preview_config['title'] ?? '',
                    'product_type' => $product_type->data['ukey'],
                    'design_key' => $segment_key,
                    'design' => '',
                    'preview_config' => $preview_config,
                    'segment_configs' => $print_template['segments'] ?? [],
                    'segment_sources' => isset($campaign_config) && !empty($campaign_config) ? $campaign_config[array_search($print_template['print_template_id'], array_column($campaign_config, 'print_template_id'))]['segments'] : $print_template['segment_source']
                ];

                switch ($segment_source['source']['type']) {
                    case 'personalizedDesign':
                        $data['design'] = [
                            'svg' => OSC::helper('catalog/react_common')->replaceSvgContent($segment_source['source']['svg'])
                        ];
                        break;
                    case 'image':
                        $image = OSC::model('catalog/campaign_imageLib_item')->load($segment_source['source']['image_id']);

                        $data['design'] = [
                            'url' => $image->getFileThumbUrl()
                        ];
                        break;
                    default:
                        break;
                }

                $mockup_configs[$segment_key] = $data;
            }

            $this->_ajaxResponse($mockup_configs);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionEditOrderDesign()
    {
        /* @var $line_item Model_Catalog_Order_Item */

        $line_item_id = intval($this->_request->get('item'));

        if ($line_item_id < 1) {
            $this->error('Line item ID is incorrect');
        }

        try {
            $line_item = OSC::model('catalog/order_item')->load($line_item_id);

            if ($line_item->getOrder()->getOrderUkey() != $this->_request->get('order')) {
                throw new Exception('You dont have permission to perform this action');
            }

            if (!$line_item->getOrder()->ableToEdit()) {
                throw new Exception('You dont have permission to perform this action');
            }

            $campaign_data_idx = $line_item->getCampaignDataIdx();

            if ($campaign_data_idx === null) {
                throw new Exception('Line item is not a campaign');
            }
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $new_config = $this->_request->get('config');

        if (is_array($new_config) && count($new_config) > 0) {
            try {
                $store_info = OSC::getStoreInfo();

                $request_data = [
                    'item_id' => $line_item->getId(),
                    'campaign_data' => OSC::helper('catalog/campaign')->orderLineItemVerifyNewDesignData($line_item->getOrderItemMeta()->data['custom_data'][$campaign_data_idx], $new_config, true, $line_item->data['product_id'], true),
                    'modifier' => $this->getAccount()->getId() > 0 ? (ucfirst($this->getAccount()->data['username'])) : $line_item->getOrder()->getFullName(),
                ];

                $response = OSC::core('network')->curl(
                    trim($store_info['master_store_url']) . '/catalog/api_order/updateCampaignPersonalizedDesign', [
                    'headers' => ['Osc-Api-Token' => $store_info['id'] . ':' . OSC_Controller::makeRequestChecksum(OSC::encode($request_data), $store_info['secret_key'])],
                    'json' => $request_data
                ]);

                if (!is_array($response['content']) || !isset($response['content']['result'])) {
                    throw new Exception('Response data is incorrect: ' . print_r($response['content'], 1));
                }

                if ($response['content']['result'] != 'OK') {
                    throw new Exception($response['content']['message']);
                }

                $this->_ajaxResponse();
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getMessage());
            }
        }

        $this->_ajaxResponse(OSC::helper('catalog/campaign')->orderLineItemGetDesignEditFrmData($line_item, $line_item->getOrderItemMeta()->data['custom_data'][$campaign_data_idx]));
    }
}
