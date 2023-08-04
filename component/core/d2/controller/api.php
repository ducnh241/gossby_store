<?php

class Controller_D2_Api extends Abstract_Core_Controller_Api {

    public function actionSyncAirtableOrderResend() {

        try {
            OSC::core('cron')->addQueue('d2/syncAirtableOrderResend', null, ['ukey' => 'd2/syncAirtableOrderResend', 'requeue_limit' => -1, 'estimate_time' => 60]);
            $this->_ajaxResponse();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

    public function actionUpdateAirtable() {
        try {
            OSC::core('cron')->addQueue('d2/updateAirtable', null, ['ukey' => 'd2/updateAirtable', 'requeue_limit' => -1, 'skip_realtime', 'estimate_time' => 60]);
            $this->_ajaxResponse();
        } catch (Exception $ex) {
            $this->_ajaxError($ex->getMessage());
        }
    }

}
