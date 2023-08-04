<?php

class Model_Frontend_HtmlPage extends Abstract_Core_Model {

    protected $_table_name = 'frontend_html_page';
    protected $_pk_field = 'page_id';

    public function getDetailUrl() {
        return OSC::getUrl('frontend/index/htmlPage', ['id' => $this->getId()]);
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        if(isset($data['page_config'])) {
            $data['page_config'] = OSC::encode($data['page_config']);
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        if(isset($data['page_config'])) {
            $data['page_config'] = OSC::decode($data['page_config']);
        }
    }
}
