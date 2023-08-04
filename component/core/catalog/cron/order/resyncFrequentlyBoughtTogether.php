<?php

class Cron_Catalog_Order_ResyncFrequentlyBoughtTogether extends OSC_Cron_Abstract {

    public function process($params, $queue_added_timestamp) {
        OSC::helper('catalog/frequentlyBoughtTogether')->resync();
    }

}
