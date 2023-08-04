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
class Model_Catalog_Order_Item_Collection extends Abstract_Core_Model_Collection {

    protected $_product_loaded = false;
    protected $_variant_loaded = false;

    /**
     *
     * @return $this
     */
    public function preLoadVariant() {
        if ($this->_variant_loaded) {
            return $this;
        }

        $variant_ids = [];

        foreach ($this as $line_item) {
            $variant_ids[] = $line_item->data['variant_id'];
        }

        $this->getNullModel()->preLoadModelData('catalog/product_variant', $variant_ids);

        $this->_variant_loaded = true;

        return $this;
    }

    /**
     *
     * @return $this
     */
    public function preLoadProduct() {
        if ($this->_product_loaded) {
            return $this;
        }

        $product_ids = [];

        foreach ($this as $line_item) {
            $product_ids[] = $line_item->data['product_id'];
        }

        $this->getNullModel()->preLoadModelData('catalog/product', $product_ids);

        $this->_product_loaded = true;

        return $this;
    }


    public function loadByOrderMasterRecordId(int $order_master_record_id) {
        return $this->addCondition('order_master_record_id', $order_master_record_id)->sort('added_timestamp', 'ASC')->load();
    }
}
