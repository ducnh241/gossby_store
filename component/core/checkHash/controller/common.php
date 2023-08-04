<?php
class Controller_CheckHash_Common extends Abstract_Core_Controller{
    public function actionRestore(){
        $action = OSC::sessionGet('checkSystemHashFail');

        if (isset($action)) {
            if ($this->_request->get('cancel')) {
                OSC::sessionRemove('checkSystemHashFail');
                OSC::register('system_auth_failed', null);
                $this->forward('backend/index/index');
            }

            OSC::register('system_auth_failed', null);

            $action = OSC::sessionGet('checkSystemHashFail');

            OSC::sessionRemove('checkSystemHashFail');

            if (isset($action) && $action != ''){
                $action = OSC::decode($action , true);
                $this->_request->sets($action['params'],'post');
                $this->forward($action['t']);
            }
        }
    }

}
