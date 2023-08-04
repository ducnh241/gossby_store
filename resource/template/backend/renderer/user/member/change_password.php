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
/* @var $this Helper_Backend_Template */
$this->addComponent('select2')
    ->push('user/member/post.scss', 'css')
    ->push('user/member/common.js', 'js');

?>

<form action="<?php echo $this->getUrl('*/*/*') ?>" method="post" class="post-frm p25" style="width: 550px">
    <div class="block">
        <div class="header">
            <div class="header__main-group"><div class="header__heading"><?= $params['form_title'] ?></div></div>
        </div>
        <div class="p20 form_user_group_select">
            <div class="frm-grid">
                <div>
                    <label for="input-password">Current Password</label>
                    <div class="input-password">
                        <input type="password" class="styled-input" name="current_password" id="input-password" tabindex="0" required />
                        <button type="button" class="btn btn-icon-eye"><?= $this->getIcon('eye-regular', array('class' => 'mr5')) ?></button>
                    </div>
                </div>
            </div>
            <div class="frm-grid">
                <div>
                    <label for="new-input-password">New Password</label>
                    <div class="input-password">
                        <input type="password" class="styled-input" name="new_password" id="new-input-password" tabindex="1" required />
                        <button type="button" class="btn btn-icon-eye"><?= $this->getIcon('eye-regular', array('class' => 'mr5')) ?></button>
                    </div>
                </div>
            </div>
            <div class="frm-grid">
                <div>
                    <label for="confirmation-new-input-password">Repeat New Password</label>
                    <div class="input-password">
                        <input type="password" class="styled-input" name="confirmation_new_password" id="confirmation-new-input-password" tabindex="2" required />
                        <button type="button" class="btn btn-icon-eye"><?= $this->getIcon('eye-regular', array('class' => 'mr5')) ?></button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>