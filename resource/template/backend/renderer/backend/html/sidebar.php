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
<ul class="sidebar">
    <li class="logo"><a href="<?php echo $this->getUrl('backend/index/dashboard'); ?>"><i></i><span>OSC</span></a></li>
    <?php $has_divide = false; ?>
    <?php foreach ($this->getBackendMenu() as $item) : ?>
    <?php if ($item['divide'] === true && $has_divide === false): $has_divide = true; ?><div class="divide"></div><?php endif; ?>
        <li class="<?php if ($item['activated']) : ?>active<?php endif; ?> <?= count($item['sub_items']) > 0 ? 'has-children' : ''; ?>">
            <a href="<?= $item['url'] ?>" title="<?= $item['title'] ?>">
                <?= $this->getIcon($item['icon']) ?>
                <span><?= $item['title'] ?></span>
                <?php if (count($item['sub_items']) > 0) : ?><span class="toggler"><?= $this->getIcon('angle-down-solid') ?></span><?php endif; ?>
            </a>
            <?php if (count($item['sub_items']) > 0) : ?>                    
                <?php echo $this->build('backend/html/sidebar/menu', array('items' => $item['sub_items'])); ?>
            <?php endif; ?>
        </li>
    <?php endforeach; ?>
</ul>
