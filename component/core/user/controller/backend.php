<?php

class Controller_User_Backend extends Abstract_User_Controller_Backend {

    public function actionIndex() {
        $this->forward('user/backend_member/list');
    }

}
