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
class Model_User_Group extends Abstract_Core_Model {

    protected $_table_name = 'groups';
    protected $_pk_field = 'group_id';
    protected $_allow_write_log = true;
    protected $_members = null;
    protected $_option_conf = array('value' => 'group_id', 'label' => 'title');

    /**
     * 
     * @return int
     */
    public function countMembers() {
        if ($this->_members === null) {
            $this->_members = OSC::model('user/member')->getCollection()->addField('member_id')->addCondition('group_id', $this->getId())->load()->length();
        }

        return $this->_members;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);

            $group_collection = OSC::model('user/group')->getCollection()
                    ->addCondition('title', $data['title'], OSC_Database::OPERATOR_EXACT)
                    ->setLimit(1)
                    ->load();

            if ($group_collection->length() > 0) {
                if ($this->getActionFlag() == self::INSERT_FLAG || $this->getId() != $group_collection->getItem()->getId()) {
                    $errors[] = OSC::core('language')->get('usr.err_group_name_already');
                }
            }
        }

        $boolean_var_keys = array('lock_flag');

        foreach ($boolean_var_keys as $boolean_var_key) {
            if (isset($data[$boolean_var_key])) {
                $data[$boolean_var_key] = intval($data[$boolean_var_key]) ? 1 : 0;
            }
        }

        if (isset($data['perm_mask_ids'])) {
            if (is_array($data['perm_mask_ids']) && count($data['perm_mask_ids']) > 0) {
                $data['perm_mask_ids'] = OSC::helper('user/permissionMask_validator')->validPermissionMaskIds($data['perm_mask_ids']);
            } else {
                $data['perm_mask_ids'] = array();
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
                if (!isset($data['title'])) {
                    $errors[] = OSC::core('language')->get('usr.err_group_name_empty');
                }

                if (!isset($data['lock_flag'])) {
                    $data['lock_flag'] = 0;
                }

                if (!isset($data['perm_mask_ids'])) {
                    $data['perm_mask_ids'] = array();
                }

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = mktime();
                }

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = 0;
                }
            } else {
                if ($this->isGuest()) {
                    $data['perm_mask_ids'] = array();
                }

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

    protected function _beforeDelete() {
        parent::_beforeDelete();

        if ($this->isRoot()) {
            $this->_error(OSC::core('language')->get('usr.err_group_root'));
        } else if ($this->countMembers() > 0) {
            $this->_error(OSC::core('language')->get('usr.err_group_not_empty'));
        }
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForSave(&$data) {
        if (isset($data['perm_mask_ids'])) {
            $data['perm_mask_ids'] = implode(',', $data['perm_mask_ids']);
        }

        parent::_preDataForSave($data);
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForUsing(&$data) {
        if (isset($data['perm_mask_ids'])) {
            $data['perm_mask_ids'] = explode(',', $data['perm_mask_ids']);
        }

        parent::_preDataForUsing($data);
    }

    public function isAdmin() {
        return $this->getId() == OSC::systemRegistry('root_group')['admin'];
    }

    public function isGuest() {
        return $this->getId() == OSC::systemRegistry('root_group')['guest'];
    }

    public function isRoot() {
        return in_array($this->getId(), OSC::systemRegistry('root_group'));
    }

}
