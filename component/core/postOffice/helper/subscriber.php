<?php

class Helper_PostOffice_Subscriber {

    public function saveEmailSubscriber($email, $full_name, $key, $content = array()){
        try {
            $subscriber = OSC::model('postOffice/subscriber');
            $confirm = 0;
            switch ($key) {
                case 'order' :
                    $flag = 3;
                    $confirm = 1;
                    break;
                case 'abandon' :
                    $flag = 2;
                    break;
                default:
                    $flag = 1;
                    break;
            }
            try {
                $subscriber->loadByEmail($email);
            } catch (Exception $ex) {
                if ($ex->getCode() !== 404) {
                    throw new Exception($ex->getMessage());
                }
                $subscriber->setData(['email' => $email, 'full_name' => $full_name, 'confirm' => $confirm])->save();
            }

            $data_update = [];
            if ($confirm > $subscriber->data['confirm']) {
                $data_update['confirm'] = $confirm;
            }
            if ($flag > $subscriber->data['flag_action']) {
                $data_update['flag_action'] = $flag;
            }
            if (($flag == 2 || $flag == 3) && $full_name) {
                $data_update['full_name'] = $full_name;
            }
            if (count($content) > 0) {
                $data_update['content'] = $subscriber->data['content'] == '' ? OSC::encode($content) : OSC::encode(array_merge(OSC::decode($subscriber->data['content']), $content));
            }
            if (count($data_update) > 0) {
                $subscriber->setData($data_update)->save();
            }
            return $subscriber;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

}
