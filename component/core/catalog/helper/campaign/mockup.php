<?php

class Helper_Catalog_Campaign_Mockup extends OSC_Object {

    const CATEGORY_MOCKUP = [
        'render' => 0,
        'tool_rerender' => 1
    ];

    protected $_list_mockup_rel = [];

    /**
     * @param Model_Catalog_Product_Variant $variant
     * @param Model_Catalog_PrintTemplate $print_template
     * @param Model_Catalog_Mockup $mockup
     */
    public function renderMockup(Model_Catalog_Product_Variant $variant, Model_Catalog_PrintTemplate $print_template, Model_Catalog_Mockup $mockup, $option = []) {
        if (!is_array($option)) {
            $option = [];
        }

        if (isset($this->_list_mockup_rel[$mockup->getId() . '_' . $print_template->getId()])) {
            $mockup_rel = $this->_list_mockup_rel[$mockup->getId() . '_' . $print_template->getId()];
        } else {
            $mockup_rel = OSC::model('catalog/printTemplate_mockupRel')->loadByUKey($mockup->getId() . '_' . $print_template->getId());
            $this->_list_mockup_rel[$mockup->getId() . '_' . $print_template->getId()] = $mockup_rel;
        }

        $mockup_rel->setPrintTemplate($print_template)->setMockup($mockup);

        return $this->getRenderCommands($mockup_rel, $variant, $option);
    }

    /**
     * @param Model_Catalog_PrintTemplate_MockupRel $mockup_rel
     * @param Model_Catalog_Product_Variant $variant
     * @return array
     * @throws OSC_Database_Model_Exception
     */
    public function getRenderCommands(Model_Catalog_PrintTemplate_MockupRel $mockup_rel, Model_Catalog_Product_Variant $variant, $option = []) {
        static $cached = [];

        if(! $variant->isCampaign()) {
            throw new Exception('The variant is not in campaign' . $variant->getId());
        }

        $product = $variant->getProduct();

        $print_template_config = null;

        $print_template_id = $mockup_rel->data['print_template_id'];

        $print_template = $mockup_rel->getPrintTemplate();

        if (isset($option['template_default']) && intval($option['template_default']) > 0){
            $print_template_id = intval($option['template_default']);

            try {
                $print_template = OSC::model('catalog/printTemplate')->load($print_template_id);
            }catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }

        foreach($product->data['meta_data']['campaign_config']['print_template_config'] as $config) {
            if($config['print_template_id'] == $print_template_id) {
                $print_template_config = $config;
                break;
            }
        }

        if(! $print_template_config) {
            throw new Exception('Missing print template config #' . $mockup_rel->data['print_template_id'] . ' in product #' . $product->getId());
        }

        $mockup = $mockup_rel->getMockup();

        if (isset($option['personalized_design_options']) && count($option['personalized_design_options']) > 0) {
            $cached_key = $product->getId() . '_' . $print_template->getId() . '_' . md5(OSC::encode($print_template_config['segments'])) . '_' . md5(OSC::encode($option['personalized_design_options']));
        } else {
            $cached_key = $product->getId() . '_' . $print_template->getId() . '_' . md5(OSC::encode($print_template_config['segments']));
        }

        $segments = $cached[$cached_key];

        if (!isset($segments) || !is_array($segments) || count($segments) < 1) {
            $personalized_design_options = [];

            if (isset($option['personalized_design_options']) && count($option['personalized_design_options']) > 0) {
                $personalized_design_options = $option['personalized_design_options'];
            }

            $segments = OSC::helper('catalog/campaign_design')->getSegmentRenderData($print_template, OSC::helper('catalog/campaign_design')->getSegmentSources($print_template_config['segments'], $personalized_design_options, ['skip_validate_config', 'render_mockup']));
            $cached[$cached_key] = $segments;
        }

        if (isset($option['default']) && $option['default'] == 1) {
            return OSC::helper('catalog/campaign_mockup_command')->parse($mockup->data['config'], $this->preCommandParams($segments, $variant->getProductTypeVariant()->getOptionValues()));
        }

        return [
            'segments' => $segments,
            'commands' => OSC::helper('catalog/campaign_mockup_command')->parse($mockup->data['config'], $this->preCommandParams($segments, $variant->getProductTypeVariant()->getOptionValues()))
        ];
    }

    public function preCommandParams($segments, $option_values) {
        $params = [];

        foreach($option_values['items'] as $option_item) {
            $params['variant_opt__' . $option_item['option_key']] = Model_Catalog_ProductType_Variant::removeOptKeyFromOptKeyValue($option_item['option_value_key']);
        }

        foreach($segments as $segment_key => $segment) {
            $params['segment_' . $segment_key . '__width'] = $segment['dimension']['width'];
            $params['segment_' . $segment_key . '__height'] = $segment['dimension']['height'];
        }

        return $params;
    }

