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
class Model_D2_Product extends Abstract_Core_Model {

    protected $_table_name = 'catalog_d2_products';
    protected $_pk_field = 'id';

    /**
     * @var Model_Catalog_Product
     */
    protected $_campaign_product = null;

    public function setCampaignProduct(Model_Catalog_Product $product) {
        $this->_campaign_product = $product;
        return $this;
    }

    public function getCampaignProduct() {

        if (is_null($this->_campaign_product)) {
            try {
                $this->_campaign_product = OSC::model('catalog/product')->load($this->data['product_id']);
            } catch (Exception $ex) {}
        }

        return $this->_campaign_product;
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG || $this->getLastActionFlag() == static::UPDATE_FLAG) {

            try {

                try {
                    $duplicate_queue = OSC::model('catalog/product_bulkQueue')->loadByUKey('d2CreateOrUpdateProduct_' . $this->data['product_id']);
                    $duplicate_queue->delete();
                } catch (Exception $ex) {
                    if ($ex->getCode() != 404) {
                        throw new Exception($ex->getMessage());
                    }
                }

                OSC::model('catalog/product_bulkQueue')->setData([
                    'ukey' => 'd2CreateOrUpdateProduct_' . $this->data['product_id'],
                    'member_id' => 1,
                    'action' => 'd2CreateOrUpdateProduct',
                    'queue_data' => [
                        'product_id' => $this->data['product_id']
                    ],
                    'queue_flag' => Model_Catalog_Product_BulkQueue::QUEUE_FLAG['queue']
                ])->save();

                OSC::core('cron')->addQueue('d2/afterProductCreated', null, ['ukey' => 'd2/afterProductD2Create', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60 * 60]);
            } catch (Exception $ex) {
                OSC::logFile('Error: ' . $ex->getMessage() , 'ExportAirtable');
            }
        }

    }
}
