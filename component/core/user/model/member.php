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

/**
 * OSECORE Model
 *
 * @package Model_User_Member
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Model_User_Member extends Abstract_Core_Model {

    protected $_table_name = 'members';
    protected $_pk_field = 'member_id';
    protected $_ukey_field = 'email';
    protected $_allow_write_log = true;

    protected $_option_conf = array('value' => 'id', 'label' => 'username');
    protected $_raw_password = '';
    protected $_email_notify_flag = true;

    /**
     *
     * @var Model_User_Group
     */
    protected $_group_model = null;

    protected $_group_admin = null;

    const AVA_EXTRA_SIZE = 120;
    const AVA_LARGE_SIZE = 60;
    const AVA_SMALL_SIZE = 30;
    const AVA_TINY_SIZE = 15;
    const SYSTEM_MEMBER_ID = 0;
    const SYSTEM_MEMBER_USERNAME = 'system';
    const SREF_NOT_HAS_ORDER = 0;
    const SREF_HAS_ORDER = 1;

    public function getSrefTypes() {
        return [
            'organic_traffic' => 'Organic Traffic',
            'email_sms' => 'Email/SMS',
            'search_paid_traffic' => 'Search Paid Traffic',
            'social_ads' => 'Socials Ads',
            'other' => 'Other'
        ];
    }

    public function getSrefTypeDefault() {
        return ['key' => 'social_ads', 'title' => 'Socials Ads'];
    }

    public function setGuestData() {
        $this->bind([
            'member_id' => 0,
            'username' => 'Guest',
            'group_id' => OSC::systemRegistry('root_group')['guest']
        ]);

        return $this;
    }

    /**
     * 
     * @param string $email
     * @return Model_User_Member
     */
    public function loadByEmail($email) {
        return $this->loadByUkey($email);
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if (isset($data['perm_mask_ids'])) {
            $data['perm_mask_ids'] = implode(',', $data['perm_mask_ids']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if (isset($data['perm_mask_ids'])) {
            $data['perm_mask_ids'] = explode(',', $data['perm_mask_ids']);
        }
    }

    /**
     *
     * @return boolean
     */
    public function isRoot() {
        return $this->getId() == 1;
    }

    public function isAdmin() {
        return $this->getGroup()->isAdmin();
    }

    public function isModerator() {
        
    }

    /**
     * 
     * @return \Model_User_Group
     */
    public function getGroup() {
        if ($this->_group_model === null) {
            $gid = OSC::systemRegistry('root_group')['guest'];

            if ($this->getId() && $this->data['group_id']) {
                $gid = $this->data['group_id'];
            }

            $this->_group_model = OSC::model('user/group')->load($gid);
        }

        return $this->_group_model;
    }
    public function getGroupAdmin() {
        $collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->addCondition('member_id', $this->getId())->setLimit(1)->load();
        if ($collection->length() > 0) {
            foreach ($collection as $model) {
                $this->_group_admin = $model;
            }
        }

        return $this->_group_admin;
    }
    /**
     * 
     * @param Model_User_Group $group
     * @return \Model_User_Member
     */
    public function setGroup(Model_User_Group $group) {
        $this->_group_model = $group;
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getGroupName() {
        return $this->getGroup()->getName();
    }

    /**
     * 
     * @return string
     */
    public function getGroupNameWithFormat() {
        return $this->getGroup()->getNameWithFormat();
    }

    /**
     * 
     * @param integer $size
     * @param boolean $skip_root
     * @param boolean $get_orig
     * @return string
     */
    public function getAvatarUrl($size = self::AVA_SMALL_SIZE, $skip_root = false, $get_orig = false) {
        if ($this->getId() > 0) {
            $extension = $this->getData('avatar_extension', $get_orig);

            if ($extension) {
                $path = 'user/avatar/' . $this->getId() . '/' . $size . '.' . $extension;
            } else {
                $path = 'user/avatar/no_ava/' . $size . '.jpg';
            }
        } else {
            $path = 'user/avatar/guest/' . $size . '.jpg';
        }

        if ($skip_root) {
            return $path;
        }

        $storage = OSC::core('storage');

        return $storage->getUrl($path);
    }

    /**
     * 
     * @return string
     */
    public function getUsernameWithFormat() {
        $format = $this->getGroup()->data['display_format'];

        if ($format == '') {
            return $this->data['username'];
        } else {
            return str_replace('{{name}}', $this->data['username'], $format);
        }
    }

    /**
     * 
     * @param boolean $notify
     * @param string $notify_message
     * @return Model_User_Member
     */
    public function unlockAccount($notify = false, $notify_message = '') {
        if ($this->data['suspended'] != 1) {
            return $this;
        }

        $this->setData('suspended', 0)->save();

        if ($notify) {
            
        }

        return $this;
    }

    /**
     * 
     * @param string $reason
     * @param integer $expire_timestamp
     * @param boolean $notify
     * @param string $notify_message
     * @return Model_User_Member
     */
    public function lockAccount($reason, $expire_timestamp = null, $notify = false, $notify_message = '') {
        if ($this->data['suspended'] == 1) {
            return $this;
        }

        $expire_timestamp = intval($expire_timestamp);

        if ($expire_timestamp < mktime()) {
            $expire_timestamp = mktime() + 60 * 60 * 24;
        }

        $this->setData(array(
            'suspended' => 1,
            'suspend_expire_timestamp' => $expire_timestamp,
            'suspend_reason' => $reason
        ))->save();

        if ($notify) {
            
        }

        return $this;
    }

    /**
     * 
     * @param boolean $flag
     * @return Model_User_Member
     */
    public function setEmailNotifyFlag($flag = true) {
        $this->_email_notify_flag = $flag;

        return $this;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        $validator = OSC::helper('user/member_validator');
        if (isset($data['username'])) {
            try {
                $data['username'] = $validator->validUsername($data['username'], $this->getId());
            } catch (OSC_Exception_Condition $e) {
                unset($data['username']);
                $errors[] = $e->getMessage();
            }
        }

        if (isset($data['email'])) {
            try {
                $data['email'] = $validator->validEmail($data['email'], $this->getId());
            } catch (OSC_Exception_Condition $e) {
                unset($data['email']);
                $errors[] = $e->getMessage();
            }
        }

        if (isset($data['group_id'])) {
            try {
                $data['group_id'] = $validator->validGroupId($data['group_id']);
            } catch (OSC_Exception_Condition $e) {
                unset($data['group_id']);
                $errors[] = $e->getMessage();
            }
        }

        if (isset($data['password_hash'])) {
            unset($data['password_hash']);
            
            $raw_password = $this->registry('RAW_PASSWORD');
            
            $this->register('RAW_PASSWORD', null);
                        
            try {
                $raw_password = $validator->validPassword($raw_password);

                $this->_raw_password = $raw_password;
                $data['password_hash'] = md5($this->_raw_password);
            } catch (OSC_Exception_Condition $e) {
                $errors[] = $e->getMessage();
            }
        }

        if (isset($data['perm_mask_ids'])) {
            if (is_array($data['perm_mask_ids']) && count($data['perm_mask_ids']) > 0) {
                $data['perm_mask_ids'] = OSC::helper('user/permissionMask_validator')->validPermissionMaskIds($data['perm_mask_ids']);
            } else {
                $data['perm_mask_ids'] = array();
            }
        }

        if (isset($data['timezone'])) {
            if (!OSC::model('core/timezone')->validTimezone($data['timezone'])) {
                unset($data['timezone']);
            }
        }

        if (isset($data['activated'])) {
            $data['activated'] = intval($data['activated']) ? 1 : 0;
        }

        if (isset($data['suspended'])) {
            $data['suspended'] = intval($data['suspended']) ? 1 : 0;

            if ($data['suspended']) {
                $data['suspend_reason'] = trim($data['suspend_reason']);

                if ($data['suspend_reason'] == '') {
                    $errors[] = OSC::core('language')->get('usr.err_suspend_reason_empty');
                }

                $data['suspend_expire_timestamp'] = intval($data['suspend_expire_timestamp']);

                if ($data['suspend_expire_timestamp'] < 1) {
                    $errors[] = OSC::core('language')->get('usr.err_suspend_expire_incorrect');
                }
            } else {
                $data['suspend_reason'] = '';
                $data['suspend_expire_timestamp'] = 0;
            }
        } else {
            if (isset($data['suspend_reason'])) {
                $data['suspend_reason'] = trim($data['suspend_reason']);
            }

            if (isset($data['suspend_expire_timestamp'])) {
                $data['suspend_expire_timestamp'] = intval($data['suspend_expire_timestamp']);

                if ($data['suspend_expire_timestamp'] < 1) {
                    $data['suspend_expire_timestamp'] = 0;
                }
            }
        }

        if (isset($data['avatar_extension']) && $data['avatar_extension'] != '' && $this->getActionFlag() == self::UPDATE_FLAG) {
            $data['avatar_extension'] = strtolower($data['avatar_extension']);

            if (!in_array($data['avatar_extension'], array('png', 'jpg', 'gif'))) {
                unset($data['avatar_extension']);
                $errors[] = OSC::core('language')->get('usr.err_ava_ext_incorrect');
            }
        }

        if (isset($data['auth_secret_key'])) {
            $data['auth_secret_key'] = preg_replace('/[^a-zA-Z0-9]/', '', $data['auth_secret_key']);

            if (strlen($data['auth_secret_key']) > 0 && strlen($data['auth_secret_key']) != 16) {
                unset($data['auth_secret_key']);
                $errors[] = OSC::core('language')->get('usr.err_auth_secret_key_incorrect');
            }
        }

        if(isset($data['sref_type'])) {
            $data['sref_type'] = trim($data['sref_type']);

            if(! $data['sref_type'] || ! isset($this->getSrefTypes()[$data['sref_type']])) {
                $data['sref_type'] = '';
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

        if (isset($data['last_visited_timestamp'])) {
            $data['last_visited_timestamp'] = intval($data['last_visited_timestamp']);

            if ($data['last_visited_timestamp'] < 1) {
                unset($data['last_visited_timestamp']);
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {
                if (!isset($data['username']) && OSC::isPrimaryStore()) {
                    $errors[] = OSC::core('language')->get('usr.err_username_empty');
                }

                if (!isset($data['email'])) {
                    $errors[] = OSC::core('language')->get('usr.err_email_empty');
                }

                if (!isset($data['group_id'])) {
                    $errors[] = OSC::core('language')->get('usr.err_group_not_select');
                }

                if (!isset($data['password_hash'])) {
                    $errors[] = OSC::core('language')->get('usr.err_password_empty');
                }

                if (!isset($data['perm_mask_ids'])) {
                    $data['perm_mask_ids'] = array();
                }

                if (!isset($data['timezone'])) {
                    $data['timezone'] = 7;
                }

                if (!isset($data['auth_secret_key'])) {
                    $data['auth_secret_key'] = '';
                }

                if (!isset($data['activated'])) {
                    $data['activated'] = 1;
                }

                if (!isset($data['avatar_extension'])) {
                    $data['avatar_extension'] = '';
                }

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = mktime();
                }

                if (!isset($data['modified_timestamp'])) {
                    $data['modified_timestamp'] = mktime();
                }

                if (!isset($data['last_visited_timestamp'])) {
                    $data['last_visited_timestamp'] = 0;
                }

                if (!isset($data['sref_type'])) {
                    $data['sref_type'] = '';
                }

                $data['avatar_extension'] = '';
            } else {
                unset($data['username']); // Don't change username
                unset($data['email']); // Don't change email
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

    protected function _afterSave() {
        parent::_afterSave();

        $keys = array('username', 'email', 'suspend_reason');

        $index_keywords = array();

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        if ($this->getActionFlag() == self::INSERT_FLAG) {
            OSC::core('observer')->dispatchEvent('add_member', array('model' => $this));

            OSC::helper('backend/common')->addIndex('', 'user', 'member_' . $this->getId(), $index_keywords, array('id' => $this->getId()));
        } else {
            OSC::helper('backend/common')->updateIndex('', 'user', 'member_' . $this->getId(), $index_keywords, array('id' => $this->getId()));
        }
    }

    protected function _afterDelete() {
        parent::_afterDelete();

        if ($this->data['activated'] != 1) {
            OSC::model('user/validating')->loadByMemberId($this->getId())->delete();
        }

        if ($this->data['avatar_extension']) {
            @unlink($this->getAvatarPath());
        }

        OSC::helper('backend/common')->deleteIndex('', 'user', 'member_' . $this->getId());
    }

    /**
     * 
     * @return string
     */
    public function getProfileUrl() {
        return OSC::core('request')->getUrl('user/profile/index', array('id' => $this->getId()));
    }

    public static function cleanUkey($ukey) {
        try {
            OSC::core('validate')->validEmail($ukey);
        } catch (Exception $ex) {
            return '';
        }

        return $ukey;
    }

    public function isIdeaResearch() {
        $idea_research_id = OSC::model('user/permissionMask')->getIdeaResearchPermId();
        $idea_research_id = intval($idea_research_id) > 0 ? intval($idea_research_id) : 0;
        if ($idea_research_id < 1) {
            return false;
        }

        $permission_ids = array_merge($this->getGroup()->data['perm_mask_ids'], $this->data['perm_mask_ids']);

        if (in_array($idea_research_id, $permission_ids)) {
            return true;
        }

        return false;
    }

    public function getListMemberIdeaResearch() {
        $collection = OSC::model('user/member')->getCollection();
        if (!OSC::isPrimaryStore()) {
            return $collection->load();
        }
        $idea_research_id = OSC::model('user/permissionMask')->getIdeaResearchPermId();
        $idea_research_id = intval($idea_research_id) > 0 ? intval($idea_research_id) : 0;
        if ($idea_research_id < 1) {
            return array();
        }

        $groups = OSC::model('user/group')->getCollection()->addField('group_id')->addCondition('perm_mask_ids', "[[:<:]]{$idea_research_id}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load();

        $members = OSC::model('user/member')->getCollection()->addField('member_id')->addCondition('perm_mask_ids', "[[:<:]]{$idea_research_id}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load();

        if ($groups->length() < 1 && $members->length() < 1) {
            return array();
        }

        if ($groups->length() > 0) {
            $group_ids = array_column($groups->toArray(), 'group_id');

            $collection->addCondition('group_id', $group_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_OR);
        }


        if ($members->length() > 0) {
            $member_ids = array_column($members->toArray(), 'member_id');
            $collection->addCondition('member_id', $member_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_OR);
        }

        return $collection->load();
    }

    public function getListMemberHasPerm($perm) {
        /* @var $DB OSC_Database */
        $clause_idx = OSC::makeUniqid();

        $collection = OSC::model('user/member')->getCollection()
            ->addClause($clause_idx)
            ->addField('member_id', 'username')
            ->addCondition('activated', 1)
            ->addCondition('group_id', OSC::systemRegistry('root_group')['admin'], OSC_Database::OPERATOR_NOT_EQUAL);

        $perms = OSC::model('user/permissionMask')->getCollection()->addField('perm_mask_id')->addCondition('permission_data', "[[:<:]]{$perm}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load();
        if ($perms->length() === 0) {
            return $collection->setNull();
        }

        foreach ($perms as $perm) {
            /**
             * @var $perm Model_User_PermissionMask
             */
            $perm_id = $perm->getId();

            $groups = OSC::model('user/group')->getCollection()->addField('group_id')->addCondition('perm_mask_ids', "[[:<:]]{$perm_id}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load();

            $members = OSC::model('user/member')->getCollection()->addField('member_id')->addCondition('perm_mask_ids', "[[:<:]]{$perm_id}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load();

            if ($groups->length() < 1 && $members->length() < 1) {
                continue;
            }

            if ($groups->length() > 0) {
                $group_ids = array_column($groups->toArray(), 'group_id');

                $collection->addCondition('group_id', $group_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_OR, $clause_idx);
            }

            if ($members->length() > 0) {
                $member_ids = array_column($members->toArray(), 'member_id');
                $collection->addCondition('member_id', $member_ids, OSC_Database::OPERATOR_IN, OSC_Database::RELATION_OR,  $clause_idx);
            }

        }

        return $collection->load();
    }

}
