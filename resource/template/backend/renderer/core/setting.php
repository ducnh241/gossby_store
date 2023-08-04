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
<form action="<?php echo $this->getUrl('*/*/*'); ?>" method="post" class="pl25 pr25 pb25 post-frm">
    <input type="hidden" name="save" value="1" />
    <div class="tab-system" data-insert-cb="initTabSystem">
        <ul class="tabs">
            <?php foreach ($params['settings'] as $tab_key => $tab_data) : ?>
                <li tab-key="<?php echo $tab_key; ?>"><?php echo $tab_data['label']; ?></li>
            <?php endforeach; ?>
        </ul>
        <div class="tabs-content">
            <?php foreach ($params['settings'] as $tab_key => $tab_data) : ?>
                <div tab-key="<?php echo $tab_key; ?>">
                    <?php foreach ($tab_data['groups'] as $group_key => $group_data) : ?>
                        <div class="block mb25" style="width: 750px; margin: auto">
                            <div class="header">
                                <div class="header__main-group"><div class="header__heading"><?= $group_data['label'] ?></div></div>
                            </div>
                            <div class="p20">
                                <?php $frm_row = array(); ?>
                                <?php foreach ($group_data['items'] as $item_key => $item_data) : ?>
                                    <?php if ($item_data === 'line' || $item_data === 'separate' || in_array($item_data['input_type'], array('desc', 'heading')) || $item_data['theme_new_row_flag']) : ?>
                                        <?php if (count($frm_row) > 0) : ?><div class="frm-grid"><?= implode('<div class="separate"></div>', $frm_row) ?></div><?php endif; ?>
                                        <?php $frm_row = array(); ?>
                                        <?php if ($item_data === 'separate') : ?>
                                            <div class="frm-separate e20"></div>
                                            <?php continue; ?>
                                        <?php elseif ($item_data === 'line') : ?>
                                            <div class="frm-line e20"></div>
                                            <?php continue; ?>
                                        <?php elseif ($item_data['input_type'] === 'heading') : ?>
                                            <div class="frm-heading">
                                                <div class="frm-heading__main">
                                                    <?php if ($item_data['title']) : ?>
                                                        <div class="frm-heading__title"><?= $item_data['title'] ?></div>
                                                    <?php endif; ?>
                                                    <?php if ($item_data['description']) : ?>
                                                        <div class="frm-heading__desc"><?= $item_data['description'] ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            <?php continue; ?>
                                        <?php elseif ($item_data['input_type'] === 'desc') : ?>
                                            <div class="frm-desc"><?= $item_data['description'] ?></div>
                                            <?php continue; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                    <?php $frm_row[] = $this->build('core/setting/input', $item_data); ?>                                    
                                <?php endforeach; ?>
                                <?php if (count($frm_row) > 0) : ?><div class="frm-grid"><?= implode('<div class="separate"></div>', $frm_row) ?></div><?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="action-bar">
        <button type="submit" class="btn btn-primary"><?= $this->_('core.save') ?></button>
    </div>
</form>