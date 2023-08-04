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
class Model_Catalog_Order_Item_Meta_Collection extends Abstract_Core_Model_Collection {

    /**
     *
     * @param array $meta_ids
     * @return $this
     * @throws Exception
     */
    public function loadByMetaIds($meta_ids) {
        $shop_id = OSC::getShop()->getId();

        if ($shop_id < 1) {
            throw new Exception('Shop ID is empty');
        }

        if(! is_array($meta_ids)) {
            $meta_ids = [$meta_ids];
        }

        $buff = [];

        foreach($meta_ids as $meta_id) {
            $meta_id = intval($meta_id);

            if($meta_id > 0) {
                $buff[] = $meta_id;
            }
        }

        if(count($buff) < 1) {
            throw new Exception('Meta IDs is empty');
        }

        $meta_ids = array_unique($buff);

        return $this->setCondition(['condition' => '`shop_id` = ' . intval($shop_id) . ' AND meta_id IN (' . implode(',', $meta_ids) . ')'])->load();
    }
}
