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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
abstract class OSC_Cron_Abstract {

    const CRON_COUNTER = 1;
    const CRON_TIMER = '* * * * *';
    const CRON_SCHEDULER_FLAG = 0;
    const CRON_DISABLED = 0;
    const CRON_LOG_PATH = '';
    const CRON_LIMIT = 15;

    protected $_cron_name = '';
    protected $_log_data = array();

    public function __construct() {
        $class = get_class($this);

        if (!preg_match('/^cron_([a-zA-Z0-9]+)_([a-zA-Z0-9].+)$/i', $class, $matches)) {
            if (!preg_match('/^osc_(cron_cron_.+)/i', $class, $matches)) {
                throw new Exception('Cron file need inside component cron directory or core/cron/cron directory');
            }
            
            $matches[1] = explode('_', $matches[1]);
            
            unset($matches[1][0]);
            unset($matches[1][1]);

            $this->_cron_name = implode('_', array_map(function($v){ return lcfirst($v); }, $matches[1]));
        } else {
            $matches[2] = preg_replace_callback('/(^|_)([A-Z])/', function($m) {
                return $m[1] . strtolower($m[2]);
            }, $matches[2]);

            $this->_cron_name = lcfirst($matches[1]) . '/' . $matches[2];
        }

        $this->_request = OSC::core('request');
    }

    abstract public function process($params, $queue_added_timestamp);

    public static function makeCronQuery() {
        
    }

    public function getQueueDescData($data) {
        return '';
    }

    public function makeCrontab() {
        if (static::CRON_DISABLED) {
            return array();
        }

        $crontab = static::CRON_TIMER . ' php-cgi -f ' . OSC_SITE_PATH . '/index.php __request_path=cron/callback/process';

        if (static::CRON_LIMIT > 0 && !static::CRON_SCHEDULER_FLAG) {
            $crontab .= ' limit=' . static::CRON_LIMIT;
        }

        $crontab .= ' cron_name=' . $this->_cron_name;
        $crontab .= ' type=' . (static::CRON_SCHEDULER_FLAG ? 'scheduler' : 'queue');

        $crontab .= ' >> ' . OSC_VAR_PATH . '/cron_log/';

        if (static::CRON_LOG_PATH) {
            $crontab .= static::CRON_LOG_PATH;
        } else {
            $crontab .= '`date +"\%d.\%m.\%Y/\%H"`/' . str_replace('_', '/', $this->_cron_name) . '.log';
        }

        $return = array();

        for ($x = 0; $x < static::CRON_COUNTER; $x ++) {
            $return[] = $crontab;
        }

        return $return;
    }

    protected function _log($message) {
        echo $message;

        $this->_log_data[] = $message;

        return $this;
    }

    public function getLogData() {
        return $this->_log_data;
    }

}
