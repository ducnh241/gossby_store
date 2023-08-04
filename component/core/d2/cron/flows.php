<?php

class Cron_D2_Flows extends OSC_Cron_Abstract {
    /**
     * @param $params
     * @param $queue_added_timestamp
     * @return void
     */
    public function process($params, $queue_added_timestamp)
    {
        try {
            $order_id = $params['order_id'];
            $order_item_id = $params['order_item_id'];
            $data = [
                'orderId' => $order_id,
                'code' => $params['order_code'],
                'orderItemId' => $order_item_id,
                'layers' => $params['layers']
            ];

            $command = "python3 " . dirname(__FILE__) . "/flowProducer.py -d '" . OSC::encode($data) . "' -k " . OSC::makeUniqid() . " -r " . OSC_SITE_PATH . " 2>&1";
            exec($command, $output, $return);

            if ($return > 0) {
                $message = OSC::encode($output) . ' - ' . $command;
                OSC::helper('d2/common')->writeLog($order_id, $order_item_id, $message, 'Run queue error');
                throw new Exception($message);
            }
        } catch (Exception $ex) {
            OSC::helper('core/common')->writeLog('D2_flows error', $ex->getMessage(), $data);

            //Channel New Things
            OSC::helper('core/telegram')->sendMessage($ex->getMessage(), '-409036884');

            throw $ex;
        }
    }
}