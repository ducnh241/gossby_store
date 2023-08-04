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
class Model_CrossSell_PrintTemplateMaps extends OSC_Database_Model {

    /**
     *
     * @var string
     */
    protected $_table_name = 'cross_sell_print_template_maps';

    /**
     *
     * @var string
     */
    protected $_pk_field = 'id';

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['product_type_variant_id'])) {
            $data['product_type_variant_id'] = intval($data['product_type_variant_id']);

            if ($data['product_type_variant_id'] < 0) {
                $errors[] = 'product_type_variant_id is error';
            }
        }

        if (isset($data['print_template_id'])) {
            $data['print_template_id'] = intval($data['print_template_id']);

            if ($data['print_template_id'] < 0) {
                $errors[] = 'print_template_id is error';
            }
        }

        if (isset($data['group_product_variant'])) {
            $data['group_product_variant'] = trim($data['group_product_variant']);

            if ($data['group_product_variant'] == '') {
                $errors[] = 'group_product_variant is error';
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
                    'product_type_variant_id' => 'product_type_variant_id is empty',
                    'print_template_id' => 'print_template_id is empty',
                    'group_product_variant' => 'group_product_variant data is empty'
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
}
