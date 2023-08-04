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
 * @copyright   Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSC_Framework::Error
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Debug extends OSC_Object {

    protected $_write_log_flag = false;
    protected $_global_timer_flag = 0;
    protected $_current_process_id = null;
    protected $_root_process_ids = array();
    protected $_process = array();
    protected $_group_process_stats = array();
    protected $_process_counter = 0;
    protected $_slow_process_time = false;
    protected $_slow_process_time_trigger = 0;
    protected $_in_error_flag = false;
    protected $_log_file_prefix = '';
    protected $_error_type_labels = array(
        E_ERROR => 'E_ERROR',
        E_PARSE => 'E_PARSE',
        E_COMPILE_ERROR => 'E_COMPILE_ERROR',
        E_CORE_ERROR => 'E_CORE_ERROR',
        E_USER_ERROR => 'E_USER_ERROR',
        E_RECOVERABLE_ERROR => 'E_RECOVERABLE_ERROR'
    );

    public function initiate() {
        ini_set('display_errors', 'Off');

        error_reporting(E_ERROR | E_PARSE);
        set_error_handler(array($this, 'errorHandler'), E_ERROR | E_PARSE);
        set_exception_handler(array($this, 'exceptionHandler'));

        if (OSC_ENV_PHP_LOG) {
            if (!ini_get('log_errors')) {
                ini_set('log_errors', true);
            }

            if (!ini_get('error_log')) {
                ini_set('error_log', true);
            }
        }

        $this->_global_timer_flag = microtime(true);

        OSC::core('observer')->addObserver('shutdown', array($this, 'shutdown'));
    }

    public function setLogPrefix($prefix = null) {
        if(is_string($prefix)) {
            $prefix = preg_replace('/[^a-zA-Z0-9\_\-]/', '', $prefix);    
            
            if($prefix) {
                $prefix .= '.';   
            }
        } else {
            $prefix = '';
        }
        
        $this->_log_file_prefix = $prefix;
        return $this;
    }
    
    public function setSlowProcessTimeTrigger($value = 0) {
        $value = intval($value);
        
        if($value <= 0) {
            $value = 0;   
        }
        
        $this->_slow_process_time_trigger = $value;
        
        return $this;
    }

    public function setWriteLogFlag($flag = true) {
        $this->_write_log_flag = $flag ? true : false;
        return $this;
    }

    public function errorHandler($errno, $errstr, $errfile, $errline, $errcontext = null) {
        if ($errno !== E_USER_ERROR) {
            return;
        }

        $this->exceptionHandler(new ErrorException($this->_error_type_labels[E_ERROR] . ': ' . $errstr, 0, E_ERROR, $errfile, $errline));
    }

    public function triggerError($error_message, $error_code = 500) {
        $debug = debug_backtrace();
        $this->exceptionHandler(new ErrorException($error_message, $error_code, E_ERROR, $debug[1]['file'], $debug[1]['line']));
    }

    public function _renderJSON($exception = null) {
        $JSON = [];

        while ($this->_current_process_id !== null) {
            $this->endProcess($this->_current_process_id);
        }

        $total_time_to_process = microtime(true) - $this->_global_timer_flag;
        $total_memory_usage = memory_get_peak_usage(true);

        $slow_process_time_trigger = $this->_slow_process_time_trigger > 0 ? $this->_slow_process_time_trigger : OSC_ENV_SLOW_PROCESS_TIME_FLAG;
            
        $this->_slow_process_time = ($total_time_to_process > $slow_process_time_trigger && !OSC::isInHeavyTask()) ? $total_time_to_process : false;

        if($exception instanceof Exception || $exception instanceof Error) {
            $JSON['error_info'] = [
                'message' => $exception->getMessage(),
                'file' => "{$exception->getFile()} [{$exception->getLine()}]",
                'trace' => []
            ];

            $steps = $exception->getTrace();

            for ($i = count($steps) - 1; $i >= 0; $i--) {
                $step = $steps[$i];

                $file = isset($step['file']) ? ($step['file'] . (isset($step['line']) ? " [{$step['line']}]" : '')) : 'N/A';

                if (isset($step['function'])) {
                    if (isset($step['class'])) {
                        $function = $step['class'] . $step['type'] . $step['function'];
                    } else {
                        $function = $step['function'];
                    }

                    if (isset($step['args']) && is_array($step['args'])) {
                        $function .= ' [' . count($step['args']) . ']';
                    }
                } else {
                    $function = 'N/A';
                }

                $JSON['error_info']['trace'][] = [
                    'file' => $file,
                    'function' => $function
                ];
            }
        }

        $JSON['server_ip'] = OSC::getServerIp();
        $JSON['server_timestamp'] = time();
        $JSON['route'] = OSC::$request_path;
        $JSON['_SERVER'] = $_SERVER;
        $JSON['_REQUEST'] = $this->_recursiveTrimLargeData($_REQUEST);

        if (OSC_ENV_DEBUG_DB > 0) {
            $JSON['DB'] = [
                'total_query' => 0,
                'total_time' => 0,
                'queries' => []
            ];

            $JSON['DB']['queries'] = OSC::core('database')->getLog();

            $total_time = 0;

            foreach($JSON['DB']['queries'] as $idx => $db_log) {
                $JSON['DB']['total_query'] += count($db_log);

                foreach($db_log as $k => $record) {
                    $record['query_time'] = floatval(preg_replace('/[^0-9\.]/', '', $record['query_time']))/1000;
                    $record['params'] = $this->_recursiveTrimLargeData($record['params']);
                    $record['query'] = $this->_recursiveTrimLargeData($record['query']);
                    $record['query_parsed'] = $this->_recursiveTrimLargeData($record['query_parsed']);

                    $db_log[$k] = $record;

                    $JSON['DB']['total_time'] += $record['query_time'];
                }

                uasort($db_log,function($a, $b) {
                    if($a['query_time'] == $b['query_time']) {
                        return 0;
                    }

                    return $a['query_time']<$b['query_time'] ? -1 : 1;
                });

                $JSON['DB']['queries'][$idx] = $db_log;
            }
        }

        $JSON['process'] = [
            'total' => $this->_process_counter,
            'time' => $total_time_to_process,
            'memory_peak' => $total_memory_usage,
            'groups' => [],
            'items' => []
        ];

        if (count($this->_group_process_stats) > 0) {
            foreach ($this->_group_process_stats as $k => $v) {
                $JSON['process']['groups'][$k] = [
                    'calls' => $v['calls'],
                    'time' => $v['total_time'],
                    'memory_peak' => $v['memory_peak_usage']
                ];
            }

            $JSON['process']['items'] = $this->_recursiveRenderProcessForJSON($this->_root_process_ids);
        }

        return OSC::encode($JSON);
    }

    protected function _recursiveRenderProcessForJSON($process_ids) {
        $items = [];

        foreach ($process_ids as $process_id) {
            $items[] = [
                'key' => $this->_process[$process_id]['key'],
                'file' => $this->_process[$process_id]['file'],
                'line' => $this->_process[$process_id]['line'],
                'time' => $this->_process[$process_id]['total_time'],
                'memory' => $this->_process[$process_id]['memory_usage'],
                'message' => $this->_recursiveTrimLargeData($this->_process[$process_id]['message']),
                'children' => $this->_recursiveRenderProcessForJSON($this->_process[$process_id]['child_process_ids'])
            ];
        }

        return $items;
    }

    protected function _recursiveTrimLargeData($data, $max_length = 1024, $max_item = 1024, $is_card_number = false) {
        if(is_object($data)) {
            $data = (array) $data;
        }

        if(is_array($data)) {
            if($max_item > 0) {
                $total_item = count($data);

                if($total_time > $max_item) {
                    $data = array_slice($data, 0, $max_item, true);
                    $data[] = '[' . ($total_time - $max_item) . ' item(s) more]';
                }
            }

            foreach($data as $k => $v) {
                $data[$k] = $this->_recursiveTrimLargeData($v, $max_length, $max_item, $is_card_number ? $is_card_number : preg_match('/(cc|card)/i', $k));
            }

            return $data;
        }

        if($is_card_number && ! preg_match('/[^0-9\-\_\. ]/', $data)) {
            $total_number = strlen(preg_replace('/[^0-9]/', '', $data));

            if($total_number > 5) {
                $counter = 0;
                $data = preg_replace_callback('/(\d)/', function($matched) use($total_number, &$counter) { $counter ++; return $counter > ($total_number - 4) ? $matched[0] : '*'; }, $data);
            }
        }

        $len = strlen($data);

        if($len > $max_length) {
            $data = substr($data, 0, $max_length) . '... [' . OSC::getFormatedSize($len) . ']';  
        }

        return $data;
    }

    public function _renderErrorHTML($exception) {
        $HTML .= '<tr><td colspan="3"></td></tr>';

        $error_info = <<<EOF
<tr class="process-list-head">
    <td><span>&nbsp;</span></td>
    <td colspan="9">ERROR INFO</td>
</tr>
<tr class="global-info">
    <td>&nbsp;</td>
    <td>Error message</td>
    <td colspan="8">{$exception->getMessage()}</td>
</tr>            
<tr class="global-info">
    <td>&nbsp;</td>
    <td>Error file</td>
    <td colspan="8">{$exception->getFile()} [{$exception->getLine()}]</td>
</tr>
<tr class="process-list-tile expandable-node" node-idx="debug-backtrace">
    <td><span>&nbsp;</span></td>
    <td colspan="9">DEBUG_BACKTRACE</td>
</tr>   
<tr class="process-list-head expandable-node" parent-node-idx="debug-backtrace" disabled="disabled">
    <td>&nbsp;</td>
    <td colspan="8">File</td>
    <td>Function</td>
</tr>
EOF;

        $steps = $exception->getTrace();

        for ($i = count($steps) - 1; $i >= 0; $i--) {
            $step = $steps[$i];

            $file = isset($step['file']) ? ($step['file'] . (isset($step['line']) ? " [{$step['line']}]" : '')) : 'N/A';

            if (isset($step['function'])) {
                if (isset($step['class'])) {
                    $function = $step['class'] . $step['type'] . $step['function'];
                } else {
                    $function = $step['function'];
                }

                if (isset($step['args']) && is_array($step['args'])) {
                    $function .= ' [' . count($step['args']) . ']';
                }
            } else {
                $function = 'N/A';
            }

            $error_info .= <<<EOF
<tr class="expandable-node" parent-node-idx="debug-backtrace" disabled="disabled">
    <td>&nbsp;</td>
    <td colspan="8">{$file}</td>
    <td>{$function}</td>
</tr>                 
EOF;
        }

        return $this->_renderDebugInfo($error_info);        
    }
    
    public function writeError($error_message, $error_code = 500) {        
        $this->_in_error_flag = true;
        
        $debug = debug_backtrace();
        
        $error_info = $this->_renderJSON(new ErrorException($error_message, $error_code, E_ERROR, $debug[1]['file'], $debug[1]['line']));

        $log_id = OSC::makeUniqid();

        OSC::writeToFile(OSC_VAR_PATH . '/debug/error/' . date('d.m.Y') . '/' . $this->_log_file_prefix . $log_id . '.' . time() . '.error.json', $error_info, array('chmod' => 0644));
    }

    public function showInfo() {
        $this->_in_error_flag = true;

        $error_info = $this->_renderErrorHTML(new Exception('Debug info'));

        echo $error_info;

        die;
    }

    public function exceptionHandler($exception) {
        $this->_in_error_flag = true;

        if (IS_ENABLE_SENTRY) {
            \Sentry\init([
                'dsn' => SENTRY_DSN,
                'environment' => OSC_ENV . '_' . OSC_SITE_KEY,
            ]);
            \Sentry\captureException($exception);
        }

        if (OSC_ENV != 'production') {
            echo $this->_renderErrorHTML($exception);
            die;
        }
        
        $error_info = $this->_renderJSON($exception);

        $log_id = OSC::makeUniqid();

        OSC::writeToFile(OSC_VAR_PATH . '/debug/error/' . date('d.m.Y') . '/' . $this->_log_file_prefix . $log_id . '.' . time() . '.error.json', $error_info, array('chmod' => 0644));

        OSC::processSystemError($log_id, $exception->getMessage());

        echo "Opps, you got a system error, please send the code \"{$log_id}\" to our admin for more information";
        die;
    }

    protected function _catchLastError() {
        $error = error_get_last();

        if ($error === null) {
            return '';
        }

        if (!in_array($error['type'], array(E_ERROR, E_PARSE, E_COMPILE_ERROR, E_CORE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR))) {
            return '';
        }

        $tpl_process_path = OSC::core('template')->registry('current_process_path');

        if ($tpl_process_path) {
            $error['message'] .= "\n\n\ntemplate_path: " . $tpl_process_path;
        }

        $this->exceptionHandler(new ErrorException($this->_error_type_labels[$error['type']] . ': ' . $error['message'], 0, $error['type'], $error['file'], $error['line']));
    }

    public function getTime() {
        $mtime = microtime();
        $mtime = explode(' ', $mtime);
        $mtime = $mtime[1] + $mtime[0];

        return $mtime;
    }

    protected function _addProcess($key, $backtrace) {
        $this->_process[] = array(
            'key' => $key,
            'file' => str_replace(OSC_ROOT_PATH . '/', '', $backtrace['file']),
            'line' => $backtrace['line'],
            'message' => array(),
            'total_time' => 0,
            'memory_usage' => 0,
            'timer_flag' => microtime(true),
            'memory_flag' => memory_get_usage(),
            'parent_process_id' => $this->_current_process_id,
            'child_process_ids' => array()
        );

        $process_id = count($this->_process) - 1;

        if ($this->_current_process_id === null) {
            $this->_root_process_ids[] = $process_id;
        } else {
            $this->_process[$this->_current_process_id]['child_process_ids'][] = $process_id;
        }

        $this->_current_process_id = $process_id;
    }

    public function startProcess($key, $message = '', $backtrace = null) {
        $this->_process_counter++;

        // if (OSC_ENV == 'production' && ! OSC::registry('SHOW_DEBUG_INFO')) {
        //     return $this;
        // }

        if (!isset($this->_group_process_stats[$key])) {
            $this->_group_process_stats[$key] = array('total_time' => 0, 'memory_peak_usage' => 0, 'calls' => 0);
        }

        $this->_group_process_stats[$key]['calls']++;

        if (!$backtrace) {
            $backtrace = debug_backtrace(false, 2);
            $backtrace = $backtrace[1];
        }

        $this->_addProcess($key, $backtrace);

        if ($message) {
            $this->addProcessMessage($message);
        }

        return $this;
    }

    public function addProcessMessage() {
        if ($this->_current_process_id === null) {
            return $this;
        }

        $process = & $this->_process[$this->_current_process_id];

        foreach (func_get_args() as $message) {
            $process['message'][] = trim($message);
        }

        return $this;
    }

    public function endProcess() {
        if ($this->_current_process_id === null) {
            return $this;
        }

        $process = & $this->_process[$this->_current_process_id];

        $process['total_time'] = microtime(true) - $process['timer_flag'];
        $process['memory_usage'] = memory_get_usage() - $process['memory_flag'];

        unset($process['timer_flag']);
        unset($process['memory_flag']);

        if ($process['memory_usage'] > $this->_group_process_stats[$process['key']]['memory_peak_usage']) {
            $this->_group_process_stats[$process['key']]['memory_peak_usage'] = $process['memory_usage'];
        }

        $this->_group_process_stats[$process['key']]['total_time'] += $process['total_time'];

        $this->_current_process_id = $process['parent_process_id'] ? $process['parent_process_id'] : null;

        return $this;
    }

    protected function _renderProcessInfo() {
        while ($this->_current_process_id !== null) {
            $this->endProcess($this->_current_process_id);
        }

        $total_time_to_process = microtime(true) - $this->_global_timer_flag;
        $total_memory_usage = memory_get_peak_usage(true);

        $slow_process_time_trigger = $this->_slow_process_time_trigger > 0 ? $this->_slow_process_time_trigger : OSC_ENV_SLOW_PROCESS_TIME_FLAG;
            
        $this->_slow_process_time = ($total_time_to_process > $slow_process_time_trigger && !OSC::isInHeavyTask()) ? $total_time_to_process : false;

        $formated_total_process = number_format($this->_process_counter);
        $formated_total_time_to_process = number_format($total_time_to_process * 1000, 3);
        $formated_total_memory_usage = preg_replace_callback('/^([^a-zA-Z]+)([a-zA-Z]+)$/', function($matches) {
            return number_format($matches[1], 2) . $matches[2];
        }, OSC::getFormatedSize($total_memory_usage));

        $HTML = <<<EOF
<tr class="global-info">
    <td>&nbsp;</td>
    <td>Total process</td>
    <td colspan="8">{$formated_total_process}</td>
</tr class="global-info">
<tr class="global-info">
    <td>&nbsp;</td>
    <td>Total time</td>
    <td colspan="8">{$formated_total_time_to_process} ms</td>
</tr class="global-info">
<tr class="global-info">
    <td>&nbsp;</td>
    <td>Memory peak</td>
    <td colspan="8">{$formated_total_memory_usage}</td>
</tr>            
EOF;

        if (count($this->_group_process_stats) < 1) {
            return $HTML;
        }

        $group_rows = '';

        foreach ($this->_group_process_stats as $k => $v) {
            $v['time_percent'] = number_format(($v['total_time'] / $total_time_to_process) * 100, 2);
            $v['calls'] = number_format($v['calls']);
            $v['total_time'] = number_format($v['total_time'] * 1000, 3);
            $v['memory_peak_usage'] = preg_replace_callback('/^([^a-zA-Z]+)([a-zA-Z]+)$/', function($matches) {
                return number_format($matches[1], 2) . $matches[2];
            }, OSC::getFormatedSize($v['memory_peak_usage']));

            $this->_group_process_stats[$k] = $v;

            $group_rows .= <<<EOF
<tr class="process-item expandable-node" parent-node-idx="process-group-root" disabled="disabled">
    <td><span>&nbsp;</span></td>
    <td>{$k}</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class="ar">{$v['calls']}</td>
    <td class="ar">{$v['total_time']} ms</td>
    <td class="ar">{$v['time_percent']}%</td>
    <td class="ar">{$v['memory_peak_usage']}</td>
    <td>&nbsp;</td>
</tr>
{$child_rows}
EOF;
        }

        $rows = $this->_recursiveRender($this->_root_process_ids, $total_memory_usage, $total_time_to_process);

        $HTML .= <<<EOF
<tr class="process-list-tile expandable-node" node-idx="process-group-root">
    <td><span>&nbsp;</span></td>
    <td colspan="9">Process group</td>
</tr>
<tr class="process-list-head expandable-node" parent-node-idx="process-group-root" disabled="disabled">
    <td>&nbsp;</td>
    <td>Name</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class="ar">Calls</td>
    <td class="ar">Time</td>
    <td class="ar">Percent</td>
    <td class="ar">Memory</td>
    <td>&nbsp;</td>
</tr>
{$group_rows}
<tr class="process-list-tile expandable-node" node-idx="process-item-root">
    <td><span>&nbsp;</span></td>
    <td colspan="9">Process list</td>
</tr>
<tr class="process-list-sub-tile expandable-node" parent-node-idx="process-item-root" disabled="disabled">
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td class="ac" colspan="3">Item</td>
    <td class="ac" colspan="4">Group</td>
    <td>&nbsp;</td>
</tr>
<tr class="process-list-head expandable-node" parent-node-idx="process-item-root" disabled="disabled">
    <td>&nbsp;</td>
    <td>Name</td>
    <td class="ar">Time</td>
    <td class="ar">Percent</td>
    <td class="ar">Memory</td>
    <td class="ar">Calls</td>
    <td class="ar">Time</td>
    <td class="ar">Percent</td>
    <td class="ar">Memory</td>
    <td>Messages</td>
</tr>
{$rows}
EOF;

        return $HTML;
    }

    protected function _recursiveRender($process_ids, $total_memory_usage, $total_time_to_process, $spacing = '') {
        $data = '';

        foreach ($process_ids as $process_id) {
            $process = $this->_process[$process_id];

            $time_percent = number_format(($process['total_time'] / $total_time_to_process) * 100, 2);

            $total_time = number_format($process['total_time'] * 1000, 3);
            $memory_usage = preg_replace_callback('/^([^a-zA-Z]+)([a-zA-Z]+)$/', function($matches) {
                return number_format($matches[1], 2) . $matches[2];
            }, OSC::getFormatedSize($process['memory_usage']));

            $message = strip_tags(implode("\n", $process['message']));

            $child_rows = '';
            $note_idx_attr = '';

            if (count($process['child_process_ids']) > 0) {
                $note_idx_attr = ' node-idx="' . $process_id . '"';
                $child_rows = $this->_recursiveRender($process['child_process_ids'], $total_memory_usage, $total_time_to_process, $spacing . '&nbsp; &nbsp; &nbsp;');
            }

            $parent_idx = $process['parent_process_id'] ? $process['parent_process_id'] : 'process-item-root';

            $data .= <<<EOF
<tr class="process-item expandable-node"{$note_idx_attr} parent-node-idx="{$parent_idx}" disabled="disabled">
    <td><span>&nbsp;</span></td>
    <td>{$spacing}{$process['key']}<br />{$process['file']} [{$process['line']}]</td>
    <td class="ar">{$total_time} ms</td>
    <td class="ar">{$time_percent}%</td>
    <td class="ar">{$memory_usage}</td>
    <td class="ar">{$this->_group_process_stats[$process['key']]['calls']}</td>
    <td class="ar">{$this->_group_process_stats[$process['key']]['total_time']} ms</td>
    <td class="ar">{$this->_group_process_stats[$process['key']]['time_percent']}%</td>
    <td class="ar">{$this->_group_process_stats[$process['key']]['memory_peak_usage']}</td>
    <td><pre>{$message}</pre></td>
</tr>
{$child_rows}
EOF;
        }

        return $data;
    }

    protected function _renderServerInfo() {
        $server_params = print_r($_SERVER, 1);

        return <<<EOF
<tr class="process-list-tile expandable-node" node-idx="server-info">
    <td><span>&nbsp;</span></td>
    <td colspan="9">\$_SERVER</td>
</tr>
<tr class="expandable-node" parent-node-idx="server-info" disabled="disabled">
    <td>&nbsp;</td>
    <td colspan="9"><pre>{$server_params}</pre></td>
</tr>        
EOF;
    }

    protected function _renderRequestParamsInfo() {
        $request_params = print_r($_REQUEST, 1);

        return <<<EOF
<tr class="process-list-tile expandable-node" node-idx="request-params">
    <td><span>&nbsp;</span></td>
    <td colspan="9">\$_REQUEST</td>
</tr>
<tr class="expandable-node" parent-node-idx="request-params" disabled="disabled">
    <td>&nbsp;</td>
    <td colspan="9"><pre>{$request_params}</pre></td>
</tr>        
EOF;
    }

    protected function _renderDBLogInfo() {

        if (OSC_ENV_DEBUG_DB < 1) {
            return '';
        }

        $db_logs = OSC::core('database')->getLog();

        $counter = 0;
        $total_time = 0;

        foreach($db_logs as $idx => $db_log) {
            $counter += count($db_log);

            foreach($db_log as $record) {
                $record['query_time'] = floatval(preg_replace('/[^0-9\.]/', '', $record['query_time']));
                $total_time += $record['query_time'];
            }

            uasort($db_log,function($a, $b) {
                $a['query_time'] = preg_replace('/[^0-9\.]/', '', $a['query_time']);
                $b['query_time'] = preg_replace('/[^0-9\.]/', '', $b['query_time']);

                if($a['query_time'] == $b['query_time']) {
                    return 0;
                }

                return $a['query_time']<$b['query_time'] ? -1 : 1;
            });

            $db_logs[$idx] = $db_log;
        }

        $db_logs = print_r($db_logs, 1);

        return <<<EOF
<tr class="process-list-tile expandable-node" node-idx="database-log">
    <td><span>&nbsp;</span></td>
    <td colspan="9">DATABASE LOGS ({$counter} - {$total_time}ms)</td>
</tr>
<tr class="expandable-node" parent-node-idx="database-log" disabled="disabled">
    <td>&nbsp;</td>
    <td colspan="9"><pre>{$db_logs}</pre></td>
</tr>        
EOF;
    }

    public function shutdown() {
        $this->_catchLastError();

        if ($this->_in_error_flag) {
            return;
        }

        $JSON = $this->_renderJSON();

        if ($this->_write_log_flag) {
            OSC::writeToFile(OSC_VAR_PATH . '/debug/' . $this->_log_file_prefix . 'log.html', $this->_renderDebugInfo());
        }

        if ($this->_slow_process_time && OSC_ENV_ALERT_SLOW_PROCESS) {
            $log_id = OSC::makeUniqid();

            OSC::writeToFile(OSC_VAR_PATH . '/debug/slow/' . date('d.m.Y') . '/' . $this->_log_file_prefix . $log_id . '.' . time() . '.slow.json', $JSON, array('chmod' => 0644));

            OSC::alertSlowProcess($log_id, number_format($this->_slow_process_time, 0) . 's');
        }
    }

    public function _renderDebugInfo($error_info = '') {
        $server_ip = OSC::getServerIp();
        $server_time = date('d/m/Y H:i:s');

        return <<<EOF
<style>
body {
    padding: 0;
    margin: 0;
}

#debug-log,
#debug-log * {
    box-sizing: border-box;
}

#debug-log {
    width: 100%;
    margin: 10px;
    border: 2px solid #717171;
    border-collapse: collapse;
    border-spacing: 0;
    font-family: tahoma;
    font-size: 12px;
    background: #fff;
}

