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
 * OSC Backend Abstract Controller
 *
 * @package Abstract_Backend_Controller
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
abstract class Abstract_Backend_Controller extends Abstract_Core_Controller {

    /**
     *
     * @var boolean 
     */
    protected $_check_perm = true;

    /**
     *
     * @var boolean 
     */
    protected $_check_hash = true;

    /**
     *
     * @var string 
     */
    protected $_hash_failed_forward = 'backend/index/dashboard';

    /**
     *
     * @var Helper_Backend_Template
     */
    protected $_template = null;

    protected $_date_range_pattern = '/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/';

    public function __construct() {
        parent::__construct();

        OSC::setUsingHashFlag(true);

        OSC::core('language')->load('backend/common');

        $perm_verify_flag = $this->_check_perm ? Abstract_Backend_Controller::_verifyBackendSession() : 0;

        if ($perm_verify_flag > 0) {
//            OSC::core('observer')->dispatchEvent('backend_auth_failed');
            if ($perm_verify_flag == 1) {
                $this->forward('backend/index/dashboard');
            } else if ($perm_verify_flag == 2) {
                $this->forward('backend/index/authentication');
            }
        }

//        if (OSC::sessionGet('checkSystemHashFail') && !OSC::registry('system_auth_failed')) {
//            OSC::helper('backend/template')->addComponent('system_hash_fail');
//        }

        if ($this->_request->get(OSC_IS_DEVELOPER_KEY) === '1') {
            OSC::cookieSetSiteOnly(OSC_IS_DEVELOPER_KEY, 1);
        } elseif ($this->_request->get(OSC_IS_DEVELOPER_KEY) === '0') {
            OSC::cookieRemoveSiteOnly(OSC_IS_DEVELOPER_KEY);
        }
    }

    public static function removeBackendTicket() {
        $cookie_key = static::_getBackendTicketKey();

        if ($cookie_key) {
            OSC::cookieRemoveSiteOnly($cookie_key);
        }
    }

    public static function setBackendTicket() {
        OSC::cookieSetSiteOnly(static::_getBackendTicketKey(), time() + (60 * 60 * 24));
    }

    protected static function _getBackendTicketKey() {
        if (OSC::helper('user/authentication')->getMember()->getId() < 1) {
            return '';
        }

        return static::makeRequestChecksum('backend_ticket', OSC::helper('user/authentication')->getMember()->data['password_hash']);
    }

    /**
     * 
     * @staticvar array $flag
     * @return int
     */
    protected static function _verifyBackendSession() {
        static $flag = null;

        if ($flag !== null) {
            return 0;
        }

        $flag = 0;

        $cookie_key = static::_getBackendTicketKey();

        $access_ticket = $cookie_key ? OSC::cookieGet($cookie_key) : null;

        if (!$access_ticket) {
            $flag = 2;
        } else if ($access_ticket < time()) {
            static::removeBackendTicket();

            OSC::core('request')->append('err', 'backend.err_session_exceed');

            $flag = 2;
        } else {
            static::_checkPermission('backend');
        }

        if (!$flag) {
            static::setBackendTicket();
        }

        return $flag;
    }

    /**
     *
     * @return Helper_Backend_Template
     */
    public function getTemplate() {
        if ($this->_template === null) {
            $this->_template = OSC::helper('backend/template');
        }

        return $this->_template;
    }

    /**
     * 
     * @param string $buffer
     * @param boolean $dock_enabled
     */
    public function output($buffer, $dock_enabled = true) {
        $buffer = $this->getTemplate()->setDock($dock_enabled)->setContent($buffer)->render();

        parent::output($buffer);
    }

    public function getUrl($action_path = null, $params = array(), $inc_hash = true) {
        return OSC::getUrl($action_path, $params, $inc_hash);
    }

    public function getCurrentUrl() {
        return OSC::getCurrentUrl();
    }

    public function rebuildUrl($params) {
        return OSC::rebuildUrl($params);
    }

