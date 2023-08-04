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
 * OSC_Framework::String
 *
 * @package OSC_Core
 * @author  Le Tuan Sang <batsatla@gmail.com>
 */
class OSC_String extends OSC_Object {

    public function __construct() {
        if (function_exists('mb_internal_encoding')) {
            mb_internal_encoding("UTF-8");
        }
    }

    public function slice($string, $length, $suffix = null) {
        if ($this->strlen($string) <= $length) {
            return $string;
        }

        if (gettype($suffix) != 'string') {
            $suffix = '...';
        }

        $length -= $this->strlen($suffix);

        return $this->substr($string, 0, $length) . $suffix;
    }

    public function toPHP($data, $var_key = 'data', $protector_code = true) {
        $data = $this->arrToStr($data);

        if ($var_key === false) {
            return $data;
        } else if (!$var_key) {
            $var_key = 'data';
        }

        if ($protector_code) {
            $protector_code = <<<EOF
if(! defined('OSC_INNER')) {
    header("HTTP/1.0 404 Not Found");
    die;
}
EOF;
        } else {
            $protector_code = '';
        }

        $data = <<<EOF
<?php
{$protector_code}
\${$var_key} = {$data};
EOF;

        return $data;
    }

    public function arrToStr($arr, $space = '    ') {
        if (is_array($arr)) {
            foreach ($arr as $k => $v) {
                if (trim($k) == '') {
                    unset($arr[$k]);
                    continue;
                }

                if (is_array($v)) {
                    $v = $this->arrToStr($v, $space . "    ");
                } else {
                    if (is_object($v)) {
                        if ($v instanceof stdClass) {
                            $v_to_arr = array();

                            foreach (get_object_vars($v) as $v_k => $v_v) {
                                $v_to_arr[$v_k] = $v_v;
                            }

                            $v = '(Object) ' . $this->arrToStr($v_to_arr, $space . "    ");
                        } else if (method_exists($v, 'selfSerialize')) {
                            $v = $v->selfSerialize();
                        } else {
                            $v = "'" . get_class($v) . "'";
                        }
                    } elseif ($v === '') {
                        $v = "''";
                    } elseif ($v === false) {
                        $v = 'false';
                    } elseif ($v === true) {
                        $v = 'true';
                    } elseif ($v === null) {
                        $v = 'null';
                    } else {
                        //$v = str_replace('$', '&#036;', $v);
                        $v = str_replace('\\', '\\\\', $v);
                        $v = preg_match('/([^0-9]|^0)/', $v) ? "'" . $this->cleanQuote($v) . "'" : $v;
                    }
                }

                $k = str_replace('$', '&#036;', $k);

                $arr[$k] = (preg_match('/[^0-9]/', $k) ? "'" . $this->cleanQuote($k) . "'" : $k ) . ' => ' . $v;
            }

            $arr = implode(",\r\n{$space}", $arr);

            $arr = "[\n{$space}{$arr}]";
        } else {
            $arr = preg_match('/([^\.0-9]|\..*\.)/', $arr) ? "'" . $this->cleanQuote($arr) . "'" : $arr;

            if (is_object($arr)) {
                $arr = "'" . get_class($arr) . "'";
            } elseif ($arr === '') {
                $arr = "''";
            } elseif ($arr === false) {
                $arr = 'false';
            } elseif ($arr === true) {
                $arr = 'true';
            } elseif ($arr === null) {
                $arr = 'null';
            } else {
                $arr = str_replace('$', '&#036;', $arr);
            }
        }

        return $arr;
    }

    public function cleanQuote($str, $delimiter = "'", $clean_by_escape = true) {
        if ($clean_by_escape) {
            $str = str_replace($delimiter, "\\{$delimiter}", $str);
        } else {
            $str = str_replace($delimiter, htmlentities($delimiter), $str);
        }

        return $str;
    }

