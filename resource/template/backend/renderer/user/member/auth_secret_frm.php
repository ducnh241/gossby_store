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
<div class="auth-secret-info">
    <?php if ($params['secret_key']) : ?>
        <div class="qr-code"><img src="<?php echo $params['qr_code_url']; ?>" /></div>
        <div class="secret-key"><?php echo $params['secret_key']; ?></div>
    <?php else : ?>
        <div class="no-secret-key">Account's secret key has not been created.</div>
    <?php endif; ?>
</div>
<div class="action-bar align-center">        
    <?php if ($params['secret_key']) : ?>
        <div class="btn btn-danger btn-small mr5" data-insert-cb="initUserMemberAuthSecretKeyAction" act-uri="<?php echo $this->getUrl('*/*/removeAuthSecretKey', array('id' => $params['model']->getId())); ?>">Remove this secret key</div>
    <?php endif; ?>
    <div class="btn btn-primary btn-small" data-insert-cb="initUserMemberAuthSecretKeyAction" act-uri="<?php echo $this->getUrl('*/*/generateAuthSecretKey', array('id' => $params['model']->getId())); ?>">Create secret key</div>
</div>