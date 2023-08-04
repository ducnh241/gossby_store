<?php


class Controller_Filter_React_Search extends Abstract_Frontend_ReactApiController
{
    public function actionSuggested()
    {
        $keyword = trim($this->_request->get('keyword'), '?& ');
        $this->sendSuccess([
            'keyword' => $keyword,
            'suggested' => OSC::helper('catalog/search_product')->fetchSuggest($keyword),
        ]);
    }

    public function actionProduct()
    {
        $keywords = strtolower(trim($this->_request->get('keywords'), '?&,-_ '));
        $page = intval($this->_request->get('page', 1));
        $size = intval($this->_request->get('size', 20));
        if ($size > 100) {
            $size = 100;
        }
        $_filters = $this->_request->get('filter_id_options');

        $filters = OSC::helper('filter/common')->validateFilterOptions($_filters);

        $debug = intval($this->_request->get('debug'));
        $sort = $this->_request->get('sort', 'default');

        try {
            OSC::core('debug')->startProcess('debug_search_product');

            $sort_options = OSC::helper('filter/search')->getSortOptions();
            $sort_options['default'] = 'Most Relevant';
            if (!in_array($sort, array_keys($sort_options))) {
                $sort = 'default';
            }
            $products = OSC::model('catalog/product')->getCollection();
            $detect_tag = [];

            [$product_ids, $total_item] = $this->_algoliaSearch($keywords, $filters, $sort, $page, $size);

            $products->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN);
            $products->load();
            foreach ($products as $product) {
                $product->setCatalogCollection($this);
            }

            $products->preLoadMockupRemove();
            $products->preLoadImageCollection();
            $products->preLoadABTestProductPrice();
            $products->preLoadVariantCollection()->preLoadProductTypeVariant();

            // Sort Product
            $product_sorts = OSC::model('catalog/product')->getCollection();

            foreach ($product_ids as $product_id) {
                $product = $products->getItemByPK(intval($product_id));
                if ($product instanceof Model_Catalog_Product) {
                    $product_sorts->addItem($product);
                }
            }

            $results = [
                'keyword' => $keywords,
                'products' => OSC::helper('catalog/product')->formatProductApi($product_sorts),
                'detect_tag' => $detect_tag,
                'page' => $page,
                'size' => $size,
                'total' => $total_item,
                'sort' => [
                    'options' => $sort_options,
                    'default' => $sort
                ]
            ];
            OSC::core('debug')->endProcess();
            if ($debug) {
                dump($products);
                OSC::core('debug')->showInfo();
            }

            $this->sendSuccess($results);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    public function actionGetFilterByCollection()
    {
        $collection_id = $this->_request->get('collection_id');

        if (intval($collection_id) < 0) {
            $collection_id = 0;
        }

        $this->apiOutputCaching([
            'collection_id' => $collection_id
        ], 0, ['ignore_location']);

        try {
            $model_filter = OSC::model('filter/collection')->getCollection()->getFilterByCollectionId($collection_id);

            if ($model_filter == null) {
                $model_filter = OSC::model('filter/collection')->getCollection()->getFilterByCollectionId(0);
            }

            $this->sendSuccess($model_filter->data['filter_setting']);
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }
    }

    /**
     * Integration algolia search engine, so not using elastic search
     * @param $keywords
     * @param $filters
     * @param $debug
     * @param $sort
     * @param $page
     * @param $size
     * @return array
     */
    protected function _elasticSearch($keywords, $filters, $debug, $sort, $page, $size): array {

        $product_ids = [0];

        $detect_tag = OSC::helper('filter/search')->detectTagInKeyword($keywords);
        if (isset($detect_tag['filters'])) {
            $keywords = $detect_tag['keyword'] ?? '';
            $filters = array_merge($filters, array_column($detect_tag['filters'], 'id'));
        }

        if (count($filters) > 0) {
            $product_ids_by_filter = OSC::helper('filter/common')->getProductIdByFilter($filters);
            $flag_filter = true;
        } else {
            $flag_filter = false;
            $product_ids_by_filter = [];
        }

        $location_code = OSC::helper('catalog/common')->getCustomerLocationCode();

        $result_search = OSC::helper('catalog/search_product')->searchProduct($keywords,
            [
                'location_code' => $location_code,
                'product_ids' => $product_ids_by_filter,
                'flag_filter' => $flag_filter,
                'debug' => $debug,
                'sort' => $sort
            ], $page, $size);

        if (!empty($result_search['list_id']) && is_array($result_search['list_id'])) {
            $product_ids = $result_search['list_id'];
        }

        return [$product_ids, $detect_tag, $result_search['total_item']];
    }

    /**
     * @param $keywords
     * @param $filters
     * @param $sort
     * @param $page
     * @param $size
     * @return array
     */
    protected function _algoliaSearch($keywords, $filters, $sort, $page, $size): array {

        $tag_ids_query = OSC::helper('filter/common')->getTagQueryByFilters($filters);
        $product_ids = [0];
        $location_code = OSC::helper('catalog/common')->getCustomerLocationCode();
        preg_match('/,(\w+),/', $location_code, $matchs);

        $result_search = OSC::helper('catalog/algolia_product')->searchProduct($keywords, [
            'tag_ids_query' => $tag_ids_query ?? [],
            'location_code' => $matchs[1] ?? '',
            'sort' => $sort
        ], $page, $size);
        $hits = array_values($result_search['hits']);

        OSC::cookieSetCrossSite('search_query_id', $result_search['queryID'] ?? '');
        OSC::cookieSetCrossSite('search_index', $result_search['index'] ?? '');

        if (count($hits)) {
            $product_ids = array_column($hits, 'product_id');
        }

        return [$product_ids, $result_search['nbHits']];
    }
}
