<?php

class OSC_Uploader_Handler_Form extends OSC_Uploader_Handler {

    public function getName() {
        return $_FILES['file']['name'];
        ;
    }

    public function getSize() {
        return $_FILES['file']['size'];
    }

    function save($path) {
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $path)) {
            throw new Exception('The file can not save');
        }

        chown($path, OSC_FS_USERNAME);
        chgrp($path, OSC_FS_USERNAME);
    }

}
