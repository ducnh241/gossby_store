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
class Model_Catalog_Cart_Item_Collection extends Abstract_Core_Model_Collection {

    public function preLoadModelData() {
        /* @var $model Model_Catalog_Cart_Item */

//        parent::preLoadModelData();
//
//        $this->preLoadVariant();
//        $this->preLoadProduct();
//
//        foreach ($this as $model) {
//            if (($model->getVariant() instanceof Model_Catalog_Product_Variant) && ($model->getProduct() instanceof Model_Catalog_Product)) {
//                $model->getVariant()->setProduct($model->getProduct());
//            }
//        }
    }

    public function preLoadVariant() {
        /* @var $item Model_Catalog_Cart_Item */
        /* @var $variant Model_Catalog_Product_Variant */

        $map = [];

        foreach ($this as $model) {
            if ($model->data['variant_id'] < 1) {
                continue;
            }
            if (!isset($map[$model->data['variant_id']])) {
                $map[$model->data['variant_id']] = [];
            }

            $map[$model->data['variant_id']][] = $model;
        }

        if (count($map) < 1) {
            return $this;
        }

        OSC_Database_Model::preLoadModelData('catalog/product_variant', array_keys($map));

        foreach ($map as $variant_id => $items) {
            $variant = OSC_Database_Model::getPreLoadedModel('catalog/product_variant', $variant_id);

            if ($variant instanceof Model_Catalog_Product_Variant) {
                foreach ($items as $item) {
                    $item->setVariant($variant);
                }
            }
        }
    }

    public function preLoadProduct() {
        /* @var $item Model_Catalog_Cart_Item */
        /* @var $product Model_Catalog_Product */

        $map = [];

        foreach ($this as $model) {
            if ($model->data['product_id'] < 1) {
                continue;
            }
            if (!isset($map[$model->data['product_id']])) {
                $map[$model->data['product_id']] = [];
            }

            $map[$model->data['product_id']][] = $model;
        }

        if (count($map) < 1) {
            return $this;
        }

        OSC_Database_Model::preLoadModelData('catalog/product', array_keys($map));

        foreach ($map as $product_id => $items) {
            $product = OSC_Database_Model::getPreLoadedModel('catalog/product', $product_id);

            if ($product instanceof Model_Catalog_Product) {
                foreach ($items as $item) {
                    $item->setProduct($product);
                }
            }
        }
    }

}
