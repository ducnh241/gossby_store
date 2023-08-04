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
 * @package Helper_Backend_Template_Form
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class Helper_Backend_Template_Form extends OSC_Object {

    /**
     *
     * @var Helper_Backend_Template
     */
    protected $_template = null;

    /**
     *
     * @var string
     */
    protected $_index = '';

    /**
     *
     * @var title 
     */
    protected $_title = '';

    /**
     *
     * @var title 
     */
    protected $_icon = '';

    /**
     *
     * @var string 
     */
    protected $_process_url = '';

    /**
     *
     * @var string 
     */
    protected $_cancel_url = '';

    /**
     *
     * @var string 
     */
    protected $_default_tab = '';

    /**
     *
     * @var array
     */
    protected $_buttons = array();

    /**
     *
     * @var array 
     */
    protected $_tabs = array();

    /**
     *
     * @var array 
     */
    protected $_groups = array();

    /**
     *
     * @var array 
     */
    protected $_elements = array();

    /**
     *
     * @var array 
     */
    protected $_hidden_elements = array();

    /**
     *
     * @var array 
     */
    protected $_js_hook = array();

    public function __construct() {
        $this->_template = OSC::helper('backend/template');
        $this->_template->addComponent('backend_form');
    }

    /**
     * 
     * @param Helper_Backend_Template $tpl
     * @return Helper_Backend_Template_Form
     */
    public function setTemplate($tpl) {
        $this->_template = $tpl;
        return $this;
    }

    /**
     * 
     * @param string $index
     * @return Helper_Backend_Template_Form
     */
    public function setIndex($index) {
        $this->_index = $index;
        return $this;
    }

    /**
     * 
     * @param string $title
     * @param string $icon
     * @return Helper_Backend_Template_Form
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
     * @param string $url
     * @return Helper_Backend_Template_Form
     */
    public function setProcessUrl($url) {
        $this->_process_url = $url;
        return $this;
    }

    /**
     * 
     * @param string $url
     * @return Helper_Backend_Template_Form
     */
    public function setCancelUrl($url) {
        $this->_cancel_url = $url;
        return $this;
    }

    /**
     * 
     * @param string $index
     * @param array $options
     * @return Helper_Backend_Template_Form
     */
    public function addTab($index, $options) {
        $options['index'] = $index;

        $this->_tabs[$index] = $options;

        return $this;
    }

    /**
     * 
     * @param string $index
     * @param string $title
     * @param string $tab_index
     * @return Helper_Backend_Template_Form
     */
    public function addGroup($index, $title = null, $tab_index = null) {
        if (!$tab_index) {
            $tab_index = $this->_getCurrentTabIndex();

            if (!$tab_index) {
                return $this;
            }
        }

        $this->_groups[$tab_index][$index] = array('index' => $index, 'title' => $title);

        return $this;
    }

    /**
     * 
     * @param string $index
     * @return Helper_Backend_Template_Form
     */
    public function removeTab($index) {
        unset($this->_tabs[$index]);
        unset($this->_groups[$index]);
        unset($this->_elements[$index]);

        return $this;
    }

    /**
     * 
     * @param string $index
     * @param string $tab_index
     * @param string $group_index
     * @return Helper_Backend_Template_Form
     */
    public function removeElement($index, $tab_index = null, $group_index = null) {
        if (!$tab_index) {
            $tab_index = $this->_getCurrentTabIndex();

            if ($tab_index === false) {
                return $this;
            }
        } else if (!isset($this->_tabs[$tab_index])) {
            return $this;
        }

        if (!$group_index) {
            $group_index = $this->_getCurrentGroupIndex($tab_index);

            if ($group_index === false) {
                return $this;
            }
        } else if (!isset($this->_groups[$tab_index][$group_index])) {
            return $this;
        }

        unset($this->_elements[$tab_index][$group_index][$index]);

        return $this;
    }

    /**
     * 
     * @param string $index
     * @param array $options
     * @return Helper_Backend_Template_Form
     */
    public function addHiddenElement($index, $options) {
        $options['index'] = $index;

        $this->_hidden_elements[$index] = $options;

        return $this;
    }

    /**
     *
     * @return string
     */
    protected function _getCurrentTabIndex() {
        $tab_index = end($this->_tabs);

        if ($tab_index === false) {
            return false;
        }

        return $tab_index['index'];
    }

    /**
     *
     * @return string
     */
    protected function _getCurrentGroupIndex($tab_index) {
        $group_index = end($this->_groups[$tab_index]);

        if ($group_index === false) {
            return false;
        }

        return $group_index['index'];
    }

    /**
     * 
     * @param string $index
     * @param array $options
     * @param string $tab_index
     * @param string $group_index
     * @return Helper_Backend_Template_Form
     */
    public function addElement($index, $options, $tab_index = null, $group_index = null) {
        if (!$tab_index) {
            $tab_index = $this->_getCurrentTabIndex();

            if ($tab_index === false) {
                return $this;
            }
        }

        if (!$group_index) {
            $group_index = $this->_getCurrentGroupIndex($tab_index);

            if ($group_index === false) {
                return $this;
            }
        }

        $options['index'] = $index;

        if (!isset($this->_elements[$tab_index])) {
            $this->_elements[$tab_index] = array();
        }

        if (!isset($this->_elements[$tab_index][$group_index])) {
            $this->_elements[$tab_index][$group_index] = array();
        }

        $this->_elements[$tab_index][$group_index][$index] = $options;

        return $this;
    }

    /**
     * 
     * @param string $index
     * @param array $config
     * @return Helper_Backend_Template_Form
     */
    public function addButton($index, $config) {
        $this->_buttons[$index] = $config;
        return $this;
    }

    /**
     * 
     * @param string $tab_index
     * @return Helper_Backend_Template_Form
     */
    public function setDefaultTab($tab_index) {
        if (array_key_exists($tab_index, $this->_tabs)) {
            $this->_default_tab = $tab_index;
        }

        return $this;
    }

    /**
     * 
     * @return string
     */
    public function render() {
        $lang = OSC::core('language')->get();

        $this->addButton(
                'save_and_continue', array(
            'label' => $lang['core.save_and_continue'],
            'icon' => 'check-square-o',
            'color' => 'blue',
            'type' => 'submit',
        ));

        $this->addButton(
                'save', array(
            'label' => $lang['core.save'],
            'icon' => 'check-square',
            'type' => 'submit',
        ));

        if ($this->_cancel_url) {
            $this->addButton(
                    'cancel', array(
                'label' => $lang['core.cancel'],
                'icon' => 'minus-square-o',
                'color' => 'red',
                'action' => $this->_cancel_url
            ));
        }

        $buttons = $this->_getButtons();
        $tabs = $this->_getTabs();
        $elements = $this->_getElements();
        $hidden_elements = $this->_getHiddenElements();

        $this->_template->setPageTitle($this->_title);

        return $this->_template->build(
                        'backend/form/complete', array(
                    'index' => $this->_index,
                    'title' => $this->_title,
                    'icon' => $this->_icon,
                    'process_url' => $this->_process_url,
                    'buttons' => $buttons,
                    'tabs' => $tabs,
                    'elements' => $elements,
                    'hidden_elements' => $hidden_elements,
                    'js_hook' => $this->_js_hook,
                        )
        );
    }

    /**
     * 
     * @return array
     */
    protected function _getButtons() {
        $buttons = array();

        foreach ($this->_buttons as $button) {
            $button['size'] = 'large';
            $buttons[] = $this->_template->build('backend/UI/button', $button);
        }

        return $buttons;
    }

    /**
     * 
     * @return array
     */
    protected function _getTabs() {
        $tabs = array();

        foreach ($this->_tabs as $tab) {
            if (!$this->_default_tab) {
                $this->setDefaultTab($tab['index']);
            }

            if (!isset($tab['open_hook'])) {
                $tab['open_hook'] = null;
            }

            if (!isset($tab['close_hook'])) {
                $tab['close_hook'] = null;
            }

            $this->_js_hook[$tab['index']] = array('open' => array(), 'close' => array());

            $tabs[] = $this->_template->build('backend/form/tab', array('index' => $this->_index, 'tab' => $tab, 'active' => $this->_default_tab == $tab['index']));
        }

        return $tabs;
    }

    /**
     * 
     * @param string $elm_index
     * @return string
     */
    protected function _getElementId($elm_index) {
        $frm_index = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $this->_index);
        $elm_index = preg_replace('/[^a-zA-Z0-9\-_]/', '_', $elm_index);

        return $frm_index . '__elements__' . $elm_index;
    }

    /**
     * 
     * @return array
     */
    protected function _getHiddenElements() {
        $elements = array();

        foreach ($this->_hidden_elements as $element) {
            $element['id'] = $this->_getElementId($element['index']);
            $elements[] = $this->_template->build('backend/UI/form/hidden', $element);
        }

        return $elements;
    }

    /**
     * 
     * @return array
     */
    protected function _getElements() {
        $frm_elements = array();

        foreach ($this->_tabs as $tab) {
            if (!$this->_default_tab) {
                $this->setDefaultTab($tab['index']);
            }

            $tab_elements = array();

            foreach ($this->_elements[$tab['index']] as $group_index => $elements) {
                $group_elements = array();

                foreach ($elements as $element) {
                    $element['require'] = isset($element['require']) && $element['require'];

                    if (!isset($element['uiconfig'])) {
                        $element['uiconfig'] = array();
                    }

                    if (!isset($element['value'])) {
                        $element['value'] = '';
                    }

                    $element['id'] = $this->_getElementId($element['index']);

                    if (isset($element['depends'])) {
                        if (!is_array($element['depends'])) {
                            unset($element['depends']);
                        } else {
                            $depends = array();

                            foreach ($element['depends'] as $k => $v) {
                                $depends[$this->_getElementId($k)] = $v;
                            }

                            $element['depends'] = $depends;
                        }
                    }

                    $element_params = array(
                        'index' => $this->_index,
                        'tab' => $tab,
                        'element' => $element,
                        'instance' => $this
                    );

                    if (isset($element['renderer']) && $element['renderer']) {
                        if (is_string($element['renderer'])) {
                            $group_elements[] = $this->_template->build($element['renderer'], $element_params);
                        } else {
                            if (!isset($element['renderer']['callback'])) {
                                throw new OSC_Exception_Condition("Backend form :: No renderer for element [{$group_index}::{$element['index']}]");
                            }

                            if (!isset($element['renderer']['params'])) {
                                $element['renderer']['params'] = array();
                            }

                            array_unshift($element['renderer']['params'], $element_params);

                            $group_elements[] = call_user_func_array($element['renderer']['callback'], $element['renderer']['params']);
                        }
                    } else {
                        $group_elements[] = $this->_template->build('backend/form/element', $element_params);
                    }
                }

                $tab_elements[] = $this->_template->build('backend/form/group', array('title' => $this->_groups[$tab['index']][$group_index]['title'], 'elements' => $group_elements));
            }

            $frm_elements[] = $this->_template->build(
                    'backend/form/tab_elements', array(
                'index' => $this->_index,
                'tab' => $tab,
                'elements' => $tab_elements,
                'active' => $this->_default_tab == $tab['index']
            ));
        }

        return $frm_elements;
    }

}
