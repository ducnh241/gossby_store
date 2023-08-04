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
 * OSC::User
 * 
 * @package Helper_User_Group_Validator
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_User_Group_Validator {

    /**
     * 
     * @param int $id
     * @return int
     * @throws OSC_Exception_Condition
     */
    public function validGroupId($id) {
        $id = intval($id);

        if ($id < 1) {
            throw new OSC_Exception_Condition(OSC::core('language')->get('usr.err_group_not_exist'));
        }

        $model = OSC::model('user/group')->load($id);

        if ($model->getId() < 1) {
            throw new OSC_Exception_Condition(OSC::core('language')->get('usr.err_group_not_exist'));
        }

        return $id;
    }

}
