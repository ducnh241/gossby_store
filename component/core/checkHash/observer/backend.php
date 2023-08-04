<?php
class Observer_CheckHash_Backend {
    public function hashVerifyFailed(){
        $this->backupRequestFail();
    }

    public function backendAuthFailed(){
        $this->backupRequestFail('auth_failed');
    }

    public function backupRequestFail($key = null){
        if (OSC::core('request')->isAjax()){
            return;
        }
        if ($key == 'auth_failed'){
            OSC::register('system_auth_failed', 1);
        }

        $requets = OSC::core('request')->getAll();

        if (isset($requets['t'])){
            unset($requets['t']);
        }

        if (is_array($requets) && count($requets) > 0){
            unset($requets['hash']);

            if(isset($requets['err'])){
                unset($requets['err']);
            }

            OSC::sessionSet('checkSystemHashFail', OSC::encode(['t' => OSC::$request_path,'params' => $requets]));
        }
    }
}
