<?php

class Controller_Catalog_Backend extends Abstract_Catalog_Controller_Backend {

    public function actionIndex() {
        $this->forward('catalog/backend_product/list');
    }
}
