<?php

class Helper_Filter_Search extends OSC_Object
{
    public function detectTagInKeyword($keyword): array
    {
        static $cache = [];
        $keyword = trim($keyword);
        $cache_key = md5($keyword);
        if (isset($cache[$cache_key])) {
            return $cache[$cache_key];
        }

        $original_keyword = $keyword;
        $results = [
            'keyword' => $keyword,
        ];

        $all_other_title_of_tag = $this->getAllOtherTitleOfTag(true);
        preg_match_all('/(?<=\s|^)(' . implode('|', $all_other_title_of_tag) . ')(?=\s|$)/', $keyword, $list_tag_detected);

        $list_tag_detected = $list_tag_detected[0] ?? [];
        if (empty($list_tag_detected)) {
            return $results;
        }

        $tag_detected_collection = OSC::model('filter/tag')->getCollection()
            ->addCondition('is_show_filter', Model_Filter_Tag::SHOW_FILTER)
            ->addClause('detect_tag_filter', OSC_Database::RELATION_AND);
        foreach ($list_tag_detected as $tag_detected) {
            $tag_detected_collection->addCondition('other_title', ',' . $tag_detected . ',', OSC_Database::OPERATOR_LIKE, OSC_Database::RELATION_OR, 'detect_tag_filter');
        }

        $tag_detected_collection = $tag_detected_collection->sort('parent_id', 'ASC')->load();
        /* @var $tag_collection Model_Filter_Tag_Collection */
        $tag_collection = OSC::helper('filter/common')->getTagCollection();
        if ($tag_detected_collection->length()) {
            $filters = [];
            $tag_last_in_keyword = end($list_tag_detected);
            uasort($list_tag_detected, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
            $keyword = preg_replace('/(?<=\s|^)(' . implode('|', $list_tag_detected) . ')(?=\s|$)/', '', $keyword);
            $keyword = trim(preg_replace("/\s{2,}/i", ' ', $keyword));
            foreach ($tag_detected_collection as $tag_detected_model) {
                $list_other_title = explode(',', trim($tag_detected_model->data['other_title'], ', '));
                $root_id = 0;
                OSC::helper('filter/common')->getRootNode($tag_detected_model->data['id'], $root_id);
                /* @var $tag_root Model_Filter_Tag */
                $tag_root = $tag_collection->getItemByPK($root_id);
                if ($tag_root && $tag_root->data['is_break_down_keyword']) {
                    $filters[$tag_detected_model->data['id']]['id'] = $tag_detected_model->data['id'];
                    $filters[$tag_detected_model->data['id']]['type_id'] = $root_id;
                    $filters[$tag_detected_model->data['id']]['title'] = $tag_detected_model->data['title'];
                    if (in_array($tag_last_in_keyword, array_map('strtolower', $list_other_title))) {
                        $filters[$tag_detected_model->data['id']]['flag_in_search_box'] = true;
                    }
                }
            }

            $results = [
                'filters' => array_values($filters),
                'keyword' => trim($keyword),
                'original_keyword' => $original_keyword
            ];
        }
        $cache[$cache_key] = $results;
        return $results;
    }

    public function getAllOtherTitleOfTag($return_flat = false): array
    {
        $tag_collections = OSC::model('filter/tag')->getCollection()
            ->addCondition('other_title', ',%,', OSC_Database::OPERATOR_LIKE)
            ->addCondition('is_show_filter', Model_Filter_Tag::SHOW_FILTER)
            ->sort('parent_id')
            ->sort('position')
            ->load();
        $other_title_data = [];
        $other_title_data_flat = [];
        foreach ($tag_collections as $tag_collection) {
            $other_title_data[$tag_collection->data['id']] = array_unique(explode(',', trim(strtolower($tag_collection->data['other_title']), ', ')));
            $other_title_data_flat = array_merge($other_title_data_flat, array_values($other_title_data[$tag_collection->data['id']]));
        }
        if ($return_flat) {
            uasort($other_title_data_flat, function ($a, $b) {
                return strlen($b) - strlen($a);
            });
            return $other_title_data_flat;
        }
        return $other_title_data;
    }

    public function getTrendingKeywords(): array
    {
        $trending_keywords = OSC::helper('core/setting')->get('search/trending_keywords');
        $trending_keywords_manual = OSC::helper('core/setting')->get('search/trending_keywords_manual');
        if (is_array($trending_keywords_manual)) {
            $keywords = $trending_keywords_manual['list'] ?? [];
            $expired = $trending_keywords_manual['expired'] ?? 0;
            if (is_array($keywords) && count($keywords) > 0 && time() <= $expired) {
                return $keywords;
            }
        }
        if (is_array($trending_keywords) && count($trending_keywords) > 0) {
            $trending_keywords = array_keys($trending_keywords);
            return array_slice($trending_keywords, 0, 3);
        }
        return [];
    }

    public function getPopularCollections(): array
    {
        $popular_categories = OSC::helper('core/setting')->get('search/popular_collections');
        if (!$popular_categories || !is_array($popular_categories)) {
            return [];
        }
        $collection_ids = array_column($popular_categories, 'collection_id');
        if (count($collection_ids) < 1) {
            return [];
        }

        $collections = OSC::model('catalog/collection')->getCollection()
            ->addField('title', 'slug', 'image')
            ->addCondition('collection_id', $collection_ids, OSC_Database::OPERATOR_IN)
            ->load();
        if (!$collections->length()) {
            return [];
        }
        /**
         * @var $collection_model Model_Catalog_Collection
         */
        foreach ($popular_categories as $key => $popular_category) {
            $collection_model = $collections->getItemByPK($popular_category['collection_id']);
            $title = $popular_category['title'] ?? '';
            $image = $popular_category['image'] ?? '';
            if (!$title) {
                $popular_categories[$key]['title'] = $collection_model->data['custom_title'] ?: $collection_model->data['title'];
            }
            if (!$image) {
                $image = $collection_model->data['image'] ?: '';
            }
            $popular_categories[$key]['image'] = $image ? OSC::core('aws_s3')->getStorageUrl($image) : '';
            $popular_categories[$key]['url'] = $collection_model->getDetailUrl();
        }
        return array_values($popular_categories);
    }

    public function getSetting(): array
    {
        $settings = OSC::model('core/setting')->getCollection()
            ->addCondition('setting_key', ['search/popular_collections', 'search/trending_keywords_manual', 'search/trending_keywords'], OSC_Database::OPERATOR_IN)
            ->load();
        $data_config_search = [];
        foreach ($settings->toArray() as $setting) {
            $data_config_search[$setting['setting_key']] = $setting['setting_value'];
        }
        return $data_config_search;
    }

    public function getTagByProductId($product_id): array
    {
        $tag_product_rel = OSC::model('filter/tagProductRel')->getCollection()
            ->addField('tag_id')
            ->addCondition('product_id', $product_id, OSC_Database::OPERATOR_EQUAL)
            ->load();
        return array_column($tag_product_rel->toArray(), 'tag_id');
    }

    public function getSortOptions($only_key = false): array
    {
        $sort = [
            'default' => 'Most Relevant',
            'solds' => 'Best Selling',
            'newest' => 'Newest'
        ];
        if ($only_key) {
            return array_keys($sort);
        }
        return $sort;
    }

    public function getTagKeyword(): array
    {
        // get all leaves
        $leave_tags = OSC::helper('filter/common')->getAllLeaves();

        $tag_keys = [];

        foreach ($leave_tags as $tag) {
            $other_title_data = $tag['other_title'] ? array_unique(explode(',', trim(strtolower($tag['other_title']), ', '))) : [];
            $tag_title = [trim(strtolower($tag['title']))];
            $tag_keys[$tag['id']] = array_unique(array_merge($other_title_data, $tag_title));
        }
        return $tag_keys;
    }
}
