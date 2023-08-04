<?php

class Model_Catalog_PrintTemplate_MockupRel_Collection extends Abstract_Core_Model_Collection {
    public function getByTemplateId($template_id, $type_flag = 0) {
        if (intval($template_id) < 1) {
            throw new Exception('template_id is empty');
        }

        return $this->addCondition('print_template_id', intval($template_id), OSC_Database::OPERATOR_EQUAL)
            ->addCondition('status' , 1, OSC_Database::OPERATOR_EQUAL)
            ->addCondition('type_flag' , $type_flag, OSC_Database::OPERATOR_EQUAL)
            ->load();
    }

    public function getListIdMockupRemove() {
        return $this->addField('id')->addCondition('status' , 0, OSC_Database::OPERATOR_EQUAL)->load();
    }
}