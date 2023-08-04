<?php


class Helper_Catalog_Algolia_Product {

    const SYNC_TYPE_DELETE_PRODUCT = 'delete';
    const SYNC_TYPE_UPDATE_PRODUCT = 'update';

    public function resync() {
        try {
            set_time_limit(0);
            ini_set('memory_limit', '-1');

            $list_product = [];

            $collection = OSC::model('catalog/product')
                ->getCollection()
                ->addCondition('discarded', 0) // Product is not  discard
                ->addCondition('listing', 1) // Product is listing
                ->addField('product_id, title, topic, description, content, product_type, selling_type, discarded, listing, solds, supply_location, added_timestamp, modified_timestamp')
                ->load();

            foreach ($collection as $product) {
                try {
                    $list_product[] = $this->_makeDataProductIndex($product);
                } catch (Exception $exception) { }

                if (count($list_product) >= 1000) {
                    OSC::core('algolia')->createOrReplaceRecords(ALGOLIA_PRODUCT_INDEX, $list_product);
                    $list_product = [];
                }
            }

            if (count($list_product)) {
                OSC::core('algolia')->createOrReplaceRecords(ALGOLIA_PRODUCT_INDEX, $list_product);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    protected function _makeDataProductIndex(Model_Catalog_Product $product): array
    {
        $product_id = $product->getId();

        $supply_locations = explode(',', $product->data['supply_location'] ?? []);
        $product_type_ukeys = explode(',', $product->data['product_type'] ?? []);
        $product_types = [];
        $product_variant = '';
        $image_url = '';

        try {
            // collect product_type
            if ($product->data['product_type']) {
                $collection = OSC::model('catalog/productType')->loadByListUKey($product_type_ukeys)->toArray();
                foreach ($collection as $item) {
                    $product_types[] = "{$item['title']} {$item['custom_title']} {$item['group_name']} {$item['tab_name']}";
                }
            }

            // collect tag
            $filter_tag_titles = $this->_getProductTag($product);

            //collect variant
            $variant_collection = $product->getVariants();
            $is_campaign = $product->isCampaignMode();
            $product_type_variant_ids = [];
            /* @var $variant Model_Catalog_Product_Variant */
            foreach ($variant_collection as $variant) {

                if (empty($image_url)) {
                    $image_url = $variant->getImageFeaturedUrl() ?? $product->getFeaturedImageUrl();
                }

                if($is_campaign) {
                    $product_type_variant_ids[] = $variant->data['product_type_variant_id'];
                } else {
                    $product_variant .= ' ' . $variant->getTitle();
                }
            }

            if (!empty($product_type_variant_ids)) {
                $product_type_variants = OSC::model('catalog/productType_variant')
                    ->getCollection()
                    ->addField('title', 'custom_title')
                    ->addCondition('id', $product_type_variant_ids, OSC_Database::OPERATOR_IN)
                    ->setLimit(count($product_type_variant_ids))
                    ->load();

                /* @var $type_variant Model_Catalog_ProductType_Variant */
                foreach ($product_type_variants as $type_variant) {
                    $product_variant .= ' ' . $type_variant->data['title'] . ' ' . $type_variant->data['custom_title'];
                }
            }

            // Detect sold group
            $sold = (int)$product->data['solds'];
            $sold_group = $this->getSoldGroup($sold);

        } catch (Exception $ex) {
            OSC::logFile('--Sync algolia error ' . $product->getId() . ': ' . $ex->getMessage(), 'algolia_log_' . date('Ymd'));
            throw new Exception($ex->getMessage());
        }

        return [
            'title' => $product->data['title'] ?? '',
            'description' => $product->data['description'] ?? '',
            'objectID' => $product_id,
            'product_id' => $product_id,
            'content' => $product->data['content'] ?? '',
            'imageURL' => OSC::wrapCDN(OSC::helper('core/image')->imageOptimize($image_url, 500, 500, false, true)),
            'topic' => $product->data['topic'] ?? '',
            'product_tag' => $filter_tag_titles,
            'listing' => (int)$product->data['listing'],
            'product_title' => $product->getProductTitle(true, false),
            'supply_location' => array_values(array_filter($supply_locations)),
            'selling_type' => (int) $product->data['selling_type'],
            'discarded' => (int)$product->data['discarded'],
            'product_type' => $product_types,
            'product_variant' => trim($product_variant),
            'solds' => $sold,
            'sold_group' => $sold_group,
            'added_timestamp' => (int)$product->data['added_timestamp']
        ];
    }

    /**
     *
     * Search product in frontend
     * @param string $keywords
     * @param array $options
     * @param int $page
     * @param int $page_size
     * @return array
     */
    public function searchProduct(string $keywords, array $options = [], int $page = 1, int $page_size = 5): array {

        $filters = [];

        $keywords = strtolower($keywords);

        $sort = $options['sort'] ?? 'default';
        if (!in_array($sort, OSC::helper('filter/search')->getSortOptions(true))) {
            $sort = 'default';
        }

        $location = '';
        if (isset($options['location_code']) && !empty($options['location_code'])) {
            $countries_render_beta_products = OSC::helper('core/setting')->get('catalog/product_listing/country_render_beta_product');
            $shipping_location = OSC::helper('catalog/common')->getCustomerShippingLocation();
            if (!in_array($shipping_location['country_code'], $countries_render_beta_products)) {
                $location = "supply_location:{$options['location_code']}";
            } else {
                $location = "(supply_location:{$options['location_code']} OR selling_type:" . Model_Catalog_Product::TYPE_SEMITEST . ')';
            }
        }

        // Setting filter query by sort
        $index_name = ALGOLIA_PRODUCT_INDEX;

        switch ($sort) {
            case 'solds':
                $index_name = ALGOLIA_REPLICAS_VIRTUAL_BEST_SELL;
                break;
            case 'newest':
                $index_name = ALGOLIA_REPLICAS_VIRTUAL_NEWEST;
                break;
        }

        // Setting filter query by location
        if ($location) {
            $filters[] = $location;
        }

        // Setting filter query by tag
        if (is_array($options['tag_ids_query']) && count($options['tag_ids_query'])) {
            /* @var $tag_collection Model_Filter_Tag_Collection */
            /* @var $tag_root Model_Filter_Tag */
            /* @var $tag_children Model_Filter_Tag */

            $tag_collection = OSC::helper('filter/common')->getTagCollection();
            $query_filter_tags = [];

            foreach ($options['tag_ids_query'] as $tag_root_id => $tag_children_ids) {
                $tag_root = $tag_collection->getItemByPK($tag_root_id);

                foreach ($tag_children_ids as $tag_children_id) {
                    $tag_children = $tag_collection->getItemByPK($tag_children_id);
                    if ($tag_children) {
                        $query_filter_tags[$tag_root_id][] = "product_tag.{$tag_root->data['title']}: {$tag_children->data['title']}";
                    }
                }
            }

            $facet_filters = [];
            foreach ($query_filter_tags as $query_filter_string) {
                $facet_filters[] = array_values($query_filter_string);
            }
        }

        $result = OSC::core('algolia')->filterRecords($index_name, $keywords, [
            'filters' => implode(' AND ', $filters),
            'facetFilters' => $facet_filters ?? [],
            'page' => $page,
            'page_size' => $page_size,
            'attributes' => ['product_id']
        ]);

        $result['index'] = $index_name;

        return $result;
    }

    /**
     *
     * @param Model_Catalog_Product $product
     * @return $this
     */
    public function addProduct(Model_Catalog_Product $product) {

        if ($product->getId() < 1) {
            return $this;
        }

        if (intval($product->data['discarded']) || !intval($product->data['listing'])) {
            $this->deleteProduct($product->getId());
        } else {
            OSC::core('algolia')->updateRecords(ALGOLIA_PRODUCT_INDEX, [$this->_makeDataProductIndex($product)]);
        }
        return $this;
    }

    /**
     *
     * @param int $product_id
     * @return $this
     */
    public function deleteProduct(int $product_id) {
        try {
            OSC::core('algolia')->deleteRecords(ALGOLIA_PRODUCT_INDEX, [$product_id]);
        } catch (Exception $exception) {

        }

        return $this;
    }

    protected function _getProductTag(Model_Catalog_Product $product) {
        try {
            /* @var $tag_collection OSC_Database_Model_Collection */
            $tag_collection = OSC::helper('filter/common')->getTagCollection();

            $filter_tag_titles = [];

            // get all tag of prod and parent tag of it
            $product_tags = OSC::model('filter/tagProductRel')
                ->getCollection()
                ->addField('tag_id', 'product_id')
                ->addCondition('product_id', $product->getId())
                ->load();

            $tags = OSC::model('filter/tag')->getCollection();

            foreach ($product_tags as $product_tag) {
                $tag = $tag_collection->getItemByPK($product_tag->data['tag_id']);
                if ($tag) {
                    $tags->addItem($tag);
                    $tag_parent = $tag_collection->getItemByPK($tag->data['parent_id']);
                    if ($tag_parent->data['parent_id'] != 0) { // if # root tag then add
                        $tags->addItem($tag_parent);
                    }
                }
            }

            // add tag with title, other tag to field algolia
            /* @var $tag Model_Filter_Tag */
            foreach ($tags as $tag) {
                $root_id = 0;
                OSC::helper('filter/common')->getRootNode($tag->getId(), $root_id);

                $root_tag = $tag_collection->getItemByPK($root_id);
                /* @var $root_tag Model_Filter_Tag */
                if ($root_tag) {
                    $other_tags = explode(',', $tag->data['other_title']);
                    $filter_tag_titles[$root_tag->data['title']][] = $tag->data['title'];
                    foreach ($other_tags as $other) {
                        if (!empty($other)) {
                            $filter_tag_titles[$root_tag->data['title']][] = $other;
                        }
                    }
                }
            }

            return $filter_tag_titles;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    /**
     * @param int $sub_day
     * @return array
     */
    public function countUniqueVisitProductsByDay(int $sub_day): array {

        $key_query = 'count_unique_product';
        /* @var $DB OSC_Database_Adapter */
        $DB = OSC::core('database')->getAdapter();
        $time_rank = $this->_getTimeRank($sub_day, false);
        $start_time = $time_rank['start_time'];
        $end_time = $time_rank['end_time'];
        $query = "SELECT product_id, sum(report_value) as `value` FROM osc_report_product_tracking WHERE `date` >= " . intval($start_time) . ' AND `date` <= ' . intval($end_time) . ' GROUP BY product_id';

        $DB->query($query, null, $key_query);
        $rows = $DB->fetchArrayAll($key_query) ?? [];
        $DB->free($key_query);

        $results = [];

        foreach ($rows as $row) {
            $results[$row['product_id']] = $row['value'];
        }
        return $results;
    }

    /**
     * @param $sub_day
     * @return array
     */
    public function countOrderProductsByDay($sub_day): array {
        $time_rank = $this->_getTimeRank($sub_day);
        $start_time = $time_rank['start_time'];
        $end_time = $time_rank['end_time'];

        $data_condition = [
            'o.order_status != "cancelled"',
            'i.order_id = o.order_id',
            'i.additional_data NOT LIKE \'%"resend":{"resend":"%\'',
            'i.shop_id = '. OSC::getShop()->getId()
        ];

        $data_condition[] = 'i.added_timestamp >= ' . $start_time;

        $data_condition[] = 'i.added_timestamp <= ' . $end_time;

        $data_condition = ' WHERE ' . implode(' AND ', $data_condition);

        /* @var $DB_MASTER OSC_Database */
        $key_query = 'fetch_report_record';
        $DB_MASTER = OSC::core('database')->getAdapter('db_master_read');

        $DB_MASTER->query("SELECT product_id, COUNT(DISTINCT (o.order_id)) AS orders FROM osc_catalog_order_item i, osc_catalog_order o {$data_condition} GROUP BY product_id", null, $key_query);

        $rows = $DB_MASTER->fetchArrayAll($key_query);
        $DB_MASTER->free($key_query);
        $result = [];

        foreach ($rows as $row) {
            $result[$row['product_id']] = $row['orders'];
        }

        return $result;
    }

    protected function _getTimeRank($sub_day, $timestampOutput = true) {
        $day_start = gmdate('Y-m-d 00:00:00', time() - $sub_day*24*60*60);
        $day_end = gmdate('Y-m-d 23:59:59', time() - 24*60*60);
        if ($timestampOutput) {
            return [
                'start_time' => strtotime($day_start),
                'end_time' => strtotime($day_end),
            ];
        }
        return [
            'start_time' => gmdate('Ymd', strtotime($day_start)),
            'end_time' => gmdate('Ymd', strtotime($day_end)),
        ];
    }

    /**
     * @param int $sold
     * @return int
     */
    public function getSoldGroup(int $sold): int {

        $sold_group = 1;
        if ($sold >= 1 && $sold <= 9) {
            $sold_group = 2;
        } else if ($sold >= 10 && $sold <= 99) {
            $sold_group = 3;
        } else if ($sold >= 100 && $sold <= 999) {
            $sold_group = 4;
        } else if ($sold >= 1000 && $sold <= 9999) {
            $sold_group = 5;
        } else if ($sold >= 10000) {
            $sold_group = 6;
        }

        return $sold_group;
    }

    /**
     * @param int $sold
     * @return int
     */
    public function getSoldShortTermGroup(int $sold): int {

        $sold_short_term_group = 1;
        if ($sold >= 1 && $sold <= 9) {
            $sold_short_term_group = 2;
        } else if ($sold >= 10 && $sold <= 99) {
            $sold_short_term_group = 3;
        } else if ($sold >= 100 && $sold <= 499) {
            $sold_short_term_group = 4;
        } else if ($sold >= 500 && $sold <= 999) {
            $sold_short_term_group = 5;
        } else if ($sold >= 1000) {
            $sold_short_term_group = 6;
        }

        return $sold_short_term_group;
    }

    /**
     * @param float $cr_short_term
     * @return int
     */
    public function getCRShortTermGroup(float $cr_short_term): int {

        $cr_short_term_group = 1;
        if ($cr_short_term > 0 && $cr_short_term <= 2) {
            $cr_short_term_group = 2;
        } else if ($cr_short_term > 2 && $cr_short_term <= 4) {
            $cr_short_term_group = 3;
        } else if ($cr_short_term > 4 && $cr_short_term <= 6) {
            $cr_short_term_group = 4;
        } else if ($cr_short_term > 6 && $cr_short_term <= 10) {
            $cr_short_term_group = 5;
        } else if ($cr_short_term > 10) {
            $cr_short_term_group = 6;
        }

        return $cr_short_term_group;
    }

}

