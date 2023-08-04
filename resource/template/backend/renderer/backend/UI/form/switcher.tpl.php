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
$default = array('id' => '', 'skin' => 'yesno', 'name' => '', 'value' => 0, 'disabled' => '', 'class' => '');

foreach($default as $k => $v) {    
    if(! isset($params[$k])) {
        $params[$k] = $v;
    }
}

$attrs = array();

if($params['id']){
    $attrs['id'] = $params['id'];
}

if($params['name']){
    $attrs['name'] = $params['name'];
}
    
$attrs['value'] = 1;

$attrs['class'] = 'mrk-switcher';

if($params['class']){
    $attrs['class'] .= ' ' . $params['class'];
}

if($params['disabled']) {
    $attrs['disabled'] = 'disabled';
}

if($params['value']) {
    $attrs['checked'] = 'checked';
}

if($params['skin']) {
    $attrs['skin'] = $params['skin'];
}

if(isset($params['tooltip']) && $params['tooltip']){
    $attrs['tooltip'] = $params['tooltip'];
}

foreach($attrs as $key => $val) {
    $attrs[$key] = $key . '="' . htmlentities($val) . '"';
}

$attrs = implode(' ', $attrs);
?>
<input type="checkbox" <?php echo $attrs; ?> />