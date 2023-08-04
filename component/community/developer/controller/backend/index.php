<?php

class Controller_Developer_Backend_Index extends Abstract_Backend_Controller
{
    public function __construct() {
        parent::__construct();
        $this->checkPermission('developer');
    }

    public function actionIndex()
    {
        $tpl = $this->getTemplate();
        $tpl->addBreadcrumb('Developer Homepage', $this->getUrl('*/*/*'));
        $tpl->setPageTitle('Developer');
        $this->output($tpl->build('developer/index'));
    }
}