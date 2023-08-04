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
 * @package OSECORE_Component_Core_Abstract_Model_Virtual_Collection
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Database_Model_Virtual_Collection extends OSC_Database_Model_Collection {

    public function __construct($params) {
        parent::__construct($params);

        $this->_total_item = 0;
        $this->_page_total_item = 0;

        foreach ($params['DB'] as $key => $item) {
            $this->addItem($key, $item);
        }

        $this->_loaded = true;
    }

    /**
     *
     * @param string $key
     * @param array $item
     * @return OSC_Database_Model_Virtual_Collection 
     */
    public function addItem($key, $item) {
        $this->_total_item++;
        $this->_page_total_item++;

        $model = $this->getNullModel()->setPkField($this->_pk_field);

        $this->_items[] = $model->bind($item);
        $this->_item_index_map[$key] = $this->_total_item - 1;

        $this->_preModel($model);

        //$items[$key] = $this->_items[$this->_totalItem - 1]->getData();

        return $this;
    }

    /**
     *
     * @param string $key
     * @return OSC_Database_Model_Virtual_Collection 
     */
    public function removeItem($key) {
        unset($this->_items[$this->_item_index_map[$key]]);
        return $this;
    }

    /**
     *
     * @return OSC_Database_Model_Virtual_Collection
     */
    public function load() {
        return $this;
    }

    /**
     *
     * @return OSC_Database_Model_Virtual_Collection
     */
    public function delete() {
        return $this;
    }

    public function count() {
        return $this->_total_item;
    }

}
