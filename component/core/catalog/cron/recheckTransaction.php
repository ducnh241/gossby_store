<?php

class Cron_Catalog_RecheckTransaction extends OSC_Cron_Abstract {

    const CRON_TIMER = '*/15 * * * *';
    const TBL_NAME = 'catalog_payment_transaction_recheck';

    public function process($params, $queue_added_timestamp) {
        /* @var $DB OSC_Database */
        $DB = OSC::core('database');
        $DB_MASTER = OSC::core('database')->getAdapter('db_master');

        $DB->delete(static::TBL_NAME, 'requeue_counter >= 10', null, 'delete_transaction');

        while (true) {
            $timestamp = time();
            $timestamp_flag = $timestamp - 60 * 15;

            $DB->select('*', static::TBL_NAME, "(state_code = 0 OR (requeue_counter < 10 AND modified_timestamp < {$timestamp_flag})) AND added_timestamp < " . ($timestamp - (60 * 15)), 'added_timestamp ASC', 1, 'fetch_transaction');

            $row = $DB->fetchArray('fetch_transaction');

            if (!$row) {
                break;
            }

            $DB->query("UPDATE {$DB->getTableName(static::TBL_NAME)} SET state_code = 1, modified_timestamp = {$timestamp}" . ($row['state_code'] == 1 ? ', requeue_counter = (requeue_counter + 1)' : '') . " WHERE record_id = {$row['record_id']} AND (state_code = 0 OR (requeue_counter < 10 AND modified_timestamp < {$timestamp_flag})) LIMIT 1", null, 'lock_transaction');

            if ($DB->getNumAffected('lock_transaction') != 1) {
                break;
            }

            $row['transaction_data'] = OSC::decode($row['transaction_data'], true);

            $DB->begin();

            try {
                $DB->delete(static::TBL_NAME, 'record_id=' . $row['record_id'], 1, 'delete_transaction');

                $DB_MASTER->select('payment_method,payment_data,cart_ukey,order_id', 'catalog/order', "cart_ukey = '{$row['cart_ukey']}'", null, 1, 'fetch_order');

                $order = $DB_MASTER->fetchArray('fetch_order');

                $payment = $this->_verify($row, $order);

                if ($payment) {
                    $payment->void($row['transaction_data']['payment_data'], $row['transaction_data']['total_price'], $row['transaction_data']['currency_code'], $row['added_timestamp']);
                }

                $DB->commit();
            } catch (Exception $ex) {
                $DB->rollback();

                $DB->update(static::TBL_NAME, ['state_code' => 0, 'error_message' => $ex->getMessage()], 'record_id=' . $row['record_id'], 1, 'update_transaction');
            }
        }
    }

    /**
     * 
     * @param type $record
     * @param type $order
     * @return \Abstract_Catalog_Payment
     * @throws Exception
     */
    protected function _verify($record, $order) {
        $payment = '';

        switch ($record['transaction_data']['payment_method']['object']['type']) {
            case 'helper':
                $payment = OSC::helper($record['transaction_data']['payment_method']['object']['name'], OSC::makeUniqid());
                break;
            case 'core':
                $payment = OSC::core($record['transaction_data']['payment_method']['object']['name'], OSC::makeUniqid());
                break;
            case 'model':
                $payment = OSC::model($record['transaction_data']['payment_method']['object']['name'], OSC::makeUniqid());
                break;
            case 'controller':
                $payment = OSC::controller($record['transaction_data']['payment_method']['object']['name'], OSC::makeUniqid());
                break;
            case 'cron':
                $payment = OSC::cron($record['transaction_data']['payment_method']['object']['name'], OSC::makeUniqid());
                break;
            case 'class':
                $payment = new $record['transaction_data']['payment_method']['object']['name']();
                break;
            default:
                throw new Exception('Unable to detect payment object');
        }

        $payment->setAccount($record['transaction_data']['payment_method']['account']);

        if (!$order) {
            return $payment;
        }

        $order['payment_method'] = OSC::decode($order['payment_method'], true);
        $order['payment_data'] = OSC::decode($order['payment_data'], true);

        if ($order['payment_method']['key'] != $record['transaction_data']['payment_method']['key'] || $order['payment_method']['account']['id'] != $record['transaction_data']['payment_method']['account']['id']) {
            return $payment;
        }

        if (!$payment->compareTransaction($record['transaction_data']['payment_data'], $order['payment_data'])) {
            return $payment;
        }

        return null;
    }

}