body > #debug-log {
    width: calc(100vw - 40px);
}

#debug-log td {
    padding: 5px;
    border-bottom: 1px solid #888888;
    border-right: 1px solid #888888;
    text-align: left;
    vertical-align: top;
}

#debug-log td.ac {
    text-align: center;
}
#debug-log td.ar {
    text-align: right;
}

#debug-log tr.global-info td:nth-child(1) {
    font-weight: bold;
    background: #f1f1f1;
}

#debug-log tr.process-list-tile td {
    font-weight: bold;
    font-size: 16px;
    background: #b5b5b5;
    text-transform: uppercase;
}

#debug-log tr.process-list-sub-tile td {
    font-weight: bold;
    font-size: 14px;
    background: #cacaca;
    text-transform: uppercase;
}

#debug-log tr.process-list-head td {
    font-weight: bold;
    background: #ddd;
}

#debug-log tr.process-item td:nth-child(3),
#debug-log tr.process-item td:nth-child(4),
#debug-log tr.process-item td:nth-child(5) {
    background: #fbfbfb;
}
#debug-log tr.process-item td:nth-child(6),
#debug-log tr.process-item td:nth-child(7),
#debug-log tr.process-item td:nth-child(8),
#debug-log tr.process-item td:nth-child(9) {
    background: #f1f1f1;
}


