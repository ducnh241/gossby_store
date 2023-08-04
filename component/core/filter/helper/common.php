<?php

class Helper_Filter_Common extends OSC_Object {
    protected $_tag_collection = null;

    public function buildFilter($flag_build_by_collection = false, $array_compare_id_in_collection = []) {
        $parent_children_relationship = $this->getParentChildrenRelationship()['parent_children_relationship'];

        foreach ($parent_children_relationship as $key => $tag) {
            if ($tag['root'] != 1) {
                unset($parent_children_relationship[$key]);
            }
        }

        if ($this->_tag_collection == null) {
            $this->_tag_collection = OSC::model('filter/tag')->getCollection()->load();
        }

        $collection = $this->_tag_collection;

        $data = [];

        foreach ($parent_children_relationship as $tag) {
            if (count($tag['children']) > 0) {

                $model_tag = $collection->getItemByPK($tag['id']);

                $data[$tag['id']] = [
                    'id' => $model_tag->getId(),
                    'title' => $model_tag->data['title'],
                    'position' => $model_tag->data['position'],
                    'type' => $model_tag->data['type']
                ];

                foreach ($tag['children'] as $tag_id) {
                    $model_tag_children = $collection->getItemByPK($tag_id);

                    if ($model_tag_children->data['is_show_filter'] == Model_Filter_Tag::HIDE_FILTER) {
                        continue;
                    }

                    if ($flag_build_by_collection && count($array_compare_id_in_collection) > 0 && !in_array($tag_id, $array_compare_id_in_collection)) {
                        continue;
                    }

                    $data[$tag['id']]['children'][] = [
                        'id' => $model_tag_children->getId(),
                        'title' => $model_tag_children->data['title'],
                        'position' => $model_tag_children->data['position'],
                    ];
                }
            }
        }

        foreach ($data as $key => $tag_root) {
            if (!isset($tag_root['children']) || count($tag_root['children']) < 1) {
                unset($data[$key]);
            }
        }

        return array_values($data);
    }

    public function getParentTags()
    {
        if ($this->_tag_collection == null) {
            $this->_tag_collection = OSC::model('filter/tag')
                ->getCollection()->load();
        }

        $tag_map = [];

        foreach ($this->_tag_collection as $item) {
            if ($item->data['parent_id'] > 0) {
                $tag_map[$item->getId()] = $item->data['parent_id'];
            } else {
                $tag_map[$item->getId()] = 0;
            }
        }

        return $tag_map;
    }

    /**
     * @param array $filters
     * @return array|void
     */
    public function getTagQueryByFilters(array $filters) {
        $data_filter = [];

        if (count($filters) < 1) {
            return [];
        }

        foreach ($filters as $tag_id) {
            $root_id = null;

            $this->getRootNode($tag_id, $root_id);

            if ($root_id != null) {
                $data_filter[$root_id][] = $tag_id;
            }
        }

        $parent_children_relationship = $this->getParentChildrenRelationship()['parent_children_relationship'];
        $tag_ids_leaves = $this->getParentChildrenRelationship()['tag_ids_leaves'];

        $tag_id_query = [];

        /* co che lay con thi bo cha
        foreach ($data_filter as $root_id => $list_tag) {
            foreach ($list_tag as $key => $tag_id) {
                if ($parent_children_relationship[$tag_id]['children']) {

                    $result = array_diff($list_tag, $parent_children_relationship[$tag_id]['children']);

                    if (count($result) == count($list_tag)) {

                        if (!is_array($tag_id_query[$root_id])) {
                            $tag_id_query[$root_id] = [];
                        }

                        $tag_id_query[$root_id] = array_merge($tag_id_query[$root_id], $parent_children_relationship[$tag_id]['children']);
                    }
                } else {
                    $tag_id_query[$root_id][] = intval($tag_id);
                }
            }
        }
        */
        /* co che lay tat ca con, cha */
        foreach ($data_filter as $root_id => $list_tag) {
            foreach ($list_tag as $key => $tag_id) {
                if ($parent_children_relationship[$tag_id]['children']) {
                    if (!is_array($tag_id_query[$root_id])) {
                        $tag_id_query[$root_id] = [];
                    }

                    $tag_id_query[$root_id] = array_merge($tag_id_query[$root_id], $parent_children_relationship[$tag_id]['children']);
                } else {
                    $tag_id_query[$root_id][] = intval($tag_id);
                }
            }
        }

        if (count($tag_id_query) < 1) {
            return [];
        }

        foreach ($tag_id_query as $root_id => $list_tag) {
            foreach ($list_tag as $key => $tag_id) {
                if (!in_array($tag_id, $tag_ids_leaves)) {
                    unset($tag_id_query[$root_id][$key]);
                }
            }

            $tag_id_query[$root_id] = array_unique($tag_id_query[$root_id]);
        }

        return $tag_id_query;
    }

