<?php

class Helper_Post_Collection
{
    public function getAllCollection()
    {
        return OSC::model('post/collection')->getCollection()->sort('title')->load();
    }
}

