<?php

class OSC_Uploader_Handler_Xhr extends OSC_Uploader_Handler {

    public function getName() {
        return OSC::core('request')->headerGet('X-File-Name');
    }

    public function getSize() {
        if (isset($_SERVER["CONTENT_LENGTH"])) {
            return (int) $_SERVER["CONTENT_LENGTH"];
        } else {
            return 0;
        }
    }

    function save($path) {
        $input = fopen("php://input", "r");
        $temp = tmpfile();
        $real_size = stream_copy_to_stream($input, $temp);
        fclose($input);

        if ($real_size != $this->getSize()) {
            throw new Exception('The file size is not match');
        }

        $target = fopen($path, "w");
        fseek($temp, 0, SEEK_SET);
        stream_copy_to_stream($temp, $target);
        fclose($target);
    }

}
