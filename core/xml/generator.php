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
 * OSECORE XML Generator class
 *
 * @package osecore.core.XML.generator
 */
class OSC_Xml_Generator {

    protected $_rootTag = null;
    protected $_rootAttributes = null;
    protected $_tmpDoc = null;
    protected $_document = null;
    protected $_collapseNewLines = false;
    protected $_groups = array();

    public function setRoot($tag, $attributes = array()) {
        $this->_rootTag = $tag;
        $this->_rootAttributes = $this->_buildAttributeString($attributes);
    }

    public function addGroup($tag, $attributes = array()) {
        $this->_groups[$tag] = "<" . $tag . $this->_buildAttributeString($attributes) . ">";
    }

    public function buildSimpleTag($tag, $description = '', $attributes = array()) {
        return "<" . $tag . $this->_buildAttributeString($attributes) . ">" . $this->_encodeString($description) . "</" . $tag . ">";
    }

    public function buildEntry($tag, $content = array(), $attributes = array()) {
        $entry = "<" . $tag . $this->_buildAttributeString($attributes) . ">\n";

        if (is_array($content) && count($content)) {
            foreach ($content as $c) {
                $entry .= "\t\t\t" . $c . "\n";
            }
        }

        $entry .= "\t\t</" . $tag . ">";

        return $entry;
    }

    public function addEntryToGroup($tag, $entry = '') {
        $this->_tmpDoc .= $this->_groups[$tag];

        if (is_array($entry) && count($entry)) {
            foreach ($entry as $e) {
                $this->_tmpDoc .= "\n" . $e . "\n";
            }
        } elseif ($entry != '') {
            $this->_tmpDoc .= $this->_encodeString($entry);
        }

        $this->_tmpDoc .= "</" . $tag . ">\n";
    }

    public function formatDocument($entry = array()) {
        $this->_document = '<?xml version="1.0" encoding="UTF-8" ?>';

        $this->_document .= "<" . $this->_rootTag . $this->_rootAttributes . ">\n";

        $this->_document .= $this->_tmpDoc;

        $this->_document .= "\n</" . $this->_rootTag . ">";

        $this->_tmpDoc = "";
    }

    protected function _encodeString($v) {
        if (preg_match("/['\"\[\]<>&]/", $v)) {
            $v = "<![CDATA[" . $this->_convertSafecData($v) . "]]>";
        }

        if ($this->_collapseNewLines) {
            $v = str_replace("\r\n", "\n", $v);
        }

        return $v;
    }

    protected function _convertSafecData($v) {
        # Legacy
        //$v = str_replace( "<![CDATA[", "<!�|CDATA|", $v );
        //$v = str_replace( "]]>"      , "|�]>"      , $v );
        # New
        $v = str_replace("<![CDATA[", "<!#^#|CDATA|", $v);
        $v = str_replace("]]>", "|#^#]>", $v);

        return $v;
    }

    protected function _buildAttributeString($array = array()) {
        if (is_array($array) && count($array)) {
            $string = array();

            foreach ($array as $k => $v) {
                $v = trim($this->_encodeAttribute($v));

                $string[] = $k . '="' . $v . '"';
            }

            return ' ' . implode(" ", $string);
        }
    }

    protected function _encodeAttribute($t) {
        $t = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $t);
        $t = str_replace("<", "&lt;", $t);
        $t = str_replace(">", "&gt;", $t);
        $t = str_replace('"', "&quot;", $t);
        $t = str_replace("'", '&#039;', $t);

        return $t;
    }

    public function getDocument() {
        return $this->_document == '' ? $this->_tmpDoc : $this->_document;
    }

    public function setDocument($document) {
        if ($this->_document == '') {
            $this->_tmpDoc = $document;
        } else {
            $this->_document = $document;
        }
    }

    public function output() {
        header('Expires: Fri, 25 Dec 1980 00:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT');
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Content-Type: text/xml');
        echo $this->_document;
        die;
    }

}
