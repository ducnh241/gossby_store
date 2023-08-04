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
$this->addComponent('select2');
?>
<form action="<?php echo $this->getUrl('*/*/*', array('id' => $params['model']->getId())); ?>" method="post" class="post-frm p25" style="width: 550px">        
    <div class="block">
        <div class="header">
            <div class="header__main-group"><div class="header__heading"><?= $params['form_title'] ?></div></div>
        </div>
        <div class="p20">
            <div class="frm-grid form_user_group_select">
                <?php if ($params['member_collection'] !== null) : ?>
                    <div>
                        <label for="input-group">Member</label>
                        <div>
                            <select class="styled-select select_user_member" name="member_id" id="input-password">
                                <?php foreach ($params['member_collection'] as $member) : ?>
                                    <option value="<?php echo $member->getId(); ?>" <?php if ($member->getId() == $params['model']->data['member_id']) : ?> selected="selected"<?php endif; ?>><?php echo $member->data['username']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="frm-grid form_user_group_select">
                <?php if ($params['group_collection'] !== null) : ?>
                    <div>
                        <label for="input-group">Groups</label>
                        <div>
                            <select class="styled-select select_user_group" style="height: 100%" name="group_ids[]" id="input-group-admin" multiple="multiple" size="5">
                                <?php foreach ($params['group_collection'] as $group) : ?>
                                    <option value="<?php echo $group->getId(); ?>"<?php if (in_array($group->getId(), $params['model']->data['group_ids'])) : ?> selected="selected"<?php endif; ?>><?php echo $group->data['title']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

        </div>

    </div>
    <div class="action-bar">
        <a href="<?= $this->getUrl('*/*/list') ?>" class="btn btn-outline mr5"><?= $this->_('core.cancel') ?></a>
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>

<script>
    $(document).ready(function () {

        $('.select_user_group').select2({
            placeholder: "Please select groups to assign Admin privileges to"
        });

        $('.select_user_member').select2({
            placeholder: "Please select member"
        });
    })
</script>