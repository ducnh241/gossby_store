<?php

class Helper_Developer_Common extends OSC_Object
{
    public function clearProductTypeVariantName($word) {
        return strtolower(preg_replace("/\s+/", "", $word));
    }
}