    public function cleanPattern($txt, $block = '/') {
        if (!$txt) {
            return $txt;
        }

        $txt = str_replace("\\", "\\\\", $txt);
        $txt = str_replace("|", "\\|", $txt);
        $txt = str_replace("]", "\\]", $txt);
        $txt = str_replace("[", "\\[", $txt);
        $txt = str_replace(")", "\\)", $txt);
        $txt = str_replace("(", "\\(", $txt);
        $txt = str_replace("^", "\\^", $txt);
        $txt = str_replace(".", "\\.", $txt);
        $txt = str_replace("+", "\\+", $txt);
        $txt = str_replace("?", "\\?", $txt);
        $txt = str_replace("*", "\\*", $txt);
        $txt = str_replace("{", "\\{", $txt);
        $txt = str_replace("}", "\\}", $txt);
        $txt = str_replace($block, "\\{$block}", $txt);

        return $txt;
    }

    public function convertHtmlSpecialChars($txt) {
        if (!$txt) {
            return $txt;
        }

//        $txt = preg_replace("/&(?!#[0-9]+;)/s", '&amp;', $txt);
//        $txt = str_replace("<", "&lt;", $txt);
//        $txt = str_replace(">", "&gt;", $txt);
//        $txt = str_replace('"', "&quot;", $txt);
//        $txt = str_replace("'", '&#39;', $txt);
//        $txt = str_replace("!", "&#33;", $txt);
//        $txt = preg_replace("/&amp;/i", '&', $txt);

        $txt = str_replace("&", '&amp;', $txt);
        $txt = str_replace("<", "&lt;", $txt);
        $txt = str_replace(">", "&gt;", $txt);
        $txt = str_replace('"', "&quot;", $txt);
        $txt = str_replace("'", '&#39;', $txt);
        $txt = str_replace("!", "&#33;", $txt);

        return $txt;
    }

    public function unConvertHtmlSpecialChars($txt) {
        if (!$txt) {
            return $txt;
        }

//        $txt = str_replace("&#33;", "!", $txt);
//        $txt = str_replace("&#39;", "'", $txt);
//        $txt = str_replace("&quot;", '"', $txt);
//        $txt = str_replace("&gt;", ">", $txt);
//        $txt = str_replace("&lt;", "<", $txt);
//        $txt = str_replace("&amp;", "&", $txt);

        $txt = str_replace("&#33;", "!", $txt);
        $txt = str_replace("&#39;", "'", $txt);
        $txt = str_replace("&quot;", '"', $txt);
        $txt = str_replace("&gt;", ">", $txt);
        $txt = str_replace("&lt;", "<", $txt);
        $txt = str_replace("&amp;", "&", $txt);

        return $txt;
    }

    public function brToNewLine($txt) {
        if (!$txt) {
            return $txt;
        }

        return preg_replace("/<br\s*\/?>/i", "\n", $txt);
    }

    public function newLineToBr($txt) {
        return $this->nl2br($txt);
    }

    public function nl2br($txt, $is_xhtml = true) {
        if (!$txt) {
            return $txt;
        }

        return str_replace("\n", "<br" . ($is_xhtml ? ' /' : '') . ">", $txt);
    }

    public function safeslashes($txt) {
        if (!$txt) {
            return $txt;
        }

        if (get_magic_quotes_gpc()) {
            $txt = stripslashes($txt);
        }

        return preg_replace("/\\\(?!&amp;#|\?#)/", "&#92;", $txt);
    }

    /**
     *
     * @param string $string
     * @return integer
     */
    public function strlen($string) {
        if (function_exists('mb_strlen')) {
            return mb_strlen($string);
        }

        return strlen($string);
    }

    public function substr_replace($string, $replacement, $start = 0, $length = null) {
        $start = intval($start);
        $length = intval($length);

        if ($start < 1) {
            $start = 0;
        }

        if ($length < 1) {
            $length = '*';
        } else {
            $length = '{' . $length . '}';
        }

        return preg_replace_callback("/^(.{{$start}})(.{$length})/us", function($matches) use($replacement) {
            return $matches[1] . $replacement;
        }, $string);
    }

    /**
     *
     * @param string $string
     * @param integer $start
     * @param integer $length
     * @return string
     */
    public function substr($string, $start, $length = null) {
        if (function_exists('mb_substr')) {
            return mb_substr($string, $start, $length);
        }

        return substr($string, $start, $length);
    }

