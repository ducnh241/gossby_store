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
class Helper_AdminGroup_Common {

    public function getAdministrationGroup($is_group_marketing, $return_user_ids = false) {
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

    /**
     * @param $group_id
     * @return array
     * @throws Exception
     */
    public function getMembersByGroup($group_id) {
        try {
            $members =  OSC::model('user/member')->getCollection()->addField('member_id')->addCondition('group_id', $group_id)->load();
            if ($members->length() < 1) {
                throw new Exception('Not have member in group #'.$group_id);
            }

            return array_column($members->toArray(), 'member_id');
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function getMembersGroup($member_id, $return_user_ids = false) {
        try {
            $member = OSC::model('user/member')->load($member_id);

            $adminGroup = $member->getGroupAdmin();

            $member_by_group = OSC::model('user/member')->getCollection();

            if ($adminGroup) {
                $member_by_group->addCondition('group_id',  $adminGroup->data['group_ids'], OSC_Database::OPERATOR_IN);
            }

            $member_by_group->addCondition('member_id', $member_id,OSC_Database::OPERATOR_EQUAL,'OR')->load();

            if ($return_user_ids) {
                $member_by_group = $member_by_group->toArray();
                $result = array_unique(array_column($member_by_group, 'member_id'));
            } else {
                $result = $member_by_group->getItems();
            }

            return $result;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }
}

