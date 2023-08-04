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

/**
 * OSC_User
 *
 * @package Model_User_PermissionAnalytic
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Model_User_PermissionAnalytic extends Abstract_Core_Model {

    protected $_table_name = 'permission_analytics';
    protected $_pk_field = 'perm_analytic_id';
    protected $_allow_write_log = true;
    protected $_option_conf = ['value' => 'perm_analytic_id'];

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            $collection = OSC::model('user/permissionAnalytic')->getCollection()
                    ->addCondition('member_id', $data['member_id'], 'EXACT')
                    ->setLimit(1)
                    ->load();

            if ($collection->length() > 0) {
                if ($this->getActionFlag() == self::INSERT_FLAG || $this->getId() != $collection->getItem()->getId()) {
                    $errors[] = 'This permission analytic already taken by another';
                }
            }
        }

        if (isset($data['member_mkt_ids'])) {
            if (!is_array($data['member_mkt_ids'])) {
                $data['member_mkt_ids'] = [];
            } else {
                foreach ($data['member_mkt_ids'] as $k => $v) {
                    if (!intval($v)) {
                        unset($data['member_mkt_ids'][$k]);
                    }
                }

                $data['member_mkt_ids'] = array_values($data['member_mkt_ids']);
            }
        }

        if (isset($data['added_timestamp'])) {
            $data['added_timestamp'] = intval($data['added_timestamp']);

            if ($data['added_timestamp'] < 1) {
                unset($data['added_timestamp']);
            }
        }

        if (isset($data['modified_timestamp'])) {
            $data['modified_timestamp'] = intval($data['modified_timestamp']);

            if ($data['modified_timestamp'] < 1) {
                unset($data['modified_timestamp']);
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {
                if (!isset($data['member_id'])) {
                    $errors[] = 'Member is empty';
                }

                if (!isset($data['member_mkt_ids'])) {
                    $errors[] = 'Analytic of members is empty';
                }

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = mktime();
                }

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = mktime();
                }
            } else {
                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = mktime();
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    /**
     *
     * @param array &$data
     */
    protected function _preDataForSave(&$data) {
        if (isset($data['member_mkt_ids'])) {
            $data['member_mkt_ids'] = implode(',', $data['member_mkt_ids']);
        }

        parent::_preDataForSave($data);
    }

    /**
     *
     * @param array &$data
     */
    protected function _preDataForUsing(&$data) {
        if (isset($data['member_mkt_ids'])) {
            $data['member_mkt_ids'] = explode(',', $data['member_mkt_ids']);
        }

        parent::_preDataForUsing($data);
    }

    public function getNamesOfAnalyticMember() {
        $member_mkt_ids = implode(',', $this->data['member_mkt_ids']);

        $member_table_name = OSC::model('user/member')->getTableName(true);

        $DB = OSC::core('database');

        $query = "SELECT username FROM {$member_table_name} WHERE member_id IN ({$member_mkt_ids})";

        $DB->query($query, null, 'fetch_list_members');

        $member_names = [];
        while ($row = $DB->fetchArray('fetch_list_members')) {
            $member_names[] = $row['username'];
        }

        return implode(', ', $member_names);
    }

    public function getNameOfMember() {
        $member = OSC::model('user/member')->load($this->data['member_id']);

        return $member->data['username'];
    }
}
