<?php
abstract class OSC_Uploader_Handler {
    abstract public function getName();
    abstract public function getSize();
    abstract public function save($path);
}
?>
