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

if (isset($params['class']) && $params['class']) {
    $row_class = $params['class'];
} else if (isset($params['has_child']) && $params['has_child']) {
    $row_class = 'head';
} else if (isset($params['group_head']) && $params['group_head']) {
    $row_class = 'group-head';
} else {
    $row_class = $params['row_counter'] % 2 ? 'even' : 'odd';
}

if(isset($params['additional_class']) && $params['additional_class']) {
    $row_class .= ' ' . $params['additional_class'];
}

$HTML = "<tr class=\"{$row_class}\">" . implode('', $params['cells']) . "</tr>";

echo $HTML;
?>
