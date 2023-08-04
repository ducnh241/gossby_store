<?php
OSC::systemRegister('FIREBASE_PROJECT_ID', 'tasklist-9f7c5');
OSC::systemRegister('FIREBASE_SENDER_ID', '1005454267956');
OSC::systemRegister('FIREBASE_API_KEY', 'AIzaSyBz5d6I5pjoUkKYo46_KMJp8rR1MZlyuCk');
OSC::systemRegister('FIREBASE_WEB_PUSH_CERTIFICATES', 'BChhWdpW5Jkn-CJ_FO8f12Ukqa1mzGitaeTh0lBuoGVTV4TxjIsZ-f2DZS-0WrwfWxbNduDikE5J7hUaVEGedqo');
OSC::systemRegister('FIREBASE_SERVER_KEY', 'AAAA6hm-ljQ:APA91bEsIRaoOs1tpxJy53rj6S5-LpO953Xm1-Lnb2K4ZZSnIKRqxebaR12Duy-yHb4C8dIP0HDgYZnCWbQ6D41--x4yq1Ojy7zRI0dkG0c4dsmFgZHr4iYvke9daHh723gGGhkOI5uK');

OSC_Controller::registerBind('firebase', 'firebase');

OSC_Template::registerComponent(
        'firebase', array(
    'type' => 'template',
    'data' => array(
        '[core]firebase/register',
    )
        )
);