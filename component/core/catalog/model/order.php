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
class Model_Catalog_Order extends Abstract_Core_Model {
    const _DB_BIN_READ = 'db_master';
    const _DB_BIN_WRITE = 'db_master';
    const ORDER_STATUS = [
        'open' => 'Open',
        'archived' => 'Archived',
        'cancelled' => 'Cancelled'
    ];
    const PAYMENT_STATUS = [
        'pending' => 'Pending',
        'void' => 'Void',
        'authorized' => 'Authorized',
        'paid' => 'Paid',
        'partially_paid' => 'Partially paid',
        'partially_refunded' => 'Partially Refunded',
        'refunded' => 'Refunded'
    ];
    const FULFILLMENT_STATUS = [
        'fulfilled' => 'Fulfilled',
        'unfulfilled' => 'Unfulfilled',
        'partially_fulfilled' => 'Partially Fulfilled'
    ];
    const PROCESS_STATUS = [
        'unprocess' => 'Unprocessed',
        'process' => 'Ready to Process',
        'partially_process' => 'Partially Processed',
        'processed' => 'Processed'
    ];
    const FRAUD_RISK_LEVEL = [
        'unknown' => ['score' => -1, 'title' => 'Unknown'],
        'normal' => ['score' => 0, 'title' => 'Normal risk'],
        'elevated' => ['score' => 61, 'title' => 'Elevated risk'],
        'highest' => ['score' => 75, 'title' => 'Highest risk']
    ];

    protected $_table_name = 'catalog_order';
    protected $_pk_field = 'master_record_id';
    const HOLD_BY_CUSTOMER = -1;

    const TIP_ERROR = 5001;

    const ADDON_SERVICE_TYPE_SINGLE_CHOICE = 0;
    const ADDON_SERVICE_TYPE_GROUP = 1;
    const ADDON_SERVICE_TYPE_VARIANT = 2;

    const ADDON_SERVICE_ACTION_TYPE = [
        'replace' => [self::ADDON_SERVICE_TYPE_GROUP, self::ADDON_SERVICE_TYPE_VARIANT],
        'extend' => [self::ADDON_SERVICE_TYPE_SINGLE_CHOICE]
    ];

    /**
     *
     * @var Model_Catalog_Order_Item_Collection
     */
    protected $_line_item_collection = null;

    /**
     *
     * @var Model_Catalog_Customer
     */
    protected $_customer = null;
    protected $_payment = null;

    /**
     *
     * @var Helper_Catalog_Shipping_Carrier
     */
    protected $_carrier = null;

    /**
     *
     * @var Model_Catalog_Order_Transaction_Collection
     */
    protected $_transaction_collection = null;

    /**
     *
     * @var Model_Catalog_Order_Fulfillment_Collection
     */
    protected $_fulfillment_collection = null;

    /**
     *
     * @var Model_Catalog_Order_Fulfillment
     */
    protected $_fulfillment_by_process_ukey = null;

    /**
     *
     * @var Model_Catalog_Order_Process_Collection_
     */
    protected $_process_collection_v2 = null;

    /**
     *
     * @var Model_Catalog_Order_Process_Collection
     */
    protected $_process_collection = null;

    /**
     *
     * @var Model_Catalog_Order_Process
     */
    protected $_process_recently = null;

    /**
     *
     * @var Model_Catalog_Order_Log_Collection
     */
    protected $_log_collection = null;

    /**
     *
     * @return Abstract_Catalog_Payment
     * @throws Exception
     */
    public function getPayment() {
        if ($this->_payment === null) {
            switch ($this->data['payment_method']['object']['type']) {
                case 'helper':
                    $this->_payment = OSC::helper($this->data['payment_method']['object']['name'], OSC::makeUniqid());
                    break;
                case 'core':
                    $this->_payment = OSC::core($this->data['payment_method']['object']['name'], OSC::makeUniqid());
                    break;
                case 'model':
                    $this->_payment = OSC::model($this->data['payment_method']['object']['name'], OSC::makeUniqid());
                    break;
                case 'controller':
                    $this->_payment = OSC::controller($this->data['payment_method']['object']['name'], OSC::makeUniqid());
                    break;
                case 'cron':
                    $this->_payment = OSC::cron($this->data['payment_method']['object']['name'], OSC::makeUniqid());
                    break;
                case 'class':
                    $this->_payment = new $this->data['payment_method']['object']['name']();
                    break;
                default:
                    throw new Exception('');
            }

            $this->_payment->setAccount($this->data['payment_method']['account']);
        }

        return $this->_payment;
    }

    /**
     *
     * @return bool
     */
    public function isUpsaleAvailable(): bool {
        return OSC::helper('core/setting')->get('payment/reference_transaction/enable') == 1
            && $this->data['added_timestamp'] > time() - intval(Model_Catalog_Upsale::UPSALE_TIME) &&
            $this->data['is_upsale'] === Model_Catalog_Upsale::STATUS_UPSALE_AVAILABLE &&
            !empty($this->data['payment_data']['ba_token']) &&
            $this->data['order_status'] == 'open' &&
            $this->data['member_hold'] === 0 &&
            in_array($this->data['payment_status'], ['authorized', 'paid']);
    }

    public function setCarrier($carrier) {
        $this->_carrier = ($carrier instanceof Helper_Catalog_Shipping_Carrier) ? $carrier : null;

        return $this;
    }

    /**
     *
     * @return $this->_carrier
     */
    public function getCarrier() {
        if ($this->_carrier === null) {
            if (!isset($this->data['shipping_line']) || !is_array($this->data['shipping_line']) || !isset($this->data['shipping_line']['carrier'])) {
                $this->_carrier = false;
            } else {
                $this->_carrier = new Helper_Catalog_Shipping_Carrier($this->data['shipping_line']['carrier']['key'], $this->data['shipping_line']['carrier']['title'], $this->data['shipping_line']['carrier']['ship_from'], $this->data['shipping_line']['carrier']['rates']);
            }
        }

        return $this->_carrier;
    }

