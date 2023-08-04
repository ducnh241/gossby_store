<?php

class Controller_Marketing_Backend extends Abstract_Backend_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->checkPermission('marketing');

        $this->getTemplate()->setCurrentMenuItemKey('marketing')->addBreadcrumb(array('marketing', 'Marketing'), $this->getUrl('marketing/backend/index'));
    }

    public function actionIndex() {

    }
}