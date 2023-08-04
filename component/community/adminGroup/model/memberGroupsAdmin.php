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
 * @package Model_User_Group
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Model_AdminGroup_MemberGroupsAdmin extends Abstract_Core_Model {

    protected $_table_name = 'member_groups_admin';
    protected $_pk_field = 'member_groups_id';
    protected $_allow_write_log = true;

    protected $_member = null;

    protected $_list_name_group = null;

    public function getMember() {
        if ($this->_member === null) {
            $this->_member = OSC::model('user/member')->load($this->data['member_id']);
        }

        return $this->_member;
    }
    public function getListGroups() {
        if ($this->_list_name_group === null) {
            $this->_list_name_group = OSC::model('user/group')->getCollection()->load($this->data['group_ids']);
        }

        return $this->_list_name_group;
    }
    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['member_id'])) {
            $data['member_id'] = intval($data['member_id']);

            if ($data['member_id'] < 1) {
                unset($data['member_id']);
            }
        }

        if (isset($data['group_ids'])) {
            if (is_array($data['group_ids']) && count($data['group_ids']) > 0) {
                foreach ($data['group_ids'] as $group_id) {
                    try {
                        $group_id = OSC::helper('user/group_validator')->validGroupId($group_id);
                    } catch (OSC_Exception_Condition $e) {
                        throw $e;
                    }
                }
            } else {
                $data['group_ids'] = array();
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
                    $errors[] = 'Member id is not empty';
                }
                if (!isset($data['group_ids'])) {
                    $data['group_ids'] = array();
                    $errors[] = 'Group id is not empty';
                }

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = mktime();
                }

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = 0;
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
        if (isset($data['group_ids'])) {
            $data['group_ids'] = implode(',', $data['group_ids']);
        }

        parent::_preDataForSave($data);
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForUsing(&$data) {
        if (isset($data['group_ids'])) {
            $data['group_ids'] = explode(',', $data['group_ids']);
        }

        parent::_preDataForUsing($data);
    }
}
