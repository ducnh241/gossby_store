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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package Helper_Core_Session
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Core_String extends OSC_String {

    public function cleanVNMask($txt) {
        if (!$txt) {
            return '';
        }

        $search = array(array('á', 'à', 'ả', 'ạ', 'ã', 'â', 'ấ', 'ầ', 'ẩ', 'ậ', 'ẫ', 'ă', 'ắ', 'ằ', 'ẳ', 'ặ', 'ẵ'),
            array('á', 'À', 'Ả', 'Ạ', 'Ã', 'Â', 'Ấ', 'Ầ', 'Ẩ', 'Ậ', 'Ẫ', 'Ă', 'Ắ', 'Ằ', 'Ẳ', 'Ặ', 'Ẵ'),
            array('ó', 'ò', 'ỏ', 'ọ', 'õ', 'ô', 'ố', 'ồ', 'ổ', 'ộ', 'ỗ', 'ơ', 'ớ', 'ờ', 'ở', 'ợ', 'ỡ'),
            array('Ó', 'Ò', 'Ỏ', 'Ọ', 'Õ', 'Ô', 'Ố', 'Ồ', 'Ổ', 'Ộ', 'Ỗ', 'Ơ', 'Ớ', 'Ờ', 'Ở', 'Ợ', 'Ỡ'),
            array('é', 'è', 'ẻ', 'ẹ', 'ẽ', 'ê', 'ế', 'ề', 'ể', 'ệ', 'ễ'),
            array('É', 'È', 'Ẻ', 'Ẹ', 'Ẽ', 'Ê', 'Ế', 'Ề', 'Ể', 'Ệ', 'Ễ'),
            array('ú', 'ù', 'ủ', 'ụ', 'ũ', 'ư', 'ứ', 'ừ', 'ử', 'ự', 'ữ'),
            array('Ú', 'Ù', 'Ủ', 'Ụ', 'Ũ', 'Ư', 'Ứ', 'Ừ', 'Ử', 'Ự', 'Ữ'),
            array('í', 'ì', 'ỉ', 'ị', 'ĩ'),
            array('í', 'Ì', 'Ỉ', 'Ị', 'Ĩ'),
            array('ý', 'ỳ', 'ỷ', 'ỵ', 'ỹ'),
            array('Ý', 'Ỳ', 'Ỷ', 'Ỵ', 'Ỹ'),
            array('đ'),
            array('Đ'),
            array('ä', 'Ä'),
            array('ö', 'Ö'),
            array('ü', 'Ü'),
            array('ß'),
        );

        $replace = array('a', 'A', 'o', 'O', 'e', 'E', 'u', 'U', 'i', 'I', 'y', 'Y', 'd', 'D', 'ae', 'oe', 'ue', 'ss');

        foreach ($search as $k => $s) {
            $txt = str_replace($s, $replace[$k], $txt);
        }

        return $txt;
    }

    public function cleanAliasKey($alias_key, $separation = '-') {
        $alias_key = strtolower($this->cleanVNMask($alias_key));

        $alias_key = preg_replace('/[^a-zA-Z0-9\-]/', $separation, $alias_key);
        $alias_key = preg_replace('/\-{2,}/', $separation, $alias_key);
        $alias_key = preg_replace('/\_{2,}/', $separation, $alias_key);
        $alias_key = preg_replace('/\.{2,}/', $separation, $alias_key);
        $alias_key = preg_replace("/^[\.\-\_]+|[\.\-\_]+$/", '', $alias_key);

        return $alias_key;
    }

}
