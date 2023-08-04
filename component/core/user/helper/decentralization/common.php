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
 * OSC::User
 *
 * @package Helper_User_Member_Group_Common
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_User_Decentralization_Common {

    public function getAllAdminGroup($return_user_ids = false) {
        try {
            $result = [];
            $member_item_ids = [];

            $collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->addField('member_id')->load();

            if ($return_user_ids) {
                foreach ($collection as $item) {
                    $member_item_ids[] = $item->data['member_id'];
                }

                $result = $member_item_ids;
            } else {
                foreach ($collection as $item) {
                    $result[] = $item->getMember();
                }
            }

            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function isAdminGroup($member_id) {
        try {
            $collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->addCondition('member_id', $member_id)->setLimit(1)->load();
            return $collection->getItem() ? true : false;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function _getParentGroup($group_item_id, &$match) {
        $group_child = OSC::model('user/group')->getCollection()->addField('group_id', 'parent_id', 'title')->addCondition('parent_id', $group_item_id)->load();

        if ($group_child->getItems()) {

            foreach ($group_child->getItems() as $child) {
                $match['group_child'][$child->data['group_id']] = $child->data;

                $match['group_child'][$child->data['group_id']]['member'] = $child->getMember();

                $this->_getParentGroup($child->data['group_id'], $match['group_child'][$child->data['group_id']]);
            }

        } else {
            $match['group_child'] = [];
        }
    }

    protected  function _getAllParentGroup($group_item_id, &$match)
    {
        $group_child = OSC::model('user/group')->getCollection()->addCondition('parent_id', $group_item_id)->load();

        if ($group_child->getItems()) {
            foreach ($group_child as $child) {
                $match[] = $child->data['group_id'];
                $this->_getAllParentGroup($child->data['group_id'], $match);
            }
        }
    }

    public function getSelectorsByLeader(
        $action = 'index',
        $sref_member_id = null,
        $sref_group_id = null,
        $range = 'today',
        $params = []
    ) {
        $group_selected_ids = [];

        if ($sref_member_id) {
            $group_selected_ids = $this->getSelectedIds($sref_member_id);
        } elseif ($sref_group_id) {
            $group_selected_ids = $this->getSelectedIds($sref_group_id, 'group');
        }

        $member_perm_ids = [];
        $group_perm_ids = [];
        $current_member = OSC::helper('user/authentication')->getMember();
        $result = [];
        if ($current_member->isAdmin()) {
            $list_member_mkt = OSC::helper('report/common')->getListMemberActiveAnalytic();
            $marketing_permission_mask_id = OSC::helper('report/common')->getMarketingPermId();


            if ($marketing_permission_mask_id) {
                $groups = OSC::model('user/group')->getCollection()->addField('group_id', 'title', 'perm_mask_ids')->addCondition('lock_flag', 0)->addCondition('perm_mask_ids', "[[:<:]]{$marketing_permission_mask_id}[[:>:]]", OSC_Database::OPERATOR_REGEXP)->load()->getItems();
            } else {
                $groups = [];
            }

            $group_ids = [];
            foreach ($groups as $key => $_group) {
                $member_belong_group = $this->getMemberBelongGroup($action, $_group, $group_selected_ids, $range, $member_perm_ids, $params);
                if ($member_belong_group) {
                    $result["g{$_group->getId()}"] = [
                        'primary_id' => $_group->getId(),
                        'title' => $_group->data['title'],
                        'selected' => in_array("g{$_group->getId()}", $group_selected_ids),
                        'type' => 'group',
                        'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['sref_group_id' => $_group->getId(), 'range' => $range], $params)),
                    ];
                    $result = $result + $member_belong_group;
                }

                $group_perm_ids[] = $_group->getId();
                $group_ids[] = $_group->getId();
            }

            $member_exclude_ids = $this->getMemberExcludeIds($group_ids);

            foreach ($list_member_mkt as $k => $member_mkt) {
                if (!in_array($member_mkt->getId(), $member_exclude_ids)) {
                    $result["m{$member_mkt->getId()}"] = [
                        'primary_id' => $member_mkt->getId(),
                        'title' => $member_mkt->data['username'],
                        'selected' => in_array("m{$member_mkt->getId()}", $group_selected_ids),
                        'type' => 'member',
                        'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['sref_member_id' => $member_mkt->getId(), 'range' => $range], $params)),
                        'child' => [],
                    ];
                    $member_perm_ids[] = $member_mkt->getId();
                }
            }
        } else {
            $flag_selected = false;

            $collection = OSC::model('adminGroup/memberGroupsAdmin')->getCollection()->addCondition('member_id', $current_member->getId())->setLimit(1)->load();

            if ($collection->getItem()) {
                $group_ids = $collection->getItem()->data['group_ids'];
                $groups = OSC::model('user/group')->getCollection()->addField('group_id', 'title', 'perm_mask_ids')->sort('group_id', OSC_Database::ORDER_DESC)->load($group_ids)->getItems();
                foreach ($groups as $key => $_group) {
                    if (!$this->checkGroupHasPermSrefReport($_group)) {
                        continue;
                    }
                    $member_belong_group = $this->getMemberBelongGroup($action, $_group, $group_selected_ids, $range, $member_perm_ids, $params);
                    if ($member_belong_group) {
                        $result["g{$_group->getId()}"] = [
                            'primary_id' => $_group->getId(),
                            'title' => $_group->data['title'],
                            'selected' => in_array("g{$_group->getId()}", $group_selected_ids),
                            'type' => 'group',
                            'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['sref_group_id' => $_group->getId(), 'range' => $range], $params)),
                        ];
                        $result = $result + $member_belong_group;
                    }

                    if (in_array("g{$_group->getId()}", $group_selected_ids)) {
                        $flag_selected = true;
                    }
                    $group_perm_ids[] = $_group->getId();
                }
                $member_exclude_ids = $this->getMemberExcludeIds($group_ids);
            }

            $collection = OSC::model('user/permissionAnalytic')->getCollection()
                ->addCondition('member_id', $current_member->getId(), 'EXACT')
                ->setLimit(1)
                ->load();

            // permission analytic
            if ($collection->getItem()->data) {
                $members_mkt = OSC::model('user/member')->getCollection()->load($collection->getItem()->data['member_mkt_ids']);
                foreach ($members_mkt->getItems() as $member_mkt) {
                    if (!in_array($member_mkt->getId(), $member_exclude_ids)) {
                        $result["m{$member_mkt->getId()}"] = [
                            'primary_id' => $member_mkt->getId(),
                            'title' => $member_mkt->data['username'],
                            'selected' => $sref_member_id == $member_mkt->getId() ? true : in_array("m{$member_mkt->getId()}", $group_selected_ids),
                            'type' => 'member',
                            'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['sref_member_id' => $member_mkt->getId(), 'range' => $range], $params)),
                            'child' => [],
                        ];
                        $member_perm_ids[] = $member_mkt->getId();
                    }
                    if ($sref_member_id == $member_mkt->getId() ? true : in_array("m{$member_mkt->getId()}", $group_selected_ids)) {
                        $flag_selected = true;
                    }
                }
            }

            if (count($result) > 0) {
                if (!in_array($current_member->getId(), $member_perm_ids)) {
                    if ($sref_member_id && $sref_member_id == $current_member->getId()) {
                        $flag_selected = true;
                    }
                    $this->addCurrentMember($action, $result, $member_perm_ids, $sref_member_id, $flag_selected, $range, $params);
                }
            } else {
                if (OSC::controller()->checkPermission('report&srefReport', false) && !$current_member->isAdmin()) {
                    $flag_selected = $sref_member_id && $sref_member_id == $current_member->getId() ? true : false;
                    $this->addCurrentMember($action, $result, $member_perm_ids, $current_member->getId(), $flag_selected, $range, $params);
                }
            }

        }

        if (count($result) > 1 || in_array($current_member->getId(), $member_perm_ids)) {
            array_unshift($result, [
                'primary_id' => '*',
                'title' => 'All Results',
                'selected' => false,
                'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['range' => $range], $params)),
                'type' => '',
                'child' => [],
            ]);
        }

        // check bypass url
        if ($sref_member_id) {
            if (!in_array($sref_member_id, $member_perm_ids)) {
                throw new Exception('Not Found Member', 404);
            }
        }
        if ($sref_group_id) {
            if (!in_array($sref_group_id, $group_perm_ids)) {
                throw new Exception('Not Found Group', 404);
            }
        }

        return $result;
    }

    private function getMemberExcludeIds($group_ids)
    {
        $members_exclude = OSC::model('user/member')->getCollection()->addField('member_id')->addCondition('group_id', $group_ids, OSC_Database::OPERATOR_IN)->load()->getItems();
        return array_map(function ($member) {
            return intval($member->getId());
        }, $members_exclude);
    }

    public function getMemberBelongGroup($action, Model_User_Group $group, $group_selected_ids, $range, &$member_perm_ids, $params = [])
    {
        $result = [];
        $members = OSC::model('user/member')->getCollection()->addField('member_id', 'username')->addCondition('group_id', $group->getId())->load()->getItems();

        foreach ($members as $key => $_member) {
            $result["m{$_member->getId()}"] = [
                'primary_id' => $_member->getId(),
                'title' => $_member->data['username'],
                'selected' => in_array("m{$_member->getId()}", $group_selected_ids),
                'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['sref_member_id' => $_member->getId(), 'range' => $range], $params)),
                'type' => 'member',
                'child_of' => 'g' . $group->getId(),
                'child' => [],
                'group_title' => $group->data['title'],
            ];
            $member_perm_ids[] = $_member->getId();
        }
        return $result;
    }

    private function addCurrentMember($action, &$result, &$member_perm_ids, $sref_member_id, $flag_selected, $range, $params = [])
    {
        $current_member = OSC::helper('user/authentication')->getMember();
        $result["m" . $current_member->getId()] = [
            'primary_id' => $current_member->getId(),
            'title' => $current_member->data['username'],
            'selected' => $flag_selected == true && $sref_member_id && $sref_member_id == $current_member->getId(),
            'link' => OSC::getUrl('srefReport/backend/' . $action, array_merge(['sref_member_id' =>$current_member->getId(), 'range' => $range], $params)),
            'type' => 'member',
        ];
        $member_perm_ids[] = $current_member->getId();
    }

    public function getSelectedIds($sref_id, $type = 'member')
    {
        try {
            if ($type == 'member') {
                $member = OSC::model('user/member')->load($sref_id);
                $group = OSC::model('user/group')->load($member->data['group_id']);
            } elseif ($type == 'group') {
                $group = OSC::model('user/group')->load($sref_id);
            }
            $group_ids = [];
            if (isset($group)) {
                $group_ids["g{$group->getId()}"] = "g{$group->getId()}";
            }
            if ($type == 'member') {
                $group_ids["m{$sref_id}"] = "m$sref_id";
            } elseif ($type == 'group') {
                $group_ids["g{$sref_id}"] = "g$sref_id";
            }
            $group_ids = array_reverse($group_ids);
            return $group_ids;
        } catch (Exception $e) {
            return [];
        }
    }

    private function checkGroupHasPermSrefReport(Model_User_Group $group)
    {
        $collection = OSC::model('user/permissionMask')->getCollection()->load($group->data['perm_mask_ids']);

        $_permission_data = array();
        foreach ($collection as $perm_mask) {
            $_permission_data = array_merge($_permission_data, $perm_mask->data['permission_data']);
        }
        $_permission_data = array_unique($_permission_data);

        return in_array('srefReport', $_permission_data);
    }
}
