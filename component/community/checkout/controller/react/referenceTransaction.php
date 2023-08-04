<?php

class Controller_Checkout_React_ReferenceTransaction extends Abstract_Frontend_ReactApiController {
    public function actionSummary() {
        $variant_id = intval($this->_request->get('variant_id'));
        $quantity = intval($this->_request->get('quantity'));

        $order_ukey = trim($this->_request->get('order_ukey'));
        $order = OSC::model('catalog/order');

        $tax_price = 0;
        $shipping_fee = 0;
        $subtotal = 0;
        $total = 0;

        try {
            if ($variant_id < 1) {
                throw new Exception('Variant ID is empty');
            }

            try {
                $variant = OSC::model('catalog/product_variant')->load($variant_id);
            } catch (Exception $ex) {
                throw new Exception(Model_Catalog_Upsale::MESSAGE_PRODUCT_NOT_FOUND, $this::CODE_NOT_FOUND);
            }

            if (!$variant->ableToOrder()) {
                throw new Exception(Model_Catalog_Upsale::MESSAGE_PRODUCT_NOT_FOUND, $this::CODE_NOT_FOUND);
            }

            $order->loadByOrderUKey($order_ukey);

            if (!$order->isUpsaleAvailable()) {
                throw new Exception(Model_Catalog_Upsale::MESSAGE_UPSALE_UNAVAILABLE, Model_Catalog_Upsale::CODE_UPSALE_UNAVAILABLE);
            }

            $location = $order->getShippingLocation();

            $catalog_upsale = OSC::model('catalog/upsale');
            $catalog_upsale->setVariant($variant);
            $catalog_upsale->setQuantity($quantity);
            $catalog_upsale->setOrder($order);
            $catalog_upsale->setLocation($location);
            $catalog_upsale->setPack(intval($this->_request->get('pack_id')));

            $subtotal = $catalog_upsale->getSubtotal();
            $shipping_fee = $catalog_upsale->getShipping();
            $tax_price = $catalog_upsale->getTaxPrice();
            $total = $catalog_upsale->getTotal();
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $this->sendSuccess([
            'quantity' => $quantity,
            'subtotal' => $subtotal,
            'tax' => !empty($tax_price) ? $tax_price : 0,
            'shipping' => !empty($shipping_fee) ? $shipping_fee : 0,
            'total' => $total
        ]);
    }

    public function actionAddToOrder() {
        $variant_id = intval($this->_request->get('variant_id'));
        $quantity = intval($this->_request->get('quantity'));

        $order_ukey = trim($this->_request->get('order_ukey'));
        $order = OSC::model('catalog/order');

        try {
            if ($variant_id < 1) {
                throw new Exception(Model_Catalog_Upsale::MESSAGE_PRODUCT_NOT_FOUND, $this::CODE_NOT_FOUND);
            }

            try {
                $variant = OSC::model('catalog/product_variant')->load($variant_id);
            } catch (Exception $ex) {
                throw new Exception(Model_Catalog_Upsale::MESSAGE_PRODUCT_NOT_FOUND, $this::CODE_NOT_FOUND);
            }

            if (!$variant->ableToOrder()) {
                throw new Exception(Model_Catalog_Upsale::MESSAGE_PRODUCT_NOT_FOUND, $this::CODE_NOT_FOUND);
            }

            $order->loadByOrderUKey($order_ukey);

            if (!$order->isUpsaleAvailable()) {
                throw new Exception(Model_Catalog_Upsale::MESSAGE_UPSALE_UNAVAILABLE, Model_Catalog_Upsale::CODE_UPSALE_UNAVAILABLE);
            }

            $location = $order->getShippingLocation();

            $catalog_upsale = OSC::model('catalog/upsale');
            $catalog_upsale->setVariant($variant);
            $catalog_upsale->setQuantity($quantity);
            $catalog_upsale->setOrder($order);
            $catalog_upsale->setLocation($location);
            $catalog_upsale->setPack(intval($this->_request->get('pack_id')));

            $catalog_upsale->placeUpsale($this->_request->getAll());
        } catch (Exception $ex) {
            $this->sendError($ex->getMessage(), $ex->getCode());
        }

        $this->sendSuccess();
    }
}