    /**
     *
     * @param Model_Catalog_Order_Transaction_Collection $transaction_collection
     * @return $this
     */
    public function setTransactionCollection(Model_Catalog_Order_Transaction_Collection $transaction_collection) {
        $this->_transaction_collection = $transaction_collection;
        return $this;
    }
    /**
     *
     * @return \Model_Catalog_Order_Transaction_Collection
     */
    public function getTransactionCollection(bool $reload = false): Model_Catalog_Order_Transaction_Collection {
        if ($this->_transaction_collection === null || $reload) {
            $this->_transaction_collection = OSC::model('catalog/order_transaction')->getCollection()->loadByOrderMasterRecordId($this->getId());

            foreach ($this->_transaction_collection as $transaction) {
                $transaction->setOrder($this);
            }
        }

        return $this->_transaction_collection;
    }

    /**
     *
     * @return \Model_Catalog_Order_Fulfillment
     */
    public function getFulfillmentByProcessUkey($process_ukey): Model_Catalog_Order_Fulfillment {
        if ($this->_fulfillment_by_process_ukey === null) {
            $this->_fulfillment_by_process_ukey = OSC::model('catalog/order_fulfillment')
                ->setCondition('order_master_record_id = "' . $this->getId() .'"' .' AND process_ukey="' . $process_ukey . '"')->load();
        }

        return $this->_fulfillment_by_process_ukey;
    }

    /**
     *
     * @return \Model_Catalog_Order_Fulfillment_Collection
     */
    public function getFulfillmentCollection(bool $reload = false): Model_Catalog_Order_Fulfillment_Collection {
        if ($this->_fulfillment_collection === null || $reload) {
            $this->_fulfillment_collection = OSC::model('catalog/order_fulfillment')->getCollection()->loadByOrderMasterRecordId($this->getId());

            foreach ($this->_fulfillment_collection as $fulfillment) {
                $fulfillment->setOrder($this);
            }
        }

        return $this->_fulfillment_collection;
    }

    /**
     *
     * @param Model_Catalog_Order_Fulfillment_Collection $fulfillment_collection
     * @return $this
     */
    public function setFulfillmentCollection(Model_Catalog_Order_Fulfillment_Collection $fulfillment_collection) {
        $this->_fulfillment_collection = $fulfillment_collection;
        return $this;
    }

    /**
     *
     * @return \Model_Catalog_Order_Process_Collection
     */
    public function getProcessCollection(bool $reload = false): Model_Catalog_Order_Process_Collection {
        if ($this->_process_collection === null || $reload) {
            $this->_process_collection = OSC::model('catalog/order_process')->getCollection()->loadByOrderMasterRecordId($this->getId());

            foreach ($this->_process_collection as $process) {
                $process->setOrder($this);
            }
        }

        return $this->_process_collection;
    }


    /**
     *
     * @return \Model_Catalog_Order_Process_Collection
     */
    public function getProcessCollectionV2(bool $reload = false): Model_Catalog_Order_ProcessV2_Collection {
        if ($this->_process_collection_v2 === null || $reload) {
            $this->_process_collection_v2 = OSC::model('catalog/order_processV2')->getCollection()->loadByOrderMasterRecordId($this->getId());

            foreach ($this->_process_collection_v2 as $process) {
                $process->setOrder($this);
            }
        }

        return $this->_process_collection_v2;
    }


    /**
     *
     * @return \Model_Catalog_Order_Process
     */
    public function getProcessRecently(bool $reload = false): Model_Catalog_Order_Process {
        $collection = $this->getProcessCollection();

        return $collection->getItem($collection->length() - 1);
    }


    /**
     *
     * @return \Model_Catalog_Order_Log_Collection
     */
    public function getLogCollection(bool $reload = false): Model_Catalog_Order_Log_Collection {
        if ($this->_log_collection === null || $reload) {
            $this->_log_collection = OSC::model('catalog/order_log')->getCollection()->loadByOrderMasterRecordId($this->getId());

            foreach ($this->_log_collection as $log) {
                $log->setOrder($this);
            }
        }

        return $this->_log_collection;
    }

    public function getRefundableAmount(): int {
        return intval($this->data['paid']) - intval($this->data['refunded']);
    }

    public function getRefundableShippingPrice() {
        return intval($this->data['shipping_price']) - intval($this->data['shipping_price_refunded']);
    }

    public function getDetailUrl($success_flag = false) {
        return OSC_FRONTEND_BASE_URL . '/catalog/order/' . $this->getOrderUkey() . ($success_flag ? '?success=1' : '');
    }

    public function getCustomer(bool $reload = false) {
        if ($this->_customer === null || $reload) {
            try {
                $this->_customer = OSC::helper('account/customer')->get(['customer_id' => $this->data['crm_customer_id']]);
            } catch (Exception $ex) {

            }
        }

        return $this->_customer;
    }

    public function addLog(string $action_key, string $action_title, $content = '') {
        return OSC::model('catalog/order_log')->setData([
            'shop_id' => $this->data['shop_id'],
            'order_master_record_id' => $this->getId(),
            'order_id' => $this->data['order_id'],
            'member_id' => OSC::helper('user/authentication')->getMember()->getId(),
            'action_key' => $action_key,
            'action_title' => $action_title,
            'content' => $content
        ])->save();
    }

