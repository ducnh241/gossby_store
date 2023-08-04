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
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Catalog_Order_Process extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    protected $_table_name = 'catalog_order_process';
    protected $_pk_field = 'master_record_id';

    /**
     *
     * @var Model_Catalog_Order
     */
    protected $_order = null;

    /**
     *
     * @return $this->_order
     */
    public function getOrder() {
        if ($this->_order === null) {
            $this->_order = $this->getPreLoadedModel('catalog/order', $this->data['order_master_record_id']);
        }

        return $this->_order;
    }

    /**
     *
     * @param Model_Catalog_Order $order
     * @return $this
     */
    public function setOrder($order) {
        $this->_order = ($order instanceof Model_Catalog_Order) ? $order : null;
        return $this;
    }

    /**
     *
     * @param int $order_record_id
     * @return $this
     * @throws Exception
     */
    public function loadByOrderRecordId(int $order_record_id) {
        if ($order_record_id < 1) {
            throw new Exception('Order Record ID is empty');
        }

        return $this->setCondition(['condition' => '`order_record_id` = :order_record_id', 'params' => ['order_record_id' => $order_record_id]])->load();
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['line_items'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['line_items'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

}
