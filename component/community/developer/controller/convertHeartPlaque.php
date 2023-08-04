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
class Controller_Developer_ConvertHeartPlaque extends Abstract_Core_Controller {

    public function actionMigrateProduct() {

        $product_ids = $this->_request->get('product_ids');
        $product_ids = array_filter(explode(',', $product_ids));

        if (count($product_ids) < 1) {
            echo 'error data product ids';
            die;
        }

        foreach ($product_ids as $product_id) {

            $ukey = 'convertHeartPlaqueProduct/product_id:' . $product_id;

            try {
                $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
                $model_bulk_queue->delete();
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }

            OSC::model('catalog/product_bulkQueue')->setData([
                'ukey' => $ukey,
                'member_id' => 1,
                'action' => 'convertHeartPlaqueProduct',
                'queue_data' => [
                    'product_id' => $product_id
                ]
            ])->save();

            echo "Added queue product: {$product_id} <br>";
        }

        OSC::core('cron')->addQueue('developer/convertHeartPlaqueProduct', null, ['ukey' => 'developer/convertHeartPlaqueProduct', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);

        echo('Convert Heart Plaque Product added Queue');
    }

    public function actionMigrateCart() {
        try {
            $data = [];

            $begin_item_id = intval($this->_request->get('begin_item_id'));

            $product_ids = array_unique(explode(',', $this->_request->get('product_ids')));

            if (count($product_ids) < 1 || !is_array($product_ids)) {
                echo 'Product id not found!';
                die;
            }

            $cart_items = OSC::model('catalog/cart_item')
                ->getCollection()
                ->addField('item_id')
                ->addCondition('product_id', $product_ids, OSC_Database::OPERATOR_IN);

            if (!empty($begin_item_id) && $begin_item_id > 0) {
                $cart_items->addCondition('item_id', $begin_item_id, OSC_Database::OPERATOR_GREATER_THAN);
            }

            $cart_items->load();

            foreach ($cart_items as $cart_item) {
                $item_id = $cart_item->getId();
                $ukey = 'convertCartItemHeartPlaque/item_id:' . $item_id;
                try {
                    $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey($ukey);
                    $model_bulk_queue->delete();
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        throw new Exception($ex->getMessage());
                    }
                }

                $data[$cart_item->getId()] = [
                    'ukey' => $ukey,
                    'member_id' => 1,
                    'action' => 'convertCartItemHeartPlaque',
                    'queue_data' => [
                        'item_id' => $item_id
                    ]
                ];
            }

            if(!empty($data)) {
                $total_append = OSC::model('catalog/product_bulkQueue')->insertMulti($data);

                OSC::core('cron')->addQueue('developer/convertHeartPlaqueCart', null, ['ukey' => 'developer/convertHeartPlaqueCart:1', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
                OSC::core('cron')->addQueue('developer/convertHeartPlaqueCart', null, ['ukey' => 'developer/convertHeartPlaqueCart:2', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
                OSC::core('cron')->addQueue('developer/convertHeartPlaqueCart', null, ['ukey' => 'developer/convertHeartPlaqueCart:3', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);

                echo('Add Cron Migrate Cart HeartPlaque Success');
            } else {
                echo('No cart item add to queue');
            }

        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function actionResetQueue() {
        $queue_ids = $this->_request->get('queue_ids');
        $type = $this->_request->get('type');

        if (trim($queue_ids) == '') {
            echo 'queue id not found';
            die;
        }

        if (trim($type) == '') {
            echo 'type not found';
            die;
        }

        $queue_ids = explode(',', $queue_ids);

        $queue_ids = array_unique($queue_ids);

        try {
            $product_queue = OSC::model('catalog/product_bulkQueue')->getCollection()->load($queue_ids);

            if ($product_queue->length() < 1) {
                echo('No queue to run');
                die;
            }

            foreach ($product_queue as $queue) {
                if ($queue->data['queue_flag'] != 1) {
                    $queue->setData([
                        'queue_flag' => 1,
                        "error" => null
                    ])->save();

                    echo "Done reconvert {$queue->getId()}" .  "<br>";
                }
            }

            OSC::core('cron')->addQueue('developer/convertHeartPlaqueCart', null, ['ukey' => 'developer/convertHeartPlaqueCart:1', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
            OSC::core('cron')->addQueue('developer/convertHeartPlaqueCart', null, ['ukey' => 'developer/convertHeartPlaqueCart:2', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
            OSC::core('cron')->addQueue('developer/convertHeartPlaqueCart', null, ['ukey' => 'developer/convertHeartPlaqueCart:3', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        echo ('done re convert');
    }
}
