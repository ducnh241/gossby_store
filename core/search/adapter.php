<?php

abstract class OSC_Search_Adapter extends OSC_Object {

    protected $_offset = 0;
    protected $_page_size = 10;
    protected $_cur_page = 0;
    protected $_sort = array();
    protected $_fields = array();
    protected $_keywords = '';
    protected $_keyword_field = 'keywords';
    protected $_like_mode = false;

    /**
     *
     * @var string 
     */
    protected $_condition = array('root' => array());

    /**
     * 
     * @param array $doc
     * @return OSC_Search_Adapter
     */
    abstract public function addDocument($doc);

    /**
     * @params array $doc
     * @params array $_ [optional]
     * @return OSC_Search_Adapter
     */
    abstract public function addDocuments($doc);

    abstract protected function _processDelete();

    abstract protected function _fetchProcess($keywords);

    abstract protected function _fetchFilterQuery($options, $relation);

    abstract public function deleteDocumentById($id);

    abstract public function deleteDocumentByIds($ids);

    public function __construct() {
        $this->_skip_reset_vars[] = '_keyword_field';
        parent::__construct();
    }

    public function setConfig($config) {
        if (isset($config['keyword_field'])) {
            $config['keyword_field'] = preg_replace('/[^a-zA-Z0-9\_]/', '', $config['keyword_field']);

            if (!$config['keyword_field']) {
                throw new Exception('Search engine have to set keyword field with correct format');
            }

            $this->_keyword_field = $config['keyword_field'];
        }

        return $this;
    }

    public function setLikeMode($flag = true) {
        $this->_like_mode = $flag ? true : false;
        return $this;
    }

    /**
     * 
     * @param string $keywords
     * @return OSC_Search_Adapter
     */
    public function setKeywords($keywords) {
        $this->_keywords = $this->cleanKeywords($keywords);
        return $this;
    }

    /**
     * 
     * @return string
     */
    public function getKeywords() {
        return $this->_keywords;
    }

    public function cleanKeywords($keywords) {
        $maps = array();

        $keywords = preg_replace('/\s{2,}/', ' ', $keywords);

        $key = OSC::makeUniqid('PHRASE_MAP');

        while (preg_match("/([\-+]?)['\"].+/", $keywords)) {
            $keywords = preg_replace_callback('/([\-+]?)([\'"])(.+)/', function($matches) use (&$maps, $key) {
                $modifier = $matches[1];

                return preg_replace_callback("/^(([^{$matches[2]}]|(?<=\\\\){$matches[2]})+)(?<!\\\\){$matches[2]}/", function($matches) use(&$maps, $modifier, $key) {
                    $maps[] = $matches[1];
                    return ' ' . $modifier . $key . ':' . (count($maps) - 1);
                }, $matches[3]);

                $maps[] = $matches[1];
            }, $keywords);
        }

        $keywords = preg_split("/[ \n\r]+/", $keywords);

        foreach ($keywords as $k => $keyword) {
            $keyword = trim($keyword);

            $modifier = preg_replace('/(?=^\P{L}+)^[^-+]*([-+])\P{L}*\p{L}.+/u', '\1', $keyword);
            $type = 'word';

            if (preg_match('/^[+\-]*' . $key . ':(\d+)$/', $keyword, $matches)) {
                $keyword = $maps[$matches[1]];
                $type = 'phrase';
            } else {
                if (preg_match('/[^\p{l}\d]/u', $keyword)) {
                    $type = 'phrase';
                }
            }

            $keyword = preg_replace('/(^[^\p{L}\d]+|[^\p{L}\d]+$)/u', '', $keyword);
            $keyword = preg_replace('/([^\p{L}\d])[^\p{L}\d]+/u', '\\1', $keyword);
            $keyword = preg_replace('/[^\p{L}\d\.\-\_ ]/u', '', $keyword);

            $keyword = OSC::core('search')->processStopWord($keyword);

            if (OSC::core('string')->strlen($keyword) < OSC_Search::KEYWORD_MIN_LENGTH) {
                unset($keywords[$k]);
            } else {
                if (!in_array($modifier, array('+', '-'))) {
                    $modifier = '';
                }

                $keyword = OSC::core('search')->processSynonymWord($keyword);

                $keywords[$k] = array('type' => $type, 'value' => $keyword, 'modifier' => $modifier);
            }
        }

        return $keywords;
    }

    public function setOffset($offset) {
        $this->_offset = $offset;
        return $this;
    }

    public function getOffset() {
        if ($this->_cur_page > 0 && $this->_page_size > 0) {
            $this->_offset = ($this->_cur_page - 1) * $this->_page_size;
        }

        return $this->_offset;
    }

    public function setCurrentPage($page) {
        $this->_cur_page = $page;
        return $this;
    }

    public function setPageSize($page_size) {
        $this->_page_size = $page_size;
        return $this;
    }

