<?php

class Cron_SmsMarketing_Abandoned extends OSC_Cron_Abstract
{
    const CRON_TIMER = '*/5 * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($data, $queue_added_timestamp)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $enable_klaviyo_sms = OSC::helper('core/setting')->get('marketing/klaviyo/sms/enable');
        $klaviyo_sms_list_id = OSC::helper('core/setting')->get('marketing/klaviyo/sms/list_id');
        $klaviyo_sms_api_key = OSC::helper('core/setting')->get('marketing/klaviyo/sms/api_key');

        if ($enable_klaviyo_sms) {
            if (!$klaviyo_sms_list_id || !$klaviyo_sms_api_key) {
                return;
            }
        }

        $collection = OSC::model('catalog/cart')->getCollection()
            ->setCondition('abandoned_sms_sent = 0 AND modified_timestamp <= ' . (time() - (5 * 60)) . " AND (shipping_phone != '' OR billing_phone != '') AND (shipping_full_name != '' OR billing_full_name != '')")
            ->setLimit(1000)
            ->load();

        $phone_prefix_countries = OSC::helper('core/country')->getPhonePrefixCountries();

        foreach ($collection as $cart) {
            try {
                $counter = 0;

                $line_items = $cart->getLineItems();

                foreach ($line_items as $line_item) {
                    if (!$line_item->isCrossSellMode() && (!$line_item->getVariant() || !$line_item->getProduct())) {
                        $line_items->removeItemByKey($line_item->getId());
                        try {
                            $line_item->delete();
                        } catch (Exception $ex) { }
                    } else {
                        $counter++;
                    }
                }

                if ($counter < 1) {
                    $cart->delete();
                    continue;
                }

                try {
                    if ($enable_klaviyo_sms) {
                        OSC::helper('smsMarketing/klaviyo')->createQueue($cart, $phone_prefix_countries);
                    }
                } catch (Exception $ex) {
                }

                $cart->increment('abandoned_sms_sent');

            } catch (Exception $ex) {
                continue;
            }
        }
    }
}