    public function getFloatTotalDiscountPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getTotalDiscountPrice());
    }

    public function getTotalDiscountPrice() {
        $price = 0;

        foreach ($this->data['discount_codes'] as $discount_code) {
            $price += ($discount_code['discount_price'] + $discount_code['discount_shipping_price']);
        }

        return -$price;
    }

    public function getFloatSubtotalPrice() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['subtotal_price']));
    }

    public function getFloatTotalPrice() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['total_price']));
    }

    public function getFloatMaxRefundAmount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getMaxRefundAmount());
    }

    public function getMaxRefundAmount() {
        return intval($this->data['paid']) - intval($this->data['refunded']);
    }

    public function ableToRefund(): bool {
        return in_array($this->data['payment_status'], ['paid', 'partially_paid', 'partially_refunded'], true);
    }

    public function getFloatPaid() {
        return OSC::helper('catalog/common')->integerToFloat(intval($this->data['paid']));
    }

    public function getShippingPriceData() {
        $data = [
            'title' => $this->getShippingMethodTitle(),
            'service_key' => $this->data['shipping_line']['carrier']['key'],
            'method_key' => $this->data['shipping_line']['carrier']['rates'][0]['key'],
            'price' => $this->data['shipping_line']['carrier']['rates'][0]['amount'],
            'price_semitest' => $this->data['shipping_line']['carrier']['rates'][0]['amount_semitest'],
            'compare_at_price' => $this->data['shipping_line']['carrier']['rates'][0]['amount'],
            'discount' => [],
            'packages' => $this->data['shipping_line']['packages']
        ];

        foreach ($this->getDiscountCodes() as $discount_code) {
            if (isset($discount_code['apply_type']) && in_array($discount_code['apply_type'], ['shipping', 'entire_order_include_shipping'])) {
                $data['discount'] = $discount_code;
                $data['price'] -= isset($discount_code['discount_shipping_price']) ? $discount_code['discount_shipping_price'] : 0;
                break;
            }
        }

        return $data;
    }

    public function getFloatShippingPriceWithoutDiscount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getShippingPriceWithoutDiscount());
    }

    public function getShippingPriceWithoutDiscount() {
        return $this->getCarrier()->getRate()->getAmount();
    }

    public function getShippingPrice() {
        $shipping_price_data = $this->getShippingPriceData();
        return $shipping_price_data['price'];
    }

    public function getShippingMethodTitle() {
        return $this->getCarrier()->getRate()->getTitleWithCarrier();
    }

    public function getShippingMethodKey() {
        return $this->data['shipping_line']['carrier']['rates'][0]['key'] ?? null;
    }

    public function isShippingDefault() {
        $shipping_is_default = $this->data['shipping_line']['carrier']['rates'][0]['is_default'];

        if (!isset($shipping_is_default)) {
            return true;
        }

        if ($shipping_is_default == Model_Shipping_Methods::STATUS_SHIPPING_METHOD_DEFAULT) {
            return true;
        }

        return false;
    }


    public function getBillingAddress(bool $use_shipping_if_not_exists = false, $getMoreInfo = false) {
        $address_data = [];

        $prefix = 'billing_';

        if ($use_shipping_if_not_exists && !$this->data['billing_country_code']) {
            $prefix = 'shipping_';
        }

        foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
            if (isset($this->data[$prefix . $address_field])) {
                $address_data[$address_field] = $this->data[$prefix . $address_field];
            } else {
                //Support for Paypal Pro, firts_name and last_name is required
                if($getMoreInfo) {
                    if($address_field == 'first_name') {
                        $address_data[$address_field] = $this->getBillingFirstName();
                    } elseif ($address_field == 'last_name') {
                        $address_data[$address_field] = $this->getBillingLastName();
                    }
                }
            }
        }

        return $address_data;
    }

    public function getBillingFullName() {
        return $this->data['billing_full_name'];
    }

    public function getBillingFirstName() {
        $full_name_segments = explode(' ', $this->data['billing_full_name'], 2);
        return $full_name_segments[0];
    }

    public function getBillingLastName() {
        $full_name_segments = explode(' ', $this->data['billing_full_name'], 2);
        return $full_name_segments[1];
    }

    public function getShippingFullName() {
        return $this->data['shipping_full_name'];
    }

    public function getShippingFirstName() {
        $full_name_segments = explode(' ', $this->data['shipping_full_name'], 2);
        return $full_name_segments[0];
    }

    public function getShippingLastName() {
        $full_name_segments = explode(' ', $this->data['shipping_full_name'], 2);
        return $full_name_segments[1];
    }

    public function getFirstName()
    {
        return $this->getBillingFirstName() ? $this->getBillingFirstName() : $this->getShippingFirstName();
    }

    public function getLastName()
    {
        return $this->getBillingLastName() ? $this->getBillingLastName() : $this->getShippingLastName();
    }

    public function getFullName()
    {
        return $this->data['billing_full_name'] ? $this->data['billing_full_name'] : $this->data['shipping_full_name'];
    }

    public function getShippingAddress($getMoreInfo = false) {
        $address_data = [];

        foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
            if (isset($this->data['shipping_' . $address_field])) {
                $address_data[$address_field] = $this->data['shipping_' . $address_field];
            } else {
                //Support for Paypal Pro, firts_name and last_name is required
                if($getMoreInfo) {
                    if($address_field == 'first_name') {
                        $address_data[$address_field] = $this->getShippingFirstName();
                    } elseif ($address_field == 'last_name') {
                        $address_data[$address_field] = $this->getShippingLastName();
                    }
                }

            }
        }

        return $address_data;
    }

    public function getShippingLocation() {
        return [
            'country_code' => $this->data['shipping_country_code'] ?? '',
            'province_code' => $this->data['shipping_province_code'] ?? ''
        ];
    }

    public function getFloatTotal() {
        return $this->getFloatTotalPrice();
    }

    public function getFloatSubtotal() {
        return OSC::helper('catalog/common')->integerToFloat($this->getSubtotal());
    }

    public function getSubtotal() {
        $subtotal = 0;

        /* @var $line_item Model_Catalog_Order_Item */
        foreach ($this->getLineItems() as $line_item) {
            $subtotal += $line_item->getAmountWithDiscount();
        }

        return $subtotal;
    }

    public function getTotalQuantity() {
        $quantity = 0;
        /* @var $line_item Model_Catalog_Order_Item */
        foreach ($this->getLineItems() as $line_item) {
            $quantity += $line_item->data['quantity'];
        }

        return $quantity;
    }

    public function getFloatSubtotalWithoutDiscount() {
        return OSC::helper('catalog/common')->integerToFloat($this->getSubtotalWithoutDiscount());
    }

    public function getSubtotalWithoutDiscount() {
        $subtotal = 0;

        /* @var $line_item Model_Catalog_Order_Item */
        foreach ($this->getLineItems() as $line_item) {
            $subtotal += $line_item->getAmount();
        }

        return $subtotal;
    }

    public function getDiscountCodes() {
        $discount_codes = $this->registry('discount_codes');

        if (!is_array($discount_codes)) {
            $discount_codes = [];
        }

        return $discount_codes;
    }

    public function getTipPrice() {
        return $this->data['tip_price'];
    }

    public function getFloatTipPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getTipPrice() ?? 0);
    }

    public function getBuyDesign() {
        $orderCustomPriceData = $this->data['custom_price_data'];
        $orderCustomPriceData = is_array($orderCustomPriceData) && !empty($orderCustomPriceData) ? $orderCustomPriceData : [];
        $orderCustomPriceData['buy_design'] = isset($orderCustomPriceData['buy_design']) && is_array($orderCustomPriceData['buy_design']) && !empty($orderCustomPriceData['buy_design']) ? $orderCustomPriceData['buy_design'] : [];

        return $orderCustomPriceData['buy_design'];
    }

    public function getFloatBuyDesignPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getBuyDesignPrice());
    }

    public function getBuyDesignPrice($reload = false) {
        $buyDesignPrice = 0;

        foreach ($this->getLineItems($reload) as $line_item) {
            $custom_price_data = $line_item->data['custom_price_data'];
            if (isset($custom_price_data['buy_design']['is_buy_design']) && $custom_price_data['buy_design']['is_buy_design'] == 1) {
                $buyDesignPrice += isset($custom_price_data['buy_design']['buy_design_price']) && !empty($custom_price_data['buy_design']['buy_design_price']) ? intval($custom_price_data['buy_design']['buy_design_price']) : 0;
            }
        }

        return $buyDesignPrice;
    }

    public function getAddonServicePrice($reload = false): int
    {
        /* @var $line_item Model_Catalog_Order_Item*/
        $price = 0;
        foreach ($this->getLineItems($reload) as $line_item) {
            $price += $line_item->getAddonServicePrice();
        }

        return $price;
    }

    public function getFloatTaxPrice() {
        return OSC::helper('catalog/common')->integerToFloat($this->getTaxPrice());
    }

    public function getTaxPrice() {
        return intval($this->data['tax_price']);
    }

    public function getTaxValue() {
        $tax_data = $this->getTaxData();

        return intval($tax_data['tax_value']) ?? 0;
    }

    public function getTaxData()
    {
        return $this->data['taxes'];
    }

    /**
     *
     * @param boolean $reload
     * @return $this->_line_item_collection
     */
    public function getLineItems($reload = false): Model_Catalog_Order_Item_Collection {
        if ($this->_line_item_collection === null || $reload) {
            $this->_line_item_collection = OSC::model('catalog/order_item')->getCollection()->loadByOrderMasterRecordId($this->getId());

            foreach ($this->_line_item_collection as $line_item) {
                $line_item->setOrder($this);
            }
        }

        return $this->_line_item_collection;
    }

    /**
     *
     * @param Model_Catalog_Order_Item_Collection $line_item_collection
     * @return $this
     */
    public function setLineItems(Model_Catalog_Order_Item_Collection $line_item_collection) {
        $this->_line_item_collection = $line_item_collection;
        return $this;
    }

    public function getLineItemByItemId($item_id) {
        foreach ($this->getLineItems() as $order_item) {
            if ($order_item->data['item_id'] == $item_id) {
                return $order_item;
            }
        }

        return null;
    }

    public function addLineItems($line_items) {
        $this->register('line_items', $line_items);

        return $this;
    }

    public function loadByCode(string $code): Model_Catalog_Order {
        $code = trim($code);

        if ($code == '') {
            throw new Exception('Code is empty');
        }
        $shop = OSC::getShop();

        return $this->setCondition(['condition' => 'shop_id = ' . intval($shop->getId()) . ' AND code = :code', 'params' => ['code' => $code]])->load();
    }

    public function checkMasterLock(): bool {
        if ($this->data['master_lock_flag'] != 0) {
            return true;
        }

        return false;
    }

    protected function _beforeSave() {
        parent::_beforeSave();

        $data = $this->_collectDataForSave();

        $errors = array();

        if (isset($data['email'])) {
            $data['email'] = trim($data['email']);

            try {
                OSC::core('validate')->validEmail($data['email']);
            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
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

        foreach (['shipping_', 'billing_'] as $address_prefix) {
            $address_data = [];

            foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
                if (isset($data[$address_prefix . $address_field])) {
                    $address_data[$address_field] = $data[$address_prefix . $address_field];
                }
            }

            if (count($address_data) > 0) {
                try {
                    $address_data = OSC::helper('core/country')->verifyAddress($address_data, $this->getData($address_prefix . 'country_code', true));

                    foreach ($address_data as $address_field => $address_value) {
                        $data[$address_prefix . $address_field] = $address_value;
                    }
                } catch (Exception $ex) {
                    $errors[] = $ex->getMessage();
                }
            }
        }

        $flag_billing_null = false;

        foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
            if (in_array($address_field, ['phone', 'full_name', 'address1', 'zip', 'country_code', 'country']) && !$data['billing_'.$address_field]) {
                $flag_billing_null  = true;
                break;
            }
        }

        if ($flag_billing_null == true) {
            foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
                if (!isset($data['shipping_'.$address_field])) {
                    continue;
                }
                $data['billing_'.$address_field] = $data['shipping_'.$address_field];
            }
        }

        if (isset($data['shipping_line'])) {
            try {
                if (!is_array($data['shipping_line']) || !isset($data['shipping_line']['carrier'])) {
                    $errors[] = 'Please set a shipping method for order';
                } else {
                    OSC::helper('catalog/checkout')->verifyShippingMethod($data['shipping_line']);
                    $data['shipping_line'] = [
                        'carrier' => $data['shipping_line']['carrier'], 
                        'packages' => $data['shipping_line']['packages'],
                        'shipping_rates' => $data['shipping_line']['shipping_rates'] ?? []
                    ];
                }
            } catch (Exception $ex) {
                $errors[] = $ex->getMessage();
            }
        }

        if (isset($data['fraud_risk_level'])) {
            unset($data['fraud_risk_level']);
        }

        if (isset($data['sref_id'])) {
            $data['sref_id'] = intval($data['sref_id']);

            if ($data['sref_id'] < 1) {
                $data['sref_id'] = null;
            } else {
                try {
                    $sref_member = OSC::model('user/member')->load($data['sref_id']);

                    $data['traffic_source'] = $sref_member->data['sref_type'] ? (isset($sref_member->getSrefTypes()[$sref_member->data['sref_type']]) ? $sref_member->data['sref_type'] : $sref_member->getSrefTypeDefault()['key']) : $sref_member->getSrefTypeDefault()['key'];
                } catch(Exception $ex) {

                }
            }
        }

        if (isset($data['fraud_data'])) {
            if (!is_array($data['fraud_data']) || !isset($data['fraud_data']['score'])) {
                $data['fraud_data'] = null;
            } else {
                $data['fraud_data'] = [
                    'score' => intval($data['fraud_data']['score']),
                    'info' => isset($data['fraud_data']['info']) ? trim($data['fraud_data']['info']) : ''
                ];

                if ($data['fraud_data']['score'] < 0 || $data['fraud_data']['score'] > 100) {
                    $data['fraud_data'] = null;
                } else {
                    $matched_fraud_level_key = null;
                    $matched_fraud_level_info = null;

                    $fraud_levels = static::FRAUD_RISK_LEVEL;

                    uasort($fraud_levels, function($a, $b) {
                        if ($a['score'] == $b['score']) {
                            return 0;
                        }

                        return ($a['score'] < $b['score']) ? -1 : 1;
                    });

                    foreach ($fraud_levels as $fraud_level_key => $fraud_level_info) {
                        if ($fraud_level_info['score'] <= $data['fraud_data']['score'] && ($matched_fraud_level_info === null || $matched_fraud_level_info['score'] < $fraud_level_info['score'])) {
                            $matched_fraud_level_key = $fraud_level_key;
                            $matched_fraud_level_info = $fraud_level_info;
                        }
                    }

                    $data['fraud_risk_level'] = $matched_fraud_level_key;
                }
            }
        }

        if (isset($data['custom_price_data'])) {
            if (!is_array($data['custom_price_data'])) {
                $data['custom_price_data'] = [];
            }
        }

        if (isset($data['payment_data']) && !empty($data['payment_data']['ba_token'])) {
            $data['is_upsale'] = Model_Catalog_Upsale::STATUS_UPSALE_AVAILABLE;
        }

        foreach (['ab_test', 'client_referer', 'client_country', 'client_device_type', 'client_browser'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = trim($data[$key]);
            }
        }

        if (count($errors) < 1) {
            if ($this->getActionFlag() == static::INSERT_FLAG) {
                $require_fields = [
                    'email' => 'Email is empty',
                    'shipping_phone' => 'Shipping address: phone is empty',
                    'shipping_full_name' => 'Shipping address: full name is empty',
                    'shipping_address1' => 'Shipping address: address is empty',
                    'shipping_city' => 'Shipping address: city is empty',
                    'shipping_zip' => 'Shipping address: ZIP/Postal code is empty',
                    'shipping_country' => 'Shipping address: country is empty',
                    'shipping_country_code' => 'Shipping address: country code is empty',
                    'shipping_line' => 'Shipping method is empty',
                    'payment_method' => 'Payment method is empty',
                    'subtotal_price' => 'Subtotal is empty',
                    'total_price' => 'Total price is empty'
                ];

                foreach ($require_fields as $field_name => $err_message) {
                    if (!isset($data[$field_name])) {
                        $errors[] = $err_message;
                    }
                }
                $temporary_data = time();
                $default_fields = [
                    'master_ukey' => $temporary_data,
                    'order_id' => $temporary_data,
                    'code' => null,
                    'order_status' => 'open',
                    'payment_status' => 'unpaid',
                    'fulfillment_status' => 'unfulfillment',
                    'process_status' => 'unprocess',
                    'payment_data' => [],
                    'is_upsale' => 0,
                    'fraud_data' => [],
                    'fraud_risk_level' => 'unknown',
                    'shipping_price' => 0,
                    'tax_price' => 0,
                    'custom_price' => 0,
                    'paid' => 0,
                    'refunded' => 0,
                    'member_id' => 0,
                    'currency_code' => 'USD',
                    'discount_codes' => [],
                    'shipping_company' => '',
                    'shipping_address2' => '',
                    'shipping_province' => '',
                    'shipping_province_code' => '',
                    'billing_phone' => '',
                    'billing_full_name' => '',
                    'billing_address1' => '',
                    'billing_city' => '',
                    'billing_province' => '',
                    'billing_zip' => '',
                    'billing_country' => '',
                    'billing_country_code' => '',
                    'billing_company' => '',
                    'billing_address2' => '',
                    'billing_province_code' => '',
                    'taxes' => [],
                    'client_info' => [],
                    'additional_data' => [],
                    'sref_id' => null,
                    'traffic_source' => '',
                    'ab_test' => null,
                    'client_referer' => null,
                    'client_country' => null,
                    'client_device_type' => null,
                    'client_browser' => null,
                    'added_timestamp' => time(),
                    'modified_timestamp' => time()
                ];

                foreach ($default_fields as $field_name => $default_value) {
                    if (!isset($data[$field_name])) {
                        $data[$field_name] = $default_value;
                    }
                }

                $data['shop_id'] = OSC::getShop()->getId();
                $data['ukey'] = OSC::helper('catalog/common')->genCodeUkey(18);
            } else {
                unset($data['shop_id']);
                unset($data['ukey']);
                unset($data['code']);
                unset($data['taxes']);
                unset($data['client_info']);
                unset($data['payment_method']);
                unset($data['member_id']);
                unset($data['currency_code']);
                unset($data['custom_price']);
                unset($data['added_timestamp']);
                $data['modified_timestamp'] = time();
            }
        }

        $this->resetDataModifiedMap()->setData($data);

        if (count($errors) > 0) {
            $this->_error($errors);
            return false;
        } else if ($this->getActionFlag() == static::INSERT_FLAG) {
            if (!isset($data['shipping_price'])) {
                $shipping_price_data = $this->getShippingPriceData();
                $this->setData('shipping_price', $shipping_price_data['price']);
            }
        }
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['discount_codes', 'shipping_line', 'taxes', 'payment_method', 'payment_data', 'fraud_data', 'client_info', 'additional_data', 'custom_price_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['discount_codes', 'shipping_line', 'taxes', 'payment_method', 'payment_data', 'fraud_data', 'client_info', 'additional_data', 'custom_price_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

    /**
     * @param int $length
     * @return string
     */

    public function updateIndex() {
        if ($this->registry('SKIP_UPDATE_INDEX') == 1) {
            return $this;
        }

        $keys = [
            'order_id', 'code', 'email', 'ukey', 'cart_ukey',
            'shipping_full_name', 'shipping_address1', 'shipping_address2', 'shipping_company', 'shipping_phone', 'shipping_city', 'shipping_province', 'shipping_province_code', 'shipping_country', 'shipping_country_code', 'shipping_zip',
            'billing_full_name', 'billing_address1', 'billing_address2', 'billing_company', 'billing_phone', 'billing_city', 'billing_province', 'billing_province_code', 'billing_country', 'billing_country_code', 'billing_zip'
        ];

        $index_keywords = [];

        foreach ($keys as $key) {
            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
                $index_keywords[$key] = strip_tags($this->data[$key]);
            }
        }

        foreach ($this->getLogCollection() as $log) {
            if ($log->data['action_key'] == 'COMMENT') {
                $index_keywords[] = $log->data['action_title'];
            }
        }

        foreach ($this->getLineItems() as $line_item) {
            foreach (['item_id', 'ukey', 'product_id', 'variant_id', 'vendor', 'title', 'sku', 'options'] as $key) {
                if ($key == 'options') {
                    foreach ($line_item->data[$key] as $option) {
                        $index_keywords[] = $option['value'];
                    }
                } else {
                    $index_keywords[] = $line_item->data[$key];
                }
            }
        }

        $index_keywords = implode(' ', $index_keywords);

        $index_model = OSC::model('catalog/order_index');

        try {
            $index_model->loadByUKey($this->getId());
        } catch (Exception $ex) {
            if ($ex->getCode() !== 404) {
                throw new Exception($ex->getMessage());
            }
        }

        $_ORDER_STATUS = [
            'open' => 1,
            'archived' => 2,
            'cancelled' => 0
        ];

        $_PAYMENT_STATUS = [
            'pending' => 1,
            'void' => 0,
            'authorized' => 2,
            'paid' => 4,
            'partially_paid' => 3,
            'partially_refunded' => 5,
            'refunded' => 6
        ];

        $_FULFILLMENT_STATUS = [
            'fulfilled' => 2,
            'unfulfilled' => 0,
            'partially_fulfilled' => 1
        ];

        $_PROCESS_STATUS = [
            'processed' => 3,
            'unprocess' => 0,
            'partially_process' => 2,
            'process' => 1,
        ];

        $_FRAUD_RISK_LEVEL = [
            'unknown' => 0,
            'normal' => 1,
            'elevated' => 2,
            'highest' => 3
        ];

        $index_model->setData([
            'order_id' => $this->getId(),
            'order_status' => $_ORDER_STATUS[$this->data['order_status']],
            'payment_status' => $_PAYMENT_STATUS[$this->data['payment_status']],
            'fulfillment_status' => $_FULFILLMENT_STATUS[$this->data['fulfillment_status']],
            'process_status' => $_PROCESS_STATUS[$this->data['process_status']],
            'fraud_risk_level' => $_FRAUD_RISK_LEVEL[$this->data['fraud_risk_level']],
            'order_added_timestamp' => $this->data['added_timestamp'],
            'keywords' => $index_keywords
        ])->save();
    }

    protected function _afterSave() {
        parent::_afterSave();

        if ($this->getLastActionFlag() == static::INSERT_FLAG) {
            $this->_updateData();
        }

//        $keys = ['code', 'email', 'ukey', 'cart_ukey', 'shipping_full_name', 'billing_full_name'];
//
//        $index_keywords = [];
//
//        foreach ($keys as $key) {
//            if (isset($this->data[$key]) && !isset($index_keywords[$key])) {
//                $index_keywords[$key] = strip_tags($this->data[$key]);
//            }
//        }
//
//        $index_keywords = implode(' ', $index_keywords);
//
//        OSC::helper('backend/common')->indexAdd('', 'catalog', 'order', $this->getId(), $index_keywords);
//
//        $this->updateIndex();
    }

    protected function _updateData() {
        $code_prefix = OSC::helper('core/setting')->get('catalog/order_code/prefix');
        $code_suffix = OSC::helper('core/setting')->get('catalog/order_code/suffix');

        $code = $code_prefix . $this->getId() . $code_suffix;

        $count_error = 0;

        while ($count_error < 4) {
            try {
                $this->getWriteAdapter()->update($this->getTableName(), ['code' => $code, 'order_id' => $this->getId(), 'master_ukey' => $this->data['shop_id'].':'.$this->getId()], 'master_record_id=' . $this->getId() . ' AND code IS NULL', 1, 'update_order_code_'.$this->getId());
                break;
            } catch (Exception $ex) {
                //Notify when deadlock sql
                if (strpos($ex->getMessage(), 'Deadlock') !== false) {
                    $count_error++;
                    OSC::helper('core/telegram')->send(OSC::$base_url.'::_updateData::1168::deadlock::' . time() . '::order #' . $this->getId() . '::' . $count_error);
                } else {
                    throw new Exception($ex->getMessage());
                }
            }
        }

        $registry = $this->getAllRegistry();

        $this->reload();

        $this->setAllRegistry($registry);
    }

    public function checkBillingSameShipping() {
        foreach (array_keys(Helper_Core_Country::ADDRESS_FIELDS) as $address_field) {
            if ($this->data['shipping_' . $address_field] != $this->data['billing_' . $address_field]) {
                return false;
            }
        }

        return true;
    }

    public function convertToHoursMins($minute) {
        if ($minute < 1) {
            return;
        }
        $value = [];
        $hours = floor($minute / 60);
        if ($hours > 0) {
            $value[] = $hours . ' hours';
        }
        $minutes = $minute % 60;
        if ($minutes > 0) {
            $value[] = $minutes . ' minutes';
        }

        return !empty($value) ? implode(' ', $value) : '';
    }

    public function getTimeToCancelMediate() {
        $time = intval(OSC::helper('core/setting')->get('catalog/order/cancel/time_to_mediate'));
        return $time > 0 ? $time*60 : 60*60*6; // default 6 hours
    }

    public function getTimeToEditOrCancelImmediate($return_number = true) {
        $time = intval(OSC::helper('core/setting')->get('catalog/order/cancel/time_to_immediate'));

        $number_second = $time > 0 ? $time*60 : 60*60*2; // default 2 hours
        if ($return_number == true) {
            return $number_second;
        }
        return $this->convertToHoursMins($number_second/60);
    }

    public function ableToEdit() {
        if ($this->checkMasterLock() && !OSC::helper('catalog/order')->ableToEdit('catalog/super|catalog/order/full/locked|catalog/order/edit/locked')) {
            return false;
        }

        if (!OSC::helper('catalog/order')->ableToEdit('catalog/super|catalog/order/full|catalog/order/edit')) {
            if ($this->data['added_timestamp'] <= (time() - $this->getTimeToEditOrCancelImmediate())) {
                return false;
            }

            if($this->data['order_status'] == 'cancelled') {
                return false;
            }

            if ($this->data['payment_status'] != 'authorized') {
                return false;
            }

            if ($this->data['process_status'] == 'processed') {
                return false;
            }
        }

        return true;
    }

    public function ableToCancel() {
        if ($this->checkMasterLock() && !OSC::helper('catalog/order')->ableToEdit('catalog/super|catalog/order/full/locked|catalog/order/cancel/locked')) {
            return false;
        }

        if (!OSC::helper('catalog/order')->ableToEdit('catalog/super|catalog/order/full|catalog/order/cancel')) {

            if ($this->data['added_timestamp'] <= (time() - $this->getTimeToCancelMediate())) {
                return false;
            }

            if($this->data['member_hold'] < 0) {
                return false;
            }

            if($this->data['order_status'] == 'cancelled') {
                return false;
            }

            if($this->data['fulfillment_status'] == 'fulfilled') {
                return false;
            }
        }

        return true;
    }

    public function getLocationOfClient() {
        return $this->data['client_info']['location'];
    }

    protected function _beforeDelete()
    {
        parent::_beforeDelete();
        OSC::helper('backend/common')->indexDelete('', 'catalog', 'order', $this->getId());
    }

    public function getItemsShippingInfo() {
        return $this->data['shipping_line']['carrier']['rates'][0]['items_shipping_info'] ?? [];
    }

    public function checkWaitRefund(): bool {
        $additional_data = $this->data['additional_data'];

        if (isset($additional_data['wait_refund'])) {
            return true;
        }

        return false;
    }

    public function checkHoldOrder(): bool {
        $member_hold = $this->data['member_hold'];

        if (intval($member_hold) == 0) {
            return true;
        }

        return false;
    }

    /**
     * Check order has full design url of item rendered
     * @return boolean
     */
    public function hasFullDesignUrlItems() {
        $total_buy_design = count($this->getBuyDesign());

        $count_line_item_has_design_url = 0;

        foreach ($this->getLineItems() as $line_item) {
            if (isset($line_item->data['custom_price_data']['buy_design']['is_buy_design']) &&
                $line_item->data['custom_price_data']['buy_design']['is_buy_design'] == 1 &&
                $line_item->data['design_url'] != ''
            ) {
                $count_line_item_has_design_url++;
            }
        }

        return $total_buy_design == $count_line_item_has_design_url;
    }

    /**
     * Get items data rendered design url
     * @return array $design_urls
     */
    public function getLineItemsRenderedDesignUrl() {
        $items = [];

        foreach ($this->getLineItems() as $key => $line_item) {
            if (isset($line_item->data['custom_price_data']['buy_design']['is_buy_design']) &&
                $line_item->data['custom_price_data']['buy_design']['is_buy_design'] == 1 &&
                $line_item->data['design_url'] != ''
            ) {
                $items[$key]['title'] = $line_item->data['title'];
                $items[$key]['sku'] = $line_item->data['sku'];
                $items[$key]['options_text'] = (count($line_item->data['options']) > 0) ? $line_item->getVariantOptionsText() : '';
                $items[$key]['design_url'] = $line_item->data['design_url'];
            }
        }

        return $items;
    }

    public function checkResendItemInOrder(): bool {
        $line_items =$this->getLineItems();

        foreach ($line_items as $line_item) {
            if (($line_item->data['additional_data'])['resend'] != null){
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function availableToRefundByCustomer(): bool {
        return $this->data['order_status'] === 'open' &&
            in_array($this->data['payment_status'], ['authorized', 'paid']) &&
            $this->data['fulfillment_status'] === 'unfulfilled' &&
            $this->data['process_status'] === 'unprocess';
    }

    /**
     * @return bool
     */
    public function hasFullItemMugs(): bool {
        $mug_counter = 0;
        $item_counter = $this->getLineItems()->length();
        foreach ($this->getLineItems() as $item) {
            if (strpos($item->data['product_type'], 'mug') !== false) {
                $mug_counter++;
            }
        }

        return $mug_counter === $item_counter;
    }

    /**
     * @return bool
     */
    public function hasItemMugs(): bool {
        $mug_counter = 0;
        foreach ($this->getLineItems() as $item) {
            if (strpos($item->data['product_type'], 'mug') !== false) {
                $mug_counter++;
            }
        }

        return $mug_counter > 0;
    }

    /**
     * @return bool
     */
    public function hasItemBlankets(): bool {
        $blanket_counter = 0;
        foreach ($this->getLineItems() as $item) {
            if (strpos($item->data['product_type'], 'blanket') !== false) {
                $blanket_counter++;
            }
        }

        return $blanket_counter > 0;
    }

    /**
     * @return bool
     */
    public function hasFullItemBlankets(): bool {
        $blanket_counter = 0;
        $item_counter = $this->getLineItems()->length();
        foreach ($this->getLineItems() as $item) {
            if (strpos($item->data['product_type'], 'blanket') !== false) {
                $blanket_counter++;
            }
        }

        return $blanket_counter === $item_counter;
    }

    /**
     *
     * @param int $order_id
     * @return Model_Catalog_Order
     * @throws Exception
     */
    public function loadByOrderId(int $order_id): Model_Catalog_Order {
        if ($order_id < 1) {
            throw new Exception('Order ID is need greater than 0');
        }

        $shop = OSC::getShop();

        return $this->setCondition('shop_id = ' . $shop->getId() . ' AND order_id = ' . intval($order_id))->load();
    }

    /**
     *
     * @param int $order_id
     * @return Model_Catalog_Order
     * @throws Exception
     */
    public function loadByOrderUkey(string $order_ukey): Model_Catalog_Order {
        $pre_order_ukey = $order_ukey;
        $order_ukey = static::cleanUkey($order_ukey);
        if ($order_ukey != $pre_order_ukey) {
            OSC::helper('core/common')->writeLog('Wrong order ukey', $pre_order_ukey);
        }

        if (!$order_ukey || !preg_match('/^[a-z0-9]{18}$/is', $order_ukey)) {
            throw new Exception('Ukey is empty');
        }

        $shop = OSC::getShop();

        return $this->setCondition('shop_id = ' . $shop->getId() . ' AND ukey = "' . $order_ukey . '"')->load();
    }

    /**
     *
     * @return Model_Shop_Shop
     */
    public function getShop() {
        try {
            return OSC::model('shop/shop')->load($this->data['shop_id']);
        } catch (Exception $ex) {
            return null;
        }
    }

    public function getOrderUkey() {
        return $this->data['ukey'];
    }

    public function showDiscount() {
        $is_default_rate = $this->isShippingDefault();
        if ($is_default_rate === false) {
            foreach ($this->data['discount_codes'] as $discount_info) {
                if (in_array($discount_info['apply_type'], ['shipping'])) {
                    return 1;
                } elseif (in_array($discount_info['apply_type'], ['entire_order_include_shipping'])) {
                    return 2;
                }
            }
        }
        return 0;
    }

    public function checkOrderWaitDesignWithItem($item_id): bool {
        $additional_data = $this->data['additional_data'];

        if (isset($additional_data['edit_design_change_product_type'])) {
            foreach ($additional_data['edit_design_change_product_type'] as $value) {
                if ($value == $item_id) {
                    return true;
                }
            }
        }

        return false;
    }

    public function isGoogleReferer() {
        return strpos($this->data['client_referer'], 'google') !== false;
    }

    public function getPaymentMethod() {
        $payment_method = $this->data['payment_method'];

        if (!empty($payment_method['payment_method']) && $payment_method['payment_method'] === 'applePay') {
            return 'Apple Pay';
        }

        return $this->getPayment()->getTextTitle();
    }
}
