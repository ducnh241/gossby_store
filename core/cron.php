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

/**
  CREATE TABLE `osc_cron_queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `ukey` varchar(100) NOT NULL,
  `scheduler_flag` tinyint(1) NOT NULL DEFAULT '0',
  `scheduler_timer` varchar(255) NOT NULL,
  `cron_name` varchar(255) NOT NULL,
  `queue_data` text NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  `running_timestamp` int(10) NOT NULL,
  `locked_key` varchar(30) NOT NULL,
  `locked_timestamp` int(10) NOT NULL DEFAULT '0',
  `requeue_limit` int(11) NOT NULL DEFAULT '0',
  `requeue_counter` int(11) NOT NULL DEFAULT '0',
  `error_flag` tinyint(1) NOT NULL DEFAULT '0',
  `error_message` text NOT NULL,
  PRIMARY KEY (`queue_id`),
  UNIQUE KEY `ukey_UNIQUE` (`ukey`),
  KEY `processor` (`cron_name`),
  KEY `locked_key` (`locked_key`),
  KEY `locked_timestamp` (`locked_timestamp`),
  KEY `transport_timestamp` (`running_timestamp`),
  KEY `locked_timestamp_2` (`locked_timestamp`,`running_timestamp`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

  CREATE TABLE `osc_cron_log` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `queue_ukey` varchar(100) NOT NULL,
  `cron_name` varchar(255) NOT NULL,
  `queue_data` text NOT NULL,
  `queue_locked_key` varchar(30) NOT NULL,
  `queue_locked_timestamp` int(10) NOT NULL,
  `log_data` longtext NOT NULL,
  `added_timestamp` int(10) NOT NULL,
  PRIMARY KEY (`log_id`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;

 */
class OSC_Cron extends OSC_Object {

    const TBL_QUEUE_NAME = 'cron_queue';
    const TBL_LOG_NAME = 'cron_log';
    const CRON_REQUEUE_LIMIT = 3;
    const CRON_SKIP_DIRECT_CALL = 0;
    const CRON_DIRECT_CALL_SERVER = '';

    public static function getSchedulerNextRun($timer) {
        return Cron\CronExpression::factory($timer)->getNextRunDate()->getTimestamp();
    }

