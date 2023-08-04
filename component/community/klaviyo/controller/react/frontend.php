<?php

class Controller_Klaviyo_React_Frontend extends Abstract_Frontend_ReactApiController
{
    public function actionApplyDiscountCode()
    {
        try {
            $current_url = OSC_FRONTEND_BASE_URL;

            $callback_url = $this->_request->get('callback_url');
            if ($callback_url) {
                try {
                    $url_decode = trim(strval(base64_decode($callback_url)));

                    $url = parse_url($url_decode);

                    if (!OSC::isUrl($url_decode) || !$url['host'] || $url['host'] != parse_url(OSC_FRONTEND_BASE_URL)['host']) {
                        throw new Exception('Data is incorrect', $this::CODE_BAD_REQUEST);
                    }

                    $current_url = $url_decode;
                } catch (Exception $ex) {

                }
            }

            $discount_code = $this->_request->get('discount_code');

            if ($discount_code) {
                $discount_code = trim(strval(base64_decode($discount_code)));

                $discount_model = OSC::model('catalog/discount_code');

                try {
                    $discount_model->loadByUKey($discount_code);
                } catch (Exception $ex) { }

                if ($discount_model->getId() < 1) {
                    throw new Exception('Not found');
                }

                $cart = OSC::helper('catalog/common')->getCart(true);

                if (count($cart->data['discount_codes']) > 0) {
                    throw new Exception('Discount code is exist');
                }

                $cart->setData('discount_codes', [$discount_model->data['discount_code']])
                    ->save();
            }
        } catch (Exception $ex) { }

        $this->sendSuccess($current_url);
    }
}