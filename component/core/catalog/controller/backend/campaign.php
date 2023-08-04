<?php

class Controller_Catalog_Backend_Campaign extends Abstract_Catalog_Controller_Backend {

    public function __construct() {
        parent::__construct();

        $this->checkPermission('catalog/super|catalog/product');

        $this->getTemplate()
            ->setCurrentMenuItemKey('catalog/product')
            ->setPageTitle('Manage Products');
    }

    public function actionBulkUpdatePrice() {
        $collection = OSC::model('catalog/product')->getCollection()->addField('title', 'meta_data', 'discarded', 'listing');
        $collection->addCondition('meta_data', '"campaign_config":{"', OSC_Database::OPERATOR_LIKE)->load();

        $price_mapping = [
            'facemask-dpi' => ['price' => 13.99, 'compare_at_price' => 29.99],
            'facemask-cw' => ['price' => 11.99, 'compare_at_price' => 26.99]/*,
            'canvas-8x10' => ['price' => 26.95, 'compare_at_price' => 49.99],
            'canvas-11x14' => ['price' => 34.95, 'compare_at_price' => 74.99],
            'canvas-16x20' => ['price' => 45.95, 'compare_at_price' => 92.99],
            'canvas-20x24' => ['price' => 56.95, 'compare_at_price' => 128.99],
            'mug-11oz' => ['price' => 13.99, 'compare_at_price' => 24.99],
            'mug-15oz' => ['price' => 18.99, 'compare_at_price' => 34.99],
            'mug-twoTone' => ['price' => 16.99, 'compare_at_price' => 29.99],
            'mug-enamelCampfire' => ['price' => 21.99, 'compare_at_price' => 38.99],
            'mug-insulatedCoffee' => ['price' => 26.99, 'compare_at_price' => 46.99]*/
        ];

        $product_ids = [];

        foreach ($collection as $model) {
            if (!$model->isCampaignMode()) {
                continue;
            }

            $meta_data = $model->data['meta_data'];

            $min_price = null;
            $min_compare_at_price = null;

            foreach ($meta_data['campaign_config'] as $product_type => $config) {
                if(isset($price_mapping[$product_type])) {
                    if( isset($price_mapping[$product_type]['price'])) {
                        $meta_data['campaign_config'][$product_type]['price'] = $price_mapping[$product_type]['price'];
                    }

                    if( isset($price_mapping[$product_type]['compare_at_price'])) {
                        $meta_data['campaign_config'][$product_type]['compare_at_price'] = $price_mapping[$product_type]['compare_at_price'];
                    }
                }

                if ($min_price === null || $min_price > OSC::helper('catalog/common')->floatToInteger($meta_data['campaign_config'][$product_type]['price'])) {
                    $min_price = OSC::helper('catalog/common')->floatToInteger($meta_data['campaign_config'][$product_type]['price']);
                    $min_compare_at_price = OSC::helper('catalog/common')->floatToInteger($meta_data['campaign_config'][$product_type]['compare_at_price']);
                }
            }

            OSC::core('database')->update($model->getTableName(), ['meta_data' => OSC::encode($meta_data), 'price' => $min_price, 'compare_at_price' => $min_compare_at_price], 'product_id = ' . $model->getId());

            $product_ids[] = $model->getId();
        }

        OSC::core('database')->query("UPDATE " . OSC::model('catalog/product_variant')->getTableName(true) . " v, " . OSC::model('catalog/product')->getTableName(true) . " p SET v.price = p.price, v.compare_at_price = p.compare_at_price WHERE v.product_id = p.product_id AND v.product_id IN (" . implode(',', array_unique($product_ids)) . ")");

        echo 'DONE';
    }

