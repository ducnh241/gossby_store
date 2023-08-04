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
 * @copyright	Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */
class Observer_Catalog_Algolia {

    /**
     * @param array $params
     * @return void
     * @throws Exception
     */
    public static function syncProduct(array $params = []) {

        $product_id = intval($params['product_id']);
        $sync_type = $params['sync_type'];

        $ukey = 'algolia_' . $product_id . '_' . $sync_type;

        try {
            $model_bulk_queue = OSC::model('catalog/product_bulkQueue')->getCollection()
                ->addCondition('ukey', $ukey)
                ->addCondition('queue_flag', Model_Catalog_Product_BulkQueue::QUEUE_FLAG['error'])
                ->load();

            if ($model_bulk_queue->length()) {
                $model_bulk_queue->delete();
            }
        } catch (Exception $ex) {
            if ($ex->getCode() != 404) {
                throw new Exception($ex->getMessage());
            }
        }

        OSC::model('catalog/product_bulkQueue')->insertMulti([
            [
                'ukey' => $ukey,
                'member_id' => 1,
                'action' => 'algolia_sync_product',
                'queue_data' => [
                    'product_id' => $product_id,
                    'sync_type' => $sync_type
                ]
            ]
        ]);

        OSC::core('cron')->addQueue('catalog/algolia_syncProduct', null, ['ukey' => 'catalog/algolia_syncProduct', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
    }

}
