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
 * OSC_Backend
 *
 * @package Model_Backend_Log
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Model_Backend_Log extends Abstract_Core_Model {

    /**
     *
     * @var string 
     */
    protected $_table_name = 'backend_logs';

    /**
     *
     * @var string 
     */
    protected $_pk_field = 'log_id';

    /**
     *
     * @var array 
     */
    protected $_option_conf = array('value' => 'log_id', 'label' => 'content');

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForSave(&$data) {
        if (isset($data['log_data'])) {
            $data['log_data'] = OSC::encode($data['log_data']);
        }

        parent::_preDataForSave($data);
    }

    /**
     * 
     * @param array &$data
     */
    protected function _preDataForUsing(&$data) {
        if (isset($data['log_data'])) {
            $data['log_data'] = OSC::decode($data['log_data']);
        }

        parent::_preDataForUsing($data);
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['ip']) && $data['username'] !=  Model_User_Member::SYSTEM_MEMBER_USERNAME) {
            $data['ip'] = OSC::cleanIpAddress($data['ip']);

            if (!$data['ip']) {
                $errors[] = OSC::core('language')->get('core.err_log_ip_incorrect');
            }
        }

        if (isset($data['member_id']) && $data['username'] != Model_User_Member::SYSTEM_MEMBER_USERNAME) {
            $data['member_id'] = OSC::helper('user/member_validator')->validMemberId($data['member_id']);

            if ($data['member_id'] < 1) {
                unset($data['member_id']);
                $errors[] = OSC::core('language')->get('backend.err_log_member_id_incorrect');
            }
        }

        if (isset($data['username'])  && $data['username'] != Model_User_Member::SYSTEM_MEMBER_USERNAME) {
            $data['username'] = OSC::helper('user/member_common')->cleanUsername($data['username']);

            if (!$data['username']) {
                unset($data['username']);
                $errors[] = OSC::core('language')->get('backend.err_log_username_incorrect');
            }
        }

        if (isset($data['content'])) {
            $data['content'] = trim($data['content']);

            if (!$data['content']) {
                unset($data['content']);
                $errors[] = OSC::core('language')->get('backend.err_log_content_empty');
            }
        }

        if (isset($data['log_data'])) {
            if (!is_array($data['log_data'])) {
                $data['log_data'] = array();
            }
        }

        if (isset($data['added_timestamp'])) {
            $data['added_timestamp'] = intval($data['added_timestamp']);

            if ($data['added_timestamp'] < 1) {
                unset($data['added_timestamp']);
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == self::INSERT_FLAG) {
                if (!isset($data['ip'])) {
                    $data['ip'] = OSC::getClientIp();
                }

                if (!isset($data['member_id'])) {
                    $data['member_id'] = OSC::helper('user/authentication')->getMember()->getId();
                    $data['username'] = OSC::helper('user/authentication')->getMember()->data['username'];
                } else if (!isset($data['username'])) {
                    $data['username'] = OSC::model('user/member')->load($data['member_id'])->data['username'];
                }

                if (!isset($data['content'])) {
                    $errors[] = OSC::core('language')->get('backend.err_log_content_empty');
                }

                if (!isset($data['log_data'])) {
                    $data['log_data'] = array();
                }

                if (!isset($data['added_timestamp'])) {
                    $data['added_timestamp'] = mktime();
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

}
