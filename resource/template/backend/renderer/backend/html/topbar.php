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
<?php /* @var $member Model_User_Member */ $member = OSC::helper('user/authentication')->getMember(); ?>
<ul class="topbar clearfix">
    <li class="account">            
        <a href="<?php echo $member->getProfileUrl(); ?>" class="username">
            <span><?php echo $member->data['username']; ?></span>
        </a>
        <div class="menu-toggler mrk-toggle-menu" data-tm-menu="#topbar-profile-menu" data-tm-divergent-x="-5" data-tm-divergent-y="7" data-tm-toggle-mode="br" data-tm-auto-toggle="1">
            <?= $this->getIcon('topbar-angle-down-solid') ?>
        </div>
    </li>
    <?php echo $this->build('backend/html/topbar/search'); ?>
    <?php echo $this->build('backend/html/topbar/openStore'); ?>
    <li class="clock">
        <?= $this->getIcon('clock-osc') ?>
        <div class="content">
            <div class="time"></div>
            <div class="date"></div>                
        </div>
    </li>
</ul>
<ul id="topbar-profile-menu" class="popup-menu dis-sel">
    <li><?= $this->getIcon('lock-alt-solid') ?><a href="<?php echo $this->getUrl('user/backend_authentication/changePassword'); ?>">Change Password</a></li>
    <li class="separate"></li>
    <li><?= $this->getIcon('sign-out-solid') ?><a href="<?php echo $this->getUrl('user/backend_authentication/index'); ?>">Sign out</a></li>
</ul>