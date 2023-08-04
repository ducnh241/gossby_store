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
<?php if (count($params['permission_map']) > 0) : ?>
    <ul>
        <?php foreach ($params['permission_map'] as $item_key => $item_data) : ?>
            <?php
            if (!is_array($item_data)) {
                $item_data = ['label' => $item_data, 'items' => []];
            }
            ?>
            <li>
                <label class="label-wrap label-wrap--checker">
                    <span class="styled-checkbox">
                        <input type="checkbox" name="permission<?php if (count($params['key_prefix']) > 0) : ?>[<?= implode('][', $params['key_prefix']) ?>]<?php endif; ?>[<?= $item_key ?>]" id="permission_<?php if (count($params['key_prefix']) > 0) : ?><?= implode('_', $params['key_prefix']) ?>_<?php endif; ?><?= $item_key ?>" value="1"<?php if (in_array((count($params['key_prefix']) > 0 ? (implode('/', $params['key_prefix']) . '/') : '') . $item_key, $params['permission_data'], true)) : ?> checked="checked"<?php endif; ?> />
                        <ins><?= $this->getIcon('check-solid') ?></ins>
                    </span>
                    <span class="ml5"><?= $item_data['label'] ?></span>
                </label>
                <?php
                $key_prefix = $params['key_prefix'];
                $key_prefix[] = $item_key;
                echo $tpl->build('user/permission_mask/post/perm_tree/item', ['key_prefix' => $key_prefix, 'permission_map' => $item_data['items'], 'permission_data' => $params['permission_data']]);
                ?>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>