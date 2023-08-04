<?php

class Cron_Feed_Klaviyo_NewArrival extends OSC_Cron_Abstract {

    const CRON_TIMER = '0 16 * * 6'; // 10AM US Saturday -6
    const CRON_SCHEDULER_FLAG = 1;
    const KLAVIYO_NEW_ARRIVAL_FILE_PATH = OSC_VAR_PATH . '/feed/klaviyo/product-new-arrivals.json';

    public function process($params, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $sref = OSC::helper('core/setting')->get('tracking/klaviyo/new_arrival_sref_id');
        $bot_token = OSC::helper('core/setting')->get('feed/notification/telegram_bot_token');
        $telegram_group_id  = OSC::helper('core/setting')->get('feed/notification/telegram_group');
        $date = date('Y-m-d', time());
        $feed_urls = OSC_FRONTEND_BASE_URL . str_replace(OSC_SITE_PATH, '', self::KLAVIYO_NEW_ARRIVAL_FILE_PATH);

        try {
            $now = time();
            $one_week = 7 * 24 * 60 * 60;
            $products = OSC::model('catalog/product')
                            ->getCollection()
                            ->addField('product_id', 'title', 'slug', 'sku','price', 'added_timestamp', 'solds')
                            ->addCondition('added_timestamp', $now - $one_week, OSC_Database::OPERATOR_GREATER_THAN)
                            ->sort('solds', OSC_Database::ORDER_DESC)
                            ->setLimit(10)
                            ->load();
    
            $data_feed = [];
            foreach ($products as $product) {
                $product_url = $product->getDetailUrl();
                if (!empty($sref)) {
                    $product_url .= (strpos($product_url, '?') !== false ? '&' : '?') . 'sref=' . $sref;
                }
                
                $price = 0;
                $price_data = $product->getSelectedOrFirstAvailableVariant()->getPriceForCustomer();
                if (isset($price_data['price'])) $price = $price_data['price'];

                $data_feed[] = [
                    'product_id'    => $product->getId(),
                    'title'         => $product->data['title'],
                    'product_link'  => $product_url,
                    'image'         => $product->getFeaturedImageUrl(),
                    'price'         => OSC::helper('catalog/common')->integerToFloat($price)
                ]; 
            }

            OSC::writeToFile(self::KLAVIYO_NEW_ARRIVAL_FILE_PATH, OSC::encode($data_feed));

            // notification to telegram
            $message = "*Klaviyo new arrival render success*\n- Date render: {$date}\n- You can see data [Here]({$feed_urls}).";
            OSC::helper('core/telegram')->sendMessage($message, $telegram_group_id, $bot_token);
        } catch (Exception $ex) {
            $message = "*Klaviyo new arrival render failed*\n- Date render: {$date}\n- Message: {$ex->getMessage()}.";
            OSC::helper('core/telegram')->sendMessage($message, $telegram_group_id, $bot_token);
            throw new Exception($ex->getMessage());
        }

    }
}