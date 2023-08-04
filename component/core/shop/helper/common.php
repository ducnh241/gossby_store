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
class Helper_Shop_Common extends OSC_Object {

    protected $_current_store_model = null;

    public function getShop($reload = false) {
        if ($reload || $this->_current_store_model === null) {
            $shop = OSC::model('shop/shop');
            try {
                $store_info = OSC::getStoreInfo();

                $shop->loadByUKey($store_info['id']);

                if ($shop->data['secret_key'] !== $store_info['secret_key']) {
                    throw new Exception('Secret key not found');
                }
                $this->_current_store_model = $shop;
            } catch (Exception $ex) {
                $this->_current_store_model = OSC::model('shop/shop');
            }
        }

        return $this->_current_store_model;
    }

    /**
     * @param $amount : Integer
     * @param $type ['request_payout', 'cancel_payout']
     * @return bool
     * @throws Exception
     */
    public function updateProfit($amount, $type) {
        if (!in_array($type, ['request_payout', 'cancel_payout'])) {
            return false;
        }

        $DB = OSC::core('database')->getWriteAdapter();
        $DB->begin();
        $locked_key = OSC::makeUniqid();
        OSC_Database_Model::lockPreLoadedModel($locked_key);
        try {
            $shop_id = OSC::getShop()->getId();
            $shop = OSC::model('shop/shop')->load($shop_id);
            $current_available_withdraw = $shop->data['available_withdraw'];
            $current_processing = $shop->data['processing'];
            switch ($type) {
                case 'request_payout':
                    $shop->setData([
                        'processing' => $current_processing + $amount,
                        'available_withdraw' => $current_available_withdraw - $amount
                    ])->save();
                    break;
                case 'cancel_payout':
                    $shop->setData([
                        'processing' => $current_processing - $amount,
                        'available_withdraw' => $current_available_withdraw + $amount
                    ])->save();
                    break;
            }
            $DB->commit();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);
        } catch (Exception $ex) {
            $DB->rollback();
            OSC_Database_Model::unlockPreLoadedModel($locked_key);

            throw new Exception($ex->getMessage());
        }

    }
}
