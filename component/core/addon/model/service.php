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
 * @copyright    Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */
class Model_Addon_Service extends Abstract_Core_Model
{
    protected $_table_name = 'addon_service';
    protected $_pk_field = 'id';

    protected $_allow_write_log = true;

    const TYPE_VARIANT = 2;
    const TYPE_ADDON = 3;

    const TYPE_NAME_ARR = [
        self::TYPE_VARIANT => 'Variant',
        self::TYPE_ADDON => 'Addon',
    ];
    const DISPLAY_CART_ONLY = 0;
    const DISPLAY_CART_AND_PRODUCT = 1;

    const LIST_COUNTRY_AVAILABLE = [
        'US'
    ];

    const LIST_EXCLUDE_PROVINCE = [
        'AK', //Alaska
        'AE', //Armed Forces Middle East
        'AA', //Armed Forces Americas
        'AP', //Armed Forces Pacific
        'GU', //Guam
        'HI', //Hawaii
        'PR', //Puerto Rico
        'VI' //Virgin Islands
    ];

    public function isDisplayAtProductDetail(): bool
    {
        if ($this->data['type'] == self::TYPE_VARIANT) {
            return true;
        }

        $addon_data = $this->getAddonServiceData();
        $display_area = $addon_data->data['version']['display_area'];

        if ($display_area == self::DISPLAY_CART_ONLY) {
            return false;
        }

        return true;
    }

    public function getTypeName($flag_key = false): string
    {
        $type_name_arr = self::TYPE_NAME_ARR;
        $type_name = $type_name_arr[$this->data['type']] ?? 'Addon';

        return $flag_key ? strtolower($type_name) : $type_name;
    }

    public function getAddonServiceTitle()
    {
        $addon_data = $this->getAddonServiceData();

        return $addon_data->data['version']['data']['service_title'];
    }

    public function getImageUrl($image)
    {
        return OSC::core('aws_s3')->getStorageUrl($image);
    }

    /**
     * Check ab_test_enable = 1 and ( time() > ab_test_start_timestamp && time() < ab_test_end_timestamp )
     * And in current time, only one addon service is AB test ON
     * @return bool
     */
    public function isRunningAbTest(): bool
    {
        return (
            $this->data['status'] == 1 &&
            $this->data['ab_test_enable'] == 1
            && $this->data['ab_test_start_timestamp'] <= time()
            && $this->data['ab_test_end_timestamp'] >= time()
        );
    }

    public function getAddonServiceData() {
        try {
            $version_data = OSC::helper('addon/service')->getVersionDistribution($this->getId());

            $this->data['version'] = $version_data;
            $this->data['data']['options'] = $version_data['data']['options'];

            return $this;
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    protected function _preDataForSave(&$data)
    {
        parent::_preDataForSave($data);

        if (isset($data['auto_apply_for_product_type_variants'])) {
            $data['auto_apply_for_product_type_variants'] = OSC::encode($data['auto_apply_for_product_type_variants']);
        }

        if (!empty($data['formatted_active_time'])) {
            unset($data['formatted_active_time']);
        }

        if (!empty($data['formatted_ab_test_time'])) {
            unset($data['formatted_ab_test_time']);
        }
    }

    protected function _beforeSave()
    {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        if ($this->getActionFlag() == static::INSERT_FLAG) {
            $data['added_timestamp'] = time();
        }
        $data['modified_timestamp'] = time();

        $this->resetDataModifiedMap()->setData($data);
    }


    protected function _preDataForUsing(&$data)
    {
        parent::_preDataForUsing($data);

        if (isset($data['auto_apply_for_product_type_variants'])) {
            $data['auto_apply_for_product_type_variants'] = OSC::decode($data['auto_apply_for_product_type_variants'], true);
        }
    }

    protected function _afterDelete()
    {
        parent::_afterDelete();

        try {
            $addon_id = $this->getId();
            OSC::model('addon/version')->getCollection()->addCondition('addon_id', $addon_id, OSC_Database::OPERATOR_EQUAL)->delete();
        } catch (Exception $ex) {
        }
    }
}
