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
class Model_D2_Product_Collection extends Abstract_Core_Model_Collection {

    public function preLoadCampaignProduct() {
        if (count($this) < 1) {
            return $this;
        }

        $d2_product_map = array();

        foreach ($this as $d2_product) {
            if (!isset($d2_product_map[$d2_product->data['product_id']])) {
                $d2_product_map[$d2_product->data['product_id']] = array();
            }

            $d2_product_map[$d2_product->data['product_id']] = $d2_product;
        }

        $campaign_products = OSC::model('catalog/product')->getCollection()->load(array_keys($d2_product_map));

        foreach ($campaign_products as $product) {

            $d2_product_map[$product->getId()]->setCampaignProduct($product);
        }

        return $this;
    }

}