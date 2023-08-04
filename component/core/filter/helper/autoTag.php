<?php

class Helper_Filter_AutoTag extends OSC_Object {

    /**
     * @param $product Model_Catalog_Product
     * @return array
     * @throws OSC_Exception_Runtime
     */
    public function generate($product) {

        try {
            if($product->getId() < 1) {
                throw new Exception('Product Id is invalid');
            }
            $model = OSC::model('core/setting')->loadByUKey(Model_Filter_AutoTag::KEY_CONFIG_SETTING_FIELDS);
            $setting_fields = $model->data['setting_value'];

            $selected_compare_field = array_filter($setting_fields, function ($field) {
                return intval($field['value']) == 1;
            });

            if (empty($selected_compare_field)) {
                return [];
            }

            $text = [];

            /* @var $variant Model_Catalog_Product_Variant */
            $variants = $product->getVariants();

            if ($product->isCampaignMode()) {
                if (in_array('product_type', array_keys($selected_compare_field))) {
                    $product_types_ukey = explode(', ', $product->data['product_type']);
                    $product_types = OSC::model('catalog/productType')
                        ->getCollection()
                        ->loadByUkey($product_types_ukey);

                    /* @var $product_type Model_Catalog_ProductType */
                    foreach ($product_types as $product_type) {
                        $text[] = $product_type->data['title'];
                    }
                }

                if (in_array('variant', array_keys($selected_compare_field))) {
                    /* @var $productTypeVariant Model_Catalog_ProductType_Variant */
                    foreach ($variants as $variant) {
                        $productTypeVariant = $variant->getProductTypeVariant();
                        $text[] = $productTypeVariant->data['title'];
                    }
                }

            }

            if ($product->isSemitestMode() && in_array('variant', array_keys($selected_compare_field))) {
                foreach ($variants as $variant) {
                    $options = $variant->data['options'];
                    $sub_text = '';
                    foreach ($options as $option) {
                        $sub_text .= " {$option}";
                    }
                    $text[] = $sub_text;
                }
            }

            if (in_array('collections', array_keys($selected_compare_field))) {
                $collections = $product->getCollections();

                /* @var $collection Model_Catalog_Collection */
                foreach ($collections as $collection) {
                    $text[] = $collection->data['title'];
                }
            }

            if (in_array('quote', array_keys($selected_compare_field))) {
                $text[] = OSC::safeString($product->data['title']);
            }

            if (in_array('topic', array_keys($selected_compare_field))) {
                $text[] = OSC::safeString($product->data['topic']);
            }

            if (in_array('description', array_keys($selected_compare_field))) {
                $text[] = OSC::safeString(OSC::core('string')->removeInvalidCharacter(strip_tags($product->data['description'])));
            }

            if (in_array('meta_slug', array_keys($selected_compare_field))) {
                $text[] = OSC::safeString($product->data['slug']);
            }

            if (in_array('meta_title', array_keys($selected_compare_field))) {
                $text[] = OSC::safeString($product->data['meta_tags']['title']);
            }

            if (in_array('meta_description', array_keys($selected_compare_field))) {
                $text[] = OSC::safeString($product->data['meta_tags']['description']);
            }

            if (in_array('meta_keyword', array_keys($selected_compare_field))) {
                $text[] = OSC::safeString($product->data['meta_tags']['keywords']);
            }

            $auto_tag_selected = [];
            $tags = OSC::helper('filter/search')->getTagKeyword();
            $text_search = strtolower(implode(' ', $text));

            foreach ($tags as $tag_id => $tag_keyword) {
                if (preg_match_all('/\s?(' . implode('|', $tag_keyword) . ')\s?/', $text_search, $matchs)) {
                    $auto_tag_selected[] = $tag_id;
                }
            }

            return $auto_tag_selected;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
