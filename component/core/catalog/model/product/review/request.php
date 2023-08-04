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
class Model_Catalog_Product_Review_Request extends Abstract_Core_Model {

    protected $_table_name = 'catalog_product_review_request';
    protected $_pk_field = 'request_id';

    /**
     *
     * @var Model_Catalog_Product
     */
    protected $_product_model = null;

    /**
     *
     * @var Model_Catalog_Customer
     */
    protected $_customer_model = null;

    /**
     *
     * @var Model_Catalog_Order
     */
    protected $_order_model = null;

    /**
     *
     * @var Model_Catalog_Product_Review
     */
    protected $_product_review_model = null;

    const SETT_REQUEST_TIMING = 20;
    const SETT_REQUEST_EVENT = 'fulfillment'; //purchase|fulfillment|delivered
    const SETT_DISCOUNT_TYPE = 'percent'; //none|percent|fixed_amount
    const SETT_DISCOUNT_VALUE = 20;

    protected $_current_lang_key = '';

    public function __construct()
    {
        $this->_current_lang_key = $current_lang_key = OSC::core('language')->getCurrentLanguageKey();
    }

    /**
     * 
     * @param mixed $product
     * @return $this
     */
    public function setProduct($product) {
        $this->_product_model = $product;
        return $this;
    }

    /**
     * 
     * @param boolean $reload
     * @return Model_Catalog_Product
     * @throws Exception
     */
    public function getProduct($reload = false) {
        if ($reload || $this->_product_model === null || ($this->_product_model->getId() > 0 && $this->_product_model->getId() != $this->data['product_id'])) {
            $this->_product_model = null;

            try {
                $this->_product_model = static::getPreLoadedModel('catalog/product', $this->data['product_id']);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_product_model;
    }

    public function getCustomer($reload = false) {
        if ($this->data['customer_id'] > 0 && ($reload || $this->_customer_model === null || ($this->_customer_model['customer_id'] > 0 && $this->_customer_model['customer_id'] != $this->data['customer_id']))) {
            $this->_customer_model = null;

            try {
                $this->_customer_model = OSC::helper('account/customer')->get(['customer_id' => $this->data['customer_id']]);
            } catch (Exception $ex) {
                throw new Exception($ex->getMessage());
            }
        }

        return $this->_customer_model;
    }

    /**
     * 
     * @param Model_Catalog_Order $order
     * @return $this
     */
    public function setOrder(Model_Catalog_Order $order) {
        $this->_order_model = $order;
        return $this;
    }

    /**
     * 
     * @param boolean $reload
     * @return Model_Catalog_Order
     * @throws Exception
     */
    public function getOrder($reload = false) {
        if ($this->data['order_id'] > 0 && ($reload || $this->_order_model === null || ($this->_order_model->getId() > 0 && $this->_order_model->getId() != $this->data['order_id']))) {
            $this->_order_model = null;

            try {
                $this->_order_model = static::getPreLoadedModel('catalog/order', $this->data['order_id']);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_order_model;
    }

    /**
     *
     * @param Model_Catalog_Product_Review $review
     * @return $this
     */
    public function setReview(Model_Catalog_Product_Review $review) {
        $this->_product_review_model = $review;
        return $this;
    }

    /**
     *
     * @param boolean $reload
     * @return Model_Catalog_Product_Review
     * @throws Exception
     */
    public function getReview($reload = false) {
        if ($this->data['review_id'] > 0 && ($reload || $this->_product_review_model === null || ($this->_product_review_model->getId() > 0 && $this->_product_review_model->getId() != $this->data['review_id']))) {
            $this->_product_review_model = null;

            try {
                $this->_product_review_model = static::getPreLoadedModel('catalog/product_review', $this->data['review_id']);
            } catch (Exception $ex) {
                if ($ex->getCode() != 404) {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        return $this->_product_review_model;
    }

    public function getUrl()
    {
        return OSC_FRONTEND_BASE_URL . '/' . $this->_current_lang_key . '/review-write/' . $this->data['ukey'];
    }

    public function getReplyUrl() {
        return OSC::getUrl('catalog/frontend/reviewReply', ['code' => $this->data['ukey']]);
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = [];

        if (isset($data['order_id'])) {
            $data['order_id'] = intval($data['order_id']);

            if ($data['order_id'] < 1) {
                $data['order_id'] = null;
            }
        }

        foreach (['added_timestamp'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = intval($data[$key]);

                if ($data[$key] < 0) {
                    $data[$key] = 0;
                }
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = array(
                    'product_id' => 'Product ID is empty'
                );

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }

                $default_fields = array(
                    'review_id' => 0,
                    'order_id' => null,
                    'customer_id' => null,
                    'added_timestamp' => time()
                );

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['ukey'] = OSC::makeUniqid(false, true);
            } else {
                unset($data['product_id']);
                unset($data['ukey']);
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        }
    }
}