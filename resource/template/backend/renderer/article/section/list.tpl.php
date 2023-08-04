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
<div class="p25">
    <div class="block">
        <h3><i class="fa fa-folder-o"></i> Danh sách section</h3>        
        <?php if (count($params['collection']) > 0) : ?>
            <ul class="cat-list">
                <?php foreach ($params['collection'] as $section) : ?>
                    <li system-flag="1"<?php if(! $this->checkPermission('article/global|article/section/' . $section->getId())) : ?> disabled="disabled"<?php endif; ?>>
                        <a href="<?php if(! $this->checkPermission('article/global|article/section/' . $section->getId())) : ?>javascript: void(0)<?php else : ?><?php echo $this->getUrl('*/*/list', array('section_id' => $section->getId())); ?><?php endif; ?>"><i class="fa fa-folder"></i></a>
                        <div class="title"><?php echo $section->data['name']; ?></div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else : ?>
            <div class="no-item">Không có section nào được tìm thấy</div>
        <?php endif; ?>
    </div>
</div>