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
$params['element']['width'] = '200px';

switch ($params['element']['type']) {
    case 'input':
        $form = $this->build('backend/UI/form/input', $params['element']);
        break;
    case 'select':
        $form = $this->build('backend/UI/form/select', $params['element']);
        break;
    case 'checkbox':
        $form = $this->build('backend/UI/form/checkbox', $params['element']);
        break;
    case 'radio':
        $form = $this->build('backend/UI/form/radio', $params['element']);
        break;
    case 'switcher':
        $form = $this->build('backend/UI/form/switcher', $params['element']);
        break;
    case 'date':
        $params['element']['width'] = '95px';
        $form_params = $params['element'];
        $form_params['id'] .= '-from';
        $form['from'] = $this->build('backend/UI/form/date', $form_params);
        $form_params = $params['element'];
        $form_params['id'] .= '-to';
        $form['to'] = $this->build('backend/UI/form/date', $form_params);
        $form = $form['from'] . ' - ' . $form['to'];
        break;
    case 'custom':
        $form = call_user_func($params['element']['type_callback'], $params);
        break;
    default:
        $params['element']['is_one_column'] = false;
        $params['element']['element_js_data'] = &$params['element_js_data'];
        $form = $this->build('backend/UI/form/' . $params['element']['type'], $params['element']);
}
?>
<?php if ($params['row_element_counter'] == 1 && $params['counter'] > 1) : ?>
</div><div class="grid-filter-frm-row">
<?php endif; ?>
<div class="grid-filter-frm-element <?php echo $params['row_element_counter'] == 1 && isset($params['element']['align']) && $params['element']['align'] != 'right' ? 'fleft' : 'fright'; ?>">
    <div class="grid-filter-frm-element-label"><?php echo $params['element']['label']; ?></div>
    <?php echo $form; ?>
</div>
<?php
    if ($params['row_element_counter'] == 2) {
        $params['row_element_counter'] = 0;
    }
?>