    public function cleanHtml($string) {
        $DOM = OSC::core('DOM');

        $string = strip_tags($string, '<div>,<p>,<br>');

        $string = $DOM->recurseAndParse('div', $string, array('function' => array($this, "cleanHtmlTag2Br")));
        $string = $DOM->recurseAndParse('p', $string, array('function' => array($this, "cleanHtmlTag2Br")));
        $string = str_replace('<break_after><break_before>', "\n", $string);
        $string = str_replace(array('<break_after>', '<break_before>'), "\n", $string);

        $string = preg_replace('/<br\s*\/?>/i', "\n", $string);
        $string = trim($string);

        return $string;
    }

    public function cleanHtmlTag2Br($params, $tag, $between_text) {
        $between_text = OSC::core('DOM')->recurseAndParse($tag, trim($between_text), array('function' => array($this, "cleanHtmlTag2Br")));

        if ($between_text != '') {
            $between_text = "<break_before>{$between_text}<break_after>";
        }

        return $between_text;
    }

    public function cleanUrlTitle($title, $replacement = '-') {
        $title = preg_replace('/[^a-zA-Z0-9\-\.]/', $replacement, $title);

        foreach (array('\\.', $replacement) as $c) {
            $title = preg_replace("/{$c}{2,}/", $c, $title);
            $title = preg_replace("/^{$c}|{$c}$/", '', $title);
        }

        return $title;
    }

    public function cleanTags($tags, $separate = ',') {
        OSC::core('observer')->dispatchEvent('clean_tag', array('tags' => &$tags));

        $tags = preg_replace("/{$separate}{2,}/", $separate, $tags);
        $tags = preg_replace("/^{$separate}+|{$separate}+$/", '', $tags);
        $tags = preg_replace("/^\s{2,}$/", ' ', $tags);

        if (!$tags) {
            return false;
        }

        $tags = preg_replace("/\s+{$separate}|{$separate}\s+/", $separate, $tags);

        $arr = explode($separate, $tags);

        $tags = array();

        foreach ($arr as $k => $v) {
            $v = preg_replace('/\s+/', '_', trim($v));

            if (strlen($v) > 3 && !in_array($v, $tags)) {
                $tags[] = $v;
            }
        }

        if (!count($tags)) {
            return false;
        }

        return $tags;
    }

    public static function removeInvalidCharacter($input) {
        $old_setting = ini_set('mbstring.substitute_character', '"none"');

        $input = mb_convert_encoding($input, 'UTF-8', 'auto');

        ini_set('mbstring.substitute_character', $old_setting);

        $output = preg_replace('/[^\x{0009}\x{000a}\x{000d}\x{0020}-\x{D7FF}\x{E000}-\x{FFFD}]+/u', '', $input);

        if (is_null($output)) {
            $osc_string = new self();
            $output = $osc_string->_ordsToUTFString($osc_string->_UTFStringToOrds($input), true);
        }

        return $output;
    }

    protected function _UTFStringToOrds($input, $encoding = 'UTF-8') {
        $input = mb_convert_encoding($input, "UCS-4BE", $encoding);

        $ords = array();

        for ($i = 0; $i < mb_strlen($input, "UCS-4BE"); $i++) {
            $s2 = mb_substr($input, $i, 1, "UCS-4BE");
            $val = unpack("N", $s2);
            $ords[] = $val[1];
        }

        return $ords;
    }

    protected function _ordsToUTFString($ords, $scrub_XML = false) {
        $output = '';

        foreach ($ords as $ord) {
            if ($ord < 0 || ($ord >= 0xD800 && $ord <= 0xDFFF) || $ord == 0xFEFF || $ord > 0x10ffff) {
                continue;
            } else if ($scrub_XML && ($ord < 0x9 || $ord == 0xB || $ord == 0xC || ($ord > 0xD && $ord < 0x20) || $ord == 0xFFFE || $ord == 0xFFFF)) {
                continue;
            } else if ($ord <= 0x007f) {
                $output .= chr($ord);
                continue;
            } else if ($ord <= 0x07ff) {
                $output .= chr(0xc0 | ($ord >> 6));
                $output .= chr(0x80 | ($ord & 0x003f));
                continue;
            } else if ($ord <= 0xffff) {
                $output .= chr(0xe0 | ($ord >> 12));
                $output .= chr(0x80 | (($ord >> 6) & 0x003f));
                $output .= chr(0x80 | ($ord & 0x003f));
                continue;
            } else if ($ord <= 0x10ffff) {
                $output .= chr(0xf0 | ($ord >> 18));
                $output .= chr(0x80 | (($ord >> 12) & 0x3f));
                $output .= chr(0x80 | (($ord >> 6) & 0x3f));
                $output .= chr(0x80 | ($ord & 0x3f));
                continue;
            }
        }

        return $output;
    }

