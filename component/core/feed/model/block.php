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
class Model_Feed_Block extends Abstract_Core_Model
{
    protected $_table_name = 'feed_block';
    protected $_pk_field = 'country_code';

    protected $_collection_block = null;
    protected $_country_block = null;

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if ($this->getActionFlag() == static::INSERT_FLAG) {

            $default_fields = [
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ];

            foreach ($default_fields as $field_name => $default_value) {
                if (!isset($data[$field_name])) {
                    $data[$field_name] = $default_value;
                }
            }
        } else {
            if (!isset($data['modified_timestamp'])) {
                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

    }

    public function getCollectionBlock() {
        if (is_null($this->_collection_block)) {
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->select('*', OSC::model('feed/block')->getTableName(), "country_code = '{$this->data['country_code']}' AND category = '{$this->data['category']}'",
                '`id` ASC', null, 'fetch_data');

            $block_collection = $DB->fetchArrayAll('fetch_data');

            $DB->free('fetch_data');

            $mapping_collection = [];
            foreach ($block_collection as $item) {
                $mapping_collection[$item['collection_id']][] = $item['sku'];
            }
            $this->_collection_block = $mapping_collection;
        }
        return $this->_collection_block;
    }

    public function insertMulti($items) {
        $queries = [];
        $addedTimestamp = time();

        foreach ($items as $key => $item) {
            $item['product_id'] = intval($item['product_id']);
            $category = $item['category'] ?? 'google';

            if ($item['product_id'] < 1 || empty($item['sku'])) {
                continue;
            }

            $queries[] = "('{$item['product_id']}', '{$item['sku']}', '{$item['collection_id']}' , '{$item['country_code']}', '{$item['member_id']}', '{$category}', {$addedTimestamp})";
        }

        $queries = implode(',', $queries);

        $this->getWriteAdapter()->query("INSERT IGNORE INTO {$this->getTableName(true)} (product_id, sku, collection_id, country_code, member_id, category, added_timestamp) VALUES {$queries};", [], 'insert_queues');

        return $this->getWriteAdapter()->getNumAffected('insert_queues');
    }

}
