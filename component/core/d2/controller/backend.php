<?php

class Controller_D2_Backend extends Abstract_Backend_Controller {

    public function actionIndex() {
        $this->forward('d2/backend_product/list');
    }
}
