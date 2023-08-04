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
<?php $this->addComponent('dragger'); ?>
<?php if($params['using_page_header']) : ?><div class="p25"><?php endif; ?>
<div class="block backend-ui-grid">
    <?php if(isset($params['title']) || isset($params['buttons'])) : ?>
        <div class="tile">
            <?php if(isset($params['title'])) { echo $params['title']; } ?>
            <?php if(isset($params['buttons'])) { echo '<div class="buttons">' . implode('&nbsp;', $params['buttons']) . '</div>'; } ?>
        </div>
    <?php endif; ?>
    <?php echo $params['top_bar']; ?>
    <table class="grid-body" id="<?php echo $params['index']; ?>.grid">
        <?php echo $params['headers']; ?>
        <?php echo implode('', $params['rows']); ?>
    </table>
    <?php echo $params['bottom_bar']; ?>
    <?php echo $params['filter_form']; ?>
</div>
<?php if($params['using_page_header']) : ?></div><?php endif; ?>
