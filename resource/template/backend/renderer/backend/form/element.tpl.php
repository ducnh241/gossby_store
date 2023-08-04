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
$is_one_column = false;
$element =& $params['element'];

if(! isset($element['type'])) {
    $element['type'] = '';
}

if(in_array($element['type'], array('input', 'select', 'textarea', 'switcher', 'editor'))) {
    if(! isset($element['uiconfig']['width'])) {
        $element['uiconfig']['width'] = 200;
    }
    
    $element['uiconfig']['width'] = intval($element['uiconfig']['width']);
    
    if($element['uiconfig']['width'] < 10) {
        $element['uiconfig']['width'] = 10;
    } else if($element['uiconfig']['width'] > 750) {
        $element['uiconfig']['width'] = 750;
    }
    
    if(! isset($element['value'])) {
        $element['value'] = '';
    }
        
    $element['uiconfig']['value'] = $element['value'];
    $element['uiconfig']['id'] = $element['id'];
    
    $form = $this->build('backend/form/element/' . $element['type'], $params);
} else if($element['type'] == 'custom') {
    $form = call_user_func($element['type_callback'], $params);
} else {
    $params['element_js_data']['is_label'] = true;
}

if ($form) {
    if(isset($element['depends'])) {
        $element['depends'] = OSC::core('json')->encode($element['depends'], true);
        $element['depends'] = " data-depends=\"{$element['depends']}\"";
    } else {
        $element['depends'] = '';
    }
    
    $err_container = '';

    if (isset($element['require']) && $element['require']) {
        $element['require'] = '<span style="font-weight: bold; color: #d10000; margin-left: 5px">*</span>';
        $err_container = '<div class="form-field-err-wrap" id="' . $element['id'] . '__err"></div>';
    } else {
        $element['require'] = '';
    }

    if ($is_one_column) {
        if (isset($element['label']) && $element['label']) {
            echo <<<EOF
<tr{$element['depends']}>
    <td colspan="2" class="head">{$element['label']}{$element['require']}</td>
</tr>        
EOF;
        }

        echo <<<EOF
<tr class="field-row"{$element['depends']}>
    <td class="field-cell" colspan="2">{$form}{$err_container}</td>
</tr>
EOF;
    } else {
        if(! isset($element['label']) || ! $element['label']) {
            $element['label'] = '&nbsp;';
        }
        
        echo <<<EOF
<tr class="field-row"{$element['depends']}>
    <td class="field-cell" style="width: 225px"><span class="field-label">{$element['label']}{$element['require']}</span></td>
    <td class="field-cell">{$form}{$err_container}</td>
</tr>
EOF;
    }
} else {
    echo <<<EOF
<tr class="field-row">
    <td colspan="2" class="field-head">{$element['label']}</td>
</tr>
EOF;
}
?>
