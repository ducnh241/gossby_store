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

class Controller_Developer_Index extends Abstract_Core_Controller {
    public function actionAddQueueMktPoint() {
        OSC::core('cron')->addQueue('marketing/updateOldOrderPoint', null, [
            'requeue_limit' => -1,
            'estimate_time' => 60 * 10
        ]);

        echo('Add Cron Update marketing point for old order Success');
    }

    public function actionRunToolsScanDesign() {
        OSC::core('cron')->addQueue('personalizedDesign/scanTools', null, [
            'requeue_limit' => -1,
            'estimate_time' => 60 * 60 * 6
        ]);

        echo('Add Cron Check Duplicate Image Design Success');
    }

    public function actionTestAlarm() {
        fsfdaaaa();
    }

    public function actionDuplicateDesign()
    {
        $design_ids = $this->_request->get('design_ids');
        $target_store_id = intval($this->_request->get('target_store_id'));

        if (!$design_ids) {
            dd('design_ids must provider');
        }
        if (!$target_store_id) {
            dd('target_store_id must provider');
        }

        $design_id_array = explode(',', $design_ids);
        foreach ($design_id_array as $k => $design_id) {
            if (!intval($design_id)) {
                unset($design_id_array[$k]);
            }
        }

        if (count($design_id_array) === 0) {
            dd('design_ids must provider and int element');
        }

        OSC::core('cron')->addQueue('personalizedDesign/duplicateDesignToDe', ['design_ids' => $design_id_array, 'target_store_id' => $target_store_id], [
            'requeue_limit' => -1,
            'estimate_time' => 60*60
        ]);
        echo('Add Cron Duplicate Design Success');
    }