    protected function _verifyCampaignData(&$campaign_data, $product = null) {
        if (!is_array($campaign_data['product_types']) || count($campaign_data['product_types']) < 1) {
            throw new Exception('Please choose a product type for campaign');
        }

        $personal_design_ids = [];
        $personal_designs = [];
        foreach ($campaign_data['campaign_config']['print_template_config'] as $item) {
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
                    $personal_designs[$design->getId()] = [
                        'id' => $design->getId(),
                        'image_url' => $design->getImageUrl()
                    ];
                }
            }

        }

        foreach ($campaign_data['campaign_config']['print_template_config'] as $key => $product_config) {

            if (!isset($product_config['segments']) || !is_array($product_config['segments']) || count($product_config['segments']) < 1) {
                throw new Exception('Print Template [#' . $product_config['title'] . '] is missing design data');
            }

            $printTemplateConfig = OSC::model('catalog/printTemplate')->load($product_config['print_template_id']);

            $this->_verifyDesignConfig($product_config, $printTemplateConfig->data['config']['segments'], $personal_designs);

            foreach ($product_config as $k => $v) {
                if (!in_array($k, ['selected_design', 'print_template_id', 'title', 'segments', 'apply_other_face'], true)) {
                    unset($product_config[$k]);
                }
            }

            $campaign_data['campaign_config']['print_template_config'][$key] = $product_config;
        }
    }

    protected function _verifyDesignConfig($productConfig, $printTemplateConfig, $personalDesigns) {
        $buff = [];

        foreach ($productConfig['segments'] as $design_key => $value) {

            if (!isset($printTemplateConfig[$design_key])) {
                continue;
            }

            $design_data = $productConfig['segments'][$design_key]['source'];

            if (!isset($design_data['type'])) {
                throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] is missing type");
            }

            $allowed_keys = ['type', 'position', 'dimension', 'rotation', 'timestamp', 'orig_size'];

            if ($design_data['type'] == 'personalizedDesign') {
                if (!isset($design_data['design_id'])) {
                    throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] is missing design id");
                }

                $design_data['design_id'] = intval($design_data['design_id']);

                if ($design_data['design_id'] < 1) {
                    throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] is missing design id");
                }

                if (!isset($personalDesigns[$design_data['design_id']])) {
                    throw new Exception('Personalized design is not exists');
                }

                $design_data['url'] = $personalDesigns[$design_data['design_id']]['image_url'];

                if (isset($design_data['option_default_values'])) {
                    if (!is_array($design_data['option_default_values']) || !isset($design_data['option_default_values']['options']) || !is_array($design_data['option_default_values']['options']) || count($design_data['option_default_values']['options']) < 1) {
                        unset($design_data['option_default_values']);
                    } else {
                        foreach ($design_data['option_default_values']['options'] as $idx => $value) {
                            $value = trim($value);

                            if (!$value) {
                                unset($design_data['option_default_values']['options'][$idx]);
                            } else {
                                $design_data['option_default_values']['options'][$idx] = $value;
                            }
                        }

                        if (count($design_data['option_default_values']['options']) < 1) {
                            unset($design_data['option_default_values']);
                        } else {
                            $design_data['option_default_values'] = ['options' => $design_data['option_default_values']['options']];
                        }
                    }
                }

                $allowed_keys[] = 'design_id';
                $allowed_keys[] = 'url';
                $allowed_keys[] = 'option_default_values';
            } else if ($design_data['type'] == 'image') {
                if (!isset($design_data['image_id'])) {
                    throw new Exception("Print template [".$productConfig['title']."] :: Design key [{$design_key}] is missing item id");
                }

                $design_data['image_id'] = intval($design_data['image_id']);

                if ($design_data['image_id'] < 1) {
                    throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] is missing item id");
                }

                try {
                    $file_item = OSC::model('catalog/campaign_imageLib_item')->load($design_data['image_id']);

                    if ($file_item->getId() < 1) {
                        throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] item is not exists");
                    } else if ($file_item->data['item_type'] != 'file') {
                        throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] item is not file type");
                    }
                } catch (Exception $ex) {
                    throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] unable to load personalized design [{$design_data['design_id']}] => " . $ex->getMessage());
                }

                $design_data['url'] = $file_item->getFileThumbUrl();

                $allowed_keys[] = 'image_id';
                $allowed_keys[] = 'url';
            } else {
                throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] have a incorrect value for type");
            }

            if (!isset($design_data['orig_size']) || !is_array($design_data['orig_size']) || !isset($design_data['orig_size']['width']) || !isset($design_data['orig_size']['height'])) {
                throw new Exception("Print template [".$productConfig['title']."] :: Design key [{$design_key}] is missing original size");
            }

            $design_data['orig_size'] = [
                'width' => intval($design_data['orig_size']['width']),
                'height' => intval($design_data['orig_size']['height'])
            ];

            if ($design_data['orig_size']['width'] < 1 || $design_data['orig_size']['height'] < 1) {
                throw new Exception("[".$productConfig['title']."] :: Design key [{$design_key}] have a incorrect value for original size");
            }

            if (!isset($design_data['dimension']) || !is_array($design_data['dimension']) || !isset($design_data['dimension']['width']) || !isset($design_data['dimension']['height'])) {
                throw new Exception("Print template [".$productConfig['title']."] :: Design key [{$design_key}] is missing dimension");
            }

            $design_data['dimension'] = [
                'width' => intval($design_data['dimension']['width']),
                'height' => intval($design_data['dimension']['height'])
            ];

            if ($design_data['dimension']['width'] < 1 || $design_data['dimension']['height'] < 1) {
                throw new Exception("Print template [".$productConfig['title']."] :: Design key [{$design_key}] have a incorrect value for size");
            }

            if (!isset($design_data['position']) || !is_array($design_data['position']) || !isset($design_data['position']['x']) || !isset($design_data['position']['y'])) {
                throw new Exception("Print template [".$productConfig['title']."] :: Design key [{$design_key}] is missing position");
            }

            $design_data['position'] = [
                'x' => intval($design_data['position']['x']),
                'y' => intval($design_data['position']['y'])
            ];

            $design_data['rotation'] = floatval($design_data['rotation']);

            foreach ($design_data as $k => $v) {
                if (!in_array($k, $allowed_keys, true)) {
                    unset($design_data[$k]);
                }
            }

            $buff[$design_key] = $design_data;
        }

        $data = $buff;
    }

    protected function _processPostVariantsImages($product, $images_data) {
        $map_variant_image_ids = [];
        $product_id = $product->getId();

        foreach ($images_data as $key => $image_data) {
            $image_id = intval($image_data['image_id']);
            if ($image_id > 0) {
                try {
                    OSC::model('catalog/product_image')->load($image_id);
                } catch (Exception $ex) {
                    throw new Exception($ex->getMessage());
                }
            } else {
                //add image Product
                $mockup_file_name = 'catalog/customer_mockup/' . $product_id . '/' . md5($image_data['url']) . '.png';
                $mockup_file_name_s3 = OSC::core('aws_s3')->getStoragePath($mockup_file_name);

                if (!OSC_Storage::tmpUrlIsExists($image_data['url'])) {
                    $this->_ajaxError('Image url error ' . $image_data['url']);
                }

                $tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($image_data['url']);
                $tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_file_name);

                if (!OSC::core('aws_s3')->doesObjectExist($mockup_file_name_s3)) {
                    try {
                        OSC::imageIsNotCorrupt(OSC_Storage::tmpGetFilePath($tmp_file_name));
                    } catch (Exception $ex) {
                        @unlink(OSC_Storage::tmpGetFilePath($tmp_file_name));
                        OSC::core('aws_s3')->delete($tmp_file_name_s3);
                        throw new Exception($ex->getMessage());
                    }

                    OSC::core('aws_s3')->copy($tmp_file_name_s3, $mockup_file_name_s3);
                }

                $mockup_ukey = $product_id . '_' . md5($mockup_file_name);

                $image = OSC::model('catalog/product_image');

                try {
                    $image->setData([
                        'product_id' => $product_id,
                        'ukey' => $mockup_ukey,
                        'position' => intval(-3 - $key),
                        'flag_main' => 0,
                        'alt' => '',
                        'filename' => $mockup_file_name,
                        'is_static_mockup' => 2
                    ])->save();
                } catch (Exception $ex) {
                    if (strpos($ex->getMessage(), '1062 Duplicate entry') !== false) {
                        $image->loadByUKey($mockup_ukey);
                    } else {
                        throw new Exception($ex->getMessage());
                    }
                }

                $image_id = $image->getId();
            }

            foreach ($image_data['variant_ids'] as $variant_id) {
                if ($map_variant_image_ids[$variant_id] == null) {
                    $map_variant_image_ids[$variant_id] = [$image_id];
                } else {
                    $map_variant_image_ids[$variant_id] = array_merge($map_variant_image_ids[$variant_id], [$image_id]);
                }

            }
        }

        $image_id_total_after_save = [];
        $images_delete = [];
        $variant_collection = $product->getVariants();

        $variantForSave = [];
        foreach ($variant_collection as $variant) {
            $variantForSave[$variant->data['id']] = $map_variant_image_ids[$variant->data['product_type_variant_id']];
        }

        foreach ($variantForSave as $variant_id => $image_ids_new) {
            $variant = $variant_collection->getItemByKey($variant_id);

            if (!($variant instanceof Model_Catalog_Product_Variant)) {
                throw new Exception('Variant error by id#' . $variant_id);
            }

            $images_ids_in_variant = $variant->getImagesByPrintTemplate();

            if (!is_array($images_ids_in_variant)) {
                $images_ids_in_variant = [];
            }

            $images_customer_in_variant_new = [];

            foreach ($image_ids_new as $image_id) {
                $images_customer_in_variant_new[] = intval($image_id);
            }

            $images_customer_in_variant_old = [];

            $images_collection_customer_in_variant_old = $variant->getImagesByCustomerUpload();

            if ($images_collection_customer_in_variant_old->length() > 0) {
                $images_customer_in_variant_old = $images_collection_customer_in_variant_old->getKeys();
            }

            $image_add = [];

            foreach ($images_ids_in_variant as $print_template => $v){
                //gop image_id customer up len voi nhung image_ids hien tai
                $image_add[$print_template] = array_unique(array_merge($images_customer_in_variant_new,$images_ids_in_variant[$print_template]));
            }

            //loc ra image can xoa của variant
            $images_in_variant_delete = array_diff($images_customer_in_variant_old,$images_customer_in_variant_new);

            foreach ($image_add as $print_template => $v) {
                //loc ra image de add vao variant sau  khi loai bo image customer xoa di
                $image_add[$print_template] = array_unique(array_diff($image_add[$print_template], $images_in_variant_delete));
            }

            $print_template_ids = null;

            $variants_print_templates_map = OSC::helper('catalog/campaign')->getVariantsPrintTemplatesMap($product);

            $variant_ids_in_map = [];

            foreach ($variants_print_templates_map as $value) {
                if ($value['variant_id'] != $variant->getId()) {
                    continue;
                }

                if (in_array($variant->getId(),$variant_ids_in_map)) {
                    $variant->reload();
                }

                $variant_meta_data = $variant->data['meta_data'];

                if (!isset($variant_meta_data['campaign_config'])) {
                    $variant_meta_data['campaign_config'] = [];
                }

                if (!isset($variant_meta_data['campaign_config']['image_ids'])) {
                    $variant_meta_data['campaign_config']['image_ids'] = [];
                }

                $matched_idx = false;

                foreach ($variant_meta_data['campaign_config']['image_ids'] as $idx => $image_data) {
                    if ($image_data['print_template_id'] ==  $value['print_template_id']){
                        $matched_idx = $idx;
                        break;
                    }
                }

                if ($image_add[$value['print_template_id']] == null) {
                    $image_add[$value['print_template_id']]= $images_customer_in_variant_new;
                }

                if ($matched_idx === false) {
                    $variant_meta_data['campaign_config']['image_ids'][] = ['print_template_id' => $value['print_template_id'], 'image_ids' => array_values($image_add[$value['print_template_id']]),'image_ids_customer' => array_unique($images_customer_in_variant_new), 'version' => 0];
                }else {
                    $variant_meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids'] = array_values($image_add[$value['print_template_id']]);
                    $variant_meta_data['campaign_config']['image_ids'][$matched_idx]['image_ids_customer'] = array_unique($images_customer_in_variant_new);
                }

                $variant_ids_in_map[] = $variant->getId();
                $variant->setData(['meta_data' => $variant_meta_data])->save();

                //lay ra tat ca image sau khi save
                $image_id_total_after_save = array_merge($image_id_total_after_save,array_values($image_add[$value['print_template_id']]));
            }

            //save image_id can xoa vao mang xoa tong
            $images_delete = array_merge($images_delete,$images_in_variant_delete);
        }

        $images_delete = array_unique($images_delete);
        $image_id_total_after_save = array_unique($image_id_total_after_save);

        foreach ($images_delete as $key => $image_id) {
            if (in_array($image_id,$image_id_total_after_save)) {
                unset($images_delete[$key]);
            }
        }

        if (count($images_delete) > 0) {
            try {
                $collection = OSC::model('catalog/product_image')->getCollection()->setLimit(count($images_delete))->load($images_delete);
                foreach ($collection as $model) {
                    $model->delete();
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

        }
    }

    protected function _processPostVariantsVideos($product, $mockup_videos) {
        $old_video_ids = [];
        $new_video_ids = [];
        $variant_video_mapping = [];

        foreach ($mockup_videos as $key => $video) {
            if (intval($video['video_id']) == 0) {
                if (!OSC_Storage::tmpUrlIsExists($video['url'])) {
                    $this->_ajaxError('Video url error ' . $video['url']);
                }

                if ($video['thumbnail'] && !OSC_Storage::tmpUrlIsExists($video['thumbnail'])) {
                    $this->_ajaxError('Video thumbnail url error ' . $video['thumbnail']);
                }

                $video_file_extension = preg_replace('/^.*(\.[a-zA-Z0-9]+)$/', '\\1', $video['url']);
                $video_file_extension = strtolower($video_file_extension);

                $video_file_name = 'catalog/mockup_video/' . $product->getId() . '/' . md5($video['url']) . $video_file_extension;
                $video_file_name_s3 = OSC::core('aws_s3')->getStoragePath($video_file_name);

                $video_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['url']);
                $video_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($video_tmp_file_name);

                if (!OSC::core('aws_s3')->doesObjectExist($video_file_name_s3)) {
                    OSC::core('aws_s3')->copy($video_tmp_file_name_s3, $video_file_name_s3);
                }

                $thumbnail_file_name = '';

                if ($video['thumbnail']) {
                    $thumbnail_file_name = 'catalog/mockup_thumbnail/' . $product->getId() . '/' . md5($video['thumbnail']) . '.png';
                    $thumbnail_file_name_s3 = OSC::core('aws_s3')->getStoragePath($thumbnail_file_name);

                    $thumbnail_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['thumbnail']);
                    $thumbnail_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($thumbnail_tmp_file_name);

                    if (!OSC::core('aws_s3')->doesObjectExist($thumbnail_file_name_s3)) {
                        OSC::core('aws_s3')->copy($thumbnail_tmp_file_name_s3, $thumbnail_file_name_s3);
                    }
                }

                try {
                    $video_ukey = $product->getId() . '_' . md5($video_file_name);
                    $model = OSC::model('catalog/product_image')->loadByUKey($video_ukey);
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        $this->_ajaxError($ex->getMessage());
                    }

                    $model = OSC::model('catalog/product_image')->setData([
                        'product_id' => $product->getId(),
                        'width' => intval($video['duration']),
                        'height' => 0,
                        'duration' => intval($video['duration']),
                        'ukey' => $video_ukey,
                        'position' => intval($key),
                        'flag_main' => 0,
                        'alt' => '',
                        'filename' => $video_file_name,
                        'thumbnail' => $thumbnail_file_name,
                        'is_static_mockup' => 3,
                    ])->save();
                }

                $video_id = $model->getId();
            } else {
                $video_id = intval($video['video_id']);

                try {
                    $model = OSC::model('catalog/product_image')->load($video_id);

                    $old_thumbnail = $model->getS3Thumbnail();

                    if ($old_thumbnail != $video['thumbnail']) {
                        // Update new video thumbnail
                        $thumbnail_file_name = null;

                        if ($video['thumbnail']) {
                            try {
                                if (!OSC_Storage::tmpUrlIsExists($video['thumbnail'])) {
                                    throw new Exception('Video thumbnail url error ' . $video['thumbnail']);
                                }

                                $thumbnail_file_name = 'catalog/mockup_thumbnail/' . $product->getId() . '/' . md5($video['thumbnail']) . '.png';
                                $thumbnail_file_name_s3 = OSC::core('aws_s3')->getStoragePath($thumbnail_file_name);

                                $thumbnail_tmp_file_name = OSC::core('aws_s3')->tmpGetFileNameFromUrl($video['thumbnail']);
                                $thumbnail_tmp_file_name_s3 = OSC::core('aws_s3')->getTmpFilePath($thumbnail_tmp_file_name);

                                if (!OSC::core('aws_s3')->doesObjectExist($thumbnail_file_name_s3)) {
                                    OSC::core('aws_s3')->copy($thumbnail_tmp_file_name_s3, $thumbnail_file_name_s3);
                                }
                            } catch (Exception $ex) {
                                throw new Exception($ex->getMessage(), 400);
                            }
                        }

                        $model->setData([
                            'thumbnail' => $thumbnail_file_name,
                        ])->save();
                    }
                } catch (Exception $ex) {
                    $this->_ajaxError($ex->getMessage());
                }
            }

            $new_video_ids[] = $video_id;

            foreach ($video['variant_ids_position'] as $variant_id => $position) {
                if (!is_array($variant_video_mapping[$variant_id])) {
                    $variant_video_mapping[$variant_id] = [];
                }
                $variant_video_mapping[$variant_id][$video_id] = $position;
            }
        }

        $variants = $product->getVariants();

        foreach ($variants as $variant) {
            $variant_video_ids = $variant->data['video_id'] ?? [];
            $meta_data = is_array($variant->data['meta_data']) ? $variant->data['meta_data'] : OSC::decode($variant->data['meta_data']);

            if (count($variant_video_ids)) {
                $old_video_ids = array_merge($old_video_ids, $variant_video_ids);
            }

            $post_video_ids = array_keys($variant_video_mapping[$variant->data['product_type_variant_id']]);
            $meta_data['video_config']['position'] = $variant_video_mapping[$variant->data['product_type_variant_id']] ?? [];

            if (
                count(array_diff($variant_video_ids, $post_video_ids)) ||
                count(array_diff($post_video_ids, $variant_video_ids))
            ) {
                $variant->setData([
                    'video_id' => $post_video_ids,
                    'meta_data' => $meta_data,
                ])->save();
            } else if (!is_array($post_video_ids)) {
                $variant->setData([
                    'meta_data' => $meta_data,
                ])->save();
            }
        }

        $video_for_delete = array_diff($old_video_ids, $new_video_ids);

        if (count($video_for_delete) > 0) {
            try {
                $video_for_delete = array_unique($video_for_delete);
                $collection = OSC::model('catalog/product_image')->getCollection()->setLimit(count($video_for_delete))->load($video_for_delete);
                foreach ($collection as $model) {
                    $model->delete();
                }
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }
    }

    public function actionRerenderMockupById() {
        if (!$this->checkPermission('catalog/product/renderMockupByListProduct')) {
            echo 'No permission';
            die;
        }

        $product_ids = $this->_request->get('id');
        $product_ids = explode(',', $product_ids);

        foreach ($product_ids as $key =>  $product_id) {
            if(intval($product_id) <= 0){
                unset($product_ids[$key]);
            } else {
                $product_ids[$key] = intval($product_id);
            }
        }

        if (count($product_ids) > 0) {
            try {
                $result = OSC::helper('catalog/campaign_common')->reRenderProductId(array_unique($product_ids), $this->getAccount());

                $this->addMessage('Rerender task has been appended to queue [' . $result['total_appended'] . ']');
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getMessage());
            }
        }

        $this->getTemplate()->setPageTitle('Rerender mockup by product ids');
        $this->output($this->getTemplate()->build('catalog/campaign/reRenderMockupFrom'));
    }

    public function actionPost() {
        $id = intval($this->_request->get('id'));
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/' . ($id > 0 ? 'edit' : 'add'));

        /* @var $model Model_Catalog_Product */
        $model = OSC::model('catalog/product');

        $campaignConfigBuilder = [];

        $list_path = 'list';

        if ($id > 0) {
            $this->getTemplate()
                ->addBreadcrumb(array('user', 'Edit Product'));
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->addErrorMessage($ex->getCode() == 404 ? 'Campaign is not exist' : $ex->getMessage());
                static::redirectLastListUrl($this->getUrl($list_path));
            }

            if (!$model->isCampaignMode()) {
                static::redirect($this->getUrl('catalog/backend_product/post', ['id' => $model->getId()]));
            }

            $productTypes = explode(',',str_replace(' ', '',$model->data['product_type']));

        } else {
            $this->getTemplate()
                ->addBreadcrumb(array('user', 'Create Product'));
        }

        if ($model->getId() > 0 && $model->checkMasterLock()) {
            $this->addErrorMessage('You do not have the right to perform this function');
            static::redirect($this->getUrl('*/*/' . $list_path));
        }

        $timestamp_old = [];
        if ($model->getId() > 0) {
            $campaign_data_old = $model->data['meta_data'];

            foreach ($campaign_data_old['campaign_config']['print_template_config'] as $product_config) {
                foreach ($product_config['segments'] as $key => $value) {
                    $timestamp_old[$product_config['print_template_id']][$key] = $value['source']['timestamp'];
                }
            }
        }

        if ($this->_request->get('state')) {
            try {
                $campaign_data = OSC::decode($this->_request->getRaw('campaign_data'));
                $timestamp_new = [];
                $tab_flag = 0;
                $print_apply_other_face = [];

                $design_ids = [];
                foreach ($campaign_data['campaign_config']['print_template_config'] as $product_config_new) {
                    if ($product_config_new['apply_other_face'] == 1 && count($product_config_new['segments']) > 1) {
                        $print_apply_other_face[] = $product_config_new['print_template_id'];
                    }

                    foreach ($product_config_new['segments'] as $key => $value) {
                        $timestamp_new[$product_config_new['print_template_id']][$key] = $value['source']['timestamp'];
                        if (isset($value['source']['type']) && $value['source']['type'] === "personalizedDesign") {
                            $design_ids[] = $value['source']['design_id'];
                        }
                    }
                }

                $check_design_tab_collection = OSC::model('personalizedDesign/design')->getCollection()
                    ->addField('design_id', 'tab_flag')
                    ->addCondition('tab_flag', 1, OSC_Database::OPERATOR_EQUAL)
                    ->addCondition('design_id', $design_ids, OSC_Database::OPERATOR_IN)
                    ->load();
                if ($check_design_tab_collection->length() > 0) {
                    $tab_flag = 1;
                }

                $model->setData(['type_flag' => 0]);

                $campaign_data['campaign_config']['tab_flag'] = $tab_flag;

                $this->_verifyCampaignData($campaign_data, $model);

                $list_product_tags = OSC::helper('filter/common')->getListTagSettingProduct();

                $product_tag_selected = $model->getProductTagSelected();

                $list_member_display_live_preview = OSC::helper('core/setting')->get('catalog/product/live_preview_members_vendor');
                $list_member_display_live_preview = OSC::model('user/member')
                    ->getCollection()
                    ->addField('username')
                    ->addCondition('member_id', $list_member_display_live_preview, OSC_Database::OPERATOR_IN)
                    ->load()
                    ->toArray();

                if ($this->_request->get('state') === 'save') {

                    $default_product_price = 0;
                    $default_product_compare_at_price = 0;

                    $product_types = [];
                    $productVariants = [];
                    foreach ($campaign_data['product_types'] as $product_key => $config) {
                        $product_types[] = $product_key;
                        if(count($config['product_variant']) > 0) {
                            foreach ($config['product_variant'] as $item) {
                                $productVariants[] = $item;

                                /* Save the lowest price to price of product table */
                                if (($default_product_price === 0 || $item['price'] < $default_product_price) && $item['price'] !== null && $item['price'] > 0) {
                                    $default_product_price = $item['price'];
                                    $default_product_compare_at_price = $item['compare_at_price'];
                                }
                            }
                        }
                    }

                    $data = [
                        'product_type' => implode(', ', $product_types),
                        'price' => $default_product_price,
                        'compare_at_price' => $default_product_compare_at_price,
                        'options' => []
                    ];

                    $data['title'] = $this->_request->get('title');
                    $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
                    $data['description'] = $this->_request->getRaw('description');
                    $data['content'] = ''; //$this->_request->getRaw('content');
                    $data['position_index'] = $this->_request->get('position_index');
                    $data['upc'] = $this->_request->get('upc');
                    $data['vendor'] = $this->_request->get('vendor');

                    // get SEO PRODUCT to save meta_data
                    $seo_title = trim($this->_request->get('seo-title'));
                    $seo_description = trim($this->_request->get('seo-description'));
                    $seo_keyword = trim($this->_request->get('seo-keyword'));
                    $seo_image = $this->_request->get('seo-image');

                    $is_buy_design = intval($this->_request->get('is_buy_design')) == 1 ? 1 : 0;
                    $buy_design_price = $this->_request->get('buy_design_price') !== '' ? doubleval($this->_request->get('buy_design_price', 0)) * 100 : '';
                    $data['topic'] = trim($this->_request->get('topic'));

                    $data['meta_tags'] = [
                        'title' => $seo_title,
                        'description' => $seo_description,
                        'keywords' => $seo_keyword,
                        'image' => $seo_image
                    ];

                    if (isset($model->data['meta_tags']['is_clone']) && intval($model->data['meta_tags']['is_clone']) > 0) {
                        $data['meta_tags']['is_clone'] = $model->data['meta_tags']['is_clone'];
                    }

                    if (!$data['slug']) {
                        $data['slug'] = OSC::core('string')->cleanAliasKey($data['topic'] . '-' . $data['title']);
                    }


                    if (isset($model->data['meta_tags']['is_clone']) && intval($model->data['meta_tags']['is_clone']) > 0) {
                        if (($data['topic'] != $model->data['topic']) || ($data['title'] != $model->data['title'])) {
                            $data['slug'] = OSC::core('string')->cleanAliasKey($data['topic'] . '-' . $data['title']);
                            unset($data['meta_tags']['is_clone']);
                        }
                    }

                    $meta_image =  OSC::helper('backend/backend/images/common')->saveMetaImage(
                        $data['meta_tags']['image'],
                        $model->data['meta_tags']['image'],
                        'meta/campaign/',
                        'campaign'
                    );

                    if ($meta_image['data_meta_image']){
                        $data['meta_tags']['image'] = $meta_image['data_meta_image'];
                    }

                    if(isset($campaign_data['other_option']) && is_array($campaign_data['other_option']) && count($campaign_data['other_option']) > 0) {
                        $other_option = $campaign_data['other_option'];
                        unset($campaign_data['other_option']);
                        $campaign_data['config'][] = $other_option;
                    }

                    $data['meta_data'] = ($id > 0 && is_array($model->data['meta_data'])) ? $model->data['meta_data'] : [];
                    $data['meta_data']['campaign_config'] = $campaign_data['campaign_config'];
                    $data['meta_data']['buy_design'] = [
                        'is_buy_design' => $is_buy_design,
                        'buy_design_price' => $buy_design_price
                    ];

                    if (OSC::helper('core/setting')->get('catalog/product_default/listing_admin') != 1 ||  $this->checkPermission('catalog/super|catalog/product/full|catalog/product/listing',false) == true) {
                        $data['listing'] = $this->_request->get('listing', 0);
                    }

                    if ($id > 0) {
                        if (OSC::helper('core/setting')->get('catalog/product_default/listing_admin') != 1 || $this->checkPermission('catalog/super|catalog/product/full|catalog/product/listing', false) == true) {
                            $data['listing'] = $this->_request->get('listing', 0);
                        }
                    }else{
                        if (OSC::helper('core/setting')->get('catalog/product_default/listing_admin') != 1 || $this->checkPermission('catalog/super|catalog/product/full|catalog/product/listing', false) == true) {
                            $data['listing'] = $this->_request->get('listing', 0);
                        }else{
                            $data['listing'] = OSC::helper('core/setting')->get('catalog/product_default/listing');
                        }
                        $data['member_id'] = OSC::helper('user/authentication')->getMember()->getId();
                        $data['selling_type'] = Model_Catalog_Product::TYPE_CAMPAIGN;
                    }

                    if ($this->getAccount()->isAdmin()) {
                        $data['discarded'] = $this->_request->get('discarded', 0);
                    }

                    $data['collection_ids'] = $this->_request->get('collection_ids');
                    $data['tags'] = $this->_request->get('tags', []);
                    $data['personalized_form_detail'] = 'default';
                    if (in_array($data['vendor'], array_column($list_member_display_live_preview, 'username'))) {
                        $data['personalized_form_detail'] = intval($this->_request->get('show_product_detail_type', 0)) == 1 ? 'live_preview' : 'default';
                    }

                    if (!is_array($data['tags'])) {
                        $data['tags'] = [];
                    }

                    if($id >= 1){
                        $data['seo_status'] = intval($this->_request->get('seo_status')) == 1 ? 1 : 0 ;
                    }

                    $product_tag_ids = $this->_request->get('product_tags');

                    // check render Mockup after edit design data
                    $is_renderMockup =  !($data['meta_data'] == $model->data['meta_data']);

                    try {
                        $data['addon_service_data'] = OSC::helper('addon/service')->verifyAddonServiceData(
                            $this->_request->get('addon_service_data'),
                            (bool)($this->_request->get('addon_service_enable'))
                        );
                        $model->setData($data);

                        OSC::helper('filter/common')->verifyTagProductRel($product_tag_ids);

                        $product_tag_selected = $this->_getProductTagSelected($product_tag_ids);

                        OSC::core('observer')->dispatchEvent('catalog/product/postFrmSaveData', ['model' => $model]);

                        $model->save();

                        OSC::helper('filter/tagProductRel')->saveTagProductRel($product_tag_ids, $model, $this->getAccount()->getId());

                        try {
                            OSC::helper('catalog/campaign')->processPostVariants($model, $productVariants);

                            $model->reload();

                            $this->_processPostVariantsImages($model, $campaign_data['custom_mockup']);
                            $this->_processPostVariantsVideos($model, $campaign_data['mockup_videos']);
                        } catch (Exception $e) {
                            throw new Exception($e->getMessage());
                        }

                        if ($meta_image['image_to_rm'] != null) {
                            $meta_image_path_to_rm_s3 = OSC::core('aws_s3')->getStoragePath($meta_image['image_to_rm']);
                            OSC::core('aws_s3')->delete($meta_image_path_to_rm_s3);
                        }

                        if ($id > 0) {
                            $message = 'Campaign #' . $model->getId() . ' đã được cập nhật';
                        } else {
                            $message = 'Campaign [#' . $model->getId() . '] "' . $model->data['title'] . '" added';
                        }

                        $this->addMessage($message);

                        if ($is_renderMockup) {
                            $data = [
                                'product_id' => $model->getId(),
                                'version' => time(),
                                'timestamp_new' => $timestamp_new,
                                'timestamp_old' => $timestamp_old,
                                'print_apply_other_face' => $print_apply_other_face,
                                'member_id' => $this->getAccount()->getId()
                            ];

                            try {
                                $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey('campaign/createMockupCampaign:' . $data['product_id']);
                                $model_bulk_queue->delete();
                            } catch (Exception $ex) {
                                if ($ex->getCode() != 404) {
                                    throw new Exception($ex->getMessage());
                                }
                            }

                            OSC::model('catalog/product_bulkQueue')->setData([
                                'ukey' => 'campaign/createMockupCampaign:' . $model->getId(),
                                'member_id' => $this->getAccount()->getId(),
                                'action' => 'createMockupCampaign',
                                'queue_data' => $data
                            ])->save();

                            OSC::core('cron')->addQueue('catalog/campaign_createMockupCampaign', null, ['ukey' => 'catalog/campaign_createMockupCampaign', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
                        }

                        /**
                         * Apply re-order to all campaign
                         * add queue to update variants
                         */

                        if ($this->checkPermission('catalog/product/apply_reorder', false) && is_array($model->data['meta_data']['campaign_config']['apply_reorder']) && count($model->data['meta_data']['campaign_config']['apply_reorder']) > 0) {
                            $model->reload();
                            $DB = OSC::core('database')->getWriteAdapter();

                            $log_data = [];

                            foreach ($model->data['meta_data']['campaign_config']['apply_reorder'] as $productTypeId) {
                                $productTypeVariantsOrder = [];
                                $variantCollection = $model->getVariants();
                                foreach ($variantCollection as $variantModel) {
                                    $metaData = $variantModel->data['meta_data'];
                                    $productTypeVariantId = $metaData['campaign_config']['product_type_variant_id'];
                                    $productTypeVariantsOrder[$productTypeVariantId] = $variantModel->data['position'];
                                }

                                /** Get all product type variants to set position for this product type */
                                $productTypeVariants = OSC::model('catalog/productType_variant')
                                    ->getCollection()
                                    ->addField('title', 'product_type_id')
                                    ->addCondition('product_type_id', $productTypeId, OSC_Database::OPERATOR_EQUAL)
                                    ->load()
                                    ->toArray();

                                /** Create cron for each product type variant ID. This cron will update to all variants build with this product type variant */
                                $logProductTypeVariant = [];
                                foreach ($productTypeVariants as $item) {

                                    /** First: Remove all old queue */
                                    $collectionOld = OSC::model('catalog/product_bulkQueue')->getCollection()
                                        ->addField('queue_id')
                                        ->addCondition('queue_flag', 1, OSC_Database::OPERATOR_EQUAL)
                                        ->addCondition('error', NULL, OSC_Database::OPERATOR_EQUAL)
                                        ->addCondition('queue_data', '"product_type_variant_id":' . $item['id'] . ',', OSC_Database::OPERATOR_LIKE)
                                        ->load()
                                        ->toArray();
                                    if ( count($collectionOld) > 0) {
                                        $queueIds = [];
                                        foreach ($collectionOld as $queue) {
                                            $queueIds[] = $queue['queue_id'];
                                        }

                                        if(count($queueIds) > 0) {
                                            $deleteIds = implode(',', $queueIds);
                                            $DB->query("DELETE FROM {$DB->getTableName('catalog_product_bulk_queue')} WHERE `queue_id` IN({$deleteIds})", null, 'clean_old_queue_'.$item['id']);
                                        }
                                    }

                                    /** Insert new queue */
                                    $position = 0;
                                    if (in_array($item['id'], array_keys($productTypeVariantsOrder))) {
                                        $position = $productTypeVariantsOrder[$item['id']];
                                    }

                                    $queueData = [
                                        'product_id' => $model->getId(),
                                        'product_type_id' => $productTypeId,
                                        'product_type_variant_id' => $item['id'],
                                        'position' => $position
                                    ];

                                    OSC::model('catalog/product_bulkQueue')->setData([
                                        'member_id' => $this->getAccount()->getId(),
                                        'action' => 'updateReorderVariants',
                                        'queue_data' => $queueData
                                    ])->save();

                                    $logProductTypeVariant[] = [
                                        'product_type_variant_id' => $item['id'],
                                        'position' => $position
                                    ];
                                }

                                $log_data[] = [
                                    'product_type_id' => $productTypeId,
                                    'product_type_variant' => $logProductTypeVariant
                                ];
                            }

                            OSC::core('cron')->addQueue(
                                'catalog/campaign_updateReorderVariants',
                                null,
                                ['ukey' => 'catalog/campaign_updateReorderVariants' , 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]
                            );

                            $log_content = "Apply re-order for all campaign from product ID = " . $model->getId() . " and product type ID = " . implode(',', $model->data['meta_data']['campaign_config']['apply_reorder']);
                            OSC::helper('core/common')->writeLog('Re-Order', $log_content, $log_data);
                        }

                        if (!$this->_request->get('continue')) {
                            static::redirectLastListUrl($this->getUrl('*/backend_product/list'));
                        } else {
                            static::redirect($this->getUrl(null, array('id' => $model->getId())));
                        }
                    } catch (Exception $ex) {
                        $this->addErrorMessage($ex->getMessage());
                    }
                }

                $list_vendors = OSC::model('user/member')->getListMemberIdeaResearch();

                if (!($list_vendors instanceof Model_User_Member_Collection)) {
                    $list_vendors = [];
                }

                $addon_services = OSC::helper('addon/service')->formatData($model->data['addon_service_data']);
                $product_type_ids = array_column($campaign_data['product_types'], 'id');
                $product_type_info = [];

                foreach ($campaign_data['product_types'] as $product_type) {
                    $product_type_info[$product_type['id']] = [
                        'name' => $product_type['name'],
                        'variant_ids' => array_column($product_type['product_variant'], 'id'),
                    ];
                }

                $pack_product_type_ids = OSC::model('catalog/product/pack')->getCollection()
                    ->addField('product_type_id')
                    ->addCondition('product_type_id', $product_type_ids, OSC_Database::OPERATOR_IN)
                    ->load()
                    ->toArray();
                $pack_product_type_ids = array_unique(array_column($pack_product_type_ids, 'product_type_id'));

                $pack_info = [];
                foreach ($campaign_data['product_types'] as $key => $product_type) {
                    if (in_array($campaign_data['product_types'][$key]['id'], $pack_product_type_ids)) {
                        $pack_info[] = $campaign_data['product_types'][$key]['name'];
                    }
                }

                $addon_list = OSC::helper('addon/service')->getAddonServiceList(
                    $product_type_ids,
                    array_column($addon_services, 'addon_service_id')
                );

                $custom_apply_addons = [];
                $auto_apply_addons = [];

                foreach ($addon_list as $addon) {
                    if (
                        empty($addon['auto_apply_for_product_type_variants']) ||
                        !is_array($addon['auto_apply_for_product_type_variants'])
                    ) {
                        $custom_apply_addons[$addon['id']] = $addon;
                        continue;
                    }

                    if (
                        in_array('*', $addon['auto_apply_for_product_type_variants'])
                    ) {
                        $addon['apply_for'] = 'All product types';
                        $addon['date_range'] = date('d/m/Y', $addon['start_timestamp']) . ' - ' . date('d/m/Y', $addon['end_timestamp']);
                        $auto_apply_addons[$addon['id']] = $addon;
                        continue;
                    }

                    $product_type_names = [];

                    foreach ($product_type_ids as $product_type_id) {
                        $auto_variant_ids = $addon['auto_apply_for_product_type_variants'][$product_type_id];

                        if (is_array($auto_variant_ids)) {
                            $name = $product_type_info[$product_type_id]['name'];
                            $intersect = array_intersect($auto_variant_ids, $product_type_info[$product_type_id]['variant_ids']);

                            if (in_array('*', $auto_variant_ids)) {
                                $product_type_names[] = $name;
                            } else if (count($intersect)) {
                                $product_type_names[] = $name . '(' . implode(', ', $intersect) . ')';
                            }
                        }
                    }

                    if (count($product_type_names)) {
                        $addon['apply_for'] = implode(',<br />', $product_type_names);
                        $addon['date_range'] = date('d/m/Y', $addon['start_timestamp']) . ' - ' . date('d/m/Y', $addon['end_timestamp']);
                        $auto_apply_addons[$addon['id']] = $addon;
                    }
                }

                $now = time();

                foreach ($addon_services as $key => $applied_addon) {
                    $auto_addon = $auto_apply_addons[$applied_addon['addon_service_id']];

                    if (!$auto_addon) continue;

                    $addon_services[$key]['auto_select'] = 1;

                    $is_show_auto_addon = false;

                    if (
                        $now >= $applied_addon['start_timestamp'] && $now <= $applied_addon['end_timestamp'] ||
                        $now >= $auto_addon['start_timestamp'] && $now <= $auto_addon['end_timestamp']
                    ) {
                        $is_show_auto_addon = true;
                    }

                    if (!$is_show_auto_addon) {
                        unset($auto_apply_addons[$applied_addon['addon_service_id']]);
                    }

                    if (
                        $applied_addon['start_timestamp'] >= $auto_addon['start_timestamp'] &&
                        $applied_addon['end_timestamp'] <= $auto_addon['end_timestamp']
                    ) {
                        continue;
                    }

                    if (
                        $applied_addon['end_timestamp'] < $auto_addon['start_timestamp'] ||
                        $applied_addon['start_timestamp'] > $auto_addon['end_timestamp']
                    ) {
                        $auto_addon['date_range'] = $applied_addon['start_timestamp'] < $auto_addon['start_timestamp']
                            ? $applied_addon['date_range'] . '<br />' .  $auto_addon['formatted_active_time']
                            : $auto_addon['formatted_active_time'] . '<br />' .  $applied_addon['date_range'];
                    } else {
                        $start_date = date('d/m/Y', min(
                            $applied_addon['start_timestamp'],
                            $auto_addon['start_timestamp']
                        ));
                        $end_date = date('d/m/Y', max(
                            $applied_addon['end_timestamp'],
                            $auto_addon['end_timestamp']
                        ));

                        $auto_addon['date_range'] = $start_date . ' - ' . $end_date;
                    }

                    $auto_addon['in_time'] = 1;

                    if ($is_show_auto_addon) {
                        $auto_apply_addons[$applied_addon['addon_service_id']] = $auto_addon;
                    }
                }

                $available_addon_ids = array_column($addon_list, 'id');
                $addon_services = array_map(function($addon) use ($available_addon_ids) {
                    if (!in_array($addon['addon_service_id'], $available_addon_ids)) {
                        $addon['not_available'] = 1;
                    }
                    return $addon;
                }, $addon_services);

                foreach ($auto_apply_addons as $key => $auto_addon) {
                    if (
                        $auto_addon['in_time'] ||
                        $now >= $auto_addon['start_timestamp'] && $now <= $auto_addon['end_timestamp']
                    ) continue;

                    unset($auto_apply_addons[$key]);
                }

                $this->output($this->getTemplate()->build('catalog/campaign/postForm', [
                    'form_title' => $model->getId() > 0 ? ('Edit campaign #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new campaign',
                    'model' => $model,
                    'list_vendors' => $list_vendors,
                    'list_member_display_live_preview' => $list_member_display_live_preview,
                    'campaign_data' => $campaign_data,
                    'custom_apply_addons' => $custom_apply_addons,
                    'auto_apply_addons' => $auto_apply_addons,
                    'addon_services' => $addon_services,
                    'pack_info' => $pack_info,
                    'list_product_tags' => $list_product_tags,
                    'list_product_tags_selected' => $product_tag_selected
                ]));
            } catch (Exception $ex) {
                echo $ex->getMessage();
                die;
            }
        } else if ($model->getId() < 1) {
            $productTypes = $this->_request->get('product_type');

            if (!is_array($productTypes) || count($productTypes) < 1) {
                $this->addErrorMessage('Please choose a product to add to campaign');
                static::redirectLastListUrl($this->getUrl('list'));
            }

            $model->setData([
                'meta_data' => ['campaign_config' => ['print_template_config' => null]],
                'type_flag' => 0
            ]);
        }

        $product_type_deactive = OSC::helper('catalog/productType')->getProductTypeDeactive($productTypes);

        $product_type_ukeys = [];
        $is_product_type_deactive = false;

        foreach ($product_type_deactive as $key => $product_type) {
            $product_type_ukeys[$product_type->data['ukey']] = $product_type->data['title'];
        }

        foreach ($productTypes as $key => $ukey) {
            if (in_array($ukey, array_keys($product_type_ukeys))) {
                if (count($productTypes) == 1) {
                    $this->addErrorMessage('The campaign cannot edit because campaign has only one product type of ' . $product_type_ukeys[$ukey] . ' is no longer activated');
                    static::redirect($this->getUrl('*/backend_product/list'));
                }
                $is_product_type_deactive = true;
                unset($productTypes[$key]);
            }
        }

        $productTypeConfigs = OSC::helper('catalog/productType')->getCampaignConfigs($productTypes);

        $customMockupConfig = null;

        /**
         * Get all variants has created for each product type
         * Push to [product_variant]
         */
        if($model->getId() > 0) {
            /**
             * Get variants position
             */
            $variantsPositions = [];
            $variantCollection = $model->getVariants();

            foreach ($variantCollection as $variantModel) {
                $metaData = $variantModel->data['meta_data'];
                $productTypeVariantId = $metaData['campaign_config']['product_type_variant_id'];
                $variantsPositions[$productTypeVariantId] = $variantModel->data['position'];
            }

            $productTypeVariants = OSC::helper('catalog/productType')->parseProductTypeVariantId($model);

            foreach ($productTypeConfigs as $key => $productType) {
                $productTypeConfigs[$key]['product_variant'] = [];
                $_productTypeConfig = [];
                foreach ($productType['product_type_variant'] as $variant) {
                    if(in_array($variant['id'], $productTypeVariants, true)) {
                        $variant['position'] = $variantsPositions[$variant['id']];
                        $_productTypeConfig[] = $variant;
                    }
                }
                /**
                 * ReOrder variants
                 */
                foreach ($productTypeVariants as $productTypeVariantId) {
                    foreach ($_productTypeConfig as $item) {
                        if($item['id'] == $productTypeVariantId) {
                            $productTypeConfigs[$key]['product_variant'][] = $item;
                        }
                    }
                }
            }

            /**
             * Reorder list_product_variants if product is campaign and flag is_reorder = 1
             */
            //if (isset($model->data['meta_data']['campaign_config']['is_reorder']) && !empty($model->data['meta_data']['campaign_config']['is_reorder'])) {
                foreach ($productTypeConfigs as $key => $productType) {
                    $listProductVariants = $productType['product_variant'];
                    usort($listProductVariants, function ($a, $b) {
                        if ($a['position'] == 0) {
                            return 1;
                        }
                        if ($b['position'] == 0) {
                            return -1;
                        }
                        if ($a['position'] == $b['position']) {
                            return 0;
                        }
                        return ($a['position'] > $b['position']) ? 1 : -1;
                    });

                    $productTypeConfigs[$key]['product_variant'] = $listProductVariants;
                }
            //}

            $customMockupConfig = OSC::helper('catalog/campaign')->getCustomMockupConfigs($model->getId());
        }

        $meta_data = $model->data['meta_data'];

        if($is_product_type_deactive && isset($meta_data['campaign_config']['print_template_config']) && count($meta_data['campaign_config']['print_template_config']) > 0) {
            $meta_data['campaign_config']['print_template_config'] = OSC::helper('catalog/product')->clearPrintTemplateDeactiveByDuplicate($meta_data['campaign_config']['print_template_config'])['print_template_configs'];
        }

        try {
            $campaignConfigBuilder['campaign_config'] = ($model->getId() > 0)?OSC::helper('catalog/productType')->parseCampaignConfigForBuilder($meta_data['campaign_config']):$meta_data['campaign_config'];
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getMessage());
            static::redirect($this->getUrl('*/backend_product/list'));
        }
        $campaignConfigBuilder['product_types'] = $productTypeConfigs;
        $campaignConfigBuilder['custom_mockup'] = $customMockupConfig;

        $model->setData(
            [
                'meta_data' =>  $meta_data,
                'type_flag' => 0
            ]
        );

        $max_video_size = OSC::helper('core/setting')->get('catalog/video_config/max_file_size');

        $this->output($this->getTemplate()->build(
            'catalog/campaign/designForm', [
                'form_title' => $model->getId() > 0 ? ('Edit campaign #' . $model->getId() . ': ' . $model->getData('title', true)) : 'Add new campaign',
                'config_builder' => $campaignConfigBuilder,
                'model' => $model,
                'max_video_size' => $max_video_size ?? 0,
                'campaign_type' => 'default']
            )
        );
    }

    /**
     * @throws OSC_Exception_Runtime
     * return topic
     */

    private function _getProductTagSelected($product_tag_ids) {
        $product_tag_selected = [];

        if (count($product_tag_ids) > 1) {
            foreach ($product_tag_ids as $key => $product_tag_id) {
                $product_tag_selected[$product_tag_id] = $product_tag_id;
            }
        }

        return $product_tag_selected;
    }

    public function actionGetProductInfo() {
        $id = intval($this->_request->get('id'));

        try {
            /* @var $model Model_Catalog_Product */
            $model = OSC::model('catalog/product');

            if ($id < 1) {
                throw new Exception('Product ID is incorrect');
            }

            try {
                $model->load($id);
            } catch (Exception $ex) {
                throw new Exception($ex->getCode() == 404 ? 'Product is not exist' : $ex->getMessage());
            }

            $model->data['meta_tags']['meta_image_url'] = $model->getMetaImageUrl();
            $this->_ajaxResponse($model->data);
        } catch (Exception $ex){
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetListProductInfo() {
        $product_ids = explode('-', trim($this->_request->get('product_ids', '')));
        $result = OSC::helper('catalog/product')->getListProductInfo($product_ids);

        if (count($result['err'])) {
            $this->_ajaxError($result['err']);
        }
        $this->_ajaxResponse($result['products']);
    }

    public function actionQuickEditProduct() {
        $id = intval($this->_request->get('id'));

        /* @var $model Model_Catalog_Product */
        $model = OSC::model('catalog/product');

        if ($id > 0) {
            try {
                $model->load($id);
            } catch (Exception $ex) {
                $this->_ajaxError($ex->getCode() == 404 ? 'Campaign is not exist' : $ex->getMessage());
            }

        }

        try {
            $data['title'] = trim($this->_request->get('title'));
            $data['slug'] = trim($this->_request->get('seo_slug'), '- ');
            $data['description'] = $this->_request->getRaw('description');
            // get SEO PRODUCT to save meta_data
            $seo_title = trim($this->_request->get('seo_title'));
            $seo_description = trim($this->_request->get('seo_description'));
            $seo_keyword = trim($this->_request->get('seo_keyword'));
            $seo_image = $this->_request->get('seo_image');

            $data['topic'] = trim($this->_request->get('topic'));

            $data['meta_tags'] = [
                'title' => $seo_title,
                'description' => $seo_description,
                'keywords' => $seo_keyword,
                'image' => $seo_image
            ];

            if (!$data['slug']){
                $data['slug'] = OSC::core('string')->cleanAliasKey($data['topic'] . '-' . $data['title']);
            }

            if (isset($model->data['meta_tags']['is_clone']) && intval($model->data['meta_tags']['is_clone']) > 0) {
                if (($data['topic'] != $model->data['topic']) || ($data['title'] != $model->data['title'])) {
                    $data['slug'] = OSC::core('string')->cleanAliasKey($data['topic'] . '-' . $data['title']);
                    unset($data['meta_tags']['is_clone']);
                }
            }

            $meta_image = OSC::helper('backend/backend/images/common')->saveMetaImage($data['meta_tags']['image'], $model->data['meta_tags']['image'], 'meta/campaign/', 'campaign');

            if ($meta_image['data_meta_image']) {
                $data['meta_tags']['image'] = $meta_image['data_meta_image'];
            }

            $data['seo_tags'] = array_values($this->_request->get('tags', []));

            if (!is_array($data['seo_tags'])) {
                $data['seo_tags'] = [];
            }

            $data['seo_status'] = intval($this->_request->get('seo_status')) == 1 ? 1 : 0 ;

            try {
                $model->setData($data)->save();;

            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }

            $this->_ajaxResponse($model->data);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionGetProductTopic() {
        $collection = OSC::model('catalog/product')->getCollection();

        try {
            $collection->load();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $topics = [];

        foreach ($collection as $model) {
            $topic = trim($model->data['topic']);

            if (strlen($topic) < 1) {
                continue;
            }

            $topics[$topic]++;
        }

        $this->_ajaxResponse(array_keys($topics));
    }

    public function actionImageLibUpload() {
        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/edit|catalog/product/add');

        try {
            $uploader = new OSC_Uploader();

            $tmp_file_path = OSC::getTmpDir() . '/' .
                $this->getAccount()->getId() . '.' .
                OSC::makeUniqid() . '.' .
                $uploader->getExtension();

            $uploader->save($tmp_file_path, true);

            $extension = OSC_File::verifyImage($tmp_file_path);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $model = OSC::model('catalog/campaign_imageLib_item');

        try {
            $store_info = OSC::getStoreInfo();

            $img_processor = new OSC_Image();
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(3000)->save();

            $filename = 'catalog/campaign/imgLib/' .
                date('Ymd') . '/' .
                $store_info['id'] . '.' .
                $this->getAccount()->getId() . '.' .
                OSC::makeUniqid() . '.' .
                $extension;
            $filename_s3 = OSC::core('aws_s3')->getStoragePath($filename);

            OSC::core('aws_s3')->upload($tmp_file_path, $filename_s3);

            $tmp_thumb_img_path = preg_replace('/^(.+)\.([^\.]+)$/', '\\1.thumb.\\2', $tmp_file_path);
            $thumb_img_path_s3 = preg_replace('/^(.+)\.([^\.]+)$/', '\\1.thumb.\\2', $filename_s3);
            $img_processor->setJpgQuality(100)->setImage($tmp_file_path)->resize(600)->save($tmp_thumb_img_path);

            OSC::core('aws_s3')->upload($tmp_thumb_img_path, $thumb_img_path_s3);

            $model->setData([
                'member_id' => $this->getAccount()->getId(),
                'item_type' => 'file',
                'name' => $uploader->getName(),
                'filename' => $filename
            ])->save();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        OSC::model('personalizedDesign/sync')->setData([
            'ukey' => 'imagelib/' . $model->getId(),
            'sync_type' => 'imagelib',
            'sync_data' => $model->data['filename']
        ])->save();

        $this->_ajaxResponse([
            'item_id' => $model->getId(),
            'name' => $model->data['name'],
            'url' => $model->getFileThumbUrl(),
            'width' => $model->data['width'],
            'height' => $model->data['height']
        ]);
    }

    public function actionImageLibBrowse() {
        /* @var $collection Model_CatalogItemCustomize_Item_Collection */
        /* @var $model Model_CatalogItemCustomize_Item */
        /* @var $search OSC_Search_Analyzer */

        try {
            $page_size = intval($this->_request->get('page_size'));

            if ($page_size == 0) {
                $page_size = 25;
            } else if ($page_size < 5) {
                $page_size = 5;
            } else if ($page_size > 100) {
                $page_size = 100;
            }

            $page = intval($this->_request->get('page'));

            if ($page < 1) {
                $page = 1;
            }

            $keywords = $this->_request->get('keywords');

            $collection = OSC::model('catalog/campaign_imageLib_item')->getCollection();

            if ($keywords && $keywords != '') {
                $search = OSC::core('search_analyzer');

                $condition = $search->addKeyword('name', 'name', OSC_Search_Analyzer::TYPE_STRING, true, false)
                        ->addKeyword('id', 'item_id', OSC_Search_Analyzer::TYPE_INT, false)
                        ->addKeyword('member_id', 'member_id', OSC_Search_Analyzer::TYPE_INT, false)
                        ->parse($keywords);

                $collection->setCondition($condition);
            }

            $collection->setPageSize($page_size)->setCurrentPage($page)->load();

            $items = [];

            foreach ($collection as $model) {
                $item = [
                    'item_id' => $model->getId(),
                    'locked' => $model->data['locked_flag'],
                    'item_type' => $model->data['item_type'],
                    'name' => $model->data['name'],
                    'image_url' => $model->getFileThumbUrl(),
                    'width' => $model->data['width'],
                    'height' => $model->data['height']
                ];

                $items[] = $item;
            }

            $this->_ajaxResponse([
                'keywords' => [],
                'total' => intval($collection->collectionLength()),
                'offset' => intval((($collection->getCurrentPage() - 1) * $collection->getPageSize()) + $collection->length()),
                'current_page' => intval($collection->getCurrentPage()),
                'page_size' => intval($collection->getPageSize()),
                'items' => $items
            ]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionRerenderMockup() {
        /* @var $product Model_Catalog_Product */

        $this->checkPermission('catalog/super|catalog/product/full|catalog/product/add');

        try {
            $product = OSC::model('catalog/product')->load($this->_request->get('id'));
        } catch (Exception $ex) {
            $this->addErrorMessage($ex->getCode() === 404 ? 'Product is not exists' : $ex->getMessage());
            static::redirectLastListUrl($this->getUrl('*/backend_product/list'));
        }

        if (!$product->isCampaignMode()) {
            $this->addErrorMessage('Product is not campaign');
            static::redirectLastListUrl($this->getUrl('*/backend_product/list'));
        }

        $campaign_data = $product->data['meta_data'];

        $print_apply_other_face = [];

        foreach ($campaign_data['campaign_config']['print_template_config'] as $product_config_new) {
            if ($product_config_new['apply_other_face'] == 1 && count($product_config_new['segments']) > 1) {
                $print_apply_other_face[] = $product_config_new['print_template_id'];
            }
        }

        $version = time();
        $product_id = $product->getId();

        $data = [
            'product_id' => $product_id,
            'version' => $version,
            'timestamp_new' => [],
            'timestamp_old' => [],
            'print_apply_other_face' => $print_apply_other_face,
            'member_id' => $this->getAccount()->getId()
        ];
        try {
            try {
                $model = OSC::model('catalog/product_bulkQueue')->loadByUKey('campaign/deleteRenderMockup:' . $product_id);
                $model->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }

            OSC::model('catalog/product_bulkQueue')->setData([
                'ukey' => 'campaign/deleteRenderMockup:' . $product_id,
                'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
                'action' => 'v2DeleteRenderCampaignMockup',
                'queue_data' => [
                    'campaign_id' => $product_id,
                    'version' => $version,
                    'data' => $data
                ]
            ])->save();

            OSC::core('cron')->addQueue('catalog/campaign_deleteRenderMockup', null, ['ukey' => 'catalog/deleteRenderCampaignMockup', 'requeue_limit' => -1, 'estimate_time' => 60 * 2]);

            $this->addMessage('Mockup rerender queue has been appended successfully');
        }catch (Exception $ex) {
            $this->addErrorMessage('There are currently errors. please try again in 5-10 minutes');
        }

        static::redirectLastListUrl($this->getUrl('*/backend_product/list'));
    }

    public function actionGetProductTypes() {
        $tabKey = $this->_request->get('tab_name');

        if(empty($tabKey)) {
            throw new Exception('Tab name is null !');
        }

        $productTypes = OSC::helper('catalog/productType')->getListProductTypeByTab($tabKey);

        $this->_ajaxResponse($productTypes);
    }

    public function actionGetProductTypeConfig() {
        $productType = $this->_request->get('product_type');
        $productTypeConfigs = OSC::helper('catalog/productType')->getCampaignConfigs($productType);

        return $this->_ajaxResponse($productTypeConfigs);
    }

    public function actionGetPrintTemplate() {
        $DB = OSC::core('database');
        $productType = $this->_request->get('product_type_id');
        $productTypeVariants = $this->_request->get('product_type_variant');

        if(empty($productType) || empty($productTypeVariants)) {
            $this->_ajaxError('Request data null !');
        }

        if(!is_array($productTypeVariants)) {
            $this->_ajaxError('Please create one variant!');
        }

        $productTypeVariants = array_unique($productTypeVariants);
        $DB->select('*', 'supplier_variant_rel', 'product_type_variant_id IN (' . implode(',', $productTypeVariants) . ')', null, null, 'fetch_rel');
        $printTemplates = [];
        while ($row = $DB->fetchArray('fetch_rel')) {
            $printTemplates[] = $row['print_template_id'];
        }

        $printTemplates = array_unique($printTemplates);
        $printTemplates = OSC::helper('catalog/campaign')->mapPrintTemplate($printTemplates);

        $collection = OSC::model('catalog/printTemplate')->getCollection()->load($printTemplates);

        $result = [];
        if($collection->length() > 0) {
            foreach ($collection as $item) {
                $result[] = [
                    'print_template_id' => $item->data['id'],
                    'title' => OSC::safeString($item->data['title']),
                    'config' => $item->data['config'],
                ];
            }
        }

        $this->_ajaxResponse($result);
    }

    public function actionGetDesignData() {
        $designId = $this->_request->get('design_id');
        if(!$designId || $designId < 1) {
            $this->_ajaxError('Design ID not valid !');
        }

        $result = [];

        $design = OSC::model('personalizedDesign/design')->load($designId);
        if($design) {
            $result = [
                'id' => $design->getId(),
                'title' => $design->data['title'],
                'url' => '#',
                'image_url' => $design->getImageUrl(),
                'document' => $design->data['design_data']['document'],
                'svg_content' => OSC::helper('personalizedDesign/common')->renderSvg($design, [], ['original_render'])
            ];
        }

        return $this->_ajaxResponse($result);
    }

    public function actionGetSvgData() {
        $id = $this->_request->get('id');
        $custom_config = $this->_request->get('config');

        if(!$id || $id < 1) {
            $this->_ajaxError('Design Id not valid!');
        }
        try {
            $model = OSC::model('personalizedDesign/design')->load($id);

            $svg = OSC::helper('personalizedDesign/common')->renderSvg($model, $custom_config, []);

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

            $this->_ajaxResponse(['svg' => $svg, 'document' => $model->data['design_data']['document'], 'document_type' => $document_type, 'size' => $size]);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getCode() == 404 ? 'Design is not exist' : $ex->getMessage());
        }
    }

    public function actionLoadMockup() {
        $campaign_id = $this->_request->get('campaign_id');
        $productTypeVariants = $this->_request->get('product_type_variants')?$this->_request->get('product_type_variants'):[];
        $result = OSC::helper('catalog/campaign')->getCustomMockupConfigs($campaign_id, $productTypeVariants);
        $this->_ajaxResponse($result);
    }

    public function actionUploadMockupCustomer() {
        try {
            $is_video = $this->_request->get('is_video', false);
            $uploader = new OSC_Uploader();

            if (!in_array($uploader->getExtension(), ['png','jpg','jpeg', 'mp4'], true)) {
                throw new Exception(strtoupper($uploader->getExtension()) . ' is not allowed to upload');
            }

            $tmp_file_path = 'customer/upload_mockup/' . $this->getAccount()->getId().'.'. OSC::makeUniqid() . '.' . time() . '.' . $uploader->getExtension();
            $tmp_file_path_s3 = OSC::core('aws_s3')->getTmpFilePath($tmp_file_path);

            $tmp_file_path_saved = OSC_Storage::preDirForSaveFile($tmp_file_path);

            $uploader->save($tmp_file_path_saved, true);
            $options = [
                'overwrite' => true,
                'permission_access_file' => 'public-read'
            ];

            if ($is_video) {
                OSC_File::verifyVideo($tmp_file_path_saved);
            } else {
                OSC_File::verifyImage($tmp_file_path_saved);
            }

            $file_url = OSC::core('aws_s3')->upload($tmp_file_path_saved, $tmp_file_path_s3, $options);

            // $this->_ajaxResponse($tmp_file_path);

            $this->_ajaxResponse($file_url);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionSaveMockupCustomer() {
        $campaign_id = $this->_request->get('campaign_id');

        try {
            $product = OSC::model('catalog/product')->load($campaign_id);
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }

        $images_data = $this->_request->getRaw('images');

        $this->_processPostVariantsImages($product, $images_data);

        $this->_ajaxResponse("update data mockup success");
    }
}
