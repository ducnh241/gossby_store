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
 * @copyright	Copyright (C) 2014 by Sang Le Tuan (http://www.osecore.com). All rights reserved.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Sang Le Tuan - batsatla@gmail.com
 */

/**
 * OSECORE Core
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Database_Condition extends OSC_Object {

    const _DEFAULT_OPERATOR = 'EQUAL';
    const _DEFAULT_RELATION = 'AND';
    const _ROOT_CLAUSE_IDX = 'root';
    const _PDO_ANCHOR_PREFIX = '__pdo_';

    /**
     *
     * @var string 
     */
    protected $_condition = array('root' => array());

    /**
     *
     * @var boolean 
     */
    protected $_pdo_parse = false;

    /**
     *
     * @var integer
     */
    protected $_pdo_idx = 0;

    /**
     *
     * @var array
     */
    protected $_pdo_bind = array();
    protected $_clause_counter = 0;
    protected $_condition_counter = 0;
    protected $_custom_anchor_prefix = 'default';
    protected $_fulltext_keyword_min_length = 4;

    /**
     * 
     */
    public function __construct() {
        $this->_reset();
    }

    /**
     * 
     * @return OSC_Database_Condition
     */
    public function reset() {
        $this->_reset();

        return $this;
    }

    public function setAnchorPrefix($prefix) {
        $this->_custom_anchor_prefix = preg_replace('/[^a-zA-Z0-9\_]/', '', (string) $prefix);
        return $this;
    }

    public function setFulltextKeywordMinLength($length = 4) {
        $length = intval($length);

        if ($length < 1) {
            $length = 1;
        }

        $this->_fulltext_keyword_min_length = $length;

        return $this;
    }

    /**
     * 
     * @return array
     */
    public function getPdoData() {
        $this->_pdo_parse = !OSC_ENV_DEBUG_DB;

        $query = (string) $this;
        $params = $this->_pdo_bind;

        $this->_pdo_parse = false;
        $this->_pdo_idx = 0;
        $this->_pdo_bind = array();

        return array(
            'condition' => $query,
            'params' => $params
        );
    }

    /**
     * 
     * @param mixed $value
     * @return string
     */
    protected function _preDataForCondition($value) {
        if ($value === null || $value === false) {
            return 'NULL';
        }

        $value = trim($value);

        if ($value == '') {
            return false;
        }

        if ($this->_pdo_parse) {
            $this->_pdo_idx++;
            $pdo_key = static::_PDO_ANCHOR_PREFIX . $this->_custom_anchor_prefix . '_' . $this->_pdo_idx;
            $this->_pdo_bind[$pdo_key] = $value;

            $value = ':' . $pdo_key;
        } else {
            $value = OSC::core('database')->getAdapter()->getDbh()->quote($value);
        }

        return $value;
    }

    /**
     * 
     * @param mixed $value
     * @return string
     */
    protected function _prepareFindInSetOperator($value) {
        if ($value === null || $value === false) {
            return false;
        }

        if (is_array($value)) {
            $value = implode(',', $value);
        } else {
            $value = strval($value);
        }

        $value = trim($value);

        if ($value == '') {
            return false;
        }

        if ($this->_pdo_parse) {
            $this->_pdo_idx++;
            $pdo_key = static::_PDO_ANCHOR_PREFIX . $this->_custom_anchor_prefix . '_' . $this->_pdo_idx;
            $this->_pdo_bind[$pdo_key] = $value;

            $value = ':' . $pdo_key;
        } else {
            $value = OSC::core('database')->getAdapter()->getDbh()->quote($value);
        }

        return $value;
    }

    /**
     * 
     * @param string $clause_idx
     * @return string
     */
    protected function _cleanClauseIndex($clause_idx) {
        return strtolower($clause_idx);
    }

    /**
     * 
     * @param string $clause_idx
     * @return boolean
     */
    public function checkClauseIsExist($clause_idx) {
        return isset($this->_condition[$clause_idx]);
    }

    /**
     * 
     * @param string $clause_idx
     * @param string $relation
     * @param string $p_clause_idx
     * @return OSC_Database_Condition
     */
    public function addClause($clause_idx = '', $relation = null, $p_clause_idx = null) {
        $clause_idx = $this->_cleanClauseIndex($clause_idx);

        if (!$clause_idx) {
            return $this;
        }

        if (!$p_clause_idx) {
            $p_clause_idx = static::_ROOT_CLAUSE_IDX;
        } else {
            $p_clause_idx = $this->_cleanClauseIndex($p_clause_idx);

            if (!$this->checkClauseIsExist($p_clause_idx)) {
                throw new OSC_Exception_Runtime("DB Condition: The clause index [{$p_clause_idx}] is not exist");
            }
        }

        $this->_condition[$p_clause_idx][] = array(
            'relation' => $relation ? $relation : static::_DEFAULT_RELATION,
            'clause_idx' => $clause_idx,
            'type' => 'clause'
        );

        $this->_condition[$clause_idx] = array();

        $this->_clause_counter ++;

        return $this;
    }

    /**
     * 
     * @param string $field
     * @param mixed $filter_value
     * @param string $operator
     * @param string $relation
     * @param string $clause_idx
     * @param string $cond_idx
     * @return OSC_Database_Condition
     * @throws OSC_Exception_Runtime
     */
    public function addCondition($field, $filter_value, $operator = null, $relation = null, $clause_idx = null, $cond_idx = null) {
        if (!$clause_idx) {
            $clause_idx = static::_ROOT_CLAUSE_IDX;
        } else {
            $clause_idx = $this->_cleanClauseIndex($clause_idx);

            if (!$this->checkClauseIsExist($clause_idx)) {
                throw new OSC_Exception_Runtime("DB Condition: The clause index [{$clause_idx}] is not exist");
            }
        }

        try {
            $condition_item = $this->makeConditionItem($field, $filter_value, $operator, $relation);
        } catch (OSC_Exception_Runtime $e) {
            throw $e;
        }

        $this->_condition[$clause_idx][$cond_idx ? $cond_idx : count($this->_condition[$clause_idx])] = $condition_item;

        $this->_condition_counter ++;

        return $this;
    }

    public function makeConditionItem($field, $filter_value, $operator = null, $relation = null) {
        if (!is_array($field)) {
            $field = array($field);
        }

        if (count($field) < 1) {
            throw new OSC_Exception_Runtime("DB Condition: The field name is required param");
        }

        $field = array_values($field);

        foreach ($field as $k => $v) {
            $v = preg_replace('/[^a-zA-Z0-9\._]/', '', $v);
            $v = preg_replace('/\.{2,}$/', '.', $v);
            $v = preg_replace('/^\.|\.$/', '', $v);

            if (strlen($v) < 1) {
                throw new OSC_Exception_Runtime("DB Condition: The field name [{$v}] is incorrect");
            }

            if (strpos($v, '.') === false) {
                $v = "`{$v}`";
            }

            $field[$k] = $v;
        }

        if ($operator == OSC_Database::OPERATOR_FULLTEXT) {
            $field = implode(',', $field);
        } else {
            if (count($field) > 1) {
                throw new OSC_Exception_Runtime("DB Condition: Multiple field name only avalible for fulltext operator");
            }

            $field = $field[0];
        }

        $negation = false;

        if (!$operator) {
            $operator = static::_DEFAULT_OPERATOR;
        } else {
            $operator = strtoupper($operator);

            if (preg_match('/(NOT_|' . OSC_Database::NEGATION_MARK . ')(.+)/', $operator, $matches)) {
                $negation = true;
                $operator = $matches[2];
            }

            switch ($operator) {
                case '=':
                    $operator = OSC_Database::OPERATOR_EQUAL;
                    break;
                case '<':
                    $operator = OSC_Database::OPERATOR_LESS_THAN;
                    break;
                case '<=':
                    $operator = OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL;
                    break;
                case '>':
                    $operator = OSC_Database::OPERATOR_GREATER_THAN;
                    break;
                case '>=':
                    $operator = OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL;
                    break;
            }
        }

        return array(
            'relation' => $relation ? $relation : static::_DEFAULT_RELATION,
            'type' => 'condition',
            'field' => $field,
            'operator' => $operator,
            'filter_value' => $filter_value,
            'negation' => $negation
        );
    }

    public function countCondition() {
        return $this->_clause_counter;
    }

    public function countClause() {
        return $this->_condition_counter;
    }

    public function getArray($p_clause_idx = null) {
        if (!$p_clause_idx) {
            $p_clause_idx = static::_ROOT_CLAUSE_IDX;
        }

        if (!isset($this->_condition[$p_clause_idx])) {
            return array();
        }

        $data = array();

        foreach ($this->_condition[$p_clause_idx] as $key => $item) {
            if ($item['type'] == 'clause') {
                $conditions = $this->getArray($item['clause_idx']);

                if (count($item) < 1) {
                    continue;
                }

                $item = array(
                    'type' => 'clause',
                    'key' => $item['clause_idx'],
                    'relation' => $item['relation'],
                    'conditions' => $conditions
                );
            } else {
                $item = array(
                    'key' => $key,
                    'field' => $item['field'],
                    'value' => $item['filter_value'],
                    'operator' => ($item['negation'] ? 'NOT_' : '') . $item['operator'],
                    'relation' => $item['relation']
                );
            }

            $data[] = $item;
        }

        return $data;
    }

    /**
     * 
     * @param array $data
     * @param string $p_clause_idx
     * @return OSC_Database_Condition
     */
    public function parse($data, $p_clause_idx = null) {
        if (!is_array($data)) {
            return $this;
        }

        if (isset($data['field']) || isset($data['key'])) {
            $data = array($data);
        }

        foreach ($data as $k => $v) {
            foreach (array('field', 'value', 'key', 'operator', 'relation') as $key) {
                if (!isset($v[$key])) {
                    $v[$key] = null;
                }
            }

            if (isset($v['type']) && $v['type'] == 'clause') {
                if (isset($v['conditions']) && count($v['conditions'])) {
                    $this->addClause($v['key'], $v['relation'], $p_clause_idx);
                    $this->parse($v['conditions'], $v['key']);
                }
            } else {
                $this->addCondition($v['field'], $v['value'], $v['operator'], $v['relation'], $p_clause_idx, $v['key']);
            }
        }

        return $this;
    }

    /**
     * 
     * @return string
     */
    public function __toString() {
        return $this->toString();
    }

    /**
     * 
     * @param string $clause_idx
     * @return string
     */
    public function toString($clause_idx = null) {
        if (!$clause_idx) {
            $clause_idx = static::_ROOT_CLAUSE_IDX;
        }

        $where = '';

        $counter = 0;

        foreach ($this->_condition[$clause_idx] as $condition) {
            if ($condition['type'] == 'clause') {
                if (count($this->_condition[$condition['clause_idx']]) < 1) {
                    continue;
                }

                $condition_str = $this->toString($condition['clause_idx']);

                if (!$condition_str) {
                    continue;
                }

                $condition_str = "({$condition_str})";
            } else {
                $condition_str = $this->buildCondition($condition);

                if (!$condition_str) {
                    continue;
                }
            }

            $counter++;

            $where .= ($counter > 1 ? ' ' . $condition['relation'] . ' ' : '') . $condition_str;
        }

        return $where;
    }

    public function buildCondition($condition) {
        switch ($condition['operator']) {
            case OSC_Database::OPERATOR_EQUAL:
            case OSC_Database::OPERATOR_LESS_THAN:
            case OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL:
            case OSC_Database::OPERATOR_GREATER_THAN:
            case OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL:
                if ($condition['filter_value'] !== null) {
                    if ($condition['operator'] == OSC_Database::OPERATOR_LESS_THAN_OR_EQUAL) {
                        $operator = '<=';
                    } elseif ($condition['operator'] == OSC_Database::OPERATOR_LESS_THAN) {
                        $operator = '<';
                    } elseif ($condition['operator'] == OSC_Database::OPERATOR_GREATER_THAN_OR_EQUAL) {
                        $operator = '>=';
                    } elseif ($condition['operator'] == OSC_Database::OPERATOR_GREATER_THAN) {
                        $operator = '>';
                    } else {
                        $operator = $condition['negation'] ? '!=' : '=';
                    }

                    $condition['filter_value'] = $this->_preDataForCondition($condition['filter_value']);
                } else {
                    $condition['filter_value'] = 'NULL';
                    $operator = $condition['negation'] ? 'IS NOT' : 'IS';
                }

                return "{$condition['field']} {$operator} {$condition['filter_value']}";
                break;
            case OSC_Database::OPERATOR_EXACT:
            case OSC_Database::OPERATOR_LIKE:
            case OSC_Database::OPERATOR_LIKE_LEFT:
            case OSC_Database::OPERATOR_LIKE_RIGHT:
                $left = $right = '';

                if ($condition['operator'] == OSC_Database::OPERATOR_LIKE || $condition['operator'] == OSC_Database::OPERATOR_LIKE_LEFT) {
                    $left = '%';
                }

                if ($condition['operator'] == OSC_Database::OPERATOR_LIKE || $condition['operator'] == OSC_Database::OPERATOR_LIKE_RIGHT) {
                    $right = '%';
                }

                $negation = $condition['negation'] ? 'NOT ' : '';
                return "{$condition['field']} {$negation}LIKE " . $this->_preDataForCondition($left . $condition['filter_value'] . $right);
                break;
            case OSC_Database::OPERATOR_REGEXP:
                $negation = $condition['negation'] ? 'NOT ' : '';
                return "{$condition['field']} {$negation}REGEXP '{$condition['filter_value']}'";
                break;
            case OSC_Database::OPERATOR_IN:
                $negation = $condition['negation'] ? 'NOT ' : '';
                $filter_value = $this->_prepareINOperator($condition['filter_value']);

                if ($filter_value !== false) {
                    return "{$condition['field']} {$negation}IN ({$filter_value})";
                }
                break;
            case OSC_Database::OPERATOR_FIND_IN_SET:
                $negation = $condition['negation'] ? 'NOT ' : '';
                $filter_value = $this->_prepareFindInSetOperator($condition['filter_value']);

                if ($filter_value !== false) {
                    return "FIND_IN_SET({$condition['field']}, {$filter_value})" . ($condition['negation'] ? ' < 1' : '');
                }
                break;
            case OSC_Database::OPERATOR_BETWEEN:
                $filter_value = array_values($condition['filter_value']);

                if (count($filter_value) == 2) {
                    $negation = $condition['negation'] ? 'NOT ' : '';

                    $filter_value[0] = (string) $filter_value[0];
                    $filter_value[1] = (string) $filter_value[1];

                    if ($filter_value !== false) {
                        return "{$condition['field']} {$negation}BETWEEN LEAST({$this->_preDataForCondition($filter_value[0])},{$this->_preDataForCondition($filter_value[1])}) AND GREATEST({$this->_preDataForCondition($filter_value[0])}, {$this->_preDataForCondition($filter_value[1])})";
                    }
                }
                break;
            case OSC_Database::OPERATOR_FULLTEXT:
                $fulltext = $this->_preDataForFulltext($condition['filter_value'], $condition['negation']);

                if ($fulltext !== false) {
                    return "MATCH ({$condition['field']}) AGAINST ({$fulltext['keywords']}{$fulltext['modifiers']})";
                }
                break;
            default:
        }

        return false;
    }

    protected function _preDataForFulltext($keywords, $negation) {
        $keywords = preg_split("/[ \n\r]+/", $keywords);

        $boolean_mode_flag = false;

        if ($negation) {
            $boolean_mode_flag = true;
        }

        foreach ($keywords as $k => $keyword) {
            $keyword = trim($keyword);

            $mark = preg_replace('/(?=^\P{L}+)^[^-+]*([-+])\P{L}*\p{L}.+/u', '\1', $keyword);
            $wildcard = substr($keyword, -1) == '*' ? '*' : '';

            $keyword = preg_replace('/[^\p{L}\d]/u', '', $keyword);

            if (OSC::core('string')->strlen($keyword) < $this->_fulltext_keyword_min_length) {
                unset($keywords[$k]);
            } else {
                if ($negation) {
                    if ($mark != '+') {
                        $mark = '+';
                    } else {
                        $mark = '-';
                    }
                } else if (!in_array($mark, array('+', '-'))) {
                    $mark = '';
                }
                
                $boolean_mode_flag = $wildcard || $mark;

                $keywords[$k] = $mark . $keyword . $wildcard;
            }
        }

        if (count($keywords) < 1) {
            return false;
        }

        $modifiers = '';

        if ($boolean_mode_flag) {
            $modifiers = ' IN BOOLEAN MODE';
        }

        return array(
            'modifiers' => $modifiers,
            'keywords' => $this->_preDataForCondition(implode(' ', $keywords))
        );
    }

    /**
     * 
     * @param mixed $filter_value
     * @return mixed
     */
    protected function _prepareINOperator($filter_value) {
        if (!is_array($filter_value)) {
            return $filter_value;
        }

        foreach ($filter_value as $idx => $val) {
            $val = $this->_preDataForCondition($val);

            if ($val === false) {
                unset($filter_value[$idx]);
                continue;
            }

            $filter_value[$idx] = $val;
        }

        if (count($filter_value) < 1) {
            return false;
        }

        return implode(",", $filter_value);
    }

}
