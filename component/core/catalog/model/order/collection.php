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
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Catalog_Order_Collection extends Abstract_Core_Model_Collection {

    public function preLoadLineItems() {
        $record_ids = [];

        foreach ($this as $model) {
            $record_ids[] = $model->getId();
        }

        if (count($record_ids) < 1) {
            return $this;
        }

        $line_item_collection = OSC::model('catalog/order_item')->getCollection()->addCondition('order_master_record_id', $record_ids, OSC_Database::OPERATOR_IN)->load();

        if ($line_item_collection->length() < 1) {
            return $this;
        }

        $cache = [];

        foreach ($line_item_collection as $line_item) {
            $order = $this->getItemByPK($line_item->data['order_master_record_id']);

            if (!$order) {
                continue;
            }

            if (!isset($cache[$order->getId()])) {
                $cache[$order->getId()] = $line_item_collection->getNullCollection()->setNull();
            }

            $cache[$order->getId()]->addItem($line_item);
        }

        foreach ($cache as $order_master_record_id => $line_item_collection) {
            $this->getItemByPK($order_master_record_id)->setLineItems($line_item_collection);
        }

        return $this;
    }

}