#debug-log tr.expandable-node[node-idx] td:nth-child(1) {
    width: 25px;
}
#debug-log tr.expandable-node[node-idx] td:nth-child(1) span {
    display: block;
    width: 18px;
    height: 18px;
    background: #eaeaea;
    border: 1px solid #b5b5b5;
    border-radius: 3px;
    position: relative;
    cursor: pointer;
}
#debug-log tr.expandable-node[node-idx] td:nth-child(1) span:hover {
    background: #d8d8d8;
    border: 1px solid #636363;    
}
#debug-log tr.expandable-node[node-idx] td:nth-child(1) span:before {
    content: '';
    display: block;
    width: 12px;
    height: 2px;
    background: #636363;
    position: absolute;
    top: calc((100% - 2px)/2);
    left: calc((100% - 12px)/2);
}
#debug-log tr.expandable-node[node-idx]:not([expanded]) td:nth-child(1) span:after {
    content: '';
    display: block;
    width: 2px;
    height: 12px;
    background: #636363;
    position: absolute;
    top: calc((100% - 12px)/2);
    left: calc((100% - 2px)/2);
}
#debug-log tr.expandable-node[parent-node-idx][disabled] {
    display: none;
}

#debug-log td pre {
    white-space: pre-wrap;
}
</style>
<table id="debug-log">
    <colgroup>
        <col width="25px" />
        <col width="300px" />
        <col width="100px" />
        <col width="60px" />
        <col width="70px" />
        <col width="50px" />
        <col width="100px" />
        <col width="70px" />
        <col width="60px" />
        <col />
    </colgroup>
    <tr class="global-info">
        <td>&nbsp;</td>
        <td>Server IP</td>
        <td colspan="8">{$server_ip}</td>
    </tr>
    <tr class="global-info">
        <td>&nbsp;</td>
        <td>Server time</td>
        <td colspan="8">{$server_time}</td>
    </tr>
    {$error_info}
    {$this->_renderServerInfo()}
    {$this->_renderRequestParamsInfo()}
    {$this->_renderDBLogInfo()}
    {$this->_renderProcessInfo()}
</table>
<script>
function osc_debug_toggler() {
    var to_expand = false;
        
    if(this.hasAttribute('expanded')) {
        this.removeAttribute('expanded');
    } else {
        this.setAttribute('expanded', 1);
        to_expand = true;
    }

    var child_nodes = document.getElementById('debug-log').querySelectorAll('[parent-node-idx="' + this.getAttribute('node-idx') + '"]');

    for(var i = 0; i < child_nodes.length; i ++){
        if(to_expand) {
            child_nodes[i].removeAttribute('disabled');
        } else {    
            if(child_nodes[i].hasAttribute('node-idx') && child_nodes[i].hasAttribute('expanded')) {
                osc_debug_toggler.apply(child_nodes[i]);
            }
    
            child_nodes[i].setAttribute('disabled', 'disabled');
        }
    }
}
        
var process_nodes = document.getElementById('debug-log').querySelectorAll('[node-idx]');

for(var i = 0; i < process_nodes.length; i ++){
    process_nodes[i].onclick = osc_debug_toggler;
}
</script>
EOF;
    }

}
