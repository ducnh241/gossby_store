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
 * OSC_Framework::XML_Parser
 *
 * @package OSC_Xml_Parser
 */
class OSC_Xml_Parser {

    protected $_parser = null;
    protected $_document = null;
    protected $_element = null;

    protected function _construct() {
        $this->_parser = xml_parser_create();

        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, 0);
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, '_open', '_close');
        xml_set_character_data_handler($this->_parser, '_data');

        $this->_document = array('children' => array());
        $this->_element = &$this->_document;
    }

    protected function _destruct() {
        xml_parser_free($this->_parser);
    }

    public function parse($data) {
        $this->_construct();

        xml_parse($this->_parser, $data);
        $tmp = $this->_document;

        $this->_destruct();

        $tmp = $tmp['children'];
        $tmp = end($tmp);
        $tmp = end($tmp);

        return $tmp;
    }

    protected function _open($parser, $tag, $attributes) {
        $element = array(
            'tag' => $tag,
            'value' => '',
            'attributes' => $attributes,
            'children' => array(),
            'parent' => &$this->_element
        );

        if (!isset($this->_element['children'][$tag])) {
            $this->_element['children'][$tag] = array();
        }

        $this->_element['children'][$tag][] = & $element;

        $this->_element = & $element;
    }

    protected function _data($parser, $data) {
        $this->_element['value'] = trim($data);
    }

    protected function _close($parser, $tag) {
        $this->_element = & $this->_element['parent'];
    }

}
