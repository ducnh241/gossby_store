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
class Model_CrossSell_SegmentsConfig extends OSC_Database_Model {

    /**
     *
     * @var string
     */
    protected $_table_name = 'cross_sell_config_segments';

    /**
     *
     * @var string
     */
    protected $_pk_field = 'id';

    const SEGMENTS_TYPE_FRONT = 1;
    const SEGMENTS_TYPE_BACK = 2;
    const SEGMENTS_TYPE_TOW_SIDE = 3;

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['segments'])) {
            $data['segments'] = trim($data['segments']);

            if ($data['segments']  == '') {
                $errors[] = 'Segments is empty';
            }
        }

        if (isset($data['segments_type'])) {
            $data['segments_type'] = intval($data['segments_type']);

            if ($data['segments_type'] < 1) {
                $errors[] = 'segments type is error';
            }
        }

        if (isset($data['product_type_id'])) {
            $data['product_type_id'] = intval($data['product_type_id']);

            if ($data['product_type_id'] < 0) {
                $errors[] = 'product_type_id is error';
            }
        }

        foreach (array('added_timestamp', 'modified_timestamp') as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'product_type_id' => 'product_type_id is empty',
                    'segments' => 'segments is empty',
                    'segments_type' => 'segments_type data is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['segments'] as $key){
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }

    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['segments']  as $key){
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }
}
