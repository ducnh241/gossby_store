<?php

use Algolia\AlgoliaSearch\Exceptions\MissingObjectId;
use \Algolia\AlgoliaSearch\SearchClient;
use Algolia\AlgoliaSearch\SearchIndex;

class OSC_Algolia {

    protected $client;

    public function __construct() {
        if (!$this->client) {
            $this->client = SearchClient::create(ALGOLIA_ID, ALGOLIA_API_KEY);
        }
    }

    /**
     * @param string $index_name
     * @return SearchIndex
     * @throws Exception
     */
    protected function _initIndex(string $index_name): SearchIndex {
        if (!$index_name) {
            throw new Exception('Index name is empty');
        }
        return $this->client->initIndex($index_name.trim());
    }

    /**
     * @param string $index_name
     * @param array $records
     * @return void
     * @throws MissingObjectId
     */
    public function createOrReplaceRecords(string $index_name, array $records) {
        $this->_initIndex($index_name)->saveObjects($records);
    }

    /**
     * @param string $index_name
     * @param array $records
     * @return void
     * @throws Exception
     */
    public function updateRecords(string $index_name, array $records) {
        $this->_initIndex($index_name)->partialUpdateObjects($records,[
            'createIfNotExists' => true
        ]);
    }

    /**
     * @param string $index_name
     * @param array $product_ids
     * @return void
     * @throws Exception
     */
    public function deleteRecords( string $index_name, array $product_ids) {
        $this->_initIndex($index_name)->deleteObjects($product_ids);
    }

    /**
     * @param $index_name
     * @param $query
     * @param array $options
     * @return mixed
     * @throws Exception
     */
    public function filterRecords(string $index_name, string $query, array $options = []) {

        $requestOptions = [];

        if (isset($options['filters']) && $options['filters']) {
            $requestOptions['filters'] = $options['filters'];
        }
        if (isset($options['facetFilters']) && !empty($options['facetFilters'])) {
            $requestOptions['facetFilters'] = $options['facetFilters'];
        }

        if (isset($options['page']) && $options['page']) {
            $requestOptions['page'] = intval($options['page']) - 1;
        }

        if (isset($options['page_size']) && $options['page_size']) {
            $requestOptions['hitsPerPage'] = intval($options['page_size']);
        }

        if (isset($options['attributes']) && $options['attributes']) {
            $requestOptions['attributesToRetrieve'] = $options['attributes'];
        }
        $requestOptions['clickAnalytics'] = true;
        $requestOptions['userToken'] = Abstract_Frontend_Controller::getTrackingKey();

        return $this->_initIndex($index_name)->search($query, $requestOptions);
    }

    /**
     * @param string $index_name
     * @param array $settings
     * @return void
     * @throws Exception
     */
    public function settingConfig(string $index_name, array $settings) {
        $this->_initIndex($index_name)->setSettings($settings);
    }

}