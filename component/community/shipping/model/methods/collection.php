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
class Model_Shipping_Methods_Collection extends Abstract_Core_Model_Collection {
    public function hasOtherShippingMethod() {
        $method = $this->addField('id')->setLimit(1)->load();

        if ($method->length() < 1) {
            return false;
        }

        return true;
    }

    public function getShippingMethodDeactive() {
        $methods = $this
            ->addCondition('shipping_status', Model_Shipping_Methods::STATUS_SHIPPING_METHOD_OFF, OSC_Database::OPERATOR_EQUAL)
            ->addField('id')
            ->load();

        if ($methods->length() < 1) {
            return [];
        }

        return $methods->toArray();
    }

    public function getShippingMethodNotDefault() {
        $methods = $this
            ->addCondition('is_default', Model_Shipping_Methods::STATUS_SHIPPING_METHOD_DEFAULT, OSC_Database::OPERATOR_NOT_EQUAL)
            ->addField('id')
            ->load();

        if ($methods->length() < 1) {
            return [];
        }

        return $methods->toArray();
    }

    public function getShippingMethodDefault() {
        $methods = $this
            ->addCondition('is_default', Model_Shipping_Methods::STATUS_SHIPPING_METHOD_DEFAULT, OSC_Database::OPERATOR_EQUAL)
            ->setLimit(1)
            ->load();

        if ($methods->length() < 1) {
            return null;
        }

        return $methods->getItem();
    }
}
