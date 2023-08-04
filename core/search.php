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
 * OSECORE Core
 *
 * @package Core_Database
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_Search extends OSC_Object {

    protected $_default_bind = 'default';
    protected $_adapters = array();

    const KEYWORD_MIN_LENGTH = 1;//2
    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';
    const _DEFAULT_OPERATOR = '=';
    const _DEFAULT_RELATION = 'AND';
    const _ROOT_CLAUSE_IDX = 'root';
    const OPERATOR_EQUAL = '=';
    const OPERATOR_LESS_THAN_OR_EQUAL = '<=';
    const OPERATOR_LESS_THAN = '<';
    const OPERATOR_GREATER_THAN_OR_EQUAL = '>=';
    const OPERATOR_GREATER_THAN = '>';
    const OPERATOR_BETWEEN = 'BETWEEN';
    const OPERATOR_LEFT = 'LEFT';
    const OPERATOR_RIGHT = 'RIGHT';
    const OPERATOR_CONTAINS = 'CONTAINS';
    const OPERATOR_REGEXP = 'REGEXP';
    const OPERATOR_IN = 'IN';
    const RELATION_AND = 'AND';
    const RELATION_OR = 'OR';

    public function __construct() {
        parent::__construct();

        $default = OSC::systemRegistry('search_default');

        if ($default) {
            $this->_default_bind = $default;
        }
    }

    /**
     * 
     * @staticvar null $adapters
     * @param string $bind
     * @return OSC_Search_Adapter
     */
    public function getAdapter($bind = null) {
        static $adapters = null;

        if ($adapters === null) {
            $adapters = & $this->_adapters;
        }

        $search_config = OSC::systemRegistry('search_config');

        if (!$bind) {
            $bind = $this->_default_bind;
        }

        $instance = $search_config['bind'][$bind];

        if (!$instance) {
            return null;
        }

        if (!isset($adapters[$instance])) {
            $config = $search_config['instance'][$instance];
            $adapters[$instance] = OSC::core('search_adapter_' . $config['adapter'], $instance)->setConfig($config);
        }

        return $adapters[$instance];
    }

    public function __call($method, $arguments) {
        if (count($arguments) < 1) {
            throw new Exception("Search with no destination");
        }

        $bind = $arguments[0];

        unset($arguments[0]);

        return call_user_func_array(array($this->getAdapter($bind), $method), $arguments);
    }

    /**
     * 
     * @param string $operator
     * @return boolean
     */
    public function operatorSupported($operator) {
        static $operators = null;

        if ($operators === null) {
            $oClass = new ReflectionClass(__CLASS__);
            $constants = $oClass->getConstants();

            $operators = array();

            foreach ($constants as $key => $val) {
                if (strtolower(substr($key, 0, 9)) == 'operator_') {
                    $operators[] = strtolower($val);
                }
            }
        }

        return in_array(strtolower($operator), $operators);
    }

    public function cleanKeywords($keywords) {
        $keywords = strtolower(trim($keywords));
        $keywords = preg_replace('/[^\p{L}\d\.\-\_\s]/u', '', $keywords);

        $buff = explode(' ', $keywords);

        $keywords = array();

        foreach ($buff as $keyword) {
            $keyword = preg_replace('/(^[^\p{L}\d]+|[^\p{L}\d]+$)/u', '', $keyword);
            $keyword = preg_replace('/([^\p{L}\d])[^\p{L}\d]+/u', '\\1', $keyword);
            $keyword = preg_replace('/[^\p{L}\d\.\-\_ ]/u', '', $keyword);

            $keyword = $this->processStopWord($keyword);

            if (OSC::core('string')->strlen($keyword) < static::KEYWORD_MIN_LENGTH) {
                continue;
            }

            $keywords[] = $this->processSynonymWord($keyword);
        }

        return implode(' ', $keywords);
    }

    public function processStopWord($keyword) {return $keyword;
        static $stopword_data = null;

        if ($stopword_data === null) {
            $stopword_data = file_get_contents(OSC_CORE_PATH . '/search/stopwords.txt');
            $stopword_data = strtolower($stopword_data);
            $stopword_data = explode("\n", $stopword_data);

            $buff = array();

            foreach ($stopword_data as $stopword) {
                $stopword = trim($stopword);

                if (strlen($stopword) > 0) {
                    $buff[] = $stopword;
                }
            }

            $stopword_data = array_unique($buff);
        }

        return in_array($keyword, $stopword_data) ? '' : $keyword;
    }

    public function processSynonymWord($keyword) {
        static $synonym_data = null;

        if ($synonym_data === null) {
            $synonym_data = file_get_contents(OSC_CORE_PATH . '/search/synonyms.txt');
            $synonym_data = strtolower($synonym_data);
            $synonym_data = explode("\n", $synonym_data);

            $buff = array();

            foreach ($synonym_data as $synonyms) {
                $synonyms = explode(',', $synonyms);

                $convert_to = null;

                foreach ($synonyms as $synonym) {
                    $synonym = trim($synonym);

                    if (strlen($synonym) > 0) {
                        if ($convert_to === null) {
                            $convert_to = $synonym;
                        } else if ($convert_to !== $synonym && !isset($buff[$synonym])) {
                            $buff[$synonym] = $convert_to;
                        }
                    }
                }
            }

            $synonym_data = $buff;
        }

        return isset($synonym_data[$keyword]) ? $synonym_data[$keyword] : $keyword;
    }

    public static function registerSearchInstance($key, $data) {
        $search_config = OSC::systemRegistry('search_config');

        if (!isset($search_config['instance'])) {
            $search_config['instance'] = array();
        }

        $search_config['instance'][$key] = $data;

        OSC::systemRegister('search_config', $search_config);
    }

    /**
     * 
     * @param string $bind
     * @param string $instance
     */
    public static function registerSearchBind($bind, $instance, $set_default = false) {
        $search_config = OSC::systemRegistry('search_config');

        if (!isset($search_config['bind'])) {
            $search_config['bind'] = array();
        }

        $search_config['bind'][$bind] = $instance;

        OSC::systemRegister('search_config', $search_config);

        if ($set_default) {
            OSC::systemRegister('search_default', $bind);
        }
    }

}
