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

<div class="location_preview" style="max-height: 500px; overflow: auto">
    <?php foreach ($params['data_preview'] as $country_code => $data): ?>
        <div class="location_preview__item">
            <span class="location_preview__icon">
                <span class="flag-icon flag-icon-<?= strtolower($country_code) ?>"></span>
            </span>
            <div class="location_preview__data"><b><?= isset($data['country_name']) ? $data['country_name'] : '' ?> </b> <?= !isset($data['province']) ?  '( All Province )' : '' ?>
            <?php if (isset($data['province'])) : ?>
                <?php foreach ($data['province'] as $province): ?>
                    <p> - <?= $province['province_name'] ?> (<?=  $province['province_code'] ?>) </p>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

