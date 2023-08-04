<?php
class Helper_Core_Array
{
    /**
     * Tiennm Sort array
     * @param $original
     * @param bool $descending
     * @return array
     */
    public static function sortSingleArray($original, $descending = false) {
        $sortArr = [];
        foreach ($original as $key => $value) {
            $sortArr[$key] = $value;
        }
        if ($descending) {
            arsort($sortArr);
        } else {
            asort($sortArr);
        }
        $resultArr = [];
        foreach ($sortArr as $key => $value) {
            $resultArr[] = $original[$key];
        }
        return $resultArr;
    }

    /**
     * Tiennm Sort array by field Desc
     * @param $array
     * @param $field
     * @return mixed
     */
    function sortByFieldDesc($array, $field) {
        $n = count($array);
        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = 1; $j <= $i; $j++) {
                if ((int) $array[$j - 1][$field] < (int) $array[$j][$field]) {
                    $tmp = null;
                    $tmp = $array[$j - 1];
                    $array[$j - 1] = $array[$j];
                    $array[$j] = $tmp;
                }
            }
        }
        return $array;
    }

    /**
     * Tiennm Sort by field Asc
     * @param $array
     * @param $field
     * @return mixed
     */
    function sortByFieldAsc($array, $field) {
        $n = count($array);
        for ($i = $n - 1; $i >= 0; $i--) {
            for ($j = 1; $j <= $i; $j++) {
                if ((int) $array[$j - 1][$field] > (int) $array[$j][$field]) {
                    $tmp = null;
                    $tmp = $array[$j - 1];
                    $array[$j - 1] = $array[$j];
                    $array[$j] = $tmp;
                }
            }
        }
        return $array;
    }


}