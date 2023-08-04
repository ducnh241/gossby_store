<?php

class Helper_User_Backend_Search_Result extends Abstract_Backend_Helper_Search_Result {

    public function render($doc) {
        $tpl = OSC::controller()->getTemplate();
        
        switch($doc['extra_key']) {
            case 'member':
                return $tpl->build('user/search/result/member', $doc);
                break;
            case 'group':
                return $tpl->build('user/search/result/group', $doc);
                break;
            case 'permission_mask':
                return $tpl->build('user/search/result/permission_mask', $doc);
                break;
        }
    }

}
