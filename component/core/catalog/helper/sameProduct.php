<?php

class Helper_Catalog_SameProduct extends OSC_Object {
    public function fetchSameProductByNeo4j(Model_Catalog_Product_Collection $listProduct, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $listProductId = [];
            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    if (!in_array($item->data['product_id'], $listProductId)) {
                        $listProductId[] = $item->data['product_id'];
                    }
                }
            }

            $listResult = [];
            if (!empty($listProductId)) {
                $listResult = OSC::helper('catalog/frequentlyBoughtTogether')->fetchByMultiProducts($listProductId, $limit, 1, Helper_Catalog_Common::displayedProductRegistry());
            }

            return $listResult;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function fetchSameProductByRedisGraph(Model_Catalog_Product_Collection $listProduct, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $listProductId = [];
            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    if (!in_array($item->data['product_id'], $listProductId)) {
                        $listProductId[] = $item->data['product_id'];
                    }
                }
            }

            $listResult = [];
            if (!empty($listProductId)) {
                //$listResult = OSC::helper('redisGraph/query')->fetchByMultiProducts($listProductId, $limit, 1, Helper_Catalog_Common::displayedProductRegistry());
            }

            return $listResult;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function fetchSameProductBySoldCollection(Model_Catalog_Product_Collection $listProduct, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $listCollection = [];
            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    $collections = $item->getCollections()->toArray();
                    if (!empty($collections)) {
                        foreach ($collections as $collection) {
                            if (isset($collection['collection_id']) && !in_array($collection['collection_id'], $listCollection) && !in_array($collection['title'], ['Best Selling', 'New Arrivals'])) {
                                $listCollection[] = $collection['collection_id'];
                            }
                        }
                    }
                }
            }

            $listResult = [];
            if (!empty($listCollection)) {
                $listCollection = OSC::model('catalog/collection')->getNullCollection()
                    ->addCondition('collection_id', $listCollection, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_AND)
                    ->load();

                //Get the collection that has the minimum item
                $selectedCollection = [];
                foreach ($listCollection as $item) {
                    try {
                        if (empty($selectedCollection) || $selectedCollection->collectionLength() > $item->collectionLength()) {
                            $selectedCollection = $item;
                        }
                    } catch (Exception $exception) {
                        $selectedCollection = $item;
                    }
                }

                //Get list array product [product_id, solds] in list collection
                $listProduct = array_map(function ($value) use ($minimum_orders) {
                    if (isset($value['solds']) && !empty($value['solds']) && $value['solds'] > $minimum_orders) {
                        return [
                            'product_id' => $value['product_id'],
                            'solds' => $value['solds']
                        ];
                    }
                }, $item->getProducts()->toArray());

                $listProduct = array_unique($listProduct, SORT_REGULAR);

                //Reorder list product by solds number
                usort($listProduct, function ($a, $b) {
                    return $b['solds'] <=> $a['solds'];
                });

                //Get list product_id by limit
                $count = 0;
                foreach ($listProduct as $item) {
                    if ($count >= $limit)
                        break;

                    if (isset($item['product_id']) && !empty($item['product_id']) && !in_array($item['product_id'], $skip_ids)) {
                        $listResult[] = $item['product_id'];
                        $count++;
                    }
                }
            }

            return $listResult;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function fetchSameProductByTag(Model_Catalog_Product_Collection $listProduct, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $listTag = [];
            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    if (isset($item->data['tags']) && !empty($item->data['tags']) && is_array($item->data['tags'])) {
                        $listTag = array_merge($listTag, $item->data['tags']);
                    }
                }
            }

            if (!empty($listTag)) {
                $listTag = array_unique($listTag);
                //Remove all tag start with meta:
                $listTag = array_filter($listTag, function ($e) {
                    return stripos($e, 'meta:') === false;
                });

                $listProduct = OSC::model('catalog/product')->getCollection()
                    ->addCondition('tags', "(^|,)(" . implode('|', $listTag) . ")(,|$)", OSC_Database::OPERATOR_REGEXP, OSC_Database::RELATION_AND)
                    ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                    ->sort('added_timestamp', OSC_Database::ORDER_DESC)
                    ->load();
            }

            $listProductId = [];
            $listResult = [];
            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    $listProductId[$item->data['product_id']] = count(array_intersect($listTag, $item->data['tags']));
                }

                if (!empty($listProductId)) {
                    arsort($listProductId);
                }

                $count = 0;
                foreach ($listProductId as $productId => $item) {
                    if ($count >= $limit)
                        break;

                    if (!in_array($productId, $skip_ids)) {
                        $listResult[] = $productId;
                        $count++;
                    }
                }
            }

            return $listResult;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function fetchSameProductByTitle(Model_Catalog_Product_Collection $listProduct, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $listResult = [];

            $listModelProduct = [];
            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    $listModelProduct[] = $item;
                }
            }

            $relatedProducts = OSC::model('catalog/product')->getNullCollection()->loadRelatedByList($listModelProduct);

            if (!empty($relatedProducts)) {
                $count = 0;
                foreach ($relatedProducts as $product) {
                    if ($count >= $limit)
                        break;

                    if (isset($product->data['product_id']) && !in_array($product->data['product_id'], $skip_ids)) {
                        $listResult[] = $product->data['product_id'];
                        $count++;
                    }
                }
            }

            return $listResult;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function fetchSameProductByBestSeller(int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $listResult = [];

            $listProduct = OSC::model('catalog/product')->getCollection()
                ->addCondition('product_id', $skip_ids, OSC_Database::OPERATOR_NOT_IN, OSC_Database::RELATION_AND)
                ->addCondition('solds', $minimum_orders, OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->sort('solds', OSC_Database::ORDER_DESC)
                ->setPageSize($limit)
                ->setCurrentPage(1)
                ->load();

            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    $listResult[] = $item->data['product_id'];
                }
            }

            return $listResult;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function fetchRandomProduct(int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        try {
            $listResult = [];

            //Fetch limit by random
            $listProduct = OSC::model('catalog/product')->getCollection()
                ->addCondition('product_id', $skip_ids, OSC_Database::OPERATOR_NOT_IN, OSC_Database::RELATION_AND)
                ->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                ->setPageSize($limit)
                ->setCurrentPage(rand(1, 15))
                ->load();

            if (!empty($listProduct)) {
                foreach ($listProduct as $item) {
                    $listResult[] = $item->data['product_id'];
                }
            }

            return $listResult;
        } catch (Exception $exception) {
            return [];
        }
    }

    public function fetchSameProduct(Model_Catalog_Product_Collection $listProduct, int $limit = 5, int $minimum_orders = 1, array $skip_ids = []): array {
        /*
         * Được set theo thứ tự ưu tiên sau (Rà từ đầu đến cuối, Nếu 1 không thoả mãn thì lấy 2, 2 không thoả mãn thì lấy 3....):
            1. Theo best seller của collection (trừ 2 collection Best Sellers và New Arrivals).
            2. Theo best seller của tag.
            3. Theo từ khoá có nghĩa trên title.
            4. Theo Best Seller.
            5. Random
        */

        $listResult = $this->fetchSameProductBySoldCollection($listProduct, $limit, $minimum_orders, $skip_ids);
        if (count($listResult) < $limit) {
            $listResult = array_merge($listResult, $this->fetchSameProductByTag($listProduct, $limit - count($listResult), $minimum_orders, $skip_ids));
        }

        if (count($listResult) < $limit) {
            $listResult = array_merge($listResult, $this->fetchSameProductByTitle($listProduct, $limit - count($listResult), $minimum_orders, $skip_ids));
        }

        if (count($listResult) < $limit) {
            $listResult = array_merge($listResult, $this->fetchSameProductByBestSeller($limit - count($listResult), $minimum_orders, $skip_ids));
        }

        if (count($listResult) < $limit) {
            $listResult = array_merge($listResult, $this->fetchRandomProduct($limit - count($listResult), $minimum_orders, $skip_ids));
        }

        return $listResult;
    }
}