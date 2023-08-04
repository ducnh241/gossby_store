<?php

class Model_Filter_Collection_Collection extends Abstract_Core_Model_Collection {
    public function getFilterByCollectionId($collection_id) {
        $collection_id = intval($collection_id);

        if ($collection_id < 0) {
            return null;
        }

        $collection = $this->addCondition('collection_id', $collection_id, OSC_Database::OPERATOR_EQUAL)
            ->load();

        if ($collection->length() < 1) {
            return null;
        }

        return  $collection->getItem();
    }
}
