<?php

class Model_Catalog_PrintTemplate_MockupRel extends Abstract_Core_Model {

    protected $_table_name = 'print_template_mockup_rel';
    protected $_pk_field = 'id';

    const MOCKUP_RENDER_BY_SYSTEM = 'system';
    const MOCKUP_DEFAULT = 'default';

    /**
     * @var Model_Catalog_PrintTemplate
     */
    protected $_print_template_model = null;

    /**
     * @var Model_Catalog_Mockup
     */
    protected $_mockup_model = null;

    /**
     * @param Model_Catalog_PrintTemplate $print_template
     * @return $this
     */
    public function setPrintTemplate(Model_Catalog_PrintTemplate $print_template) {
        $this->_print_template_model = $print_template;

        return $this;
    }

    /**
     * @param false $reset_flag
     * @return Model_Catalog_PrintTemplate
     * @throws OSC_Database_Model_Exception
     */
    public function getPrintTemplate($reset_flag = false) {
        if ($this->_print_template_model === null || $reset_flag) {
            $this->_print_template_model = $reset_flag ? OSC::model('catalog/printTemplate')->load($this->data['print_template_id']) : static::getPreLoadedModel('catalog/printTemplate', $this->data['print_template_id']);
        }

        return $this->_print_template_model;
    }

    /**
     * @param Model_Catalog_Mockup $mockup
     * @return $this
     */
    public function setMockup(Model_Catalog_Mockup $mockup) {
        $this->_mockup_model = $mockup;

        return $this;
    }

    /**
     * @param false $reset_flag
     * @return Model_Catalog_Mockup
     * @throws OSC_Database_Model_Exception
     */
    public function getMockup($reset_flag = false) {
        if ($this->_mockup_model === null || $reset_flag) {
            $this->_mockup_model = $reset_flag ? OSC::model('catalog/mockup')->load($this->data['mockup_id']) : static::getPreLoadedModel('catalog/mockup', $this->data['mockup_id']);
        }

        return $this->_mockup_model;
    }

    protected function _preDataForSave(&$data) {
        parent::_preDataForSave($data);

        foreach (['variant_product_type_ids', 'additional_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::encode($data[$key]);
            }
        }
    }

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['variant_product_type_ids', 'additional_data'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }
    }

}