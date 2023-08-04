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
if(isset($params['options'])) {
    $params['options'] = OSC::core('json')->encode($params['options']);
} else {
    $params['options'] = '';
}

$this->push("$('#{$params['index']}').osc_backend_form({$params['options']});", 'js_code');

if (is_array($params['buttons']) && count($params['buttons']) > 0) {
    $params['buttons'] = implode('&nbsp;', $params['buttons']);
} else {
    $params['buttons'] = '';
}
?>
<form action="<?php echo $params['process_url']; ?>" method="post" id="<?php echo $params['index']; ?>">
    <div class="backend-ui-frm">
        <?php echo implode('', $params['hidden_elements']); ?>
        <ul class="mrk-backend-frm-tabs tabs<?php if(count($params['tabs']) < 2) : ?> hidden<?php endif; ?> clearfix"><?php echo implode('', $params['tabs']); ?></ul>
        <div class="body">
            <?php echo implode('', $params['elements']); ?>
        </div>
        <div class="bottom-bar"><?php echo $params['buttons']; ?></div>
    </div>
</form>
