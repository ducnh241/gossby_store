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
 * @copyright    Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Feed_Product extends Abstract_Core_Model
{
    protected $_table_name = 'feed_product';
    protected $_pk_field = 'id';

    public function insertMulti($items) {
        $queries = [];
        $params = [];
        $data_counter = 0;

        foreach ($items as $key => $item) {
            $item['product_id'] = intval($item['product_id']);

            if ($item['product_id'] < 1) {
                continue;
            }

            if (isset($item['data'])) {
                $data_counter++;
                $data = ':data_' . $data_counter;

                $params['data_' . $data_counter] = OSC::encode($item['data']);
            } else {
                continue;
            }

            $queries[] = "('{$item['product_id']}', '{$item['social_chanel']}' , '{$item['country_code']}', '{$item['group_mode']}', {$data}, '{$item['added_timestamp']}')";
        }

        $queries = implode(',', $queries);

        $this->getWriteAdapter()->query("INSERT IGNORE INTO {$this->getTableName(true)} (product_id, social_chanel, country_code,  group_mode, data, added_timestamp) VALUES {$queries};", $params, 'insert_queues');

        return $this->getWriteAdapter()->getNumAffected('insert_queues');
    }
}