    public function handleDateRange($date_range, $return_string = true)
    {
        if (!preg_match('/^\s*(\d{1,2})\/(\d{1,2})\/(\d{4})(\s*\-\s*(\d{1,2})\/(\d{1,2})\/(\d{4}))?\s*$/', $date_range, $matches)) {
            throw new Exception('Date data is incorrect');
        }

        for ($i = 1; $i <= 7; $i++) {
            if ($i == 4) {
                continue;
            }

            $matches[$i] = intval($matches[$i]);
        }

        if (!checkdate($matches[2], $matches[1], $matches[3]) || ($matches[5] && !checkdate($matches[6], $matches[5], $matches[7]))) {
            throw new Exception('Date data is incorrect');
        }

        $compare_start = intval(str_pad($matches[3], 4, 0, STR_PAD_LEFT) . str_pad($matches[2], 2, 0, STR_PAD_LEFT) . str_pad($matches[1], 2, 0, STR_PAD_LEFT));

        if ($matches[5]) {
            $compare_end = intval(str_pad($matches[7], 4, 0, STR_PAD_LEFT) . str_pad($matches[6], 2, 0, STR_PAD_LEFT) . str_pad($matches[5], 2, 0, STR_PAD_LEFT));

            if ($compare_start > $compare_end) {
                $buff = $compare_end;
                $compare_end = $compare_start;
                $compare_start = $buff;
            }

            $range_timestamp = [
                'begin' => mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]),
                'end' => mktime(23, 59, 59, $matches[6], $matches[5], $matches[7])
            ];
        } else {
            $range_timestamp = [
                'begin' => mktime(0, 0, 0, $matches[2], $matches[1], $matches[3]),
                'end' => mktime(23, 59, 59, $matches[2], $matches[1], $matches[3])
            ];
        }

        $begin_date = date('d/m/Y', $range_timestamp['begin']);
        $end_date = date('d/m/Y', $range_timestamp['end']);

        if ($begin_date == $end_date) {
            $range_text = $begin_date;
        } else {
            $range_text = $begin_date . ' - ' . $end_date;
        }
        if ($return_string) {
            return $range_text;
        }
        return $range_timestamp;
    }

    public function getStringBetween($string, $start, $end) {
        $string = ' ' . $string;
        $ini = strpos($string, $start);
        if ($ini == 0) return '';
        $ini += strlen($start);
        $len = strpos($string, $end, $ini) - $ini;

        return substr($string, $ini, $len);
    }

    public function getStringBetweenAndReplace($string, $needle_start, $needle_end, $replacement) {
        $pos = strpos($string, $needle_start);
        if ($pos === false) {
            return $string;
        }

        $start = $pos + strlen($needle_start);

        $pos = strpos($string, $needle_end, $start);
        $end = $pos === false ? strlen($string) : $pos;

        return substr_replace($string, $replacement, $start, $end - $start);
    }

    public function removeEmoji($text){
        return preg_replace('/([*#0-9](?>\\xEF\\xB8\\x8F)?\\xE2\\x83\\xA3|\\xC2[\\xA9\\xAE]|\\xE2..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?(?>\\xEF\\xB8\\x8F)?|\\xE3(?>\\x80[\\xB0\\xBD]|\\x8A[\\x97\\x99])(?>\\xEF\\xB8\\x8F)?|\\xF0\\x9F(?>[\\x80-\\x86].(?>\\xEF\\xB8\\x8F)?|\\x87.\\xF0\\x9F\\x87.|..(\\xF0\\x9F\\x8F[\\xBB-\\xBF])?|(((?<zwj>\\xE2\\x80\\x8D)\\xE2\\x9D\\xA4\\xEF\\xB8\\x8F\k<zwj>\\xF0\\x9F..(\k<zwj>\\xF0\\x9F\\x91.)?|(\\xE2\\x80\\x8D\\xF0\\x9F\\x91.){2,3}))?))/', '', $text);
    }


}