    public function getProductIdByFilter($filters)
    {
        $tag_id_query = $this->getTagQueryByFilters($filters);

        $query = '';

        $count = 1;

        foreach ($tag_id_query as $list_tag) {
            if ($count == 1) {
                $query = 'SELECT DISTINCT product_id FROM ' . OSC::systemRegistry('db_prefix') . 'filter_tag_product_rel WHERE tag_id IN (' . implode(',', $list_tag) . ') ';
            } else {
                $query .= 'AND product_id IN (SELECT DISTINCT product_id FROM ' . OSC::systemRegistry('db_prefix') . 'filter_tag_product_rel WHERE tag_id IN (' . implode(',', $list_tag) . ')) ';
            }

            $count++;
        }
        $DB = OSC::core('database')->getWriteAdapter();

        $DB->query($query, null, 'fetch_queue');

        $rows = $DB->fetchArrayAll('fetch_queue');

        return array_column($rows, 'product_id');
    }

    public function getParentChildrenRelationship()
    {
        if ($this->_tag_collection == null) {
            $this->_tag_collection = OSC::model('filter/tag')
                ->getCollection()->load();
        }

        $data = [];
        foreach ($this->_tag_collection as $tag) {
            if (isset($data[$tag->getId()])) {
                $data[$tag->getId()]['id'] = $tag->getId();
                $data[$tag->getId()]['title'] = $tag->data['title'];
                $data[$tag->getId()]['type'] = $tag->data['type'];
            } else {
                $data[$tag->getId()] = [
                    'id' => $tag->getId(),
                    'title' => $tag->data['title'],
                    'type' => $tag->data['type'],
                    'required' => $tag->data['required']
                ];
            }

            if ($tag->data['parent_id'] == 0) {
                $data[$tag->getId()]['root'] = 1;
            }

            if ($tag->data['parent_id'] > 0) {
                $data[$tag->data['parent_id']]['children'][] = $tag->getId();
            }
        }

        $tag_ids_leaves = [];

        $_data = $data;

        foreach ($data as $tag_id => $tag_value) {
            if (isset($tag_value['children']) && count($tag_value['children']) > 0) {
                $result = [];
                $this->_getChildrenId($data, $tag_value['children'], $result);

                $_data[$tag_id]['children'] = array_merge($tag_value['children'], $result);
                $_data[$tag_id]['children'] = array_unique($_data[$tag_id]['children']);
            } else {
                $tag_ids_leaves[] = $tag_id;
            }
        }

        return [
            'parent_children_relationship' => $_data,
            'tag_ids_leaves' => $tag_ids_leaves
        ];
    }

    protected function _getChildrenId($data, $tag_value, &$result)
    {
        foreach ($tag_value as $id) {
            $result[] = $id;

            if (count($data[$id]['children']) > 0) {
                $this->_getChildrenId($data, $data[$id]['children'], $result);
            }
        }
    }


    public function getRootNode($tag_id, &$root_id)
    {
        $parent_array = $this->getParentTags();

        if (isset($parent_array[$tag_id])) {
            if ($parent_array[$tag_id] > 0) {
                $this->getRootNode($parent_array[$tag_id], $root_id);
            } else {
                $root_id = $tag_id;
                return;
            }
        }

    }

    protected $_tag_leave = null;

