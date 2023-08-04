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
class Model_MasterSync_Queue extends Abstract_Core_Model {

    protected $_table_name = 'mastersync_queue';
    protected $_pk_field = 'queue_id';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['sync_key'])) {
            $data['sync_key'] = strtolower(trim(strval($data['sync_key'])));

            if ($data['sync_key'] == '') {
                $errors[] = 'Sync key is empty';
            }
        }

        foreach (['syncing_flag'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]) == 1 ? 1 : 0;
            }
        }

        foreach (['added_timestamp', 'modified_timestamp'] as $key) {
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
                    'sync_key' => 'Sync key is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = [
                    'sync_data' => null,
                    'syncing_flag' => 0,
                    'error' => null,
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
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['sync_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['sync_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

}
