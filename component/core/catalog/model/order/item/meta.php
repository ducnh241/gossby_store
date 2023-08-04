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
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Catalog_Order_Item_Meta extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'catalog_order_item_meta';
    protected $_pk_field = 'master_record_id';

    /**
     *
     * @param int $order_record_id
     * @return $this
     * @throws Exception
     */
    public function loadByMetaId(int $meta_id) {
        if ($meta_id < 1) {
            throw new Exception('Order item meta ID is empty');
        }

        $shop_id = OSC::getShop()->getId();

        if ($shop_id < 1) {
            throw new Exception('Shop ID is empty');
        }

        return $this->setCondition(['condition' => '`master_ukey` = :master_ukey', 'params' => ['master_ukey' => $shop_id . ':' . $meta_id]])->load();
    }


    public function getCampaignDataIdx() {
        if (!isset($this->data['custom_data']) || !is_array($this->data['custom_data']) || count($this->data['custom_data']) < 1) {
            return null;
        }

        foreach ($this->data['custom_data'] as $idx => $custom_data_entry) {
            if ($custom_data_entry['key'] == 'campaign') {
                return $idx;
            }
        }

        return null;
    }

    public function getCampaignData() {
        $idx = $this->getCampaignDataIdx();

        if ($idx === null) {
            return null;
        }

        return $this->data['custom_data'][$idx]['data'];
    }

    public function isCampaignMode() {
        return $this->getCampaignDataIdx() !== null;
    }

    protected function _cleanCustomData($custom_data) {
        if (!is_array($custom_data)) {
            $custom_data = [];
        } else {
            foreach ($custom_data as $idx => $entry) {
                if (!is_array($entry) || !isset($entry['key']) || !isset($entry['data'])) {
                    unset($custom_data[$idx]);
                    continue;
                }

                foreach ($entry as $k => $v) {
                    if (!in_array($k, ['key', 'title', 'text', 'data','type'])) {
                        unset($entry[$k]);
                    }
                }

                if (!is_string($entry['key'])) {
                    unset($custom_data[$idx]);
                    continue;
                }

                if (!is_string($entry['title'])) {
                    unset($entry['title']);
                }

                if (!is_string($entry['text'])) {
                    unset($entry['text']);
                }
                if (!is_string($entry['type'])) {
                    unset($entry['type']);
                }
                $custom_data[$idx] = $entry;
            }

            $custom_data = array_values($custom_data);
        }

        return $custom_data;
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG) {
            $this->_updateData();
        }
    }

    protected function _beforeSave() {

        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['custom_data'])) {
            $data['custom_data'] = $this->_cleanCustomData($data['custom_data']);
            $campaign_data = $this->getCampaignData();
            $search_meta_data = [];

            if (isset($campaign_data['print_template']['segment_source']) && count($campaign_data['print_template']['segment_source']) > 0) {
                foreach ($campaign_data['print_template']['segment_source'] as $key => $segment) {
                    if ($segment['source']['type'] != 'personalizedDesign') {
                        continue;
                    }

                    $search_meta_data['config'][] = implode(',', array_map(
                        function ($v, $k) { return sprintf("%s:%s", $k, $v); },
                        $segment['source']['config'],
                        array_keys($segment['source']['config'])
                    ));
                    $search_meta_data['design_ids'][] = $segment['source']['design_id'];
                }
            }
            if(isset($search_meta_data['config']) && !empty($search_meta_data['config'])) {
                $search_meta_data['config'] = ','.implode(',', $search_meta_data['config']).',';
            }
            if(isset($search_meta_data['design_ids']) && !empty($search_meta_data['design_ids'])) {
                $search_meta_data['design_ids'] = ',#'.implode(',#', $search_meta_data['design_ids']).',';
            }

            $data['search_meta_data'] = $search_meta_data;
        }

        foreach (array('added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'custom_data' => 'Custom Data is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }
                $temporary_data = time();
                $default_fields = [
                    'master_ukey' => $temporary_data,
                    'meta_id' => $temporary_data,
                    'search_meta_data' => [],
                    'shop_id' => OSC::getShop()->getId(),
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            } else {
                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['custom_data', 'search_meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['custom_data', 'search_meta_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    protected function _updateData() {
        $this->getWriteAdapter()->update($this->getTableName(), ['meta_id' => $this->getId(), 'master_ukey' => $this->data['shop_id'].':'.$this->getId()], 'master_record_id=' . $this->getId(), 1, 'update_data_item_meta_'.$this->getId());

        $this->reload();
    }

}
