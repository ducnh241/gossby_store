<?php

class Cron_Catalog_Upsale_UpdateBillingAgreementToken extends OSC_Cron_Abstract {
    const CRON_TIMER = '0 * * * *';
    const CRON_SCHEDULER_FLAG = 1;

    public function process($params, $queue_added_timestamp) {
        $time_limit = time() - intval(Model_Catalog_Upsale::UPSALE_TIME);
        $upsale_status = Model_Catalog_Upsale::STATUS_UPSALE_AVAILABLE;
        $order_table = OSC::model('catalog/order')->getTableName();

        $DB = OSC::core('database')->getAdapter('db_master');
        $DB->select('master_record_id, order_id, is_upsale, payment_data', $order_table, "added_timestamp <= {$time_limit} AND is_upsale = {$upsale_status}", 'added_timestamp', null, 'fetch_orders');

        $count = 0;
        while ($row = $DB->fetchArray('fetch_orders')) {
            $count++;
            try {
                $update_data = [
                    'is_upsale' => Model_Catalog_Upsale::STATUS_UPSALE_EXPIRE
                ];

                $payment_data = OSC::decode($row['payment_data'], true);
                if (!empty($payment_data)) {
                    unset($payment_data['ba_token'], $payment_data['ba_id']);
                    $update_data = array_merge($update_data, ['payment_data' => OSC::encode($payment_data)]);
                }

                $DB->update($order_table, $update_data, 'master_record_id = ' . $row['master_record_id'], 1, 'update_item');
            } catch (Exception $ex) { }
        }

        $DB->free('fetch_orders');
    }
}