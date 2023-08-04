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
 * @package Helper_User_Member_Validator
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_User_Member_Validator {

    /**
     *
     * @param string $username
     * @param int $id
     * @return string
     * @throws OSC_Exception_Condition
     */
    public function validUsername($username, $id = 0) {
        if (preg_match('/[^a-zA-Z0-9_]/', $username)) {
            throw new OSC_Exception_Condition('Please do not include special characters in the Username field');
        }

        if (strlen($username) > 100 || strlen($username) < 3) {
            throw new OSC_Exception_Condition('Username length is need between 3 and 100 characters');
        }

        $id = intval($id);
        $model = null;

        try {
            $model = OSC::model('user/member');
            $model->setCondition([
                'field' => 'username',
                'value' => $username,
                'operator' => OSC_Database::OPERATOR_EQUAL
            ])->load();
        } catch (Exception $ex) {
        }

        if ($model instanceof Model_User_Member && $model->getId() > 0 && $model->getId() !== $id ) {
            throw new OSC_Exception_Condition('Username is already used');
        }

        return $username;
    }

    /**
     *
     * @param string $email
     * @param int $id
     * @return string
     * @throws OSC_Exception_Condition
     */
    public function validEmail($email, $id = 0) {
        OSC::core('validate')->validEmail($email);

        $id = intval($id);
        $model = null;

        try {
            $model = OSC::model('user/member')->loadByEmail($email);
        } catch (Exception $ex) {
        }

        if ($model instanceof Model_User_Member && $model->getId() > 0 && $model->getId() !== $id ) {
            throw new OSC_Exception_Condition(OSC::core('language')->get('usr.err_email_already'));
        }

        return $email;
    }

    /**
     *
     * @param int $group_id
     * @return int
     * @throws OSC_Exception_Condition
     */
    public function validGroupId($group_id) {
        try {
            $group_id = OSC::helper('user/group_validator')->validGroupId($group_id);
        } catch (OSC_Exception_Condition $e) {
            throw $e;
        }

        if ($group_id == OSC::systemRegistry('root_group')['guest']) {
            throw new OSC_Exception_Condition(OSC::core('language')->get('usr.err_group_not_exist'));
        }

        return $group_id;
    }

    /**
     *
     * @param string $password
     * @return string
     * @throws OSC_Exception_Condition
     */
    public function validPassword($password) {
        if (!$password) {
            throw new OSC_Exception_Condition(OSC::core('language')->get('usr.err_password_empty'));
        }

        if (strlen($password) < 6) {
            throw new OSC_Exception_Condition(OSC::core('language')->get('usr.err_password_weak'));
        } else if (strlen($password) > 32) {
            throw new OSC_Exception_Condition(OSC::core('language')->get('usr.err_password_exceed'));
        }

        return $password;
    }

    /**
     *
     * @param int $id
     * @return int
     */
    public function validMemberId($id) {
        $id = intval($id);

        if ($id < 1) {
            return 0;
        }

        try {
            OSC::model('user/member')->load($id);
        } catch (Exception $ex) {
            return 0;
        }

        return $id;
    }

    /**
     *
     * @param array $ids
     * @return array
     */
    public function validMemberIds($ids) {
        if (!is_array($ids)) {
            $buff = array($ids);
        } else {
            $buff = $ids;
        }

        $ids = array();

        foreach ($buff as $id) {
            $id = intval($id);

            if ($id > 0 && !in_array($id, $ids)) {
                $ids[] = $id;
            }
        }

        if (count($ids) > 0) {
            $collection = OSC::model('user/member')->getCollection()->load($ids);

            $ids = array();

            foreach ($collection as $model) {
                $ids[] = $model->getId();
            }

            $collection->destruct();
        }

        return $ids;
    }

}