    public function addScheduler($cron_name, $scheduler_data = [], $timer = '@daily', $options = array()) {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9\_]+\/[a-zA-Z0-9\_]+$/i', $cron_name) && !preg_match('/^[a-zA-Z][a-zA-Z0-9\_]+_[a-zA-Z0-9\_]+/i', $cron_name)) {
            throw new Exception('Cron name is incorrect');
        }

        $scheduler_data = OSC::encode($scheduler_data);

        $running_timestamp = static::getSchedulerNextRun($timer);

        $estimate_timestamp = isset($options['estimate_time']) && intval($options['estimate_time']) > 0 ? intval($options['estimate_time']) :  60*60;

        try {
            /* @var $DB OSC_Database */
            $DB = OSC::core('database');

            $DB->insert(static::TBL_QUEUE_NAME, [
                'ukey' => 'scheduler:' . $cron_name . ':' . md5($scheduler_data),
                'scheduler_flag' => 1,
                'scheduler_timer' => $timer,
                'cron_name' => $cron_name,
                'queue_data' => $scheduler_data,
                'added_timestamp' => time(),
                'running_timestamp' => $running_timestamp,
                'estimate_timestamp' => $estimate_timestamp,
                'locked_key' => '',
                'locked_timestamp' => 0,
                'requeue_limit' => 0,
                'requeue_counter' => 0,
                'error_flag' => 0,
                'error_message' => ''
                    ], 'insert_cron_scheduler');

            $queue_id = OSC::core('database')->getInsertedId();

            if ($queue_id < 1) {
                throw new Exception('Cron scheduler is not insertted');
            }
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                throw new Exception($ex->getMessage());
            }
        }

        return $this;
    }

    public function removeScheduler($cron_name, $scheduler_data = []) {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9\_]+\/[a-zA-Z0-9\_]+$/i', $cron_name) && !preg_match('/^[a-zA-Z][a-zA-Z0-9\_]+_[a-zA-Z0-9\_]+/i', $cron_name)) {
            throw new Exception('Cron name is incorrect');
        }

        OSC::core('database')->delete(static::TBL_QUEUE_NAME, ['condition' => 'ukey = :ukey', 'params' => ['ukey' => 'scheduler:' . $cron_name . ':' . md5(OSC::encode($scheduler_data))]], 1, 'remove_cron_scheduler');

        return $this;
    }

    /**
     * 
     * @param string $cron_name
     * @param mixed $cron_data
     * @param array $options
     *  <table>
     *      <tr>
     *          <td colspan="2">skip_realtime, overwrite</td>
     *      </tr>
     *      <tr>
     *          <td>running_time</td>
     *          <td>integer</td>
     *      </tr>
     *      <tr>
     *          <td>requeue_limit</td>
     *          <td>integer</td>
     *      </tr>
     *      <tr>
     *          <td>ukey</td>
     *          <td>string</td>
     *      </tr>
     *  </table>
     * @return $this
     * @throws Exception
     */
    public function addQueue($cron_name, $queue_data = array(), $options = array()) {
        if (!preg_match('/^[a-zA-Z][a-zA-Z0-9\_]+\/[a-zA-Z0-9\_]+$/i', $cron_name) && !preg_match('/^cron_cron_[a-zA-Z0-9\_]+/i', $cron_name)) {
            throw new Exception('Cron name is incorrect');
        }

        if (!is_array($options)) {
            $options = array();
        }

        $running_timestamp = $current_timestamp = time();

        if (isset($options['running_time'])) {
            $running_timestamp += intval($options['running_time']);
        }

        $estimate_timestamp = isset($options['estimate_time']) && intval($options['estimate_time']) > 0 ? intval($options['estimate_time']) : 60*60;

        $queue_ukey = isset($options['ukey']) ? $options['ukey'] : OSC::makeUniqid(OSC::randKey(5, 1), true);

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        try {
            $DB->insert(static::TBL_QUEUE_NAME, array(
                'ukey' => $queue_ukey,
                'scheduler_flag' => 0,
                'scheduler_timer' => 0,
                'cron_name' => $cron_name,
                'queue_data' => OSC::encode($queue_data),
                'added_timestamp' => time(),
                'running_timestamp' => $running_timestamp,
                'estimate_timestamp' => $estimate_timestamp,
                'locked_key' => '',
                'locked_timestamp' => 0,
                'requeue_limit' => isset($options['requeue_limit']) ? intval($options['requeue_limit']) : static::CRON_REQUEUE_LIMIT,
                'requeue_counter' => 0,
                'error_flag' => 0,
                'error_message' => ''
                    ), 'insert_cron_queue');

            $queue_id = OSC::core('database')->getInsertedId();

            if ($queue_id < 1) {
                throw new Exception('Cron queue is not insertted');
            }
        } catch (Exception $ex) {
            if (strpos($ex->getMessage(), 'Integrity constraint violation: 1062 Duplicate entry') === false) {
                throw new Exception($ex->getMessage());
            }

            if (in_array('overwrite', $options, true)) {
                $DB->update(static::TBL_QUEUE_NAME, array('queue_data' => OSC::encode($queue_data)), array('condition' => 'ukey = :ukey', 'params' => array('ukey' => $queue_ukey)), 1, 'update_cron_queue');
            }
        }

        if (!static::CRON_SKIP_DIRECT_CALL && $running_timestamp <= $current_timestamp && !in_array('skip_realtime', $options, true)) {
            $this->execute($queue_id);
        }

        return $this;
    }

    public function execute($queue_id) {
        OSC::touchUrl((static::CRON_DIRECT_CALL_SERVER ? static::CRON_DIRECT_CALL_SERVER : OSC::$base_url) . '/index.php', ['__request_path' => 'cron/callback/trigger', 'queue_id' => $queue_id]);
    }

    public function processController(&$router, &$action, &$request_string) {
        if (!preg_match('/^callback\/+([^\/]+)(\/+(.+))?$/i', $request_string, $matches)) {
            OSC::core('output')->error("URL không đúng định dạng");
        }

        $router = 'callback';
        $action = $matches[1];

        $request_string = isset($matches[3]) ? $matches[3] : '';
    }

    public static function registerScheduler($cron_name, $scheduler_data = array(), $timer = '@daily', $options = array()) {
        $cron_scheduler_list = OSC::systemRegistry('cron_scheduler');

        if (!is_array($cron_scheduler_list)) {
            $cron_scheduler_list = array();
        }

        if (!is_array($options)) {
            $options = array();
        }

        $cron_scheduler_list[] = array(
            'cron_name' => $cron_name,
            'scheduler_data' => $scheduler_data,
            'timer' => $timer,
            'options' => $options
        );

        OSC::systemRegister('cron_scheduler', $cron_scheduler_list);
    }

}
