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

if(isset($params['options'])) {
    $options = $params['options'];
} else {
    $options = array();
}

$options['id'] = $params['id'];

$options = OSC::core('json')->encode($options);

$this->push(<<<EOF
$('#{$params['id']}__container').osc_UIFormDatePicker({$options});
EOF
, 'js_code');

$params['elementJsData'][] = <<<EOF
getter : function(params){ $('#{$params['id']}').val($('#{$params['id']}__container').osc_UIFormDatePicker('getValue')); return ''; },
setter : function(params){}
EOF;

if(!isset($params['width'])) {
    $params['width'] = 1;
} else {
    $params['width'] = intval($params['width']);
}

if($params['width'] < 1) {
    $params['width'] = 150;
}

if(! isset($params['value'])) {
    $params['value'] = '';
}
?>
<div id="<?php echo $params['id']; ?>__container" style="width: <?php echo $params['width']; ?>px; display: inline-block"></div>
<input id="<?php echo $params['id']; ?>" type="hidden" name="<?php echo $params['name']; ?>" value="<?php echo $params['value']; ?>" />