    /**
     * @param $mockup_url
     * @param Model_Catalog_Product_Variant $variant
     * @param Model_Catalog_Product $product
     * @param Model_Catalog_PrintTemplate_MockupRel $mockup_rel
     * @param $version
     * @throws OSC_Database_Model_Exception
     * @throws Exception
     */
    public function uploadMockupStatic(
        $mockup_url,
        Model_Catalog_Product_Variant $variant,
        Model_Catalog_Product $product,
        Model_Catalog_PrintTemplate_MockupRel $mockup_rel,
        $version
    ) {

        $mockup_url_name = $mockup_url . '_'.filemtime(OSC_ROOT_PATH . '/' .str_replace(OSC_CMS_BASE_URL . '/', '', $mockup_url));
        $mockup_file_name = 'catalog/default_mockup/' . md5($mockup_url_name) . '.png';
        $mockup_file_name_s3 = OSC::core('aws_s3')->getStoragePath($mockup_file_name);
        $tmp_file_name = 'mockup_default/' . md5($mockup_url_name) . '.png';
        $tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_file_name);

        if (!OSC::core('aws_s3')->doesObjectExist($mockup_file_name_s3)) {
            if (!OSC::core('aws_s3')->doesObjectExist($tmp_file_name_s3)) {
                OSC::core('aws_s3')->tmpSaveFile($mockup_url, $tmp_file_name);
            }

            try {
                OSC::imageIsNotCorrupt(OSC_Storage::tmpGetFilePath($tmp_file_name));
            } catch (Exception $ex) {
                @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
                OSC::core('aws_s3')->delete($tmp_file_name_s3);
                throw new Exception($ex->getMessage());
            }

            OSC::core('aws_s3')->copy($tmp_file_name_s3, $mockup_file_name_s3);
        }

        $mockup_ukey = $product->getId() . '_' . $mockup_rel->getId() . '_' . md5($mockup_file_name) . '_' . $version;

        try {
            $image = OSC::model('catalog/product_image')->loadByUKey($mockup_ukey);
        }catch (Exception $ex) {
            if ($ex->getCode() != 404) {
                throw new Exception($ex->getMessage());
            }

            $image = OSC::model('catalog/product_image')->setData([
                'product_id' => $product->getId(),
                'ukey' => $mockup_ukey,
                'position' => $mockup_rel->data['position'],
                'flag_main' => $mockup_rel->data['flag_main'],
                'alt' => '',
                'filename' => $mockup_file_name,
                'is_static_mockup' => 1
            ])->save();
        }

        $print_template_id = $mockup_rel->data['print_template_id'];
        $image_id = $image->getId();

        $this->insertImageMockup($variant, $print_template_id, $image_id, $version);
    }

    public function insertImageMockup(Model_Catalog_Product_Variant $variant, $print_template_id, $image_id, $version, $option = []) {
        $meta_data = $variant->data['meta_data'];

        if (!isset($meta_data['campaign_config'])) {
            $meta_data['campaign_config'] = [];
        }

        if (!isset($meta_data['campaign_config']['image_ids']) || isset($meta_data['campaign_config']['convert_ornament_medallion_mdf'])) {
            $meta_data['campaign_config']['image_ids'] = [];
        }

        $matched_idx = false;

        foreach ($meta_data['campaign_config']['image_ids'] as $idx => $image_data) {
            if ($image_data['print_template_id'] == $print_template_id) {
                $matched_idx = $idx;
                break;
            }
        }

        $image_ids_delete = [];
        $image_ids_customer = [];

        if ($matched_idx === false) {
            $meta_data['campaign_config']['image_ids'][] = ['print_template_id' => $print_template_id, 'image_ids' => [$image_id],'image_ids_customer' => [] ,'version' => $version];
        } else {
            $version_old = 0;

            if (isset($meta_data['campaign_config']['image_ids'][$matched_idx]['version'])) {
                $version_old = intval($meta_data['campaign_config']['image_ids'][$matched_idx]['version']);
            }

            if ($meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids_customer'] == null || count($meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids_customer']) < 1) {
                $meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids_customer'] = [];
            }

            $image_ids_customer = array_merge($image_ids_customer,$meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids_customer']);

            if ($version_old < $version) {
                $image_ids_delete = array_merge($image_ids_delete, $meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids']);

                unset($meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids']);
                unset($meta_data['campaign_config']['image_ids'][$matched_idx]['version']);

                $meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids'][] = $image_id;
                $meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids'] = array_unique($meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids']);
                $meta_data['campaign_config']['image_ids'][$matched_idx]['version'] = $version;

            } elseif ($version_old == $version) {
                $meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids'][] = $image_id;
                $meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids'] = array_unique($meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids']);
                $meta_data['campaign_config']['image_ids'][$matched_idx]['version'] = $version;
            } else {
                $image_ids_delete[] = $image_id;
            }
        }

        $meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids'] = array_unique(array_merge($meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids'],$meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids_customer']));
        $variant->setData(['meta_data' => $meta_data])->save();

        foreach ($image_ids_delete as $key => $image_id_delete) {
            if (in_array($image_id_delete,$image_ids_customer)) {
                unset($image_ids_delete[$key]);
            }
        }

        if (count($image_ids_delete) > 0) {
            $images_collection = OSC::model('catalog/product_image')->getCollection()->load(array_unique($image_ids_delete));

            foreach ($images_collection as $model_image) {
                $model_image->delete();
            }
        }
    }
}