    /**
     * 
     * @param string $sort
     * @param string $order
     * @return \OSC_Search_Adapter
     */
    public function addSort($sort, $order = OSC_Search::ORDER_ASC) {
        $this->_sort[$sort] = $order;
        return $this;
    }

    /**
     * @param string $field
     * @param string $_ [optional]
     * @return OSC_Search_Adapter
     */
    public function addField($field) {
        if (func_num_args() > 0) {
            $this->_fields = array_merge($this->_fields, func_get_args());
            $this->_fields = array_unique($this->_fields);

            if (in_array('*', $this->_fields)) {
                $this->_fields = array();
            }
        }

        return $this;
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
     * @return OSC_Search_Adapter
     * @throws OSC_Search_Exception_Condition
     */
    public function addFilterClause($clause_idx = '', $relation = null, $p_clause_idx = null) {
        $clause_idx = $this->_cleanClauseIndex($clause_idx);

        if (!$clause_idx) {
            return $this;
        }

        if (!$p_clause_idx) {
            $p_clause_idx = OSC_Search::_ROOT_CLAUSE_IDX;
        } else {
            $p_clause_idx = $this->_cleanClauseIndex($p_clause_idx);

            if (!$this->checkClauseIsExist($p_clause_idx)) {
                throw new Exception("Search condition: The clause index [{$p_clause_idx}] is not exist");
            }
        }

        $this->_condition[$p_clause_idx][] = array(
            'relation' => $relation ? $relation : OSC_Search::_DEFAULT_RELATION,
            'clause_idx' => $clause_idx,
            'type' => 'clause'
        );

        $this->_condition[$clause_idx] = array();

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
     * @return OSC_Search_Adapter
     * @throws OSC_Search_Exception_Condition
     */
    public function addFilter($field, $filter_value, $operator = null, $relation = null, $clause_idx = null, $cond_idx = null) {
        if (!$clause_idx) {
            $clause_idx = OSC_Search::_ROOT_CLAUSE_IDX;
        } else {
            $clause_idx = $this->_cleanClauseIndex($clause_idx);

            if (!$this->checkClauseIsExist($clause_idx)) {
                throw new Exception("Search condition: The clause index [{$clause_idx}] is not exist");
            }
        }

        $field = preg_replace('/[^a-zA-Z0-9\._]/', '', $field);
        $field = preg_replace('/\.{2,}$/', '.', $field);
        $field = preg_replace('/^\.|\.$/', '', $field);

        if (strlen($field) < 1) {
            return $this;
        }

        if (!$operator) {
            $operator = OSC_Search::_DEFAULT_OPERATOR;
        }

        $operator = strtoupper($operator);

        $negation = false;

        if (preg_match('/(NOT(:|_)|\-)(.+)/i', $operator, $matches)) {
            $negation = true;
            $operator = $matches[3];
        }

        if (!$this->operatorSupported($operator)) {
            throw new Exception("The operator [{$operator}] is not supported");
        }

        $this->_condition[$clause_idx][$cond_idx ? $cond_idx : count($this->_condition[$clause_idx])] = array(
            'relation' => $relation ? $relation : OSC_Search::_DEFAULT_RELATION,
            'type' => 'condition',
            'field' => $field,
            'operator' => $operator,
            'filter_value' => $filter_value,
            'negation' => $negation
        );

        return $this;
    }

    /**
     * 
     * @param string $operator
     * @return boolean
     */
    public function operatorSupported($operator) {
        return OSC::core('search')->operatorSupported($operator);
    }

    public function delete() {
        try {
            $this->_processDelete();
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }

        $this->_reset();

        return $this;
    }

    /**
     * 
     * @param array $options allow_no_keywords, auto_fix_page
     * @return array
     * @throws Exception
     */
    public function fetch($options = array()) {
        if (!is_array($options)) {
            $options = array();
        }

        if (count($this->_keywords) < 1 && !in_array('allow_no_keywords', $options, true)) {
            throw new Exception("Search without conditions is not able to work");
        }

        $data = $this->_fetchProcess($this->_keywords);

        $data['total_item'] = intval($data['total_item']);
        $data['keywords'] = $this->_keywords;
        $data['offset'] = $this->getOffset();
        $data['page_size'] = $this->_page_size;
        $data['total_page'] = ceil($data['total_item'] / $this->_page_size);
        $data['current_page'] = floor($data['offset'] / $this->_page_size) + 1;

        if ($data['current_page'] > 0 && count($data['docs']) < 1 && in_array('auto_fix_page', $options, true)) {
            if ($data['total_item'] < 1) {
                $_data = $this->setPageSize(1)->setCurrentPage(1)->_fetchProcess($this->_keywords);
                $data['total_page'] = $_data['total_item'] > 0 ? ceil($_data['total_item'] / $data['page_size']) : 0;
            }

            if ($data['total_page'] > 0) {
                $data = $this->setPageSize($data['page_size'])->setCurrentPage($data['total_page'])->fetch();
            }
        }

        $this->_reset();

        return $data;
    }

    public function fetchFilter($options = array(), $relation = OSC_Search::_DEFAULT_RELATION) {
        if (!is_array($options)) {
            $options = array();
        }

        if (empty($options)) {
            throw new Exception("Search without conditions is not able to work");
        }

        $data = $this->_fetchFilterQuery($options, $relation);

        $data['total_item'] = intval($data['total_item']);
        $data['keywords'] = $this->_keywords;
        $data['offset'] = $this->getOffset();
        $data['page_size'] = $this->_page_size;
        $data['total_page'] = ceil($data['total_item'] / $this->_page_size);
        $data['current_page'] = floor($data['offset'] / $this->_page_size) + 1;

        if ($data['current_page'] > 0 && count($data['docs']) < 1 && in_array('auto_fix_page', $options, true)) {
            if ($data['total_item'] < 1) {
                $_data = $this->setPageSize(1)->setCurrentPage(1)->_fetchProcess($this->_keywords);
                $data['total_page'] = $_data['total_item'] > 0 ? ceil($_data['total_item'] / $data['page_size']) : 0;
            }

            if ($data['total_page'] > 0) {
                $data = $this->setPageSize($data['page_size'])->setCurrentPage($data['total_page'])->fetch();
            }
        }

        $this->_reset();

        return $data;
    }

    public function highlightKeywords($orig_string, $keywords, $html_content_flag = false, $like_mode = false, $clean_string_callback = false) {
        if ($html_content_flag) {
            try {
                $dom_doc = OSC::makeDomFromContent($orig_string);
            } catch (Exception $ex) {
                return $orig_string;
            }

            $xpath = new DOMXPath($dom_doc);

            /* @var $text_node DOMText */
            foreach ($xpath->query('//text()') as $text_node) {
                $text = $this->highlightKeywords($text_node->wholeText, $keywords, false, $like_mode, $clean_string_callback);

                $new_dom_doc = OSC::makeDomFromContent('<div>' . $text . '</div>');

                /* @var $body DOMElement */
                $body = $new_dom_doc->getElementsByTagName('div')[0];

                for ($i = 0; $i < $body->childNodes->length; $i ++) {
                    $text_node->parentNode->insertBefore($dom_doc->importNode($body->childNodes[$i], true), $text_node);
                }

                $text_node->parentNode->removeChild($text_node);
            }

            return $dom_doc->saveHTML();
        }

        $keyword_phrases = array();
        $keyword_words = array();

        foreach ($keywords as $keyword) {
            if ($keyword['modifier'] == '-') {
                continue;
            }

            $keyword['value'] = array(strtolower($keyword['value']), strlen($keyword['value']));

            if ($keyword['type'] == 'word') {
                $keyword_words[] = $keyword['value'];
            } else {
                $keyword_phrases[] = $keyword['value'];
            }
        }

        $key = OSC::makeUniqid();
        $maps = array();
        $search = array();

        $cleaned_string = $orig_string;

        if (OSC::isCallable($clean_string_callback)) {
            $cleaned_string = OSC::call($clean_string_callback, $cleaned_string);
        }

        $cleaned_string = strtolower($cleaned_string);

        foreach (array('phrase' => $keyword_phrases, 'word' => $keyword_words) as $keyword_type => $keywords) {
            foreach ($keywords as $keyword) {
                $pos = mb_strpos($cleaned_string, $keyword[0]);

                if ($pos === false) {
                    continue;
                }

                if ($keyword_type == 'phrase' || !$like_mode) {
                    if ($pos > 0) {
                        $prev_char = mb_substr($cleaned_string, $pos - 1, 1);

                        if (preg_match('/[\p{L}\d\.\-\_]/u', $prev_char)) {
                            continue;
                        }
                    }

                    if ($pos + $keyword[1] < mb_strlen($cleaned_string)) {
                        $next_char = mb_substr($cleaned_string, $pos + $keyword[1], 1);

                        if (preg_match('/[\p{L}\d\.\-\_]/u', $next_char)) {
                            continue;
                        }
                    }
                }

                $maps[] = '<span class="search-highlight">' . mb_substr($orig_string, $pos, $keyword[1]) . '</span>';

                $index = count($maps) - 1;

                $search[] = $key . ':' . $index;

                $orig_string = OSC::core('string')->substr_replace($orig_string, $key . ':' . $index, $pos, $keyword[1]);
                $cleaned_string = OSC::core('string')->substr_replace($cleaned_string, $key . ':' . $index, $pos, $keyword[1]);
            }
        }

        return str_replace($search, $maps, $orig_string);
    }

}
