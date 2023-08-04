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

/**
 * @see Helper_Backend_Template
 */

$lang = OSC::core('language')->get();

if ($params['default']['order'] == Helper_Backend_Template_Grid::ORDER_ASC) {
    $order = Helper_Backend_Template_Grid::ORDER_DESC;
} else {
    $order = Helper_Backend_Template_Grid::ORDER_ASC;
}

$params['default']['order'] = strtolower($params['default']['order']);
?>
<div class="clearfix grid-opt-sort">
    <a href="<?php echo $this->rebuildUrl(array($params['request_key']['order'] => $order)); ?>" class="btn blue fright">
        <i class="fa fa-sort-alpha-<?php echo $params['default']['order']; ?>"></i>
    </a>
    <div class="fright mrk-toggle-menu grid-opt-sort-dropdown-toggler" data-tm-menu="#<?php echo $params['index']; ?>-sort-opts" data-tm-divergent-x="-5" data-tm-divergent-y="1" data-tm-toggle-mode="br" data-tm-auto-toggle="1"><div><?php echo $params['sort_options'][$params['default']['sort']]; ?></div></div>
    <span class="btn green fright"><i class="fa fa-sort ml-n5 mr-p5"></i><?php echo $lang['core.sort_by']; ?></span>&nbsp;
</div>
<?php if (!$this->registry($params['index'] . '-sort-opts')) : ?>
    <ul id="<?php echo $params['index']; ?>-sort-opts" class="popup-menu no-icon">
        <?php foreach ($params['sort_options'] as $key => $title) : ?>
            <li<?php if ($key == $params['default']['sort']) : ?> class="selected"<?php endif; ?>>
                <a href="<?php echo $this->rebuildUrl(array($params['request_key']['sort'] => $key)); ?>"<?php if ($key == $params['default']['sort']) : ?> onclick="return false"<?php endif; ?>><?php echo $title; ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php $this->register($params['index'] . '-sort-opts', 1); ?>
<?php endif; ?>

