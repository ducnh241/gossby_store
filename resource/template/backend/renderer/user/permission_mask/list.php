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
?>
<div class="block m25">
    <div class="header">
        <div class="header__main-group"><div class="header__heading">&nbsp;</div></div>
        <div class="header__action-group">
            <a href="<?php echo $this->getUrl('*/*/post'); ?>" class="btn btn-primary btn-small"><?= $this->getIcon('plus', array('class' => 'mr5')) ?>Create Permission Mask</a>
        </div>
    </div>   
    <?php if ($params['collection']->length() > 0) : ?>        
        <table class="grid grid-borderless">
            <tr>
                <th style="width: 10px; text-align: center">ID</th>
                <th style="text-align: left">Name</th>
                <th style="width: 100px; text-align: right">Members</th>
                <th style="width: 100px; text-align: right">Groups</th>
                <th style="width: 150px; text-align: right"></th>
            </tr>
            <?php /* @var $permmask Model_User_PermissionMask */ ?>
            <?php foreach ($params['collection'] as $permmask) : ?>        
                <tr>
                    <td style="text-align: center"><?php echo $permmask->getId(); ?></td>
                    <td style="text-align: left"><?php echo $permmask->data['title']; ?></td>
                    <td style="text-align: right"><?php echo $permmask->countMembers(); ?></td>
                    <td style="text-align: right"><?php echo $permmask->countGroups(); ?></td>
                    <td style="text-align: right">
                        <a class="btn btn-small btn-icon" href="<?php echo $this->getUrl('*/*/post', array('id' => $permmask->getId())); ?>"><?= $this->getIcon('pencil') ?></a>
                        <a class="btn btn-small btn-icon" href="javascript:$.confirmAction('<?php echo $this->safeString("Do you want to delete the permission mask \"{$permmask->data['title']}\"?"); ?>', '<?php echo $this->getUrl('*/*/delete', array('id' => $permmask->getId())); ?>')"><?= $this->getIcon('trash-alt-regular') ?></a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else : ?>
        <div class="no-result">No Permission Masks created yet.</div>
    <?php endif; ?>
</div>