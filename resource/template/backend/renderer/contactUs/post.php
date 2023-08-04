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
 * @copyright    Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
/* @var $this Helper_Backend_Template */
$this->push('core/setting.scss', 'css');
?>

<form method="post" action="<?= $this->getUrl('*/*/*', ['save' => 1]) ?>">
    <div class="setting-config-panel post-frm">
        <div class="setting-config-group">
            <div class="info">
                <div class="title">Contact Us</div>
                <div class="desc">Set up Contact Us page</div>
            </div>
            <div class="block">
                <div class="p20">
                    <?php foreach ($params['key_contact_us'] as $key => $contact_us): ?>
                        <div class="frm-grid frm-grid--separate">
                            <div class="setting-item">
                                <input type="checkbox" name="config[<?= $key ?>]"
                                       data-insert-cb="initSwitcher"<?php if (OSC::helper('core/setting')->get($key) == 1) : ?> checked="checked"<?php endif; ?> />
                                <label class="label-inline ml10"><strong><?= $contact_us ?></strong></label>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="action-bar">
            <button type="submit" class="btn btn-primary" name="">Save</button>
        </div>
    </div>
</form>