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

class Model_AutoAb_AbProduct_Config extends Abstract_Core_Model {

    protected $_table_name = 'auto_ab_product_config';
    protected $_pk_field = 'id';
    protected $_ukey_field = 'ukey';

    protected $_allow_write_log = true;

    const STATUS_CREATED = 0;
    const STATUS_IN_PROGRESS = 1;
    const STATUS_ON_HOLD = 2;
    const STATUS_FINISH = 3;

    const STATUS_NAME = [
        self::STATUS_CREATED => 'Created',
        self::STATUS_IN_PROGRESS => 'In Progress',
        self::STATUS_ON_HOLD => 'Hold',
        self::STATUS_FINISH => 'End'
    ];

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['title']) && mb_strlen($data['title']) < 1) {
            $errors[] = 'Title is empty!';
        }

        if (isset($data['begin_time']) && intval($data['begin_time']) < 1) {
            $errors[] = 'Begin time empty!';
        }

        if (isset($data['finish_time']) && intval($data['finish_time']) < 1) {
            $errors[] = 'Finish time empty!';
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'title' => 'Title is empty!',
                    'begin_time' => 'Begin time empty',
                    'finish_time' => 'Finish time empty!',
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                do {
                    $ukey = OSC::randKey(5);
                    $is_exist = true;
                    try {
                        OSC::model('autoAb/abProduct_config')->loadByUKey($ukey);
                    } catch (Exception $ex) {
                        $is_exist = false;
                    }
                } while($is_exist);

                $default_fields = [
                    'ukey' => $ukey,
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

    protected function _afterDelete() {
        parent::_afterDelete();

        OSC::model('autoAb/abProduct_map')->getCollection()
            ->addCondition('config_id', $this->getId())
            ->load()
            ->delete();

        OSC::model('autoAb/abProduct_viewTracking')->getCollection()
            ->addCondition('config_id', $this->getId())
            ->load()
            ->delete();

        OSC::model('autoAb/abProduct_orderTracking')->getCollection()
            ->addCondition('config_id', $this->getId())
            ->load()
            ->delete();

        OSC::model('autoAb/abProduct_report')->getCollection()
            ->addCondition('config_id', $this->getId())
            ->load()
            ->delete();
    }


    public function isBegin() {
        $begin_time = intval($this->data['begin_time']);
        $finish_time = intval($this->data['finish_time']);
        $now_timestamp = time();

        return $begin_time <= $now_timestamp && $now_timestamp <= $finish_time && $this->data['status'] == self::STATUS_IN_PROGRESS;
    }

    /**
     * @return string
     */
    public function getHubUrl()
    {

        return OSC_FRONTEND_BASE_URL . '/p/' . $this->data['ukey'];
    }

    /**
     * @return string
     */
    public function getStatusName() {
        if ($this->getId() < 1) {
            return self::STATUS_NAME[self::STATUS_CREATED];
        }
        $finish_time = intval($this->data['finish_time']);
        $now_timestamp = time();

        if ($now_timestamp > $finish_time && $this->data['status'] != self::STATUS_FINISH) {
            $this->setData([
                'status' => self::STATUS_FINISH
            ])->save();
        }

        return self::STATUS_NAME[$this->data['status']] ?? 'End';
    }

}