    protected function _processFilterValue($filter_config, $filter_value) {
        foreach ($filter_value as $key => $value) {
            if (!isset($filter_config[$key])) {
                unset($filter_value[$key]);
                continue;
            }

            $config = $filter_config[$key];

            if (in_array($config['type'], ['select', 'radio', 'checkbox'], true) && (!isset($config['data']) || !is_array($config['data']) || count($config['data']) < 1)) {
                unset($filter_value[$key]);
                continue;
            }

            switch ($config['type']) {
                case 'checkbox':
                    if (!is_array($value) || count($value) < 1) {
                        unset($filter_value[$key]);
                        continue;
                    }

                    $buff = [];
                    foreach ($value as $v) {
                        if (!isset($config['data'][$v])) {
                            continue;
                        }

                        $buff[] = $v;
                    }

                    $value = $buff;

                    if (count($value) < 1) {
                        unset($filter_value[$key]);
                        continue;
                    }
                    break;
                case 'select':
                case 'radio':
                    if (!isset($config['data'][$value])) {
                        unset($filter_value[$key]);
                        continue;
                    }
                    break;
                case 'daterange':
                    if (!preg_match($this->_date_range_pattern, $value, $matches)) {
                        unset($filter_value[$key]);
                        continue;
                    }

                    for ($i = 1; $i <= 7; $i ++) {
                        if ($i == 4) {
                            continue;
                        }

                        $matches[$i] = intval($matches[$i]);
                    }

                    if (!checkdate($matches[2], $matches[1], $matches[3]) || ($matches[5] && !checkdate($matches[6], $matches[5], $matches[7]))) {
                        unset($filter_value[$key]);
                        continue;
                    }

                    $compare_start = intval(str_pad($matches[3], 4, 0, STR_PAD_LEFT) . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . str_pad($matches[1], 2, 0, STR_PAD_LEFT));

                    if ($matches[5]) {
                        $compare_end = intval(str_pad($matches[7], 4, 0, STR_PAD_LEFT) . str_pad($matches[6], 2, 0, STR_PAD_LEFT) . str_pad($matches[5], 2, 0, STR_PAD_LEFT));

                        if ($compare_start > $compare_end) {
                            $buff = $compare_end;
                            $compare_end = $compare_start;
                            $compare_start = $buff;
                        }

                        $value = $matches[1] . '/' . $matches[2] . '/' . $matches[3] . '-' . $matches[5] . '/' . $matches[6] . '/' . $matches[7];
                    } else {
                        $value = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
                    }
                    break;
                case 'datetimerange':
                    break;
                case 'range':
                    if ($value['time']) {
                        if (!preg_match($this->_date_range_pattern, $value['time'], $matches)) {
                            OSC::sessionRemove('catalog/product/search/sold');
                            continue;
                        }

                        for ($i = 1; $i <= 7; $i ++) {
                            if ($i == 4) {
                                continue;
                            }

                            $matches[$i] = intval($matches[$i]);
                        }

                        if (!checkdate($matches[2], $matches[1], $matches[3]) || ($matches[5] && !checkdate($matches[6], $matches[5], $matches[7]))) {
                            unset($filter_value[$key]);
                            continue;
                        }

                        $compare_start = intval(str_pad($matches[3], 4, 0, STR_PAD_LEFT) . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . str_pad($matches[1], 2, 0, STR_PAD_LEFT));

                        if ($matches[5]) {
                            $compare_end = intval(str_pad($matches[7], 4, 0, STR_PAD_LEFT) . str_pad($matches[6], 2, 0, STR_PAD_LEFT) . str_pad($matches[5], 2, 0, STR_PAD_LEFT));

                            if ($compare_start > $compare_end) {
                                $buff = $compare_end;
                            }

                            $timeValue = $matches[1] . '/' . $matches[2] . '/' . $matches[3] . '-' . $matches[5] . '/' . $matches[6] . '/' . $matches[7];
                        } else {
                            $timeValue = $matches[1] . '/' . $matches[2] . '/' . $matches[3];
                        }

                        $value['time'] = $timeValue;
                    }
                    break;
                case 'operator':
                    $value = OSC::decode($value);
                    $filter_value[$key] = $value;
                    break;
                default:
                    if (is_array($value)) {
                        unset($filter_value[$key]);
                        continue;
                    }
                    break;
            }
        }

        return $filter_value;
    }

    protected function _applyFilter($collection, $filter_config, $filter_value) {
        if (count($filter_value) > 0) {
            $collection->addClause('filter', 'AND');

            foreach ($filter_value as $key => $value) {
                if (!isset($filter_config[$key])) {
                    continue;
                }

                if (is_array($value) && count($value) == 1) {
                    $value = $value[0];
                }

                if (is_array($value)) {
                    $clause_idx = OSC::makeUniqid();

                    $collection->addClause($clause_idx, 'AND', 'filter');

                    foreach ($value as $v) {
                        $collection->addCondition($filter_config[$key]['field'], $v, OSC_Database::OPERATOR_EQUAL, 'OR', $clause_idx);
                    }
                } else if ($filter_config[$key]['type'] == 'daterange') {
                    preg_match($this->_date_range_pattern, $value, $matches);

                    $start_timestamp = mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]);

                    if ($matches[5]) {
                        $end_timestamp = mktime(24, 0, 0, $matches[6], $matches[5], $matches[7]);
                    } else {
                        $end_timestamp = mktime(24, 0, 0, $matches[2], $matches[1], $matches[3]);
                    }

                    $clause_idx = OSC::makeUniqid();

                    $collection->addClause($clause_idx, 'AND', 'filter')
                            ->addCondition($filter_config[$key]['field'], $start_timestamp, '>=', 'AND', $clause_idx)
                            ->addCondition($filter_config[$key]['field'], $end_timestamp, '<=', 'AND', $clause_idx);
                } else {
                    $collection->addCondition($filter_config[$key]['field'], $value, OSC_Database::OPERATOR_EQUAL, 'AND', 'filter');
                }
            }
        }
    }

    public static function setLastListUrl() {
        OSC::sessionSet('backend_last_list', OSC::getCurrentUrl());
    }

    public static function redirectLastListUrl($default_url = '') {
        $last_list_url = OSC::sessionGet('backend_last_list');

        static::redirect($last_list_url ? $last_list_url : $default_url);
    }
}
