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
 * @copyright	Copyright (C) 2011 by SNETSER JSC (http://www.snetser.com). All rights reserved.
 * @license	http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3
 * @author      Le Tuan Sang - batsatla@gmail.com
 */

/**
 * OSECORE Image Gradient
 *
 * @package osecore.core.image.gradient
 */
/* Usage :
 *
 * require_once('/path/to/gd-gradient-fill.php');
 * $image = new gd_gradient_fill($width,$height,$direction,$startcolor,$endcolor,$step);
 *
 * Parameters :
 *        - width and height : integers, dimesions of your image.
 *        - direction : string, shape of the gradient.
 *          Can be : vertical, horizontal, rectangle (or square), ellipse, ellipse2, circle, circle2, diamond.
 *        - startcolor : string, start color in 3 or 6 digits hexadecimal.
 *        - endcolor : string, end color in 3 or 6 digits hexadecimal.
 *        - step : integer, optional, default to 0. Step that breaks the smooth blending effect.
 * Returns a resource identifier.
 *
 * Examples :
 *
 * 1.
 * require_once('/home/ozh/www/includes/gd-gradient-fill.php');
 * $image = new gd_gradient_fill(200,200,'horizontal','#fff','#f00');
 *
 * 2.
 * require_once('c:/iis/inet/include/gd-gradient-fill.php');
 * $myimg = new gd_gradient_fill(80,20,'diamond','#ff0010','#303060');
 *
 */
class OSC_Image_Gradient extends OSC_Object {

    protected $_image;
    protected $_width;
    protected $_height;
    protected $_direction;
    protected $_startcolor;
    protected $_endcolor;
    protected $_step;

    // Constructor. Creates, fills and returns an image
    function fill(&$img, $d, $s, $e, $step = 0) {

        $this->_image = $img;
        $this->_width = imagesx($img);
        $this->_height = imagesy($img);
        $this->_direction = $d;
        $this->_startcolor = $s;
        $this->_endcolor = $e;
        $this->_step = intval(abs($step));

        /* // Attempt to create a blank image in true colors, or a new palette based image if this fails
          if (function_exists('imagecreatetruecolor')) {
          $this->image = imagecreatetruecolor($this->width,$this->height);
          } elseif (function_exists('imagecreate')) {
          $this->image = imagecreate($this->width,$this->height);
          } else {
          die('Unable to create an image');
          } */

        // Fill it
        $this->_fill($this->_image, $this->_direction, $this->_startcolor, $this->_endcolor);
    }

    // The main function that draws the gradient
    function _fill($im, $direction, $start, $end) {

        switch ($direction) {
            case 'horizontal':
                $line_numbers = imagesx($im);
                $line_width = imagesy($im);
                list($r1, $g1, $b1) = $this->hex2rgb($start);
                list($r2, $g2, $b2) = $this->hex2rgb($end);
                break;
            case 'vertical':
                $line_numbers = imagesy($im);
                $line_width = imagesx($im);
                list($r1, $g1, $b1) = $this->hex2rgb($start);
                list($r2, $g2, $b2) = $this->hex2rgb($end);
                break;
            case 'ellipse':
                $width = imagesx($im);
                $height = imagesy($im);
                $rh = $height > $width ? 1 : $width / $height;
                $rw = $width > $height ? 1 : $height / $width;
                $line_numbers = min($width, $height);
                $center_x = $width / 2;
                $center_y = $height / 2;
                list($r1, $g1, $b1) = $this->hex2rgb($end);
                list($r2, $g2, $b2) = $this->hex2rgb($start);
                imagefill($im, 0, 0, imagecolorallocate($im, $r1, $g1, $b1));
                break;
            case 'ellipse2':
                $width = imagesx($im);
                $height = imagesy($im);
                $rh = $height > $width ? 1 : $width / $height;
                $rw = $width > $height ? 1 : $height / $width;
                $line_numbers = sqrt(pow($width, 2) + pow($height, 2));
                $center_x = $width / 2;
                $center_y = $height / 2;
                list($r1, $g1, $b1) = $this->hex2rgb($end);
                list($r2, $g2, $b2) = $this->hex2rgb($start);
                break;
            case 'circle':
                $width = imagesx($im);
                $height = imagesy($im);
                $line_numbers = sqrt(pow($width, 2) + pow($height, 2));
                $center_x = $width / 2;
                $center_y = $height / 2;
                $rh = $rw = 1;
                list($r1, $g1, $b1) = $this->hex2rgb($end);
                list($r2, $g2, $b2) = $this->hex2rgb($start);
                break;
            case 'circle2':
                $width = imagesx($im);
                $height = imagesy($im);
                $line_numbers = min($width, $height);
                $center_x = $width / 2;
                $center_y = $height / 2;
                $rh = $rw = 1;
                list($r1, $g1, $b1) = $this->hex2rgb($end);
                list($r2, $g2, $b2) = $this->hex2rgb($start);
                imagefill($im, 0, 0, imagecolorallocate($im, $r1, $g1, $b1));
                break;
            case 'square':
            case 'rectangle':
                $width = imagesx($im);
                $height = imagesy($im);
                $line_numbers = max($width, $height) / 2;
                list($r1, $g1, $b1) = $this->hex2rgb($end);
                list($r2, $g2, $b2) = $this->hex2rgb($start);
                break;
            case 'diamond':
                list($r1, $g1, $b1) = $this->hex2rgb($end);
                list($r2, $g2, $b2) = $this->hex2rgb($start);
                $width = imagesx($im);
                $height = imagesy($im);
                $rh = $height > $width ? 1 : $width / $height;
                $rw = $width > $height ? 1 : $height / $width;
                $line_numbers = min($width, $height);
                break;
            default:
        }

        for ($i = 0; $i < $line_numbers; $i = $i + 1 + $this->_step) {
            // old values :
            $old_r = $r;
            $old_g = $g;
            $old_b = $b;
            // new values :
            $r = ( $r2 - $r1 != 0 ) ? intval($r1 + ( $r2 - $r1 ) * ( $i / $line_numbers )) : $r1;
            $g = ( $g2 - $g1 != 0 ) ? intval($g1 + ( $g2 - $g1 ) * ( $i / $line_numbers )) : $g1;
            $b = ( $b2 - $b1 != 0 ) ? intval($b1 + ( $b2 - $b1 ) * ( $i / $line_numbers )) : $b1;
            // if new values are really new ones, allocate a new color, otherwise reuse previous color.
            // There's a "feature" in imagecolorallocate that makes this function
            // always returns '-1' after 255 colors have been allocated in an image that was created with
            // imagecreate (everything works fine with imagecreatetruecolor)
            if ("$old_r,$old_g,$old_b" != "$r,$g,$b")
                $fill = imagecolorallocate($im, $r, $g, $b);
            switch ($direction) {
                case 'vertical':
                    imagefilledrectangle($im, 0, $i, $line_width, $i + $this->_step, $fill);
                    break;
                case 'horizontal':
                    imagefilledrectangle($im, $i, 0, $i + $this->_step, $line_width, $fill);
                    break;
                case 'ellipse':
                case 'ellipse2':
                case 'circle':
                case 'circle2':
                    imagefilledellipse($im, $center_x, $center_y, ($line_numbers - $i) * $rh, ($line_numbers - $i) * $rw, $fill);
                    break;
                case 'square':
                case 'rectangle':
                    imagefilledrectangle($im, $i * $width / $height, $i * $height / $width, $width - ($i * $width / $height), $height - ($i * $height / $width), $fill);
                    break;
                case 'diamond':
                    imagefilledpolygon($im, array(
                        $width / 2, $i * $rw - 0.5 * $height,
                        $i * $rh - 0.5 * $width, $height / 2,
                        $width / 2, 1.5 * $height - $i * $rw,
                        1.5 * $width - $i * $rh, $height / 2), 4, $fill);
                    break;
                default:
            }
        }
    }

    // #ff00ff -> array(255,0,255) or #f0f -> array(255,0,255)
    function hex2rgb($color) {
        $color = str_replace('#', '', $color);
        $s = strlen($color) / 3;
        $rgb[] = hexdec(str_repeat(substr($color, 0, $s), 2 / $s));
        $rgb[] = hexdec(str_repeat(substr($color, $s, $s), 2 / $s));
        $rgb[] = hexdec(str_repeat(substr($color, 2 * $s, $s), 2 / $s));
        return $rgb;
    }

}
