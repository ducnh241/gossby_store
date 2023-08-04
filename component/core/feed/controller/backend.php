<?php

class Controller_Feed_Backend extends Abstract_Backend_Controller
{

    public function actionIndex()
    {
        $this->forward('feed/backend_block/google');
    }
}
