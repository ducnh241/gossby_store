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
 * @see Helper_Backend_Template
 */

if(! isset($params['label'])) {
    $params['label'] = '';
}

$additional_class = '';

if(isset($params['size'])) {
    $params['size'] = strtolower($params['size']);
    
    if(in_array($params['size'], array('small','large','huge'))) {
        $additional_class .= ' ' . $params['size'];
    }
}

if(isset($params['color']) && $params['color']) {
    $additional_class .= ' ' . strtolower($params['color']);    
}

if (isset($params['icon'])) {
    if (strpos($params['icon'], '/') === false) {
        $params['icon'] = "<i class=\"fa fa-{$params['icon']} " . (isset($params['size']) && $params['size'] && ($params['size'] != 'normal') ? ($params['size'] == 'large' ? 'mr-p10' : 'mr-p10') : 'mr-p15') . "\"></i>";
    } else {
        $params['icon'] = "<img src=\"{$params['icon']}\" />";
    }
} else {
    $params['icon'] = '';
}

if(isset($params['type']) && in_array(strtolower($params['type']), array('reset', 'submit', 'button'))) {
    $HTML = <<<EOF
<button type="{$params['type']}" class="btn{$additional_class}">{$params['icon']}{$params['label']}</button>
EOF;
} else if (isset($params['action'])) {
    $HTML = <<<EOF
<a href="{$params['action']}" class="btn{$additional_class}">{$params['icon']}{$params['label']}</a>
EOF;
} else {
    $HTML = <<<EOF
<div class="btn{$additional_class}">{$params['icon']}{$params['label']}</div>
EOF;
}

echo $HTML;
?>
