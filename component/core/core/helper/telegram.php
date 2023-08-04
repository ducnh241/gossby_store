<?php

class Helper_Core_Telegram {
    public function send($message){
        try {
            $telegram_group_id  = OSC::helper('core/setting')->get('warning_estimate_cron_running/telegram_group_id');
            if ($telegram_group_id && $telegram_group_id != '') {
                $this->sendMessage($message, $telegram_group_id);
            }
        } catch (Exception $ex) {

        }
    }

    public function sendMessage($message, $telegram_group_id = '', $token_bot = null) {
        try {
            $DB = OSC::core('database')->getWriteAdapter();

            $DB->insert('core_notify_queue', [
                'queue_data' => OSC::encode([
                    'message' => $message,
                    'telegram_group_id' => $telegram_group_id,
                    'token_bot' => $token_bot,
                ]),
                'added_timestamp' => time()
            ], 'insert_notify_queue');

        } catch (Exception $ex) {

        }
    }

    /**
     * @param $message
     * @param string $telegram_group_id
     * @param null $token_bot
     * @throws Exception
     */
    public function sendDirectMessage($message, $telegram_group_id = '', $token_bot = null) {
        try {
            if (isset($token_bot) && trim($token_bot) != '') {
                $_token_bot = $token_bot;
            } else {
                $_token_bot = '1151351654:AAGiQushIasj2ZlDRmqB42Mv8IFEfvhkbDc';
            }

            $params = [
                'chat_id' => $telegram_group_id,
                'text' => $message,
                'parse_mode' => 'markdown',
                'disable_web_page_preview' => true,
            ];

            $ch = curl_init("https://api.telegram.org/bot" . $_token_bot . '/sendMessage');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage(), $ex->getCode());
        }
    }

}
