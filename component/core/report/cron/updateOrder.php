<?php

class Cron_Report_UpdateOrder extends OSC_Cron_Abstract {

    public function process($data, $queue_added_timestamp) {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        /* @var $DB OSC_Database */
        $DB = OSC::core('database');

        $DB->select('client_info,order_id', 'catalog/order', null, 'order_id DESC');

        while ($row = $DB->fetchArray()) {
            $row['client_info'] = OSC::decode($row['client_info']);

            $client_info = new WhichBrowser\Parser($row['client_info']['user_agent']);

            $sref_id = isset($row['client_info']['DLS_SALE_REF']) && is_array($row['client_info']['DLS_SALE_REF']) ? intval($row['client_info']['DLS_SALE_REF']['id']) : null;

            if ($sref_id < 1) {
                $sref_id = null;
            }

            $DB->update('catalog/order', [
                'sref_id' => $sref_id,
                'client_referer' => isset($row['client_info']['referer']) && is_array($row['client_info']['referer']) ? $row['client_info']['referer']['host'] : null,
                'client_country' => isset($row['client_info']['location']) && is_array($row['client_info']['location']) ? $row['client_info']['location']['country_code'] : null,
                'client_device_type' => $client_info->device->type,
                'client_browser' => $client_info->browser->name ? trim($client_info->browser->name) : ''
                    ], 'order_id = ' . $row['order_id'], 1, 'update_order');
        }
        
        OSC::core('cron')->removeScheduler('report/updateOrder', null);

        echo 'DONE';
    }

}
