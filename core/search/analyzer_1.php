<?php

/**
 * OSECORE
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License version 3
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@osecore.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade OSECORE to newer
 * versions in the future. If you wish to customize OSECORE for your
 * needs please refer to http://www.osecore.com for more information.
 *
 * @copyright	Copyright (C) 2011 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Object
 *
 * @package OSECORE_Core_Search_Analyzer
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Search_Analyzer {

    private $clause = array();
    private $advanced = array();
    private $advanced_map = array();
    private $advanced_map_all = array();
    private $custom_call_back = array();
    private $custom_call_back_params = array();
    private $check = array();
    private $is_int = array();
    private $is_float = array();
    private $is_tag = array();
    private $is_date = array();
    private $val_in = array();
    private $is_key = array();
    private $multiply = array();
    private $divide = array();
    private $keywords = array();
    private $keyMap = array();
    private $chr_map_search = array('\\\\', '\\*', '\\|', '\\OR', '\\AND', '\\(', '\\)', '\\"');
    private $chr_map_replace = array('osc.chr.map.1', 'osc.chr.map.2', 'osc.chr.map.3', 'osc.chr.map.4',
        'osc.chr.map.5', 'osc.chr.map.6', 'osc.chr.map.7', 'osc.chr.map.8');

    private function resetParam() {
        $this->clause = array();
        $this->advanced = array();
        $this->advanced_map = array();
        $this->advanced_map_all = array();
        $this->custom_call_back = array();
        $this->custom_call_back_params = array();
        $this->check = array();
        $this->is_int = array();
        $this->is_float = array();
        $this->is_tag = array();
        $this->is_date = array();
        $this->val_in = array();
        $this->is_key = array();
        $this->multiply = array();
        $this->divide = array();
        $this->keywords = array();
        $this->keyMap = array();
    }

    public function process($str, $advanceds, $isRaw = false) {
        $this->resetParam();

        if ($str == '') {
            return false;
        }

        if (!$isRaw) {
            $str = OSC::core('format')->unClean($str);
        }

        foreach ($advanceds as $k => $v) {
            $k = strtolower($k);

            $this->advanced_map[$k] = $v['field'];

            $advanced[] = $k;

            if (isset($v['custom_call_back'])) {
                if (isset($v['custom_call_back_params'])) {
                    $this->custom_call_back_params[$k] = $v['custom_call_back_params'];
                }

                $this->custom_call_back[$k] = $v['custom_call_back'];
            } elseif ($v['int']) {
                $this->is_int[$k] = 1;

                if ($v['divide']) {
                    $this->divide[$k] = $v['divide'];
                } elseif ($v['multiply']) {
                    $this->multiply[$k] = $v['multiply'];
                }
            } elseif ($v['tag']) {
                $this->is_tag[$k] = 1;
            } elseif ($v['date']) {
                $this->is_date[$k] = 1;
            }

            if (!$v['skipkey']) {
                $this->is_key[$k] = 1;
            }

            if (count($v['val'])) {
                if ($v['check']) {
                    $this->check[$k] = 1;
                }

                $this->val_in[$k] = $v['val'];
            }

            if (!$v['skip']) {
                $this->advanced_map_all[$k] = $v['field'];
            }
        }

        $advanced = implode('|', $advanced);

        $str = preg_replace("@osc\.(chr\.map|advanced|clause|search)\.[0-9]+@i", '', $str);
        $str = str_replace($this->chr_map_search, $this->chr_map_replace, $str);

        $str = str_replace("|", ' OR ', $str);
        $str = str_replace("*", '%', $str);

        if (preg_match("@(\!?)({$advanced})\:\"(.+?)\"@ie", $str)) {
            $str = preg_replace("@(\!?)({$advanced})\:\"(.+?)\"@ie", "\$this->advanced('\\2', '\\3', '\\1' )", $str);
        } else {
            $str = OSC::core('string')->cleanPattern($str);
            $str = str_replace($this->chr_map_search, $this->chr_map_replace, $str);
            $str = preg_replace("@(\!?)\"(.+?)\"@ie", "\$this->all_advanced('\\2', '\\1' )", $str);
        }

        while (preg_match("@\(([^\(\)]+?)\)@ie", $str)) {
            $str = preg_replace("@\(([^\(\)]+?)\)@ie", "\$this->clause('\\1')", $str);
        }

        if (count($this->advanced) || count($this->clause)) {
            $str = $this->safe($str);
        } else {
            $str = $this->all_advanced($str, 0);
        }

        while (preg_match("@osc\.clause\.([0-9]+)@i", $str)) {
            $str = preg_replace("@osc\.clause\.([0-9]+)@ie", "'( ' . \$this->safe(\$this->clause[\\1]) . ' )'", $str);
        }

        while (preg_match("@osc\.advanced\.([0-9]+)@i", $str)) {
            $str = preg_replace("@osc\.advanced\.([0-9]+)@ie", "\$this->bursh_advanced('\\1')", $str);
        }

        if (count($this->check)) {
            foreach ($this->check as $adv => $k) {
                foreach ($this->val_in[$adv] as $val) {
                    if ($this->is_int[$adv]) {
                        $val = floatval($val);
                    } else {
                        $val = $this->safe_query($val);
                    }

                    $arr[] = "'{$val}'";
                }

                $insert[] = $this->advanced_map[$adv] . " IN (" . implode(',', $arr) . ")";
            }

            $str .= ' AND ' . implode(' AND ', $insert);
        }

        return array('code' => trim($str), 'key' => $this->keywords);
    }

    private function safe($str) {
        $new_str = '';

        preg_replace("@(OR|AND|osc\.(clause|advanced|search)\.([0-9]+))@ie", "\$new_str .= '\\1 '", $str);

        while (preg_match("@(OR|AND)((\s?)+)(OR|AND)@i", $new_str)) {
            $new_str = preg_replace("@(OR|AND)((\s?)+)(OR|AND)@i", 'OR', $new_str);
        }

        while (preg_match("@(osc\.(clause|advanced|search)\.([0-9]+))((\s?)+)(osc\.(clause|advanced|search)\.([0-9]+))@i", $new_str)) {
            $new_str = preg_replace("@(osc\.(clause|advanced|search)\.([0-9]+))((\s?)+)(osc\.(clause|advanced|search)\.([0-9]+))@i", '\\1 OR \\5', $new_str);
        }

        $new_str = preg_replace("@(^|^)((\s?)+)(OR|AND)@i", '', $new_str);
        $new_str = preg_replace("@(OR|AND)((\s?)+)$@i", '', $new_str);

        return $new_str;
    }

    private function safe_keyword($str) {
        $str = $this->safe_query($str);
        $str = OSC::core('string')->cleanPattern($str);
        return $str;
    }

    private function safe_query($str) {
        $str = str_replace($this->chr_map_replace, $this->chr_map_search, $str);
        $str = OSC::core('format')->clean($str);
        return $str;
    }

    private function bursh_advanced($id) {
        if ($this->advanced[$id]) {
            if ($this->advanced_map[$this->advanced[$id][1]]) {
                if ($this->custom_call_back[$this->advanced[$id][1]]) {
                    return call_user_func($this->custom_call_back[$this->advanced[$id][1]], $this->advanced[$id][1], $this->advanced_map[$this->advanced[$id][1]], $this->advanced[$id][0], $this->advanced[$id][2], isset($this->custom_call_back_params[$this->advanced[$id][1]]) ? $this->custom_call_back_params[$this->advanced[$id][1]] : null);
                } elseif ($this->is_int[$this->advanced[$id][1]]) {
                    if (preg_match("/^(>|<)(\d+)$/", $this->advanced[$id][2], $arr)) {
                        return $this->advanced_map[$this->advanced[$id][1]] . " {$arr[1]} '{$arr[2]}'";
                    } elseif (preg_match("/^(\d+)-(\d+)$/", $this->advanced[$id][2], $arr)) {
                        return $this->advanced_map[$this->advanced[$id][1]] . " BETWEEN '{$arr[1]}' AND '{$arr[2]}'";
                    } else {
                        return $this->advanced_map[$this->advanced[$id][1]] . " " . ( $this->advanced[$id][0] ? 'NOT ' : '' ) . "IN (" . $this->advanced[$id][2] . ")";
                    }
                } elseif ($this->is_tag[$this->advanced[$id][1]]) {
                    return $this->advanced_map[$this->advanced[$id][1]] . " REGEXP " . ( $this->advanced[$id][0] ? 'NOT ' : '' ) . "'[[:<:]]" . $this->advanced[$id][2] . "[[:>:]]'";
                } elseif ($this->is_date[$this->advanced[$id][1]]) {
                    if ($this->advanced[$id][0]) {
                        return "( " . $this->advanced_map[$this->advanced[$id][1]] . " < '" . $this->advanced[$id][2][0] . "' AND " . $this->advanced_map[$this->advanced[$id][1]] . " > '" . $this->advanced[$id][2][1] . "' )";
                    } else {
                        return "( " . $this->advanced_map[$this->advanced[$id][1]] . " >= '" . $this->advanced[$id][2][0] . "' AND " . $this->advanced_map[$this->advanced[$id][1]] . " <= '" . $this->advanced[$id][2][1] . "' )";
                    }
                } else {
                    return $this->advanced_map[$this->advanced[$id][1]] . " " . ( $this->advanced[$id][0] ? 'NOT ' : '' ) . "LIKE '%" . $this->safe_query($this->advanced[$id][2]) . "%'";
                }
            }
        }
    }

    private function all_advanced($str, $not) {
        foreach ($this->advanced_map_all as $adv => $v) {
            $advanced[] = $this->advanced($adv, $str, $not);
        }

        if (!count($advanced)) {
            return '';
        }

        return $this->clause(implode(' OR ', $advanced));
    }

    private function clause($str) {
        static $i = 0;
        $i++;

        $this->clause[$i] = $str;
        return ' osc.clause.' . $i . ' ';
    }

    private function advanced($adv, $str, $not) {
        static $i = 0;

        $adv = strtolower($adv);

        if ($this->is_int[$adv]) {
            if (!preg_match("/^((>|<)(\d+)|(\d+)-(\d+))$/", $str)) {
                $ids = explode(',', $str);

                $str = array();
                $key = array();

                foreach ($ids as $id) {
                    $id = round($id, 2);

                    if ($this->val_in[$adv]) {
                        if (in_array($id, $this->val_in[$adv])) {
                            $str[] = "'{$id}'";
                            $key[] = $id;
                        }
                    } else {
                        $str[] = "'{$id}'";
                        $key[] = $id;
                    }
                }

                if (!count($str)) {
                    return '';
                }

                if ($this->is_key[$adv] && !$not) {
                    if (count($this->keywords[$adv])) {
                        $this->keywords[$adv] = array_merge($this->keywords[$adv], $key);
                    } else {
                        $this->keywords[$adv] = $key;
                    }
                }

                $str = implode(',', $str);
            }
        } elseif ($this->is_tag[$adv]) {
            $str = OSC::core('string')->cleanTags($str);

            if (!$str) {
                return '';
            }

            $str = implode('|', $str);

            if (strpos($str, '|') !== false) {
                $str = "({$str})";
            }

            if ($this->is_key[$adv] && !$not) {
                $this->keywords[$adv][] = $this->safe_keyword($str);
            }
        } elseif ($this->is_date[$adv]) {
            $str = str_replace(".", '/', $str);
            $str = preg_replace("/[^0-9\/\-]/", '', $str);
            $str = preg_replace("/\/{2,}/", '/', $str);
            $str = preg_replace("/-{2,}/", '-', $str);
            $str = preg_replace("/^(\/|-)|(\/|-)$/", '', $str);

            $str = explode('-', $str);

            if (count($str) == 2 && $str[0] == $str[1]) {
                $str = array(0 => $str[0]);
            }

            if (count($str) < 3) {
                if (count($str) == 2) {
                    foreach ($str as $k => $v) {
                        $_date = explode('/', $v);

                        if (count($_date) == 3 && checkdate($_date[1], $_date[0], $_date[2])) {
                            $date[$k] = $_date;
                            $compare[$k] = $_date[2] . $_date[1] . $_date[0];
                        } else {
                            $date[$k] = 0;
                            $compare[$k] = 0;
                        }
                    }

                    if ($compare[0] > 0 || $compare[1] > 0) {
                        if ($compare[0] > $compare[1]) {
                            $_date = $date[0];
                            $date[0] = $date[1];
                            $date[1] = $_date;
                        }

                        $end_date = mktime(23, 59, 59, $date[1][1], $date[1][0], $date[1][2]);
                        $start_date = is_array($date[0]) ? mktime(0, 0, 0, $date[0][1], $date[0][0], $date[0][2]) : 0;

                        $str = array($start_date, $end_date);
                    } else {
                        return '';
                    }
                } else {
                    $str = explode('/', $str[0]);

                    if (count($str) == 3 && checkdate($str[1], $str[0], $str[2])) {
                        $start_date = mktime(0, 0, 0, $str[1], $str[0], $str[2]);
                        $end_date = mktime(23, 59, 59, $str[1], $str[0], $str[2]);

                        $str = array($start_date, $end_date);
                    } else {
                        return '';
                    }
                }
            }

            if ($this->is_key[$adv] && !$not) {
                $this->keywords[$adv][] = $this->safe_keyword(implode('-', $str));
            }
        } else {
            if ($this->val_in[$adv]) {
                if (!in_array($str, $this->val_in[$adv])) {
                    return '';
                }
            }

            if ($this->is_key[$adv] && !$not) {
                $this->keywords[$adv][] = $this->safe_keyword($str);
            }
        }

        $i++;

        if ($this->check[$adv]) {
            unset($this->check[$adv]);
        }

        $this->advanced[$i] = array($not != '' ? 1 : 0, $adv, $str);

        return ' osc.advanced.' . $i . ' ';
    }

}
