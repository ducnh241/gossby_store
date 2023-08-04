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

    protected $_keyword_data = array();
    protected $_default_keyword = array();
    protected $_clause = array();
    protected $_condition = array();
    protected $_highlight_data = array();

    /**
     *
     * @var OSC_Database_Condition 
     */
    protected $_condition_obj = null;

    const TYPE_INT = 'int';
    const TYPE_STRING = 'string';
    const TYPE_DATE = 'date';
    const TYPE_TAG = 'tag';
    const TYPE_STATE = 'state';

    /**
     * 
     * @param string $keyword
     * @param string $field
     * @param string $data_type
     * @param bool $is_default
     * @param bool $highlight
     * @param array $options     
     * @return OSC_Search_Analyzer
     */
    public function addKeyword($keyword, $field, $data_type = null, $is_default = true, $highlight = true, $options = null) {
        $keyword = strtolower($keyword);

        if (!is_array($options)) {
            $options = array();
        }

        if (!is_array($data_type)) {
            $data_type = (string) $data_type;
            $data_type = trim($data_type);
        }

        if (!$data_type) {
            $data_type = static::TYPE_STRING;
        } else if (!in_array($data_type, array(static::TYPE_INT, static::TYPE_TAG, static::TYPE_DATE, static::TYPE_STRING, static::TYPE_STATE))) {
            $callback = null;
            $callback_params = array();

            if (is_array($data_type) && isset($data_type['function'])) {
                $callback = $data_type['function'];

                if (isset($data_type['params'])) {
                    $callback_params = $data_type['params'];
                }
            } else {
                $callback = $data_type;
            }

            $data_type = array('function' => $callback, 'params' => $callback_params);
        }

        $keyword_data = array(
            'key' => $keyword,
            'field' => $field,
            'type' => $data_type
        );

        if ($highlight) {
            $keyword_data['highlight'] = true;
        }

        $validates = array();

        if (isset($options['validates'])) {
            $validates = array_values($options['validates']);
        }

        if (isset($options['validate'])) {
            array_unshift($validates, $options['validate']);
        }

        if (count($validates) > 0) {
            $keyword_data['validates'] = $validates;
        }

        $this->_keyword_data[$keyword] = $keyword_data;

        if ($is_default) {
            $this->_default_keyword[] = $keyword;
        }

        return $this;
    }

    public function parse($search_query) {
        $keywords = implode('|', array_keys($this->_keyword_data));

        while (preg_match("/(\!?)({$keywords}):['\"]?.+/i", $search_query)) {
            $search_query = preg_replace_callback("/(\!?)({$keywords})\:(['\"]?)(.+)/i", [$this, '_fetchKeyword'], $search_query);
        }

        while (preg_match("/(\!?)['\"].+/", $search_query)) {
            $search_query = preg_replace_callback("/(\!?)(['\"])(.+)/", [$this, '_fetchNonKeywordWithQuote'], $search_query);
        }

        $search_query = preg_replace_callback("/(\!?)(\S+)/", [$this, '_fetchNonKeyword'], $search_query);

        while (preg_match('/\([^\(\)]+\)/', $search_query)) {
            $search_query = preg_replace_callback('/\(([^\(\)]+)\)/', function($matches) {
                return $this->_packClause($matches[1]);
            }, $search_query);
        }

        $root_clause_idx = 'search_analyze_clause_0';

        $this->_getConditionObj()->addClause($root_clause_idx);

        $this->_unpack($search_query, $root_clause_idx);

        return $this->_getConditionObj();
    }

    protected function _fetchKeyword($matches) {
        $negative = $matches[1] == '!' ? true : false;

        if ($matches[3] != '') {
            return preg_replace_callback("/^(([^{$matches[3]}]|(?<=\\\\){$matches[3]})+)(?<!\\\\){$matches[3]}/", function($_matches) use($matches, $negative) {
                return $this->_packCondition($matches[2], $_matches[1], $negative);
            }, $matches[4]);
        } else {
            return preg_replace_callback("/^([^\s]+)/", function($_matches) use($matches, $negative) {
                return $this->_packCondition($matches[2], $_matches[1], $negative);
            }, $matches[4]);
        }
    }

    protected function _fetchNonKeywordWithQuote($matches) {
        return preg_replace_callback("/^(([^{$matches[2]}]|(?<=\\\\){$matches[2]})+)(?<!\\\\){$matches[2]}/", function($_matches) use($matches) {
            return $this->_addDefaultKeyword($_matches[1], $matches[1] == '!');
        }, $matches[3]);
    }

    protected function _fetchNonKeyword($matches) {
        if (preg_match('/^(or|and|\(|\)|(osc\.(clause|condition)\.\d+))+$/i', $matches[2])) {
            return $matches[2];
        }

        return $this->_addDefaultKeyword($matches[2], $matches[1] == '!');
    }

    protected function _addDefaultKeyword($value, $negative) {
        $conditions = array();

        foreach ($this->_default_keyword as $keyword) {
            $conditions[] = $this->_packCondition($keyword, $value, $negative);
        }

        if (!count($conditions)) {
            return '';
        }

        return $this->_packClause(implode(' OR ', $conditions));
    }

    protected function _packCondition($keyword, $value, $negative) {
        static $i = 0;

        $keyword = strtolower($keyword);

        if (!isset($this->_keyword_data[$keyword])) {
            return '';
        }

        $value = preg_replace("/(?<!\\\\)\\\\(?!\\\\)/", '', $value);
        $value = preg_replace("/(\\\\{2})/", '\\', $value);

        $value = trim($value);

        if (!$value) {
            return '';
        }

        $i++;

        $this->_condition[$i] = array('keyword' => $keyword, 'value' => $value, 'negative' => $negative);

        return " osc.condition.{$i}";
    }

    protected function _packClause($conditions) {
        static $i = 0;

        $conditions = trim($conditions);

        if (!$conditions) {
            return '';
        }

        $i++;

        $this->_clause[$i] = $conditions;

        return " osc.clause.{$i} ";
    }

    /**
     * @return OSC_Database_Condition
     */
    public function _getConditionObj() {
        if ($this->_condition_obj === null) {
            $this->_condition_obj = OSC::core('database')->getReadAdapter()->getCondition(true);
        }

        return $this->_condition_obj;
    }

    protected function _unpackCallback($relation, $type, $index, $parent_clause_idx) {
        $relation = strtolower(trim($relation)) == 'and' ? OSC_Database::RELATION_AND : OSC_Database::RELATION_OR;

        if ($type == 'clause') {
            if (isset($this->_clause[$index])) {
                $clause_idx = 'search_analyze_clause_' . $index;

                $this->_getConditionObj()->addClause($clause_idx, $relation, $parent_clause_idx);
                $this->_unpack($this->_clause[$index], $clause_idx);
            }

            return;
        }

        if (!isset($this->_condition[$index])) {
            return;
        }

        $condition_data = $this->_condition[$index];

        $keyword = $condition_data['keyword'];
        $keyword_data = $this->_keyword_data[$keyword];

        if (isset($keyword_data['validates'])) {
            foreach ($keyword_data['validates'] as $validator) {
                if (call_user_func($validator, $condition_data['value']) === false) {
                    return;
                }
            }
        }

        switch ($keyword_data['type']) {
            case static::TYPE_INT:
                $value = $this->_parseInt($condition_data['value']);
                break;
            case static::TYPE_TAG:
                $value = $this->_parseTag($condition_data['value']);
                break;
            case static::TYPE_DATE:
                $value = $this->_parseDate($condition_data['value']);
                break;
            case static::TYPE_STRING:
            case static::TYPE_STATE:
                $value = $this->_parseString($condition_data['value']);
                break;
            default:
                $callback = $keyword_data['type'];

                array_unshift($callback['params'], $this->_getConditionObj(), $condition_data, $relation, $parent_clause_idx);

                call_user_func_array($callback['function'], $callback['params']);

                return;
        }

        if (!$value) {
            return;
        }

        if (!is_array($value)) {
            $value = array('value' => $value);
        }

        if (!isset($value['mark'])) {
            $value['mark'] = '';
        }

        switch ($keyword_data['type']) {
            case static::TYPE_INT:
                $this->_addIntCondition($value['mark'], $value['value'], $relation, $index, $parent_clause_idx);
                break;
            case static::TYPE_TAG:
                $this->_addTagCondition($value['mark'], $value['value'], $relation, $index, $parent_clause_idx);
                break;
            case static::TYPE_DATE:
                $this->_addDateCondition($value['mark'], $value['value'], $relation, $index, $parent_clause_idx);
                break;
            case static::TYPE_STRING:
                $this->_addStringCondition($value['mark'], $value['value'], $relation, $index, $parent_clause_idx);
                break;
            case static::TYPE_STATE:
                $this->_addStateCondition($value['mark'], $value['value'], $relation, $index, $parent_clause_idx);
                break;
        }

        if (isset($keyword_data['highlight']) && !$condition_data['negative']) {
            if (!isset($this->_highlight_data[$keyword])) {
                $this->_highlight_data[$keyword] = array();
            }

            $this->_highlight_data[$keyword] = array_merge($this->_highlight_data[$keyword], $value);
        }
    }

    protected function _addIntCondition($mark, $value, $relation, $index, $parent_clause_idx) {
        $condition_data = $this->_condition[$index];

        $keyword = $condition_data['keyword'];
        $keyword_data = $this->_keyword_data[$keyword];

        if (is_array($value) && count($value) < 2) {
            //$value = array_values($value);
            $value = $value[0];
        }

        if ($mark) {
            if ($mark == '-') {
                $operator = OSC_Database::OPERATOR_BETWEEN;
            } else if (substr($mark, 0, 1) == '>') {
                if (strlen($mark) == 2) {
                    $operator = OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL;
                } else {
                    $operator = OSC_Database::OPERATOR_GREATER_THAN;
                }
            } else {
                if (strlen($mark) == 2) {
                    $operator = OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL;
                } else {
                    $operator = OSC_Database::OPERATOR_LESS_THAN;
                }
            }
        } else {
            if (is_array($value)) {
                $operator = OSC_Database::OPERATOR_IN;
            } else {
                $operator = OSC_Database::OPERATOR_EQUAL;
            }
        }

        if ($condition_data['negative']) {
            $operator = OSC_Database::NEGATION_MARK . $operator;
        }

        $this->_getConditionObj()->addCondition($keyword_data['field'], $value, $operator, $relation, $parent_clause_idx);
    }

    protected function _addTagCondition($mark, $value, $relation, $index, $parent_clause_idx) {
        $condition_data = $this->_condition[$index];

        $keyword = $condition_data['keyword'];
        $keyword_data = $this->_keyword_data[$keyword];

        $operator = OSC_Database::OPERATOR_REGEXP;

        if ($condition_data['negative']) {
            $operator = OSC_Database::NEGATION_MARK . $operator;
        }

        $this->_getConditionObj()->addCondition($keyword_data['field'], "[[:<:]]{$value}[[:>:]]", $operator, $relation, $parent_clause_idx);
    }

    protected function _addStringCondition($mark, $value, $relation, $index, $parent_clause_idx) {
        $condition_data = $this->_condition[$index];

        $keyword = $condition_data['keyword'];
        $keyword_data = $this->_keyword_data[$keyword];

        $operator = OSC_Database::OPERATOR_LIKE;

        if ($condition_data['negative']) {
            $operator = OSC_Database::NEGATION_MARK . $operator;
        }

        $this->_getConditionObj()->addCondition($keyword_data['field'], $value, $operator, $relation, $parent_clause_idx);
    }

    protected function _addStateCondition($mark, $value, $relation, $index, $parent_clause_idx) {
        $condition_data = $this->_condition[$index];

        $keyword = $condition_data['keyword'];
        $keyword_data = $this->_keyword_data[$keyword];

        $operator = OSC_Database::OPERATOR_EQUAL;

        if ($condition_data['negative']) {
            $operator = OSC_Database::NEGATION_MARK . $operator;
        }

        $this->_getConditionObj()->addCondition($keyword_data['field'], $value, $operator, $relation, $parent_clause_idx);
    }

    protected function _addDateCondition($mark, $value, $relation, $index, $parent_clause_idx) {
        $condition_data = $this->_condition[$index];

        $keyword = $condition_data['keyword'];
        $keyword_data = $this->_keyword_data[$keyword];

        if (!is_array($value)) {
            if (substr($mark, 0, 1) == '>') {
                if (strlen($mark) == 2) {
                    $operator = OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL;
                } else {
                    $operator = OSC_Database::OPERATOR_GREATER_THAN;
                }
            } else {
                if (strlen($mark) == 2) {
                    $operator = OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL;
                } else {
                    $operator = OSC_Database::OPERATOR_LESS_THAN;
                }
            }

            if ($condition_data['negative']) {
                $operator = OSC_Database::NEGATION_MARK . $operator;
            }

            $this->_getConditionObj()->addCondition($keyword_data['field'], $value, $operator, $relation, $parent_clause_idx);

            return;
        }

        $date_clause_idx = $parent_clause_idx . '_' . $index;
        $this->_getConditionObj()->addClause($date_clause_idx, $relation, $parent_clause_idx);

        if ($condition_data['negative']) {
            $this->_getConditionObj()->addCondition($keyword_data['field'], $value[0], OSC_Database::OPERATOR_LESS_THAN, null, $date_clause_idx)
                    ->addCondition($keyword_data['field'], $value[1], OSC_Database::OPERATOR_GREATER_THAN, OSC_Database::RELATION_AND, $date_clause_idx);
        } else {
            $this->_getConditionObj()->addCondition($keyword_data['field'], $value[0], OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL, null, $date_clause_idx)
                    ->addCondition($keyword_data['field'], $value[1], OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL, OSC_Database::RELATION_AND, $date_clause_idx);
        }
    }

    protected function _unpack($search_query, $clause_idx) {
        preg_replace_callback('/(\s*(OR|AND)?\s*(osc\.(clause|condition)\.(\d+)))/i', function($matches) use($clause_idx) {
            return $this->_unpackCallback($matches[2], $matches[4], $matches[5], $clause_idx);
        }, $search_query);
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

    protected function _parseInt($value) {
        preg_match('/^\s*((>|<)=?)?(.+)$/', $value, $matches);

        $mark = $matches[1];
        $value = $matches[3];

        $between_flag = false;

        if (strpos($value, '-') !== false) {
            $between_flag = true;
            $value_arr = explode('-', $value);
        } else {
            $value_arr = explode(',', $value);
        }

        foreach ($value_arr as $k => $v) {
            $v = trim($v);

            if (preg_match('/[^0-9\.\-]/', $v) || substr_count($v, '.') > 1 || substr_count($v, '-') > 1 || preg_match('/^.+-/', $v)) {
                unset($value_arr[$k]);
                continue;
            }

            $value_arr[$k] = round($v, 2);
        }

        if (count($value_arr) < 1) {
            return false;
        }

        $value_arr = array_values(array_unique($value_arr));

        if ($between_flag && count($value_arr) > 1) {
            $min = min($value_arr);
            $max = max($value_arr);

            $value_arr = array($min, $max);

            $mark = '-';
        } else if ($mark) {
            if (substr($mark, 0, 1) == '>') {
                $value_arr = max($value_arr);
            } else {
                $value_arr = min($value_arr);
            }

            $value_arr = array($value_arr);
        }

        return array('mark' => $mark, 'value' => $value_arr);
    }

    protected function _parseTag($value) {
        $value = OSC::core('string')->cleanTags($value);

        if (!$value) {
            return false;
        }

        if (count($tags) < 1) {
            return false;
        }

        return array('value' => $value);
    }

    protected function _parseDate($value) {
        preg_match('/^\s*((>|<)=?)?(.+)$/', $value, $matches);

        $mark = $matches[1];
        $value = $matches[3];

        $value = str_replace('.', '/', $value);
        $value = preg_replace('/[^0-9\/\-]/', '', $value);
        $value = preg_replace('/\/{2,}/', '/', $value);
        $value = preg_replace('/-{2,}/', '-', $value);
        $value = preg_replace('/^(\/|-)|(\/|-)$/', '', $value);

        $date_array = explode('-', $value);

        if (count($date_array) == 2 && $date_array[0] == $date_array[1]) {
            $date_array = array($date_array[0]);
        }

        if (count($date_array) > 2) {
            return false;
        }

        foreach ($date_array as $k => $date) {
            $date = explode('/', $date);

            if (count($date) != 3 || !checkdate($date[1], $date[0], $date[2])) {
                unset($date_array[$k]);
                continue;
            }

            $date_array[$k] = $date;
        }

        if (count($date_array) < 1) {
            return false;
        }

        $date_array = array_values($date_array);

        if (count($date_array) == 2) {
            if (intval($date_array[0][2] . $date_array[0][1] . $date_array[0][0]) > intval($date_array[1][2] . $date_array[1][1] . $date_array[1][0])) {
                $buff = $date_array[0];

                $date_array[0] = $date_array[1];
                $date_array[1] = $buff;
            }

            if (!$mark) {
                return array(
                    'mark' => '',
                    'value' => array(
                        mktime(0, 0, 0, $date_array[0][1], $date_array[0][0], $date_array[0][2]),
                        mktime(23, 59, 59, $date_array[1][1], $date_array[1][0], $date_array[1][2])
                    )
                );
            }

            $date_array = array($date_array[substr($mark, 0, 1) == '<' ? 0 : 1]);
        }

        if (!$mark) {
            return array(
                'mark' => '',
                'value' => array(
                    mktime(0, 0, 0, $date_array[0][1], $date_array[0][0], $date_array[0][2]),
                    mktime(23, 59, 59, $date_array[0][1], $date_array[0][0], $date_array[0][2])
                )
            );
        }

        if (substr($mark, 0, 1) == '<') {
            $hour = 0;
            $minute = 0;
            $second = 0;
        } else {
            $hour = 23;
            $minute = 59;
            $second = 59;
        }

        return array(
            'mark' => $mark,
            'value' => mktime($hour, $minute, $second, $date_array[0][1], $date_array[0][0], $date_array[0][2])
        );
    }

    protected function _parseString($value) {
        return trim($value);
    }

}