    public function getAllLeaves()
    {
        if ($this->_tag_leave == null) {
            // get all leaves
            $query = 'SELECT id, title, other_title FROM ' . OSC::systemRegistry('db_prefix') . 'filter_tag WHERE 
            parent_id != 0 AND id NOT IN (SELECT DISTINCT parent_id FROM ' . OSC::systemRegistry('db_prefix') . 'filter_tag WHERE parent_id != 0)';

            $DB = OSC::core('database')->getWriteAdapter();

            $DB->query($query, null, 'fetch_queue');

            $rows = $DB->fetchArrayAll('fetch_queue');

            if (count($rows) < 1) {
                $this->_tag_leave = [];
                return $this->_tag_leave;
            }

            $this->_tag_leave = $rows;
        }

        return $this->_tag_leave;
    }

    public function getListTagSettingProduct() {
        if ($this->_tag_collection == null) {
            $this->_tag_collection = OSC::model('filter/tag')
                ->getCollection()
                ->sort('required', OSC_Database::ORDER_DESC)
                ->sort('title')
                ->load();
        }

        $filter_setting = $this->getParentChildrenRelationship();

        $tag_ids_leaves = $filter_setting['tag_ids_leaves'];
        $parent_children_relationship = $filter_setting['parent_children_relationship'];

        foreach ($parent_children_relationship as $key => $tag) {
            if ($tag['root'] == 1) {
                foreach ($tag['children'] as $_key => $tag_leaves) {
                    if (!in_array($tag_leaves, $tag_ids_leaves)){
                        unset($parent_children_relationship[$key]['children'][$_key]);
                    }
                }
            } else {
                unset($parent_children_relationship[$key]);
            }
        }

        $tag_collection = $this->_tag_collection;

        $data = [];

        foreach ($parent_children_relationship as $key => $value) {
            $data[$value['id']] = [
                'id' => $value['id'],
                'title' => $value['title'],
                'required' => $value['required']
            ];

            if (isset($value['children']) && count($value['children']) > 0) {
                foreach ($value['children'] as $_key => $tag_id) {
                    $tag_children = $tag_collection->getItemByPK($tag_id);

                    $data[$value['id']]['children'][] = [
                        'id' => $tag_id,
                        'title' => $tag_children->data['title']
                    ];
                }
            }
        }

        return $data;
    }

    public function verifyTagProductRel($product_tag_ids) {
        $leaves = $this->getAllLeaves();
        $leaves_ids = array_column($leaves, 'id');

        foreach ($product_tag_ids as $product_tag_id) {
            if ($product_tag_id < 1) {
                throw new Exception('Product tag id not found');
            }

            if (!in_array($product_tag_id, $leaves_ids)) {
                throw new Exception('The tag attached to the product needs to be a leaf. ID#' . $product_tag_id);
            }
        }
    }

    public function validateFilterOptions($_filters) {
        if (!is_array($_filters) || count($_filters) < 1) {
            return [];
        }

        $filters = [];

        foreach ($_filters as $filter_id) {
            if (!is_numeric($filter_id) || intval($filter_id) < 1) {
                continue;
            }

            $filters[] = $filter_id;
        }

        return $filters;
    }


    public function getParentTagsAndAllTheirChildren()
    {
        $all_tag = OSC::model('filter/tag')
            ->getCollection()
            ->addField('id', 'title', 'parent_id')
            ->load()
            ->toArray();

        $_all_tag = [];
        foreach ($all_tag as $tag) {
            $_all_tag[$tag['id']] = $tag;
        }

        foreach ($_all_tag as $tag) {
            if ($tag['parent_id'] === 0) {
                continue;
            } else {
                $_tag = $tag;
                while (isset($_tag) && $_tag['parent_id'] !== 0) {
                    $_tag = $_all_tag[$_tag['parent_id']];
                }

                if (isset($_tag)) {
                    $_all_tag[$_tag['id']]['children'][] = $tag;
                }
            }
        }

        return array_filter($_all_tag, function ($tag) {
            return !empty($tag['children']);
        });

    }

    /**
     * @return OSC_Database_Model_Collection
     * @throws OSC_Exception_Runtime
     */
    public function getTagCollection() {
        if ($this->_tag_collection == null) {
            $this->_tag_collection = OSC::model('filter/tag')->getCollection()->load();
        }

        return $this->_tag_collection;
    }
}
