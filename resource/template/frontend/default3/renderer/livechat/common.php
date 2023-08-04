<?php
/* @var $this Helper_Frontend_Template */
$is_display_live_chat = OSC::helper('frontend/frontend')->checkIsPageDisplayLiveChat();

if ($is_display_live_chat && intval(OSC::registry('is_ticket_page')) != 1) {
    $this->push('liveChat/common.js', 'js');
}
?>