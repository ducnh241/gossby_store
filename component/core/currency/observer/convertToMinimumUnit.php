<?php

class Observer_Currency_ConvertToMinimumUnit extends OSC_Object {

    public function USD($value) {
        return intval(round(round($value, 2) * 100, 0));
    }

}