    public function actionRenderSvg() {
        $design_id = $this->_request->get('design_id');
        try {
            $design = OSC::model('personalizedDesign/design')->load($design_id);
            $svg = OSC::helper('personalizedDesign/common')->renderSvg($design, [], ['render_design']);
            echo '<div style="margin: 50px auto; width: 500px;">' . $svg . '</div>';
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function actionRenderSupplyVariant() {
        $supplier_location_rel_ids = explode(',', $this->_request->get('supplier_location_rel_ids'));

        OSC::core('cron')->addQueue('supplier/renderSupplyVariant', ['supplier_location_rel_ids' => $supplier_location_rel_ids], [
            'requeue_limit' => -1,
            'estimate_time' => 60 * 60
        ]);

        echo('Add Cron Cache Selling Variant Success');
    }

    public function actionRemoveCache()
    {
        $cache_key = trim($this->_request->get('key', ''));
        $list_key_allow = ['actionGetHomeSection', 'getNavigation', 'actionGetBestSelling', 'actionGetListProductByCollection'];

        if (in_array($cache_key, $list_key_allow)) {
            $list_keys = OSC::helper('core/cache')->deleteByPattern([$cache_key]);
            dd('Delete success!', $list_keys);
        }

        try {
            OSC::model('catalog/product')->loadByUKey($cache_key);
            $list_keys = OSC::helper('core/cache')->deleteByPattern([$cache_key]);
            dd('Delete success!', $list_keys);
        } catch (Exception $ex) {
            dd($ex->getMessage());
        }
    }

    public function actionGetCollectionProductRel()
    {
        header('Content-Type: application/json; charset=utf-8');

        $version = $this->_request->get('v');
        $cache_key = 'developer_tool_get_collection_product_rel';
        if ($version) {
            $cache_key = $cache_key . $version;
        }
        $product_collection_rel = OSC::core('cache')->get($cache_key);
        if ($product_collection_rel) {
            echo $product_collection_rel;
            die();
        }
        $collections = OSC::model('catalog/collection')->getCollection()->load();
        $data = [];
        foreach ($collections as $collection) {
            $data[$collection->getId()]['id'] = $collection->getId();
            $data[$collection->getId()]['title'] = $collection->data['title'];
            $list_product = $collection->loadProducts(['flag_feed' => true, 'page_size' => 'all'])->toArray();
            $data[$collection->getId()]['product_ids'] = array_column($list_product, 'product_id');
        }
        $product_collection_rel = json_encode($data);
        OSC::core('cache')->set($cache_key, $product_collection_rel, OSC_CACHE_TIME);
        echo $product_collection_rel;
        die();
    }

    public function actionMongoWrite() {
        try {
            $bind = $this->_request->get('bind');
            $bind = $bind ?? 'product';

            $collection = 'mongo_test_connection';
            $mongodb = OSC::core('mongodb');
            $document = [
                'product_id' => 1,
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ];

            $result = $mongodb->insert($collection, $document, $bind);
            dd($result);
        } catch (Exception $exception) {
            dd($exception);
        }
    }

    public function actionMongoRead() {
        try {
            $bind = $this->_request->get('bind');
            $bind = $bind ?? 'product';

            $collection = 'mongo_test_connection';
            $mongodb = OSC::core('mongodb');

            $result = $mongodb->selectCollection($collection, $bind)->find([
                'product_id' => 1
            ], ['typeMap' => ['root' => 'array', 'document' => 'array']])->toArray();
            dd($result);
        } catch (Exception $exception) {
            dd($exception);
        }
    }

    public function actionAddQueueRenderDesign() {
        for ($i = 1; $i <= 7; $i ++) {
            OSC::core('cron')->addQueue('catalog/campaign_rerenderDesignV2', null, ['ukey' => 'catalog/campaign_rerenderDesignV2:' . $i, 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60, 'running_time' => 30*$i]);
            OSC::core('cron')->addQueue('catalog/campaign_renderDesignOrderBeta', null, ['ukey' => 'catalog/campaign_rerenderDesignOrderBeta:' . $i, 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60, 'running_time' => 30*$i]);
        }
    }

    public function actionMigrateOptionsDesignOrder() {
        try {
            $test_flag = $this->_request->get('test_flag', 0);
            $gossby_product_id = $this->_request->get('product_id', 0);
            $feelik_shop_id = $this->_request->get('shop_id', 0);
            if (intval($gossby_product_id) < 1 || intval($feelik_shop_id) < 1) {
                throw new Exception('Product or shop is incorrect');
            }

            OSC::core('cron')->addQueue('developer/migrateOptionsDesignOrder', [
                'test_flag' => $test_flag,
                'gossby_product_id' => $gossby_product_id,
                'feelik_shop_id' => $feelik_shop_id
            ], [
                'requeue_limit' => -1,
                'estimate_time' => 60 * 60
            ]);

            echo('Add Cron Migrate Options Design Order Success');
        } catch (Exception $ex) {
            echo 'Error :: ' . $ex->getMessage();
        }
    }

    public function actionGetClipArtSVGFromOrder() {
        $order_item_id = intval($this->_request->get('order_item_id'));

        if ($order_item_id < 1) {
            throw new Exception('Order Id not found');
        }

        $options = ['render_design', 'remove_pattern_layer'];

        try {
            $line_item = OSC::model('catalog/order_item')->load($order_item_id);

            $item_meta = $line_item->getOrderItemMeta();
            $custom_data = $item_meta->data['custom_data'];

            foreach ($custom_data[0]['data'] as $design_id => $data) {
                $config = $data['config'];
                $design = OSC_Database_Model::getPreLoadedModel('personalizedDesign/design', $design_id);

                if (!($design instanceof Model_PersonalizedDesign_Design)) {
                    throw new Exception('Cannot load personalized design');
                }

                $params = [
                    'design' => $design,
                    'custom_config' => $config,
                    'options' => $options
                ];

                $clip_arts = OSC::helper('personalizedDesign/common')->renderClipArtSvg($params);
                $clip_arts['design_svg_beta'] = $data['design_svg_beta'];
                $clip_arts['design_svg'] = $data['design_svg'];
                foreach ($clip_arts as $ps_clip_art_name => $clip_art) {
                    echo "-----------------------";
                    echo "<br/>";
                    echo "<h1> $ps_clip_art_name </h1>";

                    echo "<div style='width: 50%'> $clip_art </div>";
                }
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}