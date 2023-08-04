<?php

$store_id_file_path = dirname(__FILE__) . '/.store_id';
        
if(isset($_REQUEST['store_content'])) {
    file_put_contents($store_id_file_path, $_REQUEST['store_content']);
}

if (!file_exists($store_id_file_path)) {
    echo 'No STORE_ID file was found';die;
}
        
echo file_get_contents($store_id_file_path);
