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

$class = isset($params['options']['align']) && $params['options']['align'] ? $params['options']['align'] . ' ' : '';

if (isset($params['options']['filter']) && $params['options']['filter']) {
    $rel = " rel=\"" . strip_tags($params['value']) . "\"";
} else {
    $rel = '';
}

if (isset($params['options']['tooltip']) && $params['options']['tooltip']) {
    if (!$params['options']['id']) {
        if (!$this->_buff['randomId']) {
            $this->_buff['randomId'] = array();
        }

        do {
            $params['options']['id'] = gmmktime() . '.' . OSC::core('function')->random(8);
        } while (in_array($params['options']['id'], $this->_buff['randomId']));
    }

    if (!is_array($params['options']['tooltip'])) {
        $params['options']['tooltip'] = array('content' => $params['options']['tooltip']);
    }

    $params['options']['tooltip']['id'] = $params['options']['id'];
    $this->build('core/element/tooltip', $params['options']['tooltip']);
}

if (isset($params['options']['id']) && $params['options']['id']) {
    $params['options']['id'] = " id=\"{$params['options']['id']}\"";
} else {
    $params['options']['id'] = '';
}

if (isset($params['options']['width']) && $params['options']['width']) {
    $params['options']['width'] = " style=\"width: {$params['options']['width']}\"";
} else {
    $params['options']['width'] = '';
}

if (isset($params['options']['rowspan']) && $params['options']['rowspan']) {
    $params['options']['rowspan'] = " colspan=\"{$params['options']['rowspan']}\"";
} else {
    $params['options']['rowspan'] = '';
}

if (isset($params['options']['colspan']) && $params['options']['colspan']) {
    $params['options']['colspan'] = " colspan=\"{$params['options']['colspan']}\"";
} else {
    $params['options']['colspan'] = '';
}

$action = '';

if (isset($params['options']['action']) && $params['options']['action']) {
    $class .= 'cell-action';

    if (isset($params['options']['action_confirm_message']) && $params['options']['action_confirm_message']) {
        $action = " onclick=\"sns.confirmAction('{$params['options']['action_confirm_message']}', '{$params['options']['action']}')\"";
    } else {
        $action = " onclick=\"window.location = '{$params['options']['action']}'\"";
    }
}

$class = $class ? "class=\"{$class}\" " : '';

$HTML = "<td{$action} {$class}{$rel}{$params['options']['id']}{$params['options']['width']}{$params['options']['colspan']}{$params['options']['rowspan']}>{$params['cell']}</td>";

echo $HTML;
?>
