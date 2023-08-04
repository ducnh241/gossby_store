<?php

class Controller_Filter_React_GiftFinder extends Abstract_Frontend_ReactApiController
{
    public function actionGetConfig()
    {
        $gift_finder_config = OSC::helper('core/setting')->get('filter_search/gift_finder/config');


        $this->apiOutputCaching([
            'gift_finder_config' => $gift_finder_config
        ]);

        $filter_tag = OSC::model('filter/tag')->getCollection()
            ->addField('id', 'image', 'type')
            ->load()
            ->toArray();

        $list_tag = array_reduce($filter_tag, function ($carry, $tag) {
            $carry[$tag['id']] = [
                'type' => $tag['type'],
                'image' => $tag['image'] ? OSC::wrapCDN(OSC::core('aws_s3')->getStorageUrl($tag['image'])) : ''
            ];
            return $carry;
        }, []);

        if (is_array($gift_finder_config)) {
            $gift_finder_config = array_map(function ($step) use ($list_tag) {
                $step['show_image'] = intval($step['show_image']);
                $step['is_one_choice'] = intval($list_tag[$step['parent_tag']]['type']);
                $step['children_tag'] = array_map(function ($tag) use ($list_tag) {
                    $tag['image'] = $list_tag[$tag['id']]['image'];
                    return $tag;
                }, $step['children_tag']);

                return $step;
            }, array_values($gift_finder_config));
        } else {
            $gift_finder_config = [];
        }

        $this->sendSuccess($gift_finder_config);
    }

    public function actionGetProducts()
    {
        $page = intval($this->_request->get('page', 1));
        $size = intval($this->_request->get('size', 20));
        $size = $size > 0 && $size <= 20 ? $size : 20;
        $_filters = $this->_request->get('filter_id_options');

        $filters = OSC::helper('filter/common')->validateFilterOptions($_filters);
        try {
            $products = OSC::model('catalog/product')->getCollection();

            if (count($filters) > 0) {
                $product_ids_by_filter = OSC::helper('filter/common')->getProductIdByFilter($filters);
                $flag_filter = true;
            } else {
                $flag_filter = false;
                $product_ids_by_filter = [];
            }

            $location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

            $result_search = OSC::helper('catalog/search_product')->searchProduct('',
                [
                    'location_code' => $location_code,
                    'product_ids' => $product_ids_by_filter,
                    'flag_filter' => $flag_filter,
                    'sort' => 'solds'
                ], $page, $size);

            $product_ids = [0];
            if (!empty($result_search['list_id']) && is_array($result_search['list_id'])) {
                $product_ids = $result_search['list_id'];
            }

            $products->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN)
                ->sort('solds', OSC_Database::ORDER_DESC)
                ->load();
            foreach ($products as $product) {
                $product->setCatalogCollection($this);
            }

            $products->preLoadMockupRemove();
            $products->preLoadImageCollection();
            $products->preLoadABTestProductPrice();
            $products->preLoadVariantCollection()->preLoadProductTypeVariant();

            $results = [
                'products' => OSC::helper('catalog/product')->formatProductApi($products),
                'page' => $page,
                'size' => $size,
                'total' => $result_search['total_item'] ?? 0,
            ];
            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }
}