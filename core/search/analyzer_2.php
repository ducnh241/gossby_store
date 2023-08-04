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

    protected $_chr_map_search = array('\\\\', '\\*', '\\|', '\\OR', '\\AND', '\\(', '\\)', '\\"');
    protected $_chr_map_replace = array('osc.chr.map.1', 'osc.chr.map.2', 'osc.chr.map.3', 'osc.chr.map.4', 'osc.chr.map.5', 'osc.chr.map.6', 'osc.chr.map.7', 'osc.chr.map.8');
    protected $_advanced_map = array();
    protected $_is_int = array();
    protected $_divide = array();
    protected $_multiply = array();
    protected $_is_tag = array();
    protected $_is_date = array();
    protected $_is_string = array();
    protected $_custom_type = array();
    protected $_default_advanced_map = array();
    protected $_highlight = array();
    protected $_highlight_data = array();
    protected $_validate = array();
    protected $_clause = array();
    protected $_advanced = array();
    protected $_append_if_not_exist = array();

    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_DATE = 'date';
    const TYPE_TAG = 'tag';

    /**
     * 
     * @param string $keyword
     * @param string $field
     * @param string $data_type
     * @param bool $is_default
     * @param bool $highlight_flag
     * @param array $options     
     * @return OSC_Search_Analyzer
     */
    public function addKeyword($keyword, $field, $data_type = self::TYPE_STRING, $is_default = true, $highlight_flag = true, $options = null) {
        $keyword = strtolower($keyword);

        $this->_advanced_map[$keyword] = $field;

        $advanced[] = $keyword;

        if (!is_array($options)) {
            $options = array();
        }

        if (!$data_type) {
            $date_type = self::TYPE_STRING;
        }

        if ($data_type == self::TYPE_INT) {
            $this->_is_int[$keyword] = 1;

            if (isset($options['divide'])) {
                $this->_divide[$keyword] = $options['divide'];
            } else if (isset($options['multiply'])) {
                $this->_multiply[$keyword] = $options['multiply'];
            }
        } else if ($data_type == self::TYPE_TAG) {
            $this->_is_tag[$keyword] = 1;
        } else if ($data_type == self::TYPE_DATE) {
            $this->_is_date[$keyword] = 1;
        } else if ($data_type == self::TYPE_STRING) {
            $this->_is_string[$keyword] = 1;
        } else {
            $this->_custom_type[$keyword] = $data_type;
        }

        if ($highlight_flag) {
            $this->_highlight[$keyword] = 1;
        }

        if (isset($options['validate'])) {
            $this->_validate[$keyword] = $options['validate'];
        }

        if (isset($options['append_if_not_exist'])) {
            $this->_append_if_not_exist[$keyword] = $options['append_if_not_exist'];
        }

        if ($is_default) {
            $this->_default_advanced_map[$keyword] = $field;
        }

        return $this;
    }

    public function parse($search_query) {
        $search_query = preg_replace("/osc\.(chr\.map|advanced|clause|search)\.\d+/i", '', $search_query);
        $search_query = str_replace($this->_chr_map_search, $this->_chr_map_replace, $search_query);

        $search_query = str_replace('|', ' OR ', $search_query);
        $search_query = str_replace('*', '%', $search_query);

        $keywords = implode('|', array_keys($this->_advanced_map));

        if (preg_match("/(\!?)({$keywords})\:\"(.+?)\"/ie", $search_query)) {
            $search_query = preg_replace("/(\!?)({$keywords})\:\"(.+?)\"/ie", "\$this->_advanced('\\2', '\\3', '\\1' )", $search_query);
        } else {
            $search_query = preg_quote($search_query);
            $search_query = str_replace($this->_chr_map_search, $this->_chr_map_replace, $search_query);
            $search_query = preg_replace("/(\!?)\"(.+?)\"/ie", "\$this->_allAdvanced('\\2', '\\1' )", $search_query);
        }

        while (preg_match("/\(([^\(\)]+?)\)/ie", $search_query)) {
            $search_query = preg_replace("/\(([^\(\)]+?)\)/ie", "\$this->_clause('\\1')", $search_query);
        }

        if (count($this->_advanced) || count($this->_clause)) {
            $search_query = $this->_safe($search_query);
        } else {
            $search_query = $this->_allAdvanced($search_query, 0);
        }

        while (preg_match('/osc\.clause\.([0-9]+)/i', $search_query)) {
            $search_query = preg_replace('/osc\.clause\.([0-9]+)/ie', "'( ' . \$this->_safe(\$this->_clause[\\1]) . ' )'", $search_query);
        }

        while (preg_match('/osc\.advanced\.([0-9]+)/i', $search_query)) {
            $search_query = preg_replace('/osc\.advanced\.([0-9]+)/ie', "\$this->_burshAdvanced('\\1')", $search_query);
        }

        if (count($this->_append_if_not_exist)) {
            foreach ($this->_append_if_not_exist as $keyword => $data) {
                $arr = array();

                foreach ($data as $val) {
                    if (isset($this->_is_int[$keyword])) {
                        $val = floatval($val);
                    } else {
                        $val = $this->_safeQuery($val);
                    }

                    $arr[] = "'{$val}'";
                }

                $insert[] = $this->_advanced_map[$keyword] . " IN (" . implode(',', $arr) . ")";
            }

            $search_query .= ' AND ' . implode(' AND ', $insert);
        }
        print_r($this->_clause);
        print_r($this->_advanced);
        return array('code' => trim($search_query), 'highlight_data' => $this->_highlight_data);
    }

    protected function _burshAdvanced($id) {
        if (!isset($this->_advanced[$id]) || !isset($this->_advanced_map[$this->_advanced[$id][1]])) {
            return '';
        }

        if (isset($this->_is_int[$this->_advanced[$id][1]])) {
            if (preg_match("/^(>|<)(\d+)$/", $this->_advanced[$id][2], $arr)) {
                return $this->_advanced_map[$this->_advanced[$id][1]] . " {$arr[1]} '{$arr[2]}'";
            } elseif (preg_match("/^(\d+)-(\d+)$/", $this->_advanced[$id][2], $arr)) {
                return $this->_advanced_map[$this->_advanced[$id][1]] . " BETWEEN '{$arr[1]}' AND '{$arr[2]}'";
            } else {
                return $this->_advanced_map[$this->_advanced[$id][1]] . " " . ( $this->_advanced[$id][0] ? 'NOT ' : '' ) . "IN (" . $this->_advanced[$id][2] . ")";
            }
        } else if (isset($this->_is_tag[$this->_advanced[$id][1]])) {
            return $this->_advanced_map[$this->_advanced[$id][1]] . " REGEXP " . ( $this->_advanced[$id][0] ? 'NOT ' : '' ) . "'[[:<:]]" . $this->_advanced[$id][2] . "[[:>:]]'";
        } else if (isset($this->_is_date[$this->_advanced[$id][1]])) {
            if ($this->_advanced[$id][0]) {
                return "( " . $this->_advanced_map[$this->_advanced[$id][1]] . " < '" . $this->_advanced[$id][2][0] . "' AND " . $this->_advanced_map[$this->_advanced[$id][1]] . " > '" . $this->_advanced[$id][2][1] . "' )";
            } else {
                return "( " . $this->_advanced_map[$this->_advanced[$id][1]] . " >= '" . $this->_advanced[$id][2][0] . "' AND " . $this->_advanced_map[$this->_advanced[$id][1]] . " <= '" . $this->_advanced[$id][2][1] . "' )";
            }
        } else if (isset($this->_is_string[$this->_advanced[$id][1]])) {
            return $this->_advanced_map[$this->_advanced[$id][1]] . " " . ( $this->_advanced[$id][0] ? 'NOT ' : '' ) . "LIKE '%" . $this->_safeQuery($this->_advanced[$id][2]) . "%'";
        } else if (isset($this->_custom_type[$this->_advanced[$id][1]])) {
            $callback = null;
            $callback_params = array();

            if (is_array($this->_custom_type[$this->_advanced[$id][1]]) && isset($this->_custom_type[$this->_advanced[$id][1]]['function'])) {
                $callback = $this->_custom_type[$this->_advanced[$id][1]]['function'];

                if (isset($this->_custom_type[$this->_advanced[$id][1]]['params'])) {
                    $callback_params = $this->_custom_type[$this->_advanced[$id][1]]['params'];
                }
            } else {
                $callback = $this->_custom_type[$this->_advanced[$id][1]];
            }

            array_unshift($callback_params, $this->_advanced[$id][1], $this->_advanced_map[$this->_advanced[$id][1]], $this->_advanced[$id][0], $this->_advanced[$id][2]);

            return call_user_func_array($callback, $callback_params);
        }
    }

    protected function _allAdvanced($search_query, $negative) {
        foreach ($this->_default_advanced_map as $keyword => $v) {
            $advanced[] = $this->_advanced($keyword, $search_query, $negative);
        }

        if (!count($advanced)) {
            return '';
        }

        return $this->_clause(implode(' OR ', $advanced));
    }

    protected function _clause($search_query) {
        static $i = 0;
        $i++;

        $this->_clause[$i] = $search_query;
        return ' osc.clause.' . $i . ' ';
    }

    protected function _safe($search_query) {
        $new_search_query = '';

        preg_replace('/(OR|AND|osc\.(clause|advanced|search)\.([0-9]+))/ie', "\$new_search_query .= '\\1 '", $search_query);

        while (preg_match('/(OR|AND)((\s?)+)(OR|AND)/i', $new_search_query)) {
            $new_search_query = preg_replace('/(OR|AND)((\s?)+)(OR|AND)/i', 'OR', $new_search_query);
        }

        while (preg_match('/(osc\.(clause|advanced|search)\.([0-9]+))((\s?)+)(osc\.(clause|advanced|search)\.([0-9]+))/i', $new_search_query)) {
            $new_search_query = preg_replace('/(osc\.(clause|advanced|search)\.([0-9]+))((\s?)+)(osc\.(clause|advanced|search)\.([0-9]+))/i', '\\1 OR \\5', $new_search_query);
        }

        $new_search_query = preg_replace('/(^|^)((\s?)+)(OR|AND)/i', '', $new_search_query);
        $new_search_query = preg_replace('/(OR|AND)((\s?)+)$/i', '', $new_search_query);

        return $new_search_query;
    }

    protected function _safeKeyword($str) {
        $str = $this->_safeQuery($str);
        $str = preg_quote($str);

        return $str;
    }

    protected function _safeQuery($str) {
        $str = str_replace($this->_chr_map_replace, $this->_chr_map_search, $str);
        $str = OSC::core('format')->clean($str);
        return $str;
    }

    protected function _validate($value, $keyword) {
        try {
            $validator = null;
            $callback_params = array();

            if (isset($this->_validate['in_array'])) {
                if (!is_array($this->_validate['in_array'])) {
                    $this->_validate['in_array'] = explode(',', $this->_validate['in_array']);
                }

                $validator = '_validateInArray';
                $callback_params[] = $this->_validate[$keyword]['in_array'];
            } else if (isset($this->_validate[$keyword]['callback'])) {
                $validator = $this->_validate[$keyword]['callback'];

                if (isset($this->_validate[$keyword]['params'])) {
                    $callback_params = $this->_validate[$keyword]['params'];
                }
            }

            if (!$validator) {
                return false;
            }

            array_unshift($callback_params, $id);

            $id = call_user_func_array($validator, $callback_params);
        } catch (Exception $ex) {
            return false;
        }

        return $id;
    }

    protected function _parseInt($keyword, $search_query, $negative) {
        if (preg_match("/^((>|<)(\d+)|(\d+)-(\d+))$/", $search_query)) {
            return false;
        }

        $ids = explode(',', $search_query);

        foreach ($ids as $idx => $id) {
            $ids[$idx] = round($id, 2);
        }

        $ids = array_unique($ids);

        if (isset($this->_validate[$keyword])) {
            $ids = $this->_validate($ids, $keyword);
        }

        if ($ids === false || count($ids) < 1) {
            return false;
        }

        $search_query = array();
        $highlight_key = array();

        foreach ($ids as $id) {
            $search_query[] = "'{$id}'";
            $highlight_key[] = $id;
        }

        if (isset($this->_highlight[$keyword]) && !$negative) {
            if (!isset($this->_highlight_data[$keyword])) {
                $this->_highlight_data[$keyword] = array();
            }

            $this->_highlight_data[$keyword] = array_merge($this->_highlight_data[$keyword], $highlight_key);
        }

        return implode(',', $search_query);
    }

    protected function _parseTag($keyword, $search_query, $negative) {
        $tags = OSC::core('string')->cleanTags($search_query);

        if (!$tags) {
            return false;
        }

        if (isset($this->_validate[$keyword])) {
            $tags = $this->_validate($tags, $keyword);
        }

        if ($tags === false || count($tags) < 1) {
            return false;
        }

        $search_query = implode('|', $tags);

        if (strpos($search_query, '|') !== false) {
            $search_query = "({$search_query})";
        }

        if (isset($this->_highlight[$keyword]) && !$negative) {
            if (!isset($this->_highlight_data[$keyword])) {
                $this->_highlight_data[$keyword] = array();
            }

            $this->_highlight_data[$keyword] = array_merge($this->_highlight_data[$keyword], $tags);
        }

        return $search_query;
    }

    protected function _parseDate($keyword, $search_query, $negative) {
        $search_query = str_replace(".", '/', $search_query);
        $search_query = preg_replace("/[^0-9\/\-]/", '', $search_query);
        $search_query = preg_replace("/\/{2,}/", '/', $search_query);
        $search_query = preg_replace("/-{2,}/", '-', $search_query);
        $search_query = preg_replace("/^(\/|-)|(\/|-)$/", '', $search_query);

        $search_query = explode('-', $search_query);

        if (count($search_query) == 2 && $search_query[0] == $search_query[1]) {
            $search_query = array($search_query[0]);
        }

        if (count($search_query) > 2) {
            return false;
        }

        if (count($search_query) == 2) {
            foreach ($search_query as $k => $v) {
                $_date = explode('/', $v);

                if (count($_date) == 3 && checkdate($_date[1], $_date[0], $_date[2])) {
                    $date[$k] = $_date;
                    $compare[$k] = $_date[2] . $_date[1] . $_date[0];
                } else {
                    $date[$k] = 0;
                    $compare[$k] = 0;
                }
            }

            if ($compare[0] <= 0 && $compare[1] <= 0) {
                return false;
            }

            if ($compare[0] > $compare[1]) {
                $_date = $date[0];
                $date[0] = $date[1];
                $date[1] = $_date;
            }

            $end_date = mktime(23, 59, 59, $date[1][1], $date[1][0], $date[1][2]);
            $start_date = is_array($date[0]) ? mktime(0, 0, 0, $date[0][1], $date[0][0], $date[0][2]) : 0;

            $search_query = array($start_date, $end_date);
        } else {
            $search_query = explode('/', $search_query[0]);

            if (count($search_query) != 3 || !checkdate($search_query[1], $search_query[0], $search_query[2])) {
                return false;
            }

            $start_date = mktime(0, 0, 0, $search_query[1], $search_query[0], $search_query[2]);
            $end_date = mktime(23, 59, 59, $search_query[1], $search_query[0], $search_query[2]);

            $search_query = array($start_date, $end_date);
        }

        if (isset($this->_validate[$keyword])) {
            $search_query = $this->_validate($search_query, $keyword);
        }

        if ($search_query === false || count($search_query) != 2) {
            return false;
        }

        if (isset($this->_highlight[$keyword]) && !$negative) {
            if (!isset($this->_highlight_data[$keyword])) {
                $this->_highlight_data[$keyword] = array();
            }

            $this->_highlight_data[$keyword][] = $this->_safeKeyword(implode('-', $search_query));
        }

        return $search_query;
    }

    protected function _parseString($keyword, $search_query, $negative) {
        if (isset($this->_validate[$keyword])) {
            $search_query = $this->_validate($search_query, $keyword);
        }

        if ($search_query === false) {
            return false;
        }

        if (isset($this->_highlight[$keyword]) && !$negative) {
            if (!isset($this->_highlight_data[$keyword])) {
                $this->_highlight_data[$keyword] = array();
            }

            $this->_highlight_data[$keyword][] = $this->_safeKeyword($search_query);
        }

        return $search_query;
    }

    protected function _advanced($keyword, $search_query, $negative) {
        static $i = 0;

        $keyword = strtolower($keyword);

        if ($this->_is_int[$keyword]) {
            $search_query = $this->_parseInt($keyword, $search_query, $negative);
        } elseif ($this->is_tag[$keyword]) {
            $search_query = $this->_parseTag($keyword, $search_query, $negative);
        } elseif ($this->is_date[$keyword]) {
            $search_query = $this->_parseDate($keyword, $search_query, $negative);
        } else {
            $search_query = $this->_parseString($keyword, $search_query, $negative);
        }

        if (!$search_query) {
            return '';
        }

        $i++;

        if (isset($this->_append_if_not_exist[$keyword])) {
            unset($this->_append_if_not_exist[$keyword]);
        }

        $this->_advanced[$i] = array($negative ? 1 : 0, $keyword, $search_query);

        return ' osc.advanced.' . $i . ' ';
    }

}
