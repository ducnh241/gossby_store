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
 * @package Model_User_PermissionMask
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Model_User_PermissionMask extends Abstract_Core_Model {

    protected $_table_name = 'permission_masks';
    protected $_pk_field = 'perm_mask_id';
    protected $_allow_write_log = true;
    protected $_groups = null;
    protected $_members = null;
    protected $_idea_research_id = null;
    protected $_option_conf = array('value' => 'perm_mask_id', 'label' => 'title');
    const PERMISSION_MARKETING_TITLE = 'Marketing';
    const PERMISSION_IDEA_RESEARCH_TITLE = 'Idea Research';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['title'])) {
            $data['title'] = trim($data['title']);

            $collection = OSC::model('user/permissionMask')->getCollection()
                    ->addCondition('title', $data['title'], 'EXACT')
                    ->setLimit(1)
                    ->load();

            if ($collection->length() > 0) {
                if ($this->getActionFlag() == self::INSERT_FLAG || $this->getId() != $collection->getItem()->getId()) {
                    $errors[] = OSC::core('language')->get('usr.permmask_err_name_already');
                }
            }
        }

        if (isset($data['permission_data'])) {
            if (!is_array($data['permission_data'])) {
                $data['permission_data'] = array();
            } else {
                foreach ($data['permission_data'] as $k => $v) {
                    if (preg_match('/([^a-zA-Z0-9\/_\-]|(?<![a-zA-Z0-9])\/|\/(?![a-zA-Z0-9]))/', $v)) {
                        unset($data['permission_data'][$k]);
                    }
                }

                $data['permission_data'] = array_values($data['permission_data']);
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
                    $errors[] = OSC::core('language')->get('usr.permmask_err_name_empty');
                }

                if (!isset($data['permission_data'])) {
                    $data['permission_data'] = array();
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
        if (isset($data['permission_data'])) {
            $data['permission_data'] = implode(',', $data['permission_data']);
        }

        parent::_preDataForSave($data);
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForUsing(&$data) {
        if (isset($data['permission_data'])) {
            $data['permission_data'] = explode(',', $data['permission_data']);
        }

        parent::_preDataForUsing($data);
    }

    public function countGroups() {
        if ($this->_groups === null) {
            $this->_groups = OSC::model('user/group')->getCollection()->addField('group_id')->addCondition('perm_mask_ids', "[[:<:]]{$this->getId()}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load()->length();
        }

        return $this->_groups;
    }

    public function countMembers() {
        if ($this->_members === null) {
            $this->_members = OSC::model('user/member')->getCollection()->addField('member_id')->addCondition('perm_mask_ids', "[[:<:]]{$this->getId()}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load()->length();
        }

        return $this->_members;
    }

    protected function _afterSave() {
        parent::_afterSave();

        $index_keywords = strip_tags($this->data['title']);

        if ($this->getActionFlag() == self::INSERT_FLAG) {
            OSC::helper('backend/common')->addIndex('', 'user', 'permMask_' . $this->getId(), $index_keywords, array('id' => $this->getId()));
        } else {
            OSC::helper('backend/common')->updateIndex('', 'user', 'permMask_' . $this->getId(), $index_keywords, array('id' => $this->getId()));
        }
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        OSC::helper('backend/common')->deleteIndex('', 'user', 'permMask_' . $this->getId());
    }

    public function getIdeaResearchPermId()
    {
        $marketing_permission_masks = OSC::model('user/permissionMask')->getCollection()->addField('perm_mask_id')->addCondition('title', Model_User_PermissionMask::PERMISSION_IDEA_RESEARCH_TITLE)->setLimit(1)->load();
        return $marketing_permission_masks->getItem() ? $marketing_permission_masks->getItem()->getId() : 0;

    }
}
