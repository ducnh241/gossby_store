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
$default = array('disabled' => '', 'size' => 'normal', 'id' => '', 'label' => '', 'icon' => '', 'type' => 'text', 'style' => '', 'name' => '', 'value' => array(), 'width' => '250');

foreach($default as $k => $v) {    
    if(! isset($params[$k])) {
        $params[$k] = $v;
    }
}

$conf = array();

if (isset($params['tooltip'])) {
    $conf['tooltip'] = $params['tooltip'];
}

if (!is_array($params['value'])) {
    $params['value'] = array($params['value']);
}

$options = array();

if(! isset($params['source']['type'])) {
    $params['source']['type'] = 'array';
}

if(in_array($params['source']['type'], array('collection','helper'))) {
    if(isset($params['source']['name'])) {
        if($params['source']['type'] != 'helper' && (! isset($params['source']['function']) || ! $params['source']['function'])) {
            $params['source']['function'] = 'load';
        }

        if(isset($params['source']['function']) && $params['source']['function']) {
            if($params['source']['type'] == 'helper') {
                $collector = OSC::helper($params['source']['name']);
            } else {
                $collector = OSC::model($params['source']['name'])->getCollection();
            }
            
            if(isset($params['source']['before_load'])) {
                if(! is_array($params['source']['before_load'])) {
                    $params['source']['before_load'] = array(array($params['source']['before_load']));
                }

                foreach($params['source']['before_load'] as $before_load_params) {
                    $catch_return_flag = false;
                    
                    if(!is_array($before_load_params)) {
                        $before_load_params = array($before_load_params);
                    } else {
                        if(isset($before_load_params['catch_return_flag'])) {
                            if($before_load_params['catch_return_flag']) {
                                $catch_return_flag = true;
                            }
                            
                            unset($before_load_params['catch_return_flag']);
                        }
                    }

                    $callback = array_shift($before_load_params);

                    if($catch_return_flag) {
                        $collector = call_user_func_array(array($collector, $callback), $before_load_params);
                    } else {
                        call_user_func_array(array($collector, $callback), $before_load_params);
                    }
                }
            }
            
            if(isset($params['source']['params'])) {
                $options = call_user_func_array(array($collector, $params['source']['function']), $params['source']['params']);
            } else {
                $options = $collector->$params['source']['function']();
            }
                        
            if($params['source']['type'] == 'collection') {
                if(isset($params['source']['after_load'])) {
                    if(! is_array($params['source']['after_load'])) {
                        $params['source']['after_load'] = array(array($params['source']['after_load']));
                    }
                    
                    foreach($params['source']['after_load'] as $after_load_params) {
                        $catch_return_flag = false;

                        if(!is_array($after_load_params)) {
                            $after_load_params = array($after_load_params);
                        } else {
                            if(isset($after_load_params['catch_return_flag'])) {
                                if($after_load_params['catch_return_flag']) {
                                    $catch_return_flag = true;
                                }

                                unset($after_load_params['catch_return_flag']);
                            }
                        }

                        $callback = array_shift($after_load_params);

                        if($catch_return_flag) {
                            $options = call_user_func_array(array($options, $callback), $after_load_params);
                        } else {
                            call_user_func_array(array($options, $callback), $after_load_params);
                        }
                    }
                }
                
                $options = $options->getOptions();
            }
        }
    }
} else if($params['source']['type'] == 'callback') {
    if(isset($params['source']['callback']) && is_array($params['source']['callback']) && count($params['source']['callback']) > 0) { 
        $callback = array_shift($params['source']['callback']);
        
        if($callback) {
            $options = call_user_func_array($callback, $params['source']['callback']);   
        }
    }
} else {
    $options = isset($params['source']['data']) ? $params['source']['data'] :  array();    
}

if(!is_array($options)) {
    $options = array();
}

foreach ($options as $k => $v) {
    $options[$k] = "<option value=\"{$v['value']}\"" . (in_array($v['value'], $params['value']) ? ' selected' : '') . ">{$v['label']}</option>";
}

$options = implode('', $options);

if (isset($params['null_label'])) {
    $options = "<option value=\"\">{$params['null_label']}</option>" . $options;
}

$attrs = array();

if (isset($params['attributes']) && is_array($params['attributes'])) {
    foreach ($params['attributes'] as $k => $v) {
        $attrs[$k] = $v;
    }
}

if (isset($params['multiple'])) {
    if (isset($params['name'])) {
        if (substr($params['name'], strlen($params['name']) - 2) !== '[]') {
            $params['name'] = $params['name'] . '[]';
        }
    }

    $attrs['multiple'] = 'multiple';
}

if ($params['disabled']) {
    $attrs['disabled'] = 'disabled';
}

if ($params['id']) {
    $attrs['id'] = $params['id'];
}

if ($params['name']) {
    $attrs['name'] = $params['name'];
}

if ($params['width']) {
    if(! isset($attrs['style'])) {
        $attrs['style'] = '';
    } else {
        $attrs['style'] .= ';';
    }

    $attrs['style'] .= 'width: ' . $params['width'] . 'px';
}

$attrs['class'] = 'mrk-ui-frm-select';

if (isset($params['class'])) {
    $attrs['class'] .= ' ' . $params['class'];
}

foreach ($attrs as $key => $val) {
    $attrs[$key] = $key . '="' . $val . '"';
}

$attrs = implode(' ', $attrs);
?>
<select <?php echo $attrs; ?>><?php echo $options; ?></select>