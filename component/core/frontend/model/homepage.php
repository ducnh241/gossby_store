<?php

class Model_Frontend_Homepage extends Abstract_Core_Model {

    protected $_table_name = 'homepage';
    protected $_pk_field = 'id';

    public function getImageUrl($image) {
        return $image ? OSC::core('aws_s3')->getStorageUrl($image) : '';
    }

}
