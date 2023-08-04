<?php

class Model_Filter_AutoTag_Collection extends Abstract_Core_Model_Collection {

    /**
     * @return $this
     * @throws OSC_Exception_Runtime
     */
    public function preLoadTags() {
        if ($this->length() < 1) {
            return $this;
        }

        /* @var $autoTag Model_Filter_AutoTag */
        foreach ($this as $autoTag) {
            $tag_ids = array_merge($autoTag->data['auto_tag'], $autoTag->data['deleted_tag'] ?? [], $autoTag->data['new_tag'] ?? []);
            $tags = OSC::model('filter/tag')->getCollection()->load($tag_ids);
            $autoTag->setTags($tags);
        }

        return $this;
    }

}
