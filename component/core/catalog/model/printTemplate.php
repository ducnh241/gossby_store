<?php

class Model_Catalog_PrintTemplate extends Abstract_Core_Model {
    protected $_table_name = 'print_template';
    protected $_pk_field = 'id';

    /**
     * @var Model_Catalog_PrintTemplate_MockupRel
     */
    protected $_default_mockup_rel = null;

    protected function _preDataForUsing(&$data) {
        parent::_preDataForUsing($data);

        foreach (['config', 'merge_config', 'mockup_config'] as $key) {
            if (isset($data[$key])) {
                $data[$key] = OSC::decode($data[$key], true);
            }
        }

        //Parse preview_config's layers
        if (isset($data['config']['preview_config']) && is_array($data['config']['preview_config']) && !empty($data['config']['preview_config'])) {
            foreach ($data['config']['preview_config'] as &$config_layer) {
                if (isset($config_layer['layer']) && !empty($config_layer['layer'])) {
                    foreach ($config_layer['layer'] as &$layer) {
                        if (!empty($layer) && $layer !== 'main') {
                            $layer = OSC_CMS_BASE_URL . '/resource/template/core/image/' . $layer;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param false $reset
     * @return Model_Catalog_PrintTemplate_MockupRel|null
     */
    public function getDefaultMockupRel($reset = false) {
        if($this->_default_mockup_rel === null || $reset) {
            $this->_default_mockup_rel = OSC::model('catalog/printTemplate_mockupRel')->getCollection()->addCondition('print_template_id', $this->getId())->addCondition('is_default_mockup', 1)->addCondition('status', 1)->setLimit(1)->load()->getItem();
        }

        return $this->_default_mockup_rel;
    }

    /**
     * @param Model_Catalog_PrintTemplate_MockupRel $mockup_rel
     * @return $this
     */
    public function setDefaultMockupRel(Model_Catalog_PrintTemplate_MockupRel $mockup_rel) {
        $this->_default_mockup_rel = $mockup_rel;
        return $this;
    }
}