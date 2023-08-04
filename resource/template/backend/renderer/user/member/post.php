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
$members_mkt = OSC::helper('report/common')->getListMemberMkt();

?>

<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25" style="width: 550px">
    <div class="block">
        <div class="header">
            <div class="header__main-group"><div class="header__heading"><?= $params['form_title'] ?></div></div>
        </div>
        <div class="p20 form_user_group_select">
            <div class="frm-grid">
                <?php if (OSC::isPrimaryStore()) : ?>
                    <div>
                        <label for="input-username">Username</label>
                        <div><input type="text" class="styled-input" name="username" id="input-username" value="<?= $this->safeString($params['model']->data['username']); ?>" <?php if ($params['model']->getId() > 0) : ?>readonly="readonly" style="cursor: not-allowed; background-color: #ebebeb; color: #a1a1a1;" <?php endif;?> /></div>
                    </div>
                <?php else: ?>
                    <div>
                        <label for="input-email">Email address</label>
                        <div><input type="text" name="email" class="styled-input" id="input-email" value="<?= $this->safeString($params['model']->data['email']); ?>" /></div>
                    </div>
                <?php endif; ?>
                <div class="separate"></div>
                <div>
                    <label for="input-password">Password</label>
                    <div class="input-password">
                        <input type="password" class="styled-input" name="password" id="input-password" />
                        <?php if (!OSC::isPrimaryStore()) : ?>
                            <button type="button" class="btn btn-icon-eye"><?= $this->getIcon('eye-regular', array('class' => 'mr5')) ?></button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="frm-grid">
                <?php if (OSC::isPrimaryStore()) : ?>
                    <div>
                        <label for="input-email">Email address</label>
                        <div><input type="text" name="email" class="styled-input" id="input-email" value="<?= $this->safeString($params['model']->data['email']); ?>"  <?php if ($params['model']->getId() > 0) : ?>readonly="readonly" style="cursor: not-allowed; background-color: #ebebeb; color: #a1a1a1;"<?php endif;?>/></div>
                    </div>
                    <div class="separate"></div>
                <?php endif; ?>
                <?php if ($params['group_collection'] !== null) : ?>
                    <div>
                        <label for="input-group">Group</label>
                        <div>
                            <select class="styled-select" name="group_id" id="input-group">
                                <?php foreach ($params['group_collection'] as $group) : ?>
                                    <option value="<?php echo $group->getId(); ?>"<?php if ($group->getId() == $params['model']->data['group_id']) : ?> selected="selected"<?php endif; ?>><?php echo $group->data['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if (!OSC::isPrimaryStore()) : ?>
                    <div class="separate"></div>
                    <div></div>
                <?php endif; ?>
            </div>
            <?php if (OSC::isPrimaryStore() && OSC::helper('user/authentication')->getMember()->isAdmin()) : ?>
                <div class="frm-grid">
                    <div>
                        <label for="input-permmask">Permission mask</label>
                        <div>
                            <select class="styled-select select_user_group" style="height: 100%" name="perm_mask[]" id="input-permmask" multiple="multiple" size="5">
                                <?php foreach ($params['perm_mask_collection'] as $perm_mask) : ?>
                                    <option value="<?php echo $perm_mask->getId(); ?>"<?php if (in_array($perm_mask->getId(), $params['model']->data['perm_mask_ids'])) : ?> selected="selected"<?php endif; ?>><?php echo $perm_mask->data['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="separate"></div>
                    <div>
                        <label for="input-sref_type">Sref Type</label>
                        <div>
                            <select class="styled-select" name="sref_type" id="input-sref_type">
                                <option value="">Default (<?= $params['model']->getSrefTypeDefault()['title'] ?>)</option>
                                <?php foreach ($params['model']->getSrefTypes() as $key => $title) : ?>
                                    <option value="<?= $key ?>"<?php if ($key == $params['model']->data['sref_type']) : ?> selected="selected"<?php endif; ?>><?= $title ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>