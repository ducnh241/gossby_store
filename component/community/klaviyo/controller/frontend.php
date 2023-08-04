<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright   Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Controller_Klaviyo_Frontend extends Abstract_Core_Controller
{
    public function actionProductFeed()
    {
        $filter = $this->_request->get('filter');
        $collection_id = $this->_request->get('collection');
        $size = 25;
        $paged = $this->_request->get('page') ? intval($this->_request->get('page')) : 1;
        $previous_page = null;
        $next_page = null;
        $collection = OSC::model('catalog/collection');

        try {
            if ($collection_id == 'all' || intval($collection_id) === 0) {
                $collection->bind(array(
                    'title' => 'All products',
                ))->lock();
            } else {
                $collection->load($collection_id);
            }

            $collection->loadProducts([
                'page_size' => $size,
                'page' => $paged,
                'before_load_callback' => function (Model_Catalog_Product_Collection $product_collection) {
                    $product_collection->addCondition('discarded', 0, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND)
                        ->addCondition('listing', 1, OSC_Database::OPERATOR_EQUAL, OSC_Database::RELATION_AND);
                }
            ]);

            $products = $collection->getProducts();
            $page_index = $products->getCurrentPage();
            $page_size = $products->getPageSize();
            $total_item = $products->collectionLength();

            $page_count = ceil($total_item / $page_size);

            $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
            $feed_url = OSC_FRONTEND_BASE_URL . '/' . $current_lang_key . '/klaviyo/frontend/productFeed/collection/' . $collection_id;

            if (isset($paged) && ($paged > 1)) {
                $previous_page = $feed_url . '/page/' . ($paged - 1);
            }

            if (isset($paged) && $paged < $page_count) {
                $next_page = $feed_url . '/page/' . ($paged + 1);
            }

            $feed = [
                'count' => $total_item,
                'page' => $page_index,
                'page_count' => $page_count,
                'per_page' => $page_size,
                'next_page' => $next_page,
                'previous_page' => $previous_page,
                'sort_by' => 'position',
                'sort_order' => 'asc',
                'items' => []
            ];

            if ($products->length() > 0) {
                $n = 0;
                foreach ($products as $product) {
                    $imageUrl = null;
                    try {
                        $imageUrl = $product->getFeaturedImageUrl(true, true);
                    } catch (Exception $ex) {
                    }

                    $items[] = [
                        'id' => $product->getId(),
                        'SKU' => $product->data['sku'],
                        'title' => $product->getProductTitle(),
                        'link' => $product->getDetailUrl(),
                        'image_link' => $imageUrl ? $imageUrl : null,
                        'description' => $product->data['description'] ? $product->data['description'] : null,
                        'price' => $product->getFloatPrice(),
                        'categories' => array_map(function ($a) {
                            return trim($a);
                        }, explode(',', $product->data['product_type'])),
                        'inventory_quantity' => '',
                        'position' => $n
                    ];
                    $n++;
                }
            }

            $feed['items'] = $items;
            $this->_ajaxResponse($feed);
        } catch (Exception $ex) {
            $this->_ajaxError('Fail to load product');
        }
    }
}
