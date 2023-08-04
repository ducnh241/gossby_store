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
class Model_D2_Resource extends Abstract_Core_Model {

    protected $_table_name = 'd2_resource';
    protected $_pk_field = 'id';

    /**
     * @var Model_D2_Condition
     */
    protected $_d2_conditions = null;

    public function setConditions($conditions) {
        $this->_d2_conditions = $conditions;
        return $this;
    }

    public function getConditions() {

        if (is_null($this->_d2_conditions)) {
            try {
                $this->_d2_conditions = OSC::model('d2/condition')->getCollection()->addCondition('resource_id', $this->getId())->load();
            } catch (Exception $ex) {}
        }

        return $this->_d2_conditions;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if ($this->getActionFlag() == static::INSERT_FLAG) {

            $default_fields = [
                'added_timestamp' => time(),
                'modified_timestamp' => time()
            ];

            foreach ($default_fields as $field_name => $default_value) {
                if (!isset($data[$field_name])) {
                    $data[$field_name] = $default_value;
                }
            }
        } else {
            if (!isset($data['modified_timestamp'])) {
                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

    }

    protected function _afterDelete()
    {
        parent::_afterDelete();
        try {
            /* @var $DB OSC_Database_Adapter */
            $DB = OSC::core('database')->getWriteAdapter();
            $DB->delete('d2_condition', "resource_id={$this->getId()}", null, 'delete_condition');
            $DB->free('delete_condition');
        } catch (Exception $ex) {

        }
    }
}
