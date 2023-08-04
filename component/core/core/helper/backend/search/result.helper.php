<?php

class Helper_Core_Backend_Search_Result extends Abstract_Backend_Helper_Search_Result {

    public function render($doc) {
        $tpl = OSC::controller()->getTemplate();
        
        switch($doc['extra_key']) {
            case 'section':
                return $tpl->build('core/search/result/section', $doc);
                break;
            case 'module':
                return $tpl->build('core/search/result/module', $doc);
                break;
        }
    }

}
