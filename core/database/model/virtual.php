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
 * OSECORE
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Database_Model_Virtual extends OSC_Database_Model {

    /**
     *
     * @var array
     */
    protected $_DB = array();

    /**
     *
     * @param integer $id
     * @return OSC_Database_Model_Virtual
     */
    public function load($id) {
        foreach ($this->_DB as $index => $data) {
            if ($index == $id) {
                $this->bind($data);
                break;
            }
        }

        return $this;
    }

    /**
     *
     * @return OSC_Database_Model_Virtual
     */
    public function save($flag = null) {
        return $this;
    }

    /**
     *
     * @return OSC_Database_Model_Virtual
     */
    public function delete() {
        return $this;
    }

    /**
     * 
     * @param array &$params
     */
    protected function _beforeGetCollection(&$params) {
        parent::_beforeGetCollection($params);
        $params['DB'] = $this->_DB;
    }

    /**
     *
     * @param string $field
     * @return OSC_Database_Model_Virtual
     */
    public function setPkField($field) {
        $this->_pk_field = $field;
        return $this;
    }

    /**
     *
     * @param array $DB
     * @return OSC_Database_Model_Virtual
     */
    public function setDb($DB) {
        $this->_DB = $DB;
        return $this;
    }

}
