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
 * OSC_User
 *
 * @package Model_User_Group_Collection
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Model_User_Group_Collection extends Abstract_Core_Model_Collection {

    /**
     *
     * @var boolean
     */
    protected $_skip_guest = false;

    /**
     *
     * @var boolean 
     */
    protected $_skip_root = false;

    /**
     * 
     * @return Model_User_Group_Collection
     */
    public function setSkipGuest() {
        $this->_skip_guest = true;
        return $this;
    }

    /**
     * 
     * @return Model_User_Group_Collection
     */
    public function setSkipRoot() {
        $this->_skip_root = true;
        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getOptions() {
        $options = array();

        $root_group = OSC::systemRegistry('root_group');

        foreach ($this as $item) {
            if (($this->_skip_guest && $item->getId() == $root_group['guest']) || ($this->_skip_root && in_array($item->getId(), $root_group))) {
                continue;
            }

            $options[] = array(
                'value' => $item->data[$this->_option_conf['value']],
                'label' => $item->data[$this->_option_conf['label']]
            );
        }

        $this->_skip_guest = false;
        $this->_skip_root = false;

        return $options;
    }

}
