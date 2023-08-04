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
 * OSC_Backend
 *
 * @package Helper_Backend_Template_Grid
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Backend_Template_Grid extends OSC_Object {

    /**
     *
     * @var string
     */
    protected $_index = '';

    /**
     *
     * @var array
     */
    protected $_column = array();

    /**
     *
     * @var array
     */
    protected $_button = array();

    /**
     *
     * @var string
     */
    protected $_title = '';

    /**
     *
     * @var mixed
     */
    protected $_collector = null;

    /**
     *
     * @var array
     */
    protected $_page_data = array();

    /**
     *
     * @var Helper_Backend_Template
     */
    protected $_template = null;

    /**
     *
     * @var string
     */
    protected $_icon = '';

    /**
     *
     * @var mixed
     */
    protected $_row_callback = null;

    /**
     *
     * @var string
     */
    protected $_filter_enabled = false;
    protected $_filter_url = '';
    protected $_last_filter_group_index = 'general';
    protected $_filter_group = array('general' => '');
    protected $_filter_form = array();

    /**
     *
     * @var boolean
     */
    protected $_no_head = false;

    /**
     *
     * @var boolean
     */
    protected $_using_page_header = true;

    /**
     *
     * @var array
     */
    protected $_no_row = array();

    /**
     *
     * @var string
     */
    protected $_page_request_key = 'page';
    protected $_pager_section = 5;
    protected $_sort_request_key = 'sort';
    protected $_order_request_key = 'order';

    /**
     *
     * @var OSC_Database_Model_Collection
     */
    protected $_collection = null;

    /**
     *
     * @var string
     */
    protected $_sort_condition_key = null;

    /**
     *
     * @var string
     */
    protected $_order = null;

    /**
     *
     * @var array
     */
    protected $_sort_condition = array();

    const ORDER_ASC = 'ASC';
    const ORDER_DESC = 'DESC';

    public function __construct() {
        $this->_template = OSC::helper('backend/template');
        $this->_template->addComponent('backend_grid');
    }

    /**
     * 
     * @param Helper_Backend_Template $tpl
     * @return Helper_Backend_Template_Grid
     */
    public function setTemplate($tpl) {
        $this->_template = $tpl;
        return $this;
    }

    /**
     * 
     * @param boolean $flag
     * @return Helper_Backend_Template_Grid
     */
    public function usingPageHeader($flag = true) {
        $this->_using_page_header = $flag ? true : false;
        return $this;
    }

    /**
     *
     * @param array $config
     * @return Helper_Backend_Template_Grid
     */
    public function initialize($config) {
        if (isset($config['title'])) {
            $this->setTitle($config['title'], $config['icon']);
        }

        if (isset($config['collector'])) {
            $this->setCollector($config['collector']);
        }

        if (isset($config['column'])) {
            if (is_array($config['column'])) {
                foreach ($config['column'] as $column) {
                    if (isset($column[0]) && isset($column[1])) {
                        $this->addColumn($column[0], $column[1]);
                    }
                }
            }
        }

        return $this;
    }

    /**
     *
     * @param string $order
     * @return Helper_Backend_Template_Grid 
     */
    public function setOrder($order) {
        $order = strtoupper($order);

        if ($order != self::ORDER_ASC) {
            $order = self::ORDER_DESC;
        }

        $this->_order = $order;

        return $this;
    }

    /**
     *
     * @param string $key
     * @param string $title
     * @param boolean $default
     * @return Helper_Backend_Template_Grid 
     */
    public function addSortCondition($key, $title, $default = null) {
        $key = strtolower($key);

        $this->_sort_condition[$key] = $title;

        if ($default) {
            $default = strtoupper((string) $default);

            if ($default == self::ORDER_ASC || $default == self::ORDER_DESC) {
                $this->_sort_condition_key = $key;
                $this->_order = $default;
            }
        }

        return $this;
    }

    /**
     *
     * @param string $url
     * @return Helper_Backend_Template_Grid
     */
    public function setFilterUrl($url = null) {
        $this->_filter_url = $url;
        return $this;
    }

    /**
     * 
     * @param string $index
     * @param string $title
     * @return Helper_Backend_Template_Grid
     */
    public function addFilterGroup($index, $title) {
        if ($title) {
            $this->_filter_group[$index] = $title;
            $this->_last_filter_group_index = $index;
        }

        return $this;
    }

    /**
     * 
     * @param string $index
     * @param array $config
     * @param string $group_index
     * @return Helper_Backend_Template_Grid
     */
    public function addFilterForm($index, $config, $group_index = null) {
        if (!$group_index) {
            $group_index = $this->_last_filter_group_index;
        }

        $config['group_index'] = $group_index;
        $config['index'] = $index;

        $this->_filter_form[$index] = $config;

        return $this;
    }

    /**
     *
     * @param string $index
     * @return Helper_Backend_Template_Grid
     */
    public function setIndex($index) {
        $this->_index = $index;
        return $this;
    }

    /**
     *
     * @param string $title
     * @param string $icon
     * @return Helper_Backend_Template_Grid
     */
    public function setTitle($title, $icon = null) {
        $this->_title = $title;

        if ($icon) {
            $this->_icon = $icon;
        }

        return $this;
    }

    /**
     *
     * @param boolean $set
     * @return Helper_Backend_Template_Grid
     */
    public function setNoHead($set) {
        $this->_no_head = $set;
        return $this;
    }

    /**
     *
     * @param OSC_Database_Model_Collection $collector
     * @return Helper_Backend_Template_Grid
     */
    public function setCollector($collector) {
        $this->_collector = $collector;
        return $this;
    }

    /**
     *
     * @param mixed $object
     * @return Helper_Backend_Template_Grid 
     */
    public function setRowCallback($object) {
        $this->_row_callback = $object;
        return $this;
    }

    /**
     *
     * @param string $index
     * @param array $config
     * @return Helper_Backend_Template_Grid
     */
    public function addButton($index, $config) {
        $this->_button[$index] = $config;
        return $this;
    }

    /**
     *
     * @param array $options
     * @return Helper_Backend_Template_Grid 
     */
    public function setNoRow($options) {
        $this->_no_row = $options;
        return $this;
    }

    /**
     * @param string $index
     * @param array $config
     * @return Helper_Backend_Template_Grid
     */
    public function addColumn($index, $config) {
        if (!isset($config['align']) || !in_array($config['align'], array('left', 'right', 'center'))) {
            $config['align'] = 'left';
        }

        if (isset($config['getter'])) {
            if (!is_array($config['getter']) && substr($config['getter'], 0, 1) == '.') {
                $config['getter'] = array('function', substr($config['getter'], 1));
            }
        }

        $this->_column[$index] = $config;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function render() {
        $filter_form = $this->_getFilterForm();

        $params = array(
            'rows' => $this->_getRows(),
            'index' => $this->_index,
            'filter_form' => $filter_form,
            'top_bar' => $this->_getTopBar(),
            'bottom_bar' => $this->_getBottomBar(),
            'headers' => $this->_getHeaders(),
            'using_page_header' => $this->_using_page_header
        );

        if ($this->_using_page_header) {
            $this->_template->setPageTitle($this->_title);
        } else {
            $params['icon'] = $this->_icon;
            $params['title'] = $this->title;
            $params['buttons'] = $this->_getButtons();
        }

        return $this->_template->build('backend/grid/complete', $params);
    }

    /**
     * 
     * @param string $elm_idx
     * @return string
     */
    protected function _getFilterFormElementId($elm_idx) {
        return $this->_index . '__' . '__filter__' . $elm_idx;
    }

    /**
     * 
     * @return string
     */
    protected function _getFilterForm() {
        if (count($this->_filter_form) < 1) {
            return '';
        }

        $this->_filter_enabled = true;

        $groups = array();

        foreach ($this->_filter_form as $element) {
            if (!isset($groups[$element['group_index']])) {
                $groups[$element['group_index']] = array();
            }

            $groups[$element['group_index']][] = $element;
        }

        $form_groups = array();

        foreach ($groups as $group_index => $elements) {
            if (count($elements) < 1) {
                continue;
            }

            $group_elements = array();

            $row_element_counter = 0;
            $element_counter = 0;

            foreach ($elements as $element) {
                $row_element_counter++;
                $element_counter++;

                $element['id'] = $this->_getFilterFormElementId($element['index']);
                $element['name'] = 'filter[' . $element['index'] . ']';

                $element_params = array(
                    'counter' => $element_counter,
                    'row_element_counter' => &$row_element_counter,
                    'element' => $element,
                    'index' => $this->_index,
                    'instance' => $this
                );

                if (isset($element['renderer']) && $element['renderer']) {
                    if (is_string($element['renderer'])) {
                        $group_elements[] = $this->_template->build($element['renderer'], $element_params);
                    } else {
                        $group_elements[] = call_user_func($element['renderer'], $element_params);
                    }
                } else {
                    $group_elements[] = $this->_template->build('backend/grid/filter/form/element', $element_params);
                }
            }

            $form_groups[] = $this->_template->build('backend/grid/filter/form/group', array('title' => $this->_filter_group[$group_index], 'elements' => $group_elements));
        }

        return $this->_template->build('backend/grid/filter/form/complete', array('index' => $this->_index, 'process_url' => $this->_filter_url, 'groups' => $form_groups));
    }

    /**
     * 
     * @param string $position
     * @return string
     */
    protected function _getBar($position) {
        $params = array();

        $pager = $this->_template->buildPager(
                $this->_index . '.' . $position . '_bar.pager', $this->_collection->getCurrentPage(), $this->_collection->getTotalItem(), $this->_page_request_key, $this->_pager_section, $this->_collection->getPageSize()
        );

        if ($pager) {
            $params['pager'] = $pager;
        }

        if (count($this->_sort_condition) > 0) {
            $params['sort_options'] = $this->_template->build('backend/grid/sort_option', array('index' => $this->_index, 'sort_options' => $this->_sort_condition, 'default' => array('sort' => $this->_sort_condition_key, 'order' => $this->_order), 'request_key' => array('sort' => $this->_sort_request_key, 'order' => $this->_order_request_key)));
        }

        if ($this->_filter_enabled) {
            $params['filter_toggler'] = $this->_template->build('backend/grid/filter/toggler', array('index' => $this->_index));
        } else {
            $params['filter_toggler'] = '';
        }

        if (count($params) > 0) {
            return $this->_template->build('backend/grid/' . $position . '_bar', $params);
        } else {
            return '';
        }
    }

    /**
     * 
     * @return string
     */
    protected function _getTopBar() {
        return $this->_getBar('top');
    }

    /**
     * 
     * @return string
     */
    protected function _getBottomBar() {
        return $this->_getBar('bottom');
    }

    /**
     * @return array
     */
    protected function _getRows() {
        $rows = array();

        $collector = $this->_collector;

        $sort = null;
        $order = null;

        if (count($this->_sort_condition) > 0) {
            $sort = strtolower(OSC::core('request')->get($this->_sort_request_key));
            $order = strtoupper(OSC::core('request')->get($this->_order_request_key));

            if ($sort) {
                OSC::sessionSet('backend_grid.' . $this->_index . '.sort', $sort);
            } else {
                $sort = OSC::sessionGet('backend_grid.' . $this->_index . '.sort');
            }

            if (!$sort || !isset($this->_sort_condition[$sort])) {
                if (!$this->_sort_condition_key) {
                    foreach ($this->_sort_condition as $key => $title) {
                        $sort = $key;
                        break;
                    }
                } else {
                    $sort = $this->_sort_condition_key;
                }
            }

            if ($order) {
                OSC::sessionSet('backend_grid.' . $this->_index . '.order', $order);
            } else {
                $order = OSC::sessionGet('backend_grid.' . $this->_index . '.order');
            }

            if (!$order || ($order != self::ORDER_ASC && $order != self::ORDER_DESC)) {
                if (!$this->_order) {
                    $order = self::ORDER_ASC;
                } else {
                    $order = $this->_order;
                }
            }

            $this->_sort_condition_key = $sort;
            $this->_order = $order;
        } else if (!$this->_order) {
            $this->_order = self::ORDER_ASC;
        }

        if (gettype($collector) !== 'object') {
            $this->_collection = call_user_func($collector, array('grid' => $this, 'current_page' => intval(OSC::core('request')->get($this->_page_request_key)), 'sort' => $sort, 'order' => $order));
        } else {
            $this->_collection = $collector;
        }

        $counter = 0;

        if ($this->_collection->length() < 1) {
            $this->_no_row['colspan'] = count($this->_column);
            $row_params = array('row_counter' => 1);
            $row_params['cells'] = array($this->_template->build('backend/grid/cell', array('cell' => $this->_template->build('backend/grid/cell/no_row', array('message' => $this->_no_row['message'])), 'options' => $this->_no_row, 'value' => $this->_no_row['message'])));
            $rows[] = $this->_template->build('backend/grid/row', $row_params);
        } else {
            foreach ($this->_collection as $model) {
                $counter++;

                $cells = array();

                $row_params = array('row_counter' => $counter);
                $columns = $this->_column;

                if ($this->_row_callback !== null) {
                    call_user_func($this->_row_callback, array('model' => $model, 'counter' => &$counter, 'row_params' => &$row_params, 'columns' => &$columns));
                }

                foreach ($columns as $column) {
                    if (isset($column['getter'])) {
                        if (is_array($column['getter'])) {
                            if ($column['getter'][0] == 'value') {
                                $value = $column['getter'][1];
                            } else if ($column['getter'][0] == 'function') {
                                $value = $model->$column['getter'][1]();
                            } else {
                                $value = call_user_func($column['getter'], array('model' => $model, 'row_counter' => $counter));
                            }
                        } else {
                            $value = $model->data[$column['getter']];
                        }
                    } else {
                        $value = '';
                    }

                    if (isset($column['pre_callback']) && $column['pre_callback']) {
                        $column = call_user_func($column['pre_callback'], array('column' => $column, 'value' => &$value, 'model' => $model, 'row_counter' => $counter));
                    }

                    if (isset($column['action'])) {
                        $parser_params = array('model' => $model);

                        if (!isset($column['string_dynamic_data_parser']) || !$column['string_dynamic_data_parser']) {
                            $column['action'] = $this->parseStringDynamicData($column['action'], $parser_params);

                            if (isset($column['action_confirm_message']) && $column['action_confirm_message']) {
                                $column['action_confirm_message'] = $this->parseStringDynamicData($column['action_confirm_message'], $parser_params);
                            }
                        } else {
                            $column['action'] = call_user_func($column['string_dynamic_data_parser'], $parser_params);

                            if (isset($column['action_confirm_message']) && $column['action_confirm_message']) {
                                $column['action_confirm_message'] = call_user_func($column['action_confirm_message'], $parser_params);
                            }
                        }
                    }

                    $params = array('options' => $column, 'model' => $model, 'value_idx' => $column, 'grid' => $this, 'value' => $value, 'row_counter' => $counter);

                    $render = true;

                    if (isset($column['validator']) && $column['validator']) {
                        $render = call_user_func($column['validator'], array('model' => $model, 'row_counter' => $counter, 'column' => $column));
                    }

                    if ($render) {
                        if (isset($column['renderer'])) {
                            $cell = call_user_func($column['renderer'], $params);
                        } else {
                            $cell = $this->_template->build('backend/grid/cell/' . $column['type'], $params);
                        }
                    } else {
                        $cell = '&nbsp;';
                    }


                    $cells[] = $this->_template->build('backend/grid/cell', array('cell' => $cell, 'options' => $column, 'value' => $value));
                }

                $row_params['cells'] = $cells;

                $rows[] = $this->_template->build('backend/grid/row', $row_params);
            }
        }

        return $rows;
    }

    /**
     * 
     * @param string $action
     * @param array $params
     * @return string
     */
    public function parseStringDynamicData($action, $params) {
        while (preg_match('/{{([a-z0-9_]+)}}/i', $action, $matches)) {
            $action = str_replace("{{{$matches[1]}}}", $matches[1] == 'id' ? $params['model']->getId() : $params['model']->data[$matches[1]], $action);
        }

        return $action;
    }

    /**
     * 
     * @return array
     */
    protected function _getButtons() {
        $buttons = array();

        foreach ($this->_button as $button) {
            $button['size'] = 'large';
            $buttons[] = $this->_template->build('backend/UI/button', $button);
        }

        return $buttons;
    }

    /**
     * @return string
     */
    protected function _getHeaders() {
        if ($this->_no_head) {
            return '';
        }

        $headers = array();

        foreach ($this->_column as $column) {
            $headers[] = array(isset($column['title']) ? $column['title'] : null, isset($column['width']) ? $column['width'] : null, isset($column['align']) ? $column['align'] : null);
        }

        return $this->_template->build('backend/grid/header', $headers);
    }

}
