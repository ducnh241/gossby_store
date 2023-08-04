<?php

class Helper_Catalog_EmailMarketing extends OSC_Object {

    public function validateAbandoned($cart_id) {
        try {
            $cart = OSC_Database_Model::getPreLoadedModel('catalog/cart', $cart_id);

            if (!($cart instanceof Model_Catalog_Cart)) {
                return false;
            }
        } catch (Exception $ex) {
            return false;
        }

        return $cart->getId() > 0;
    }